<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Ekspedisi;
use App\Models\Order;
use App\Models\Produk;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function customer(): User
    {
        $u = User::factory()->create();
        $u->customerProfile()->create([]);

        return $u;
    }

    private function produk(array $o = []): Produk
    {
        return Produk::create($o + ['nama_produk' => 'Kaos', 'harga' => 100000, 'stok' => 5, 'status' => 'Aktif']);
    }

    private function alamat(User $u): Address
    {
        return $u->addresses()->create([
            'recipient_name' => 'Budi', 'phone' => '0812', 'province' => 'Jabar', 'city' => 'Bandung',
            'full_address' => 'Jl. Test 1', 'is_default' => true,
        ]);
    }

    private function ekspedisi(): Ekspedisi
    {
        return Ekspedisi::create(['nama_kurir' => 'JNE', 'kode' => 'JNE-REG', 'jenis_layanan' => 'Reguler', 'zona' => 'Jawa', 'tarif' => 10000, 'status' => 'Aktif']);
    }

    // ---------- Matrix: melihat katalog (semua role) ----------
    public function test_catalog_visible_to_guest_customer_admin(): void
    {
        $this->produk(['nama_produk' => 'Aktif A']);
        $this->produk(['nama_produk' => 'Nonaktif B', 'status' => 'Nonaktif']);

        $this->get('/')->assertOk()->assertSee('Aktif A')->assertDontSee('Nonaktif B');
        $this->get('/produk')->assertOk()->assertSee('Aktif A');
        $this->actingAs($this->customer())->get('/produk')->assertOk()->assertSee('Aktif A')->assertDontSee('Nonaktif B');
        $this->actingAs($this->admin())->get('/produk')->assertOk()->assertSee('Aktif A')->assertSee('Nonaktif B');
    }

    // ---------- Matrix: mengelola produk (admin saja) ----------
    public function test_manage_products_admin_only(): void
    {
        $p = $this->produk();
        $payload = ['nama_produk' => 'Baru', 'harga' => 1000, 'stok' => 1, 'status' => 'Aktif'];

        // Guest -> login
        $this->get('/produk/create')->assertRedirect('/login');
        $this->post('/produk', $payload)->assertRedirect('/login');
        $this->put("/produk/{$p->id}", $payload)->assertRedirect('/login');
        $this->delete("/produk/{$p->id}")->assertRedirect('/login');

        // Customer -> 403
        $c = $this->customer();
        $this->actingAs($c)->get('/produk/create')->assertForbidden();
        $this->actingAs($c)->post('/produk', $payload)->assertForbidden();
        $this->actingAs($c)->put("/produk/{$p->id}", $payload)->assertForbidden();
        $this->actingAs($c)->delete("/produk/{$p->id}")->assertForbidden();
        $this->assertDatabaseCount('produks', 1);

        // Admin -> ok
        $a = $this->admin();
        $this->actingAs($a)->get('/produk/create')->assertOk();
        $this->actingAs($a)->post('/produk', $payload)->assertRedirect('/produk');
        $this->assertDatabaseHas('produks', ['nama_produk' => 'Baru']);
        $this->actingAs($a)->put("/produk/{$p->id}", $payload)->assertRedirect('/produk');
        $this->assertDatabaseHas('produks', ['id' => $p->id, 'nama_produk' => 'Baru']);
        $this->actingAs($a)->delete("/produk/{$p->id}")->assertRedirect('/produk');
        $this->assertDatabaseMissing('produks', ['id' => $p->id]);
    }

    // ---------- Matrix: ekspedisi & voucher (admin saja) ----------
    public function test_ekspedisi_and_voucher_admin_only(): void
    {
        $e = $this->ekspedisi();
        $v = Voucher::create(['kode' => 'X', 'nama' => 'X', 'tipe_diskon' => 'persen', 'nilai' => 10, 'min_belanja' => 0, 'aktif' => true]);

        $urls = ['/admin/ekspedisi', '/admin/ekspedisi/create', "/admin/ekspedisi/{$e->id}/edit", '/admin/voucher', '/admin/voucher/create', "/admin/voucher/{$v->id}/edit", '/admin/dashboard'];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        foreach ($urls as $url) {
            $this->actingAs($this->customer())->get($url)->assertForbidden();
        }
        foreach ($urls as $url) {
            $this->actingAs($this->admin())->get($url)->assertOk();
        }

        $c = $this->customer();
        $this->actingAs($c)->post('/admin/ekspedisi', [])->assertForbidden();
        $this->actingAs($c)->delete("/admin/voucher/{$v->id}")->assertForbidden();

        $a = $this->admin();
        $this->actingAs($a)->post('/admin/ekspedisi', ['nama_kurir' => 'SiCepat', 'kode' => 'SC', 'jenis_layanan' => 'Express', 'zona' => 'Jawa', 'tarif' => 20000, 'status' => 'Aktif'])->assertRedirect('/admin/ekspedisi');
        $this->assertDatabaseHas('ekspedisis', ['kode' => 'SC', 'jenis_layanan' => 'Express']);
        $this->actingAs($a)->post('/admin/voucher', ['kode' => 'abc', 'nama' => 'Nominal', 'tipe_diskon' => 'nominal', 'nilai' => 5000, 'min_belanja' => 0, 'aktif' => 1])->assertRedirect('/admin/voucher');
        $this->assertDatabaseHas('vouchers', ['kode' => 'ABC', 'tipe_diskon' => 'nominal', 'aktif' => 1]);
        $this->actingAs($a)->post('/admin/voucher', ['kode' => 'bad', 'nama' => 'x', 'tipe_diskon' => 'persen', 'nilai' => 150, 'min_belanja' => 0])->assertSessionHasErrors('nilai');
    }

    // ---------- Matrix: profil, alamat, voucher use (customer saja; admin Tidak) ----------
    public function test_profile_address_checkout_customer_only(): void
    {
        $urls = ['/profil', '/alamat', '/alamat/create', '/checkout'];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        foreach ($urls as $url) {
            $this->actingAs($this->admin())->get($url)->assertForbidden();
        }
        foreach ($urls as $url) {
            $this->actingAs($this->customer())->get($url)->assertOk();
        }

        $this->actingAs($this->admin())->put('/profil', ['name' => 'x'])->assertForbidden();
        $this->actingAs($this->admin())->post('/checkout', [])->assertForbidden();
    }

    public function test_customer_manages_own_profile_and_addresses_only(): void
    {
        $c = $this->customer();
        $other = $this->customer();
        $otherAddr = $this->alamat($other);

        $this->actingAs($c)->put('/profil', ['name' => 'Nama Baru', 'phone' => '0811', 'gender' => 'female', 'date_of_birth' => '2000-01-01'])->assertRedirect('/profil');
        $this->assertDatabaseHas('users', ['id' => $c->id, 'name' => 'Nama Baru']);
        $this->assertDatabaseHas('customer_profiles', ['user_id' => $c->id, 'phone' => '0811', 'gender' => 'female']);

        $addr = ['recipient_name' => 'Ani', 'phone' => '08', 'province' => 'Jabar', 'city' => 'Bandung', 'full_address' => 'Jl A'];
        $this->actingAs($c)->post('/alamat', $addr)->assertRedirect('/alamat');
        $first = $c->addresses()->first();
        $this->assertTrue($first->is_default); // alamat pertama otomatis utama

        $this->actingAs($c)->post('/alamat', $addr + ['is_default' => 1]);
        $this->assertSame(1, $c->addresses()->where('is_default', true)->count());

        // tidak bisa menyentuh alamat milik user lain
        $this->actingAs($c)->get("/alamat/{$otherAddr->id}/edit")->assertNotFound();
        $this->actingAs($c)->put("/alamat/{$otherAddr->id}", $addr)->assertNotFound();
        $this->actingAs($c)->delete("/alamat/{$otherAddr->id}")->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $otherAddr->id]);

        // hapus alamat utama -> yang lain jadi utama
        $default = $c->addresses()->where('is_default', true)->first();
        $this->actingAs($c)->delete("/alamat/{$default->id}")->assertRedirect('/alamat');
        $this->assertSame(1, $c->addresses()->where('is_default', true)->count());
    }

    // ---------- Registrasi, login (bcrypt), pengalihan role ----------
    public function test_register_creates_customer_with_bcrypt_and_ignores_role_input(): void
    {
        $this->post('/registrasi', [
            'name' => 'Siti', 'email' => 'siti@test.com', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'role' => 'admin', 'is_active' => 0,
        ])->assertRedirect('/login');

        $u = User::where('email', 'siti@test.com')->first();
        $this->assertNotNull($u);
        $this->assertSame('customer', $u->role);
        $this->assertTrue($u->is_active);
        $this->assertStringStartsWith('$2y$', $u->password);
        $this->assertTrue(Hash::check('rahasia123', $u->password));
        $this->assertNotNull($u->customerProfile);
        $this->assertGuest();
    }

    public function test_register_validation(): void
    {
        User::factory()->create(['email' => 'dup@test.com']);
        $this->post('/registrasi', ['name' => 'A', 'email' => 'dup@test.com', 'password' => 'short', 'password_confirmation' => 'x'])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_redirects_by_role_and_rejects_bad_credentials(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'a@test.com', 'password' => 'password123']);
        $cust = User::factory()->create(['email' => 'c@test.com', 'password' => 'password123']);

        $this->post('/login', ['email' => 'a@test.com', 'password' => 'password123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();

        $this->post('/login', ['email' => 'c@test.com', 'password' => 'password123'])->assertRedirect(route('produk.index'));
        $this->assertAuthenticatedAs($cust);
        $this->post('/logout');

        $this->post('/login', ['email' => 'c@test.com', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login_or_use_existing_session(): void
    {
        $u = User::factory()->inactive()->create(['email' => 'off@test.com', 'password' => 'password123']);
        $this->post('/login', ['email' => 'off@test.com', 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($u)->get('/profil')->assertRedirect('/login');
    }

    public function test_logged_in_user_is_redirected_away_from_login_by_role(): void
    {
        $this->actingAs($this->admin())->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->customer())->get('/login')->assertRedirect(route('produk.index'));
    }

    // ---------- Checkout & voucher ----------
    private function checkoutPayload(array $o = []): array
    {
        return $o + ['product' => [], 'voucher_id' => 0];
    }

    public function test_checkout_with_percent_voucher_ongkir_and_stock(): void
    {
        $c = $this->customer();
        $addr = $this->alamat($c);
        $eks = $this->ekspedisi();
        $p1 = $this->produk(['harga' => 100000, 'stok' => 3]);
        $p2 = $this->produk(['nama_produk' => 'Tas', 'harga' => 50000, 'stok' => 1]);
        $v = Voucher::create(['kode' => 'H10', 'nama' => '10', 'tipe_diskon' => 'persen', 'nilai' => 10, 'min_belanja' => 100000, 'aktif' => true]);

        $this->actingAs($c)->post('/checkout', [
            'product' => [$p1->id, $p2->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id, 'voucher_id' => $v->id,
        ])->assertRedirect('/checkout')->assertSessionHas('success');

        $o = Order::first();
        $this->assertSame(150000, $o->subtotal);
        $this->assertSame(15000, $o->diskon);
        $this->assertSame(10000, $o->ongkir);
        $this->assertSame(145000, $o->total);
        $this->assertSame($c->id, $o->user_id);
        $this->assertSame(2, $p1->fresh()->stok);
        $this->assertSame(0, $p2->fresh()->stok);
        $this->assertCount(2, $o->items);
    }

    public function test_checkout_nominal_voucher_and_min_belanja(): void
    {
        $c = $this->customer();
        $addr = $this->alamat($c);
        $eks = $this->ekspedisi();
        $p = $this->produk(['harga' => 100000]);
        $nom = Voucher::create(['kode' => 'N25', 'nama' => 'n', 'tipe_diskon' => 'nominal', 'nilai' => 25000, 'min_belanja' => 200000, 'aktif' => true]);
        $big = Voucher::create(['kode' => 'BIG', 'nama' => 'b', 'tipe_diskon' => 'nominal', 'nilai' => 500000, 'min_belanja' => 0, 'aktif' => true]);

        // belum memenuhi min belanja
        $this->actingAs($c)->post('/checkout', ['product' => [$p->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id, 'voucher_id' => $nom->id])
            ->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $p->fresh()->stok);

        // nominal > subtotal dibatasi sebesar subtotal
        $this->actingAs($c)->post('/checkout', ['product' => [$p->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id, 'voucher_id' => $big->id]);
        $o = Order::first();
        $this->assertSame(100000, $o->diskon);
        $this->assertSame(10000, $o->total);
    }

    public function test_checkout_rejects_inactive_voucher_other_users_address_and_out_of_stock(): void
    {
        $c = $this->customer();
        $other = $this->customer();
        $otherAddr = $this->alamat($other);
        $addr = $this->alamat($c);
        $eks = $this->ekspedisi();
        $p = $this->produk();
        $off = Voucher::create(['kode' => 'OFF', 'nama' => 'o', 'tipe_diskon' => 'persen', 'nilai' => 50, 'min_belanja' => 0, 'aktif' => false]);
        $empty = $this->produk(['nama_produk' => 'Habis', 'stok' => 0]);
        $nonaktif = $this->produk(['nama_produk' => 'Mati', 'status' => 'Nonaktif']);

        $base = ['product' => [$p->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id];

        $this->actingAs($c)->post('/checkout', $base + ['voucher_id' => $off->id])->assertSessionHas('error');
        $this->actingAs($c)->post('/checkout', ['address_id' => $otherAddr->id] + $base)->assertSessionHas('error');
        $this->actingAs($c)->post('/checkout', ['product' => [$empty->id]] + $base)->assertSessionHas('error');
        $this->actingAs($c)->post('/checkout', ['product' => [$nonaktif->id]] + $base)->assertSessionHas('error');
        $this->actingAs($c)->post('/checkout', ['product' => []] + $base)->assertSessionHasErrors('product');
        $this->assertSame(0, Order::count());
    }

    public function test_customer_can_edit_and_delete_only_own_orders_with_stock_restored(): void
    {
        $c = $this->customer();
        $other = $this->customer();
        $addr = $this->alamat($c);
        $eks = $this->ekspedisi();
        $p1 = $this->produk(['harga' => 100000, 'stok' => 2]);
        $p2 = $this->produk(['nama_produk' => 'B', 'harga' => 20000, 'stok' => 2]);

        $this->actingAs($c)->post('/checkout', ['product' => [$p1->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id]);
        $o = Order::first();
        $this->assertSame(1, $p1->fresh()->stok);

        // edit: ganti produk
        $this->actingAs($c)->put("/checkout/{$o->id}", ['product' => [$p2->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id, 'voucher_id' => 0])
            ->assertRedirect('/checkout')->assertSessionHas('success');
        $this->assertSame(2, $p1->fresh()->stok);
        $this->assertSame(1, $p2->fresh()->stok);
        $this->assertSame(30000, $o->fresh()->total);
        $this->assertCount(1, $o->fresh()->items);

        // user lain tidak bisa
        $this->actingAs($other)->put("/checkout/{$o->id}", ['product' => [$p1->id]])->assertNotFound();
        $this->actingAs($other)->delete("/checkout/{$o->id}")->assertNotFound();
        $this->actingAs($other)->get("/checkout?edit={$o->id}")->assertNotFound();

        // hapus -> stok kembali
        $this->actingAs($c)->delete("/checkout/{$o->id}")->assertRedirect('/checkout');
        $this->assertSame(2, $p2->fresh()->stok);
        $this->assertSame(0, Order::count());
    }

    public function test_customer_does_not_see_other_customers_orders(): void
    {
        $a = $this->customer();
        $b = $this->customer();
        $addr = $this->alamat($a);
        $eks = $this->ekspedisi();
        $p = $this->produk(['nama_produk' => 'RahasiaItem']);
        $this->actingAs($a)->post('/checkout', ['product' => [$p->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id]);

        $this->actingAs($b)->get('/checkout')->assertOk()->assertDontSee('Jl. Test 1');
    }

    public function test_deleting_product_used_in_order_is_blocked_gracefully(): void
    {
        $c = $this->customer();
        $addr = $this->alamat($c);
        $eks = $this->ekspedisi();
        $p = $this->produk();
        $this->actingAs($c)->post('/checkout', ['product' => [$p->id], 'address_id' => $addr->id, 'ekspedisi_id' => $eks->id]);

        $this->actingAs($this->admin())->delete("/produk/{$p->id}")->assertRedirect('/produk')->assertSessionHas('error');
        $this->assertDatabaseHas('produks', ['id' => $p->id]);
    }

    public function test_seeder_runs_and_seeded_accounts_can_login(): void
    {
        $this->seed();
        $this->post('/login', ['email' => 'admin@sevshop.test', 'password' => 'admin12345'])->assertRedirect(route('admin.dashboard'));
        $this->post('/logout');
        $this->post('/login', ['email' => 'customer@sevshop.test', 'password' => 'customer12345'])->assertRedirect(route('produk.index'));
    }

    public function test_pages_render_for_each_role(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $cust = User::where('role', 'customer')->first();
        $this->alamat($cust);

        $this->get('/login')->assertOk()->assertSee('_token', false);
        $this->get('/registrasi')->assertOk()->assertSee('_token', false);
        $this->actingAs($cust)->get('/checkout')->assertOk()->assertSee('HEMAT10');
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }
}
