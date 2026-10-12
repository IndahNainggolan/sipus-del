<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    protected $model = Buku::class;

    public function definition(): array
    {
        $judul = fake()->unique()->sentence(4);

        return [
            'isbn' => fake()->unique()->numerify('978##########'),
            'judul' => ucfirst($judul),
            'penulis' => fake()->name(),
            'penerbit' => fake()->company(),
            'tahun_terbit' => fake()->numberBetween(2018, 2026),
            'kategori_id' => Kategori::query()->inRandomOrder()->value('id')
                ?? Kategori::factory()->create()->id,
            'stok' => fake()->numberBetween(0, 100),
            'sinopsis' => fake()->paragraph(),
        ];
    }
}
