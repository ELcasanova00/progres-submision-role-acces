<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Katalog produk — dapat dilihat Admin, Customer, dan Guest.
     * Guest & Customer hanya melihat produk berstatus Aktif; Admin melihat semuanya.
     */
    public function index(Request $request)
    {
        $query = Produk::query()->orderBy('id');

        if (! $request->user()?->isAdmin()) {
            $query->aktif();
        }

        return view('produk.index', ['produks' => $query->get()]);
    }

    public function create()
    {
        return view('produk.tambah');
    }

    public function store(Request $request): RedirectResponse
    {
        Produk::create($this->validated($request));

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Produk $produk)
    {
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $produk->update($this->validated($request));

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        try {
            $produk->delete();
        } catch (QueryException $e) {
            return redirect()->route('produk.index')->with(
                'error',
                'Produk tidak bisa dihapus karena sudah ada di pesanan. Ubah statusnya menjadi Nonaktif.'
            );
        }

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_produk' => ['required', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'merek' => ['nullable', 'string', 'max:50'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'berat' => ['nullable', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ]);
    }
}
