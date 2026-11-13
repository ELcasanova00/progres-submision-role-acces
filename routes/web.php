<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EkspedisiController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\AlamatController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\ProfilController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik — Guest, Customer, Admin: melihat katalog produk
|--------------------------------------------------------------------------
*/
Route::get('/', [ProdukController::class, 'index']);
Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');

/*
|--------------------------------------------------------------------------
| Guest saja — registrasi & login
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/registrasi', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/registrasi', [RegisterController::class, 'register'])->name('register.store');
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin saja — kelola produk, ekspedisi, voucher
|--------------------------------------------------------------------------
*/
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('produk', ProdukController::class)->except(['index', 'show']);

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('ekspedisi', EkspedisiController::class)->except(['show']);
        Route::resource('voucher', VoucherController::class)->except(['show']);
    });
});

/*
|--------------------------------------------------------------------------
| Customer saja — profil, alamat, checkout (gunakan voucher)
|--------------------------------------------------------------------------
*/
Route::middleware('role:customer')->group(function () {
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    Route::get('/alamat', [AlamatController::class, 'index'])->name('alamat.index');
    Route::get('/alamat/create', [AlamatController::class, 'create'])->name('alamat.create');
    Route::post('/alamat', [AlamatController::class, 'store'])->name('alamat.store');
    Route::get('/alamat/{id}/edit', [AlamatController::class, 'edit'])->name('alamat.edit');
    Route::put('/alamat/{id}', [AlamatController::class, 'update'])->name('alamat.update');
    Route::delete('/alamat/{id}', [AlamatController::class, 'destroy'])->name('alamat.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::put('/checkout/{id}', [CheckoutController::class, 'update'])->name('checkout.update');
    Route::delete('/checkout/{id}', [CheckoutController::class, 'destroy'])->name('checkout.destroy');
});
