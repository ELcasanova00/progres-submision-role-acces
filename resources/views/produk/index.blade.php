@extends('layouts.app')
@section('title', 'Katalog Produk')

@section('content')
<div class="card">
    <h2>Daftar Produk Toko Online</h2>

    @auth
        @if (auth()->user()->isAdmin())
            <p><a href="{{ route('produk.create') }}" class="btn">+ Tambah Produk Baru</a></p>
        @endif
    @endauth

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Merek</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Berat</th>
                <th>Deskripsi</th>
                @if (auth()->user()?->isAdmin())
                    <th>Status</th>
                    <th width="130">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($produks as $key => $row)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $row->nama_produk }}</td>
                    <td>{{ $row->kategori }}</td>
                    <td>{{ $row->merek }}</td>
                    <td>Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                    <td>{{ $row->stok }}</td>
                    <td>{{ $row->berat }} gr</td>
                    <td>{{ $row->deskripsi }}</td>
                    @if (auth()->user()?->isAdmin())
                        <td>{{ $row->status }}</td>
                        <td>
                            <a href="{{ route('produk.edit', $row->id) }}" class="btn btn-edit">Edit</a>
                            <form action="{{ route('produk.destroy', $row->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-hapus">Hapus</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()?->isAdmin() ? 10 : 8 }}" align="center">Data belum ada</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @guest
        <p class="muted">Ingin membeli? <a href="{{ route('login') }}">Login</a> atau <a href="{{ route('register') }}">daftar</a> sebagai customer.</p>
    @endguest
</div>
@endsection
