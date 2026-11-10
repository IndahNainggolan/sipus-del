<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $links = [
            'self' => [
                'href' => route('api.v1.bukus.show', $this->id),
                'method' => 'GET',
            ],
        ];

        if ($this->stok > 0) {
            $links['pinjam_buku'] = [
                'href' => route('api.v1.peminjamans.store'),
                'method' => 'POST',
            ];
        }

        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'kategori_id' => $this->kategori_id,
            'kategori' => $this->whenLoaded('kategori', fn () => [
                'id' => $this->kategori->id,
                'kode_kategori' => $this->kategori->kode_kategori,
                'nama_kategori' => $this->kategori->nama_kategori,
            ]),
            'stok' => $this->stok,
            'sinopsis' => $this->sinopsis,
            'peminjamans_count' => $this->whenCounted('peminjamans'),
            'riwayat_peminjaman' => $this->whenLoaded(
                'peminjamans',
                fn () => PeminjamanResource::collection($this->peminjamans),
            ),
            '_links' => $links,
        ];
    }
}
