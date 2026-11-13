@extends('layouts.app')
@section('title', 'Form Tambah Produk')

@section('content')
@php $p = $produk ?? null; @endphp
<div class="card">
    <h2>Form Tambah Produk</h2>
    <form action="{{ route('produk.store') }}" method="POST">
        @csrf
        
        <div class="form-row"><label>Nama Produk</label><input type="text" name="nama_produk" value="{{ old('nama_produk', $p?->nama_produk) }}" required></div>
        <div class="form-row"><label>Kategori</label><input type="text" name="kategori" value="{{ old('kategori', $p?->kategori) }}"></div>
        <div class="form-row"><label>Merek</label><input type="text" name="merek" value="{{ old('merek', $p?->merek) }}"></div>
        <div class="form-row"><label>Harga (Rp)</label><input type="number" min="0" name="harga" value="{{ old('harga', $p?->harga) }}" required></div>
        <div class="form-row"><label>Stok</label><input type="number" min="0" name="stok" value="{{ old('stok', $p?->stok) }}" required></div>
        <div class="form-row"><label>Berat (gram)</label><input type="number" min="0" name="berat" value="{{ old('berat', $p?->berat) }}"></div>
        <div class="form-row">
            <label>Status</label>
            <select name="status">
                @foreach (['Aktif', 'Nonaktif'] as $s)
                    <option value="{{ $s }}" {{ old('status', $p?->status ?? 'Aktif') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-row"><label>Deskripsi</label><textarea name="deskripsi" rows="3">{{ old('deskripsi', $p?->deskripsi) }}</textarea></div>
        <button type="submit" class="btn">{{ $p ? 'Update' : 'Simpan' }}</button>
        <a href="{{ route('produk.index') }}" class="btn btn-gray">Kembali</a>
    </form>
</div>
@endsection
