<?php

namespace Database\Seeders;

use App\Models\Ekspedisi;
use App\Models\Produk;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Password di-hash bcrypt oleh cast 'hashed' pada model User.
        User::updateOrCreate(['email' => 'admin@sevshop.test'], [
            'name' => 'Admin SevShop',
            'password' => 'admin12345',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $customer = User::updateOrCreate(['email' => 'customer@sevshop.test'], [
            'name' => 'Customer Demo',
            'password' => 'customer12345',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
        $customer->customerProfile()->firstOrCreate([]);

        if (Produk::count() === 0) {
            Produk::insert([
                ['nama_produk' => 'Kaos Polos Hitam', 'kategori' => 'Pakaian', 'merek' => 'Sev', 'harga' => 75000, 'stok' => 50, 'berat' => 250, 'deskripsi' => 'Kaos katun combed 30s.', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_produk' => 'Tas Ransel Kanvas', 'kategori' => 'Aksesoris', 'merek' => 'Sev', 'harga' => 185000, 'stok' => 20, 'berat' => 700, 'deskripsi' => 'Ransel kanvas muat laptop 14".', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_produk' => 'Botol Minum 750ml', 'kategori' => 'Perlengkapan', 'merek' => 'Hydro', 'harga' => 95000, 'stok' => 35, 'berat' => 300, 'deskripsi' => 'Stainless steel, tahan panas.', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_produk' => 'Sepatu Lari', 'kategori' => 'Sepatu', 'merek' => 'Runner', 'harga' => 450000, 'stok' => 10, 'berat' => 900, 'deskripsi' => 'Sol ringan untuk harian.', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_produk' => 'Topi Baseball (Arsip)', 'kategori' => 'Aksesoris', 'merek' => 'Sev', 'harga' => 60000, 'stok' => 0, 'berat' => 120, 'deskripsi' => 'Tidak dijual lagi.', 'status' => 'Nonaktif', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        Voucher::updateOrCreate(['kode' => 'HEMAT10'], ['nama' => 'Diskon 10%', 'tipe_diskon' => 'persen', 'nilai' => 10, 'min_belanja' => 100000, 'aktif' => true]);
        Voucher::updateOrCreate(['kode' => 'POTONG25K'], ['nama' => 'Potongan Rp 25.000', 'tipe_diskon' => 'nominal', 'nilai' => 25000, 'min_belanja' => 200000, 'aktif' => true]);

        Ekspedisi::updateOrCreate(['kode' => 'JNE-REG'], ['nama_kurir' => 'JNE', 'jenis_layanan' => 'Reguler', 'zona' => 'Jawa', 'tarif' => 12000, 'estimasi' => '2-3 hari', 'status' => 'Aktif']);
        Ekspedisi::updateOrCreate(['kode' => 'JNE-EXP'], ['nama_kurir' => 'JNE', 'jenis_layanan' => 'Express', 'zona' => 'Jawa', 'tarif' => 25000, 'estimasi' => '1 hari', 'status' => 'Aktif']);
        Ekspedisi::updateOrCreate(['kode' => 'SICEPAT-REG'], ['nama_kurir' => 'SiCepat', 'jenis_layanan' => 'Reguler', 'zona' => 'Luar Jawa', 'tarif' => 30000, 'estimasi' => '4-6 hari', 'status' => 'Aktif']);
    }
}
