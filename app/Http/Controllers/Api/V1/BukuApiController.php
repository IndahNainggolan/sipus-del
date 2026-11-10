<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class BukuApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('kategori_id'), fn ($query) => $query->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->query('tersedia') === '1', fn ($query) => $query->where('stok', '>', 0));

        $sort = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        if (str_starts_with($sort, '-')) {
            $sort = substr($sort, 1);
            $direction = 'desc';
        }

        $allowedSorts = ['judul', 'penulis', 'penerbit', 'tahun_terbit', 'stok', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $bukus = $query->orderBy($sort, $direction)
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();

        return BukuResource::collection($bukus)
            ->additional(['message' => 'Katalog buku berhasil ditemukan.']);
    }

    public function show(Buku $buku)
    {
        $buku->load([
            'kategori',
            'peminjamans' => fn ($query) => $query->latest('tanggal_pinjam')->latest('id'),
        ])->loadCount('peminjamans');

        return (new BukuResource($buku))
            ->additional(['message' => 'Detail buku berhasil ditemukan.']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->bookRules());
        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return (new BukuResource($buku))
            ->additional(['message' => 'Buku berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.bukus.show', $buku->id));
    }

    public function replace(Request $request, Buku $buku)
    {
        $buku->update($request->validate($this->bookRules($buku)));
        $buku->load('kategori')->loadCount('peminjamans');

        return (new BukuResource($buku))
            ->additional(['message' => 'Representasi buku berhasil diganti sepenuhnya (PUT).']);
    }

    public function update(Request $request, Buku $buku)
    {
        $payloadKeys = array_keys($request->all());
        $allowedKeys = array_keys($this->bookRules($buku, partial: true));
        if ($payloadKeys === [] || array_diff($payloadKeys, $allowedKeys) !== []) {
            return $this->problem(422, 'Unprocessable Content', 'Kirim setidaknya satu atribut buku untuk diperbarui.');
        }

        $buku->update($request->validate($this->bookRules($buku, partial: true)));
        $buku->load('kategori')->loadCount('peminjamans');

        return (new BukuResource($buku))
            ->additional(['message' => 'Atribut buku berhasil diperbarui sebagian (PATCH).']);
    }

    public function destroy(Buku $buku)
    {
        if ($buku->peminjamans()->where('status', 'dipinjam')->exists()) {
            return $this->problem(
                409,
                'Conflict',
                'Buku tidak dapat dihapus selama masih memiliki peminjaman aktif.',
            );
        }

        $buku->delete();

        return response()->noContent();
    }

    public function externalReference(Buku $buku)
    {
        try {
            $response = Http::acceptJson()
                ->timeout(8)
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => 'ISBN:'.$buku->isbn,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);
        } catch (ConnectionException) {
            return $this->success([
                'buku_id' => $buku->id,
                'isbn' => $buku->isbn,
                'external_reference' => null,
                'available' => false,
            ], 'Layanan referensi eksternal sedang tidak dapat dihubungi.');
        }

        if (! $response->successful()) {
            return $this->success([
                'buku_id' => $buku->id,
                'isbn' => $buku->isbn,
                'external_reference' => null,
                'available' => false,
            ], 'Layanan referensi eksternal tidak mengembalikan data.');
        }

        $payload = $response->json();
        $reference = is_array($payload) ? ($payload['ISBN:'.$buku->isbn] ?? null) : null;

        return $this->success([
            'buku_id' => $buku->id,
            'isbn' => $buku->isbn,
            'external_reference' => $reference,
            'available' => $reference !== null,
        ], $reference === null
            ? 'Buku tidak ditemukan pada layanan referensi eksternal.'
            : 'Metadata referensi eksternal berhasil ditemukan.');
    }

    /** @return array<string, mixed> */
    private function bookRules(?Buku $buku = null, bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'isbn' => [
                ...$required,
                'string',
                'max:255',
                $buku === null ? Rule::unique('bukus', 'isbn') : Rule::unique('bukus', 'isbn')->ignore($buku->id),
            ],
            'judul' => [...$required, 'string', 'min:5', 'max:255'],
            'penulis' => [...$required, 'string', 'max:255'],
            'penerbit' => [...$required, 'string', 'max:255'],
            'tahun_terbit' => [...$required, 'integer', 'min:1000', 'max:'.now()->year],
            'kategori_id' => [...$required, 'integer', 'exists:kategoris,id'],
            'stok' => [...$required, 'integer', 'min:0'],
            'sinopsis' => $partial
                ? ['sometimes', 'nullable', 'string']
                : ['present', 'nullable', 'string'],
        ];
    }
}
