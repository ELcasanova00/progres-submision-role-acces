@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="card">
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, {{ auth()->user()->name }}.</p>
    <div class="stats">
        <div class="stat"><b>{{ $jumlahProduk }}</b>Produk</div>
        <div class="stat"><b>{{ $jumlahEkspedisi }}</b>Ekspedisi</div>
        <div class="stat"><b>{{ $jumlahVoucher }}</b>Voucher</div>
        <div class="stat"><b>{{ $jumlahCustomer }}</b>Customer</div>
        <div class="stat"><b>{{ $jumlahPesanan }}</b>Pesanan</div>
    </div>
    <p style="margin-top:18px">
        <a class="btn" href="{{ route('produk.index') }}">Kelola Produk</a>
        <a class="btn" href="{{ route('admin.ekspedisi.index') }}">Kelola Ekspedisi</a>
        <a class="btn" href="{{ route('admin.voucher.index') }}">Kelola Voucher</a>
    </p>
</div>
@endsection
