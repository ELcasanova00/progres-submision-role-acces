<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlamatController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->orderBy('id')->get();

        return view('customer.alamat.index', compact('addresses'));
    }

    public function create()
    {
        return view('customer.alamat.form', ['address' => new Address]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validated($request);

        DB::transaction(function () use ($user, $data, $request) {
            // Alamat pertama otomatis menjadi alamat utama.
            $makeDefault = $request->boolean('is_default') || ! $user->addresses()->exists();

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create($data + ['is_default' => $makeDefault]);
        });

        return redirect()->route('alamat.index')->with('success', 'Alamat berhasil ditambahkan!');
    }

    public function edit(Request $request, int $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);

        return view('customer.alamat.form', compact('address'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);
        $data = $this->validated($request);

        DB::transaction(function () use ($user, $address, $data, $request) {
            if ($request->boolean('is_default')) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
                $data['is_default'] = true;
            } else {
                // Alamat utama tidak boleh "dilepas" tanpa alamat utama pengganti.
                $data['is_default'] = $address->is_default;
            }

            $address->update($data);
        });

        return redirect()->route('alamat.index')->with('success', 'Alamat berhasil diperbarui!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        DB::transaction(function () use ($user, $address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $user->addresses()->orderBy('id')->first()?->update(['is_default' => true]);
            }
        });

        return redirect()->route('alamat.index')->with('success', 'Alamat berhasil dihapus!');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'full_address' => ['required', 'string'],
        ]);
    }
}
