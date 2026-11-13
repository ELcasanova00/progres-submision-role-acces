@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="card">
    <h2>Profil Saya</h2>
    <form action="{{ route('profil.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-row"><label>Nama Lengkap</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
        <div class="form-row"><label>Email</label><input type="email" value="{{ $user->email }}" disabled></div>
        <div class="form-row"><label>Nomor Telepon</label><input type="text" name="phone" value="{{ old('phone', $profile->phone) }}"></div>
        <div class="form-row">
            <label>Jenis Kelamin</label>
            <select name="gender">
                <option value="">-</option>
                @foreach (['male' => 'Laki-laki', 'female' => 'Perempuan', 'other' => 'Lainnya'] as $val => $label)
                    <option value="{{ $val }}" {{ old('gender', $profile->gender) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-row"><label>Tanggal Lahir</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth?->format('Y-m-d')) }}"></div>
        <button type="submit" class="btn">Simpan Profil</button>
    </form>
</div>
@endsection
