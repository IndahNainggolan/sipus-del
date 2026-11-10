<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $links = [
            'self' => [
                'href' => route('api.v1.peminjamans.show', $this->id),
                'method' => 'GET',
            ],
        ];

        if ($this->status === 'dipinjam') {
            $links['kembalikan_buku'] = [
                'href' => route('api.v1.peminjamans.kembalikan', $this->id),
                'method' => 'PATCH',
            ];
        }

        return [
            'id' => $this->id,
            'kode_pinjam' => $this->kode_pinjam,
            'buku_id' => $this->buku_id,
            'buku' => $this->whenLoaded('buku', fn () => [
                'id' => $this->buku->id,
                'isbn' => $this->buku->isbn,
                'judul' => $this->buku->judul,
            ]),
            'nim_peminjam' => $this->nim_peminjam,
            'nama_peminjam' => $this->nama_peminjam,
            'tanggal_pinjam' => $this->tanggal_pinjam?->toDateString(),
            'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo?->toDateString(),
            'tanggal_kembali' => $this->tanggal_kembali?->toDateString(),
            'status' => $this->status,
            '_links' => $links,
        ];
    }
}
