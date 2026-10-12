<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->paginate(10);

        return view('kategoris.index', compact('kategoris'));
    }

    public function create(): View
    {
        return view('kategoris.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:255|unique:kategoris,kode_kategori',
            'nama_kategori' => 'required|string|max:255',
        ]);

        Kategori::create($validated);

        return redirect()
            ->route('kategoris.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Kategori $kategori): View
    {
        $kategori->load('bukus');

        return view('kategoris.show', compact('kategori'));
    }

    public function edit(Kategori $kategori): View
    {
        return view('kategoris.edit', compact('kategori'));
    }

    public function update(
        Request $request,
        Kategori $kategori
    ): RedirectResponse {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:255|unique:kategoris,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori->update($validated);

        return redirect()
            ->route('kategoris.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $kategori->delete();

        return redirect()
            ->route('kategoris.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
