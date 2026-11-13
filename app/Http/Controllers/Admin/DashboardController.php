<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekspedisi;
use App\Models\Order;
use App\Models\Produk;
use App\Models\User;
use App\Models\Voucher;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'jumlahProduk' => Produk::count(),
            'jumlahEkspedisi' => Ekspedisi::count(),
            'jumlahVoucher' => Voucher::count(),
            'jumlahCustomer' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'jumlahPesanan' => Order::count(),
        ]);
    }
}
