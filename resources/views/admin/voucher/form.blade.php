@extends('layouts.app')
@section('title', $voucher->exists ? 'Edit Voucher' : 'Tambah Voucher')

@section('content')
<div class="card">
    <h2>{{ $voucher->exists ? 'Edit Voucher' : 'Tambah Voucher' }}</h2>
    <form action="{{ $voucher->exists ? route('admin.voucher.update', $voucher->id) : route('admin.voucher.store') }}" method="POST">
        @csrf
        @if ($voucher->exists) @method('PUT') @endif

        <div class="form-row"><label>Kode Voucher</label><input type="text" name="kode" value="{{ old('kode', $voucher->kode) }}" required></div>
        <div class="form-row"><label>Nama</label><input type="text" name="nama" value="{{ old('nama', $voucher->nama) }}" required></div>
        <div class="form-row">
            <label>Jenis Diskon</label>
            <select name="tipe_diskon">
                @foreach (['persen' => 'Persen (%)', 'nominal' => 'Nominal (Rp)'] as $val => $label)
                    <option value="{{ $val }}" {{ old('tipe_diskon', $voucher->tipe_diskon) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-row"><label>Nilai Diskon</label><input type="number" min="1" name="nilai" value="{{ old('nilai', $voucher->nilai) }}" required><div class="muted">Persen: 1–100. Nominal: jumlah rupiah.</div></div>
        <div class="form-row"><label>Minimal Belanja (Rp)</label><input type="number" min="0" name="min_belanja" value="{{ old('min_belanja', $voucher->min_belanja ?? 0) }}" required></div>
        <div class="form-row">
            <label class="check"><input type="checkbox" name="aktif" value="1" {{ old('aktif', $voucher->aktif) ? 'checked' : '' }}> Voucher aktif</label>
        </div>
        <button type="submit" class="btn">Simpan</button>
        <a href="{{ route('admin.voucher.index') }}" class="btn btn-gray">Kembali</a>
    </form>
</div>
@endsection
