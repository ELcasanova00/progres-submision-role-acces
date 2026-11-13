<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ekspedisi;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\Voucher;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Checkout & penggunaan voucher — khusus Customer.
 */
class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $editOrder = null;
        $selectedProducts = [];
        $selectedVoucherId = 0;
        $selectedAddressId = null;
        $selectedEkspedisiId = null;

        if ($request->filled('edit')) {
            $editOrder = $user->orders()->with('items')->findOrFail($request->query('edit'));
            $selectedProducts = $editOrder->items->pluck('produk_id')->map(fn ($id) => (int) $id)->all();
            $selectedVoucherId = (int) $editOrder->voucher_id;
            $selectedAddressId = $editOrder->address_id;
            $selectedEkspedisiId = $editOrder->ekspedisi_id;
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->orderBy('id')->get();

        if ($selectedAddressId === null) {
            $selectedAddressId = $addresses->firstWhere('is_default', true)?->id;
        }

        // Produk yang sedang ada di pesanan yang diedit tetap ditampilkan walau stoknya kini 0.
        $products = Produk::aktif()
            ->where(fn ($q) => $q->where('stok', '>', 0)->orWhereIn('id', $selectedProducts))
            ->orderBy('id')
            ->get();

        return view('customer.checkout.index', [
            'products' => $products,
            'vouchers' => Voucher::aktif()->orderBy('id')->get(),
            'ekspedisis' => Ekspedisi::aktif()->orderBy('id')->get(),
            'addresses' => $addresses,
            'orders' => $user->orders()->with(['voucher', 'items.produk'])->orderByDesc('id')->get(),
            'editOrder' => $editOrder,
            'selectedProducts' => $selectedProducts,
            'selectedVoucherId' => $selectedVoucherId,
            'selectedAddressId' => $selectedAddressId,
            'selectedEkspedisiId' => $selectedEkspedisiId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateInput($request);

        try {
            DB::transaction(function () use ($request) {
                $calc = $this->calculateOrder($request);

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'address_id' => $calc['address']->id,
                    'ekspedisi_id' => $calc['ekspedisi']->id,
                    'voucher_id' => $calc['voucher']?->id,
                    'alamat_pengiriman' => $calc['address']->formatted(),
                    'ekspedisi_label' => $calc['ekspedisi']->label(),
                    'subtotal' => $calc['subtotal'],
                    'diskon' => $calc['diskon'],
                    'ongkir' => $calc['ongkir'],
                    'total' => $calc['total'],
                ]);

                $this->saveItems($order, $calc['produks']);
            });
        } catch (DomainException $e) {
            return redirect()->route('checkout.index')->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('checkout.index')->with('success', 'Pesanan berhasil disimpan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $order = $request->user()->orders()->findOrFail($id);
        $this->validateInput($request);

        try {
            DB::transaction(function () use ($request, $order) {
                $this->restoreStock($order);

                $calc = $this->calculateOrder($request);

                $order->update([
                    'address_id' => $calc['address']->id,
                    'ekspedisi_id' => $calc['ekspedisi']->id,
                    'voucher_id' => $calc['voucher']?->id,
                    'alamat_pengiriman' => $calc['address']->formatted(),
                    'ekspedisi_label' => $calc['ekspedisi']->label(),
                    'subtotal' => $calc['subtotal'],
                    'diskon' => $calc['diskon'],
                    'ongkir' => $calc['ongkir'],
                    'total' => $calc['total'],
                ]);

                $order->items()->delete();
                $this->saveItems($order, $calc['produks']);
            });
        } catch (DomainException $e) {
            return redirect()->route('checkout.index', ['edit' => $id])->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('checkout.index')->with('success', "Pesanan #$id diperbarui.");
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $order = $request->user()->orders()->findOrFail($id);

        DB::transaction(function () use ($order) {
            $this->restoreStock($order);
            $order->delete();
        });

        return redirect()->route('checkout.index')->with('success', "Pesanan #$id dihapus.");
    }

    private function validateInput(Request $request): void
    {
        $request->validate([
            'product' => ['required', 'array', 'min:1'],
            'product.*' => ['integer'],
            'address_id' => ['required', 'integer'],
            'ekspedisi_id' => ['required', 'integer'],
            'voucher_id' => ['nullable', 'integer'],
        ], [
            'product.required' => 'Pilih minimal satu produk.',
            'product.min' => 'Pilih minimal satu produk.',
            'address_id.required' => 'Pilih alamat pengiriman.',
            'ekspedisi_id.required' => 'Pilih ekspedisi pengiriman.',
        ]);
    }

    /**
     * Hitung pesanan dari input. Harus dipanggil di dalam transaksi karena
     * produk dikunci (lockForUpdate) dan stoknya langsung dikurangi.
     *
     * @return array{produks: \Illuminate\Support\Collection, address: \App\Models\Address, ekspedisi: Ekspedisi, voucher: ?Voucher, subtotal: int, diskon: int, ongkir: int, total: int}
     */
    private function calculateOrder(Request $request): array
    {
        $user = $request->user();

        $productIds = array_values(array_unique(array_map('intval', (array) $request->input('product', []))));

        $produks = Produk::whereIn('id', $productIds)->lockForUpdate()->get();

        if ($produks->count() !== count($productIds)) {
            throw new DomainException('Ada produk yang tidak ditemukan.');
        }

        foreach ($produks as $produk) {
            if ($produk->status !== 'Aktif') {
                throw new DomainException('Produk "'.$produk->nama_produk.'" sedang tidak tersedia.');
            }
            if ($produk->stok < 1) {
                throw new DomainException('Stok produk "'.$produk->nama_produk.'" habis.');
            }
        }

        $address = $user->addresses()->find($request->input('address_id'));
        if (! $address) {
            throw new DomainException('Alamat pengiriman tidak valid.');
        }

        $ekspedisi = Ekspedisi::aktif()->find($request->input('ekspedisi_id'));
        if (! $ekspedisi) {
            throw new DomainException('Ekspedisi tidak valid / tidak aktif.');
        }

        $subtotal = (int) $produks->sum('harga');
        $diskon = 0;
        $voucher = null;

        $voucherId = (int) $request->input('voucher_id', 0);
        if ($voucherId > 0) {
            $voucher = Voucher::aktif()->find($voucherId);
            if (! $voucher) {
                throw new DomainException('Voucher tidak valid / tidak aktif.');
            }
            if ($subtotal < (int) $voucher->min_belanja) {
                throw new DomainException('Voucher '.$voucher->kode.' butuh minimal belanja Rp '.number_format($voucher->min_belanja, 0, ',', '.').'.');
            }
            $diskon = $voucher->hitungDiskon($subtotal);
        }

        $ongkir = (int) $ekspedisi->tarif;

        // Kurangi stok (hanya setelah semua validasi lolos).
        foreach ($produks as $produk) {
            $produk->decrement('stok');
        }

        return [
            'produks' => $produks,
            'address' => $address,
            'ekspedisi' => $ekspedisi,
            'voucher' => $voucher,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'ongkir' => $ongkir,
            'total' => $subtotal - $diskon + $ongkir,
        ];
    }

    private function saveItems(Order $order, $produks): void
    {
        foreach ($produks as $produk) {
            OrderItem::create([
                'order_id' => $order->id,
                'produk_id' => $produk->id,
                'harga' => (int) $produk->harga,
            ]);
        }
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items()->get() as $item) {
            Produk::where('id', $item->produk_id)->increment('stok');
        }
    }
}
