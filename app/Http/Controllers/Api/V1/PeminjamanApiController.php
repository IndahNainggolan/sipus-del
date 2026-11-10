<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['sometimes', Rule::in(['dipinjam', 'dikembalikan'])],
            'nim' => ['sometimes', 'string', 'max:20'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $peminjamans = Peminjaman::query()
            ->with('buku:id,isbn,judul')
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->filled('nim'), fn (Builder $query) => $query->where('nim_peminjam', strtoupper(trim((string) $request->query('nim')))))
            ->latest('id')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();

        return PeminjamanResource::collection($peminjamans)
            ->additional(['message' => 'Daftar transaksi peminjaman berhasil ditemukan.']);
    }

    public function show(Peminjaman $peminjaman)
    {
        $peminjaman->load('buku:id,isbn,judul');

        return (new PeminjamanResource($peminjaman))
            ->additional(['message' => 'Detail transaksi peminjaman berhasil ditemukan.']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'integer', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'min:4', 'max:20'],
            'nama_peminjam' => ['required', 'string', 'min:3', 'max:150'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        try {
            $peminjaman = DB::transaction(function () use ($validated) {
                $buku = Buku::query()
                    ->whereKey($validated['buku_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($buku->stok < 1) {
                    throw new HttpResponseException($this->problem(
                        422,
                        'Unprocessable Content',
                        'Stok buku sedang habis.',
                    ));
                }

                $nim = strtoupper(trim($validated['nim_peminjam']));
                $hasActiveLoan = Peminjaman::query()
                    ->where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->exists();

                if ($hasActiveLoan) {
                    throw new HttpResponseException($this->problem(
                        409,
                        'Conflict',
                        'Mahasiswa ini masih memiliki peminjaman aktif untuk buku yang sama.',
                    ));
                }

                $today = now()->startOfDay();
                $buku->decrement('stok');

                return Peminjaman::create([
                    'kode_pinjam' => 'PJ-'.now()->format('Ymd').'-'.strtoupper(Str::random(10)),
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nim,
                    'nama_peminjam' => trim($validated['nama_peminjam']),
                    'tanggal_pinjam' => $today->toDateString(),
                    'tanggal_jatuh_tempo' => $today->copy()->addDays((int) $validated['durasi_hari'])->toDateString(),
                    'status' => 'dipinjam',
                ]);
            }, 3);
        } catch (HttpResponseException $exception) {
            return $exception->getResponse();
        }

        $peminjaman->load('buku:id,isbn,judul');

        return (new PeminjamanResource($peminjaman))
            ->additional(['message' => 'Peminjaman buku berhasil dicatat.'])
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.peminjamans.show', $peminjaman->id));
    }

    public function kembalikan(Peminjaman $peminjaman)
    {
        try {
            $returnedLoan = DB::transaction(function () use ($peminjaman) {
                // Semua mutasi sirkulasi mengunci buku lebih dulu untuk menjaga urutan lock.
                $buku = Buku::query()
                    ->whereKey($peminjaman->buku_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedLoan = Peminjaman::query()
                    ->whereKey($peminjaman->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedLoan->status !== 'dipinjam') {
                    throw new HttpResponseException($this->problem(
                        409,
                        'Conflict',
                        'Transaksi ini sudah berstatus dikembalikan.',
                    ));
                }

                $lockedLoan->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);
                $buku->increment('stok');

                return $lockedLoan->fresh()->load('buku:id,isbn,judul');
            }, 3);
        } catch (HttpResponseException $exception) {
            return $exception->getResponse();
        }

        return (new PeminjamanResource($returnedLoan))
            ->additional(['message' => 'Buku berhasil dikembalikan dan stok diperbarui.']);
    }
}
