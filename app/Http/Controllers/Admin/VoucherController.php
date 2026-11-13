<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index()
    {
        return view('admin.voucher.index', ['vouchers' => Voucher::orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.voucher.form', ['voucher' => new Voucher(['aktif' => true, 'tipe_diskon' => 'persen', 'min_belanja' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Voucher::create($this->validated($request));

        return redirect()->route('admin.voucher.index')->with('success', 'Voucher berhasil ditambahkan!');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.voucher.form', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($this->validated($request, $voucher));

        return redirect()->route('admin.voucher.index')->with('success', 'Voucher berhasil diperbarui!');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return redirect()->route('admin.voucher.index')->with('success', 'Voucher berhasil dihapus!');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('vouchers', 'kode')->ignore($voucher?->id)],
            'nama' => ['required', 'string', 'max:100'],
            'tipe_diskon' => ['required', 'in:persen,nominal'],
            'nilai' => ['required', 'integer', 'min:1', $request->input('tipe_diskon') === 'persen' ? 'max:100' : 'max:4294967295'],
            'min_belanja' => ['required', 'integer', 'min:0'],
        ]);

        $data['kode'] = strtoupper($data['kode']);
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
