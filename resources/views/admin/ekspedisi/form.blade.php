@extends('layouts.app')
@section('title', $ekspedisi->exists ? 'Edit Ekspedisi' : 'Tambah Ekspedisi')

@section('content')
<div class="card">
    <h2>{{ $ekspedisi->exists ? 'Edit Ekspedisi' : 'Tambah Ekspedisi' }}</h2>
    <form action="{{ $ekspedisi->exists ? route('admin.ekspedisi.update', $ekspedisi->id) : route('admin.ekspedisi.store') }}" method="POST">
        @csrf
        @if ($ekspedisi->exists) @method('PUT') @endif

        <div class="form-row"><label>Nama Kurir</label><input type="text" name="nama_kurir" value="{{ old('nama_kurir', $ekspedisi->nama_kurir) }}" required></div>
        <div class="form-row"><label>Kode</label><input type="text" name="kode" value="{{ old('kode', $ekspedisi->kode) }}" required></div>
        <div class="form-row">
            <label>Jenis Layanan</label>
            <select name="jenis_layanan">
                @foreach (['Reguler', 'Express'] as $j)
                    <option value="{{ $j }}" {{ old('jenis_layanan', $ekspedisi->jenis_layanan) === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-row"><label>Zona</label><input type="text" name="zona" value="{{ old('zona', $ekspedisi->zona) }}" placeholder="mis. Jawa, Luar Jawa" required></div>
        <div class="form-row"><label>Tarif (Rp)</label><input type="number" min="0" name="tarif" value="{{ old('tarif', $ekspedisi->tarif ?? 0) }}" required></div>
        <div class="form-row"><label>Estimasi</label><input type="text" name="estimasi" value="{{ old('estimasi', $ekspedisi->estimasi) }}" placeholder="mis. 2-3 hari"></div>
        <div class="form-row">
            <label>Status</label>
            <select name="status">
                @foreach (['Aktif', 'Nonaktif'] as $s)
                    <option value="{{ $s }}" {{ old('status', $ekspedisi->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn">Simpan</button>
        <a href="{{ route('admin.ekspedisi.index') }}" class="btn btn-gray">Kembali</a>
    </form>
</div>
@endsection
