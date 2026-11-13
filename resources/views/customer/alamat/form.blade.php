@extends('layouts.app')
@section('title', $address->exists ? 'Edit Alamat' : 'Tambah Alamat')

@section('content')
<div class="card">
    <h2>{{ $address->exists ? 'Edit Alamat' : 'Tambah Alamat' }}</h2>
    <form action="{{ $address->exists ? route('alamat.update', $address->id) : route('alamat.store') }}" method="POST">
        @csrf
        @if ($address->exists) @method('PUT') @endif

        <div class="form-row"><label>Nama Penerima</label><input type="text" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" required></div>
        <div class="form-row"><label>Telepon</label><input type="text" name="phone" value="{{ old('phone', $address->phone) }}" required></div>
        <div class="form-row"><label>Provinsi</label><input type="text" name="province" value="{{ old('province', $address->province) }}" required></div>
        <div class="form-row"><label>Kota/Kabupaten</label><input type="text" name="city" value="{{ old('city', $address->city) }}" required></div>
        <div class="form-row"><label>Kecamatan</label><input type="text" name="district" value="{{ old('district', $address->district) }}"></div>
        <div class="form-row"><label>Kode Pos</label><input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}"></div>
        <div class="form-row"><label>Alamat Lengkap</label><textarea name="full_address" rows="3" required>{{ old('full_address', $address->full_address) }}</textarea></div>
        <div class="form-row">
            <label class="check"><input type="checkbox" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}> Jadikan alamat utama</label>
        </div>
        <button type="submit" class="btn">Simpan</button>
        <a href="{{ route('alamat.index') }}" class="btn btn-gray">Kembali</a>
    </form>
</div>
@endsection
