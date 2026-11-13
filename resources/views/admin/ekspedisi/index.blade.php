@extends('layouts.app')
@section('title', 'Ekspedisi')

@section('content')
<div class="card">
    <h2>Master Ekspedisi</h2>
    <p><a href="{{ route('admin.ekspedisi.create') }}" class="btn">+ Tambah Ekspedisi</a></p>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>No</th><th>Kurir</th><th>Kode</th><th>Layanan</th><th>Zona</th><th>Tarif</th><th>Estimasi</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($ekspedisis as $i => $e)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $e->nama_kurir }}</td>
                    <td>{{ $e->kode }}</td>
                    <td>{{ $e->jenis_layanan }}</td>
                    <td>{{ $e->zona }}</td>
                    <td>Rp {{ number_format($e->tarif, 0, ',', '.') }}</td>
                    <td>{{ $e->estimasi }}</td>
                    <td><span class="badge {{ $e->status === 'Aktif' ? 'badge-ok' : 'badge-off' }}">{{ $e->status }}</span></td>
                    <td>
                        <a href="{{ route('admin.ekspedisi.edit', $e->id) }}" class="btn btn-edit">Edit</a>
                        <form action="{{ route('admin.ekspedisi.destroy', $e->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus ekspedisi ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" align="center">Data belum ada</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
