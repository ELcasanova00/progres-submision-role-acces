@extends('layouts.app')
@section('title', 'Alamat Pengiriman')

@section('content')
<div class="card">
    <h2>Alamat Pengiriman</h2>
    <p><a href="{{ route('alamat.create') }}" class="btn">+ Tambah Alamat</a></p>
    <div class="table-wrap">
    <table>
        <thead><tr><th>No</th><th>Penerima</th><th>Telepon</th><th>Alamat</th><th>Utama</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse ($addresses as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $a->recipient_name }}</td>
                    <td>{{ $a->phone }}</td>
                    <td>{{ $a->full_address }}, {{ $a->district ? $a->district.', ' : '' }}{{ $a->city }}, {{ $a->province }} {{ $a->postal_code }}</td>
                    <td>@if ($a->is_default)<span class="badge badge-ok">Utama</span>@endif</td>
                    <td>
                        <a href="{{ route('alamat.edit', $a->id) }}" class="btn btn-edit">Edit</a>
                        <form action="{{ route('alamat.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus alamat ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" align="center">Belum ada alamat.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
