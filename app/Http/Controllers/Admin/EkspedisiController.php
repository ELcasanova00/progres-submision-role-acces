<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekspedisi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EkspedisiController extends Controller
{
    public function index()
    {
        return view('admin.ekspedisi.index', ['ekspedisis' => Ekspedisi::orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.ekspedisi.form', ['ekspedisi' => new Ekspedisi(['status' => 'Aktif', 'jenis_layanan' => 'Reguler'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Ekspedisi::create($this->validated($request));

        return redirect()->route('admin.ekspedisi.index')->with('success', 'Ekspedisi berhasil ditambahkan!');
    }

    public function edit(Ekspedisi $ekspedisi)
    {
        return view('admin.ekspedisi.form', compact('ekspedisi'));
    }

    public function update(Request $request, Ekspedisi $ekspedisi): RedirectResponse
    {
        $ekspedisi->update($this->validated($request, $ekspedisi));

        return redirect()->route('admin.ekspedisi.index')->with('success', 'Ekspedisi berhasil diperbarui!');
    }

    public function destroy(Ekspedisi $ekspedisi): RedirectResponse
    {
        $ekspedisi->delete();

        return redirect()->route('admin.ekspedisi.index')->with('success', 'Ekspedisi berhasil dihapus!');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Ekspedisi $ekspedisi = null): array
    {
        return $request->validate([
            'nama_kurir' => ['required', 'string', 'max:100'],
            'kode' => ['required', 'string', 'max:20', Rule::unique('ekspedisis', 'kode')->ignore($ekspedisi?->id)],
            'jenis_layanan' => ['required', 'in:Reguler,Express'],
            'zona' => ['required', 'string', 'max:100'],
            'tarif' => ['required', 'integer', 'min:0'],
            'estimasi' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ]);
    }
}
