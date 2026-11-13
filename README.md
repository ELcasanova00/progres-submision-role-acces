# SevShop — E-Commerce / Toko Online 

Gabungan modul: Katalog Produk, Pelanggan & Alamat, Ekspedisi Kirim, Voucher & Promo + Checkout.
Laravel 12 · PHP >= 8.2 · MySQL.


```bash
composer install
cp .env.example .env         
php artisan key:generate
# buat database kosong bernama: toko_online
php artisan migrate:fresh --seed
php artisan serve
```
Buka http://127.0.0.1:8000

## Akun demo
| Role     | Email                  | Password      |
|----------|------------------------|---------------|
| Admin    | admin@sevshop.test     | admin12345    |
| Customer | customer@sevshop.test  | customer12345 |

Guest = belum login (hanya melihat katalog). Registrasi publik selalu membuat akun **customer**.

## Hak akses per role
| Fitur                  | Admin | Customer | Guest |
|------------------------|:-----:|:--------:|:-----:|
| Melihat katalog produk | Ya    | Ya       | Ya    |
| Mengelola produk       | Ya    | Tidak    | Tidak |
| Mengelola profil       | Tidak | Ya       | Tidak |
| Mengelola alamat       | Tidak | Ya       | Tidak |
| Mengelola ekspedisi    | Ya    | Tidak    | Tidak |
| Mengelola voucher      | Ya    | Tidak    | Tidak |
| Menggunakan voucher    | Tidak | Ya       | Tidak |

Ditegakkan oleh middleware `role:admin` / `role:customer` (app/Http/Middleware/RoleMiddleware.php):
guest diarahkan ke /login, role yang salah mendapat 403.

## Tes
```bash
php artisan test
```
