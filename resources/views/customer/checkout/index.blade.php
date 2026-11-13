@extends('layouts.app')
@section('title', 'Checkout & Voucher')

@section('content')
<div class="card">
    <form action="{{ $editOrder ? route('checkout.update', $editOrder->id) : route('checkout.store') }}" method="POST">
        @csrf
        @if ($editOrder) @method('PUT') @endif

        @if ($editOrder)
            <h2>Edit Pesanan #{{ $editOrder->id }}</h2>
        @endif

        <h3>Pilih Produk</h3>
        <div class="grid">
            @forelse ($products as $p)
                <label class="choice">
                    <input type="checkbox" class="prod" name="product[]" value="{{ $p->id }}" data-price="{{ $p->harga }}"
                        {{ in_array($p->id, old('product', $selectedProducts)) ? 'checked' : '' }}>
                    <span>{{ $p->nama_produk }}<br><b>Rp {{ number_format($p->harga, 0, ',', '.') }}</b><br><span class="muted">Stok {{ $p->stok }}</span></span>
                </label>
            @empty
                <p class="muted">Belum ada produk yang tersedia.</p>
            @endforelse
        </div>

        <h3>Alamat Pengiriman</h3>
        @if ($addresses->isEmpty())
            <p class="alert alert-error">Kamu belum punya alamat. <a href="{{ route('alamat.create') }}">Tambah alamat</a> dulu sebelum checkout.</p>
        @else
            <div class="opts">
                @foreach ($addresses as $a)
                    <label class="choice">
                        <input type="radio" name="address_id" value="{{ $a->id }}" {{ (int) old('address_id', $selectedAddressId) === $a->id ? 'checked' : '' }}>
                        <span>{{ $a->formatted() }} @if ($a->is_default)<span class="badge badge-ok">Utama</span>@endif</span>
                    </label>
                @endforeach
            </div>
        @endif

        <h3>Ekspedisi</h3>
        <div class="opts">
            @forelse ($ekspedisis as $e)
                <label class="choice">
                    <input type="radio" name="ekspedisi_id" class="eks" value="{{ $e->id }}" data-tarif="{{ $e->tarif }}"
                        {{ (int) old('ekspedisi_id', $selectedEkspedisiId) === $e->id ? 'checked' : '' }}>
                    <span>{{ $e->label() }} — Rp {{ number_format($e->tarif, 0, ',', '.') }} @if ($e->estimasi)<span class="muted">({{ $e->estimasi }})</span>@endif</span>
                </label>
            @empty
                <p class="muted">Belum ada ekspedisi aktif.</p>
            @endforelse
        </div>

        <h3>Voucher</h3>
        <div class="opts">
            <label class="choice">
                <input type="radio" name="voucher_id" class="vch" value="0" data-tipe="persen" data-nilai="0" data-min="0"
                    {{ (int) old('voucher_id', $selectedVoucherId) === 0 ? 'checked' : '' }}>
                <span>Tanpa voucher</span>
            </label>
            @foreach ($vouchers as $v)
                <label class="choice">
                    <input type="radio" name="voucher_id" class="vch" value="{{ $v->id }}"
                        data-tipe="{{ $v->tipe_diskon }}" data-nilai="{{ $v->nilai }}" data-min="{{ $v->min_belanja }}"
                        {{ (int) old('voucher_id', $selectedVoucherId) === $v->id ? 'checked' : '' }}>
                    <span>{{ $v->deskripsi() }}</span>
                </label>
            @endforeach
        </div>
        <p style="color:#dc2626;font-size:13px;font-weight:bold" id="info"></p>

        <div style="margin-top:16px">
            <div class="summary-row"><span>Subtotal</span><span id="subtotal">Rp 0</span></div>
            <div class="summary-row" style="color:#dc2626"><span>Diskon</span><span id="diskon">-Rp 0</span></div>
            <div class="summary-row"><span>Ongkir</span><span id="ongkir">Rp 0</span></div>
            <div class="total-row"><span>Total</span><span id="total">Rp 0</span></div>
        </div>

        <p style="margin-top:16px">
            <button type="submit" class="btn">{{ $editOrder ? 'Simpan Perubahan' : 'Simpan Pesanan' }}</button>
            @if ($editOrder)
                <a href="{{ route('checkout.index') }}" class="btn btn-gray">Batal</a>
            @endif
        </p>
    </form>
</div>

<div class="card">
    <h3>Riwayat Pesanan</h3>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>#</th><th>Item</th><th>Pengiriman</th><th>Subtotal</th><th>Diskon</th><th>Ongkir</th><th>Total</th><th>Waktu</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($orders as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>
                        @foreach ($r->items as $it)
                            {{ $it->produk->nama_produk ?? 'Produk' }} (Rp {{ number_format($it->harga, 0, ',', '.') }})<br>
                        @endforeach
                    </td>
                    <td class="muted">{{ $r->ekspedisi_label }}<br>{{ $r->alamat_pengiriman }}</td>
                    <td>Rp {{ number_format($r->subtotal, 0, ',', '.') }}</td>
                    <td>
                        @if ($r->diskon > 0)
                            -Rp {{ number_format($r->diskon, 0, ',', '.') }}<br>
                            <span class="muted">({{ $r->voucher->kode ?? 'voucher' }})</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>Rp {{ number_format($r->ongkir, 0, ',', '.') }}</td>
                    <td><b>Rp {{ number_format($r->total, 0, ',', '.') }}</b></td>
                    <td class="muted">{{ $r->created_at }}</td>
                    <td>
                        <a href="{{ route('checkout.index', ['edit' => $r->id]) }}" class="btn btn-edit">Edit</a>
                        <form action="{{ route('checkout.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pesanan #{{ $r->id }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" align="center" class="muted">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
    const prods = document.querySelectorAll('.prod');
    const vchs = document.querySelectorAll('.vch');
    const ekss = document.querySelectorAll('.eks');
    const rp = n => 'Rp ' + n.toLocaleString('id-ID');

    function hitung() {
        let subtotal = 0;
        prods.forEach(p => { if (p.checked) subtotal += Number(p.dataset.price); });

        let diskon = 0, info = '';
        vchs.forEach(v => {
            if (!v.checked || Number(v.value) === 0) return;
            const min = Number(v.dataset.min);
            if (subtotal >= min) {
                diskon = v.dataset.tipe === 'persen'
                    ? Math.floor(subtotal * Number(v.dataset.nilai) / 100)
                    : Math.min(Number(v.dataset.nilai), subtotal);
            } else {
                info = 'Voucher belum berlaku: kurang belanja ' + rp(min - subtotal) + ' lagi.';
            }
        });

        let ongkir = 0;
        ekss.forEach(e => { if (e.checked) ongkir = Number(e.dataset.tarif); });

        document.getElementById('subtotal').innerText = rp(subtotal);
        document.getElementById('diskon').innerText = '-' + rp(diskon);
        document.getElementById('ongkir').innerText = rp(ongkir);
        document.getElementById('total').innerText = rp(subtotal - diskon + ongkir);
        document.getElementById('info').innerText = info;
    }

    document.addEventListener('change', hitung);
    hitung();
</script>
@endsection
