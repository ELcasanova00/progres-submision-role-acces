@extends('layouts.app')
@section('title', 'Voucher')

@section('content')
<div class="card">
    <h2>Master Voucher &amp; Promo</h2>
    <p><a href="{{ route('admin.voucher.create') }}" class="btn">+ Tambah Voucher</a></p>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>No</th><th>Kode</th><th>Nama</th><th>Jenis Diskon</th><th>Nilai</th><th>Min. Belanja</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($vouchers as $i => $v)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $v->kode }}</td>
                    <td>{{ $v->nama }}</td>
                    <td>{{ ucfirst($v->tipe_diskon) }}</td>
                    <td>{{ $v->tipe_diskon === 'persen' ? $v->nilai.'%' : 'Rp '.number_format($v->nilai, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($v->min_belanja, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $v->aktif ? 'badge-ok' : 'badge-off' }}">{{ $v->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>
                        <a href="{{ route('admin.voucher.edit', $v->id) }}" class="btn btn-edit">Edit</a>
                        <form action="{{ route('admin.voucher.destroy', $v->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus voucher ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" align="center">Data belum ada</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
