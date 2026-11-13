<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $profile = $user->customerProfile()->firstOrCreate([]);

        return view('customer.profil.edit', compact('user', 'profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $user->update(['name' => $validated['name']]);

        $user->customerProfile()->updateOrCreate([], [
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
        ]);

        return redirect()->route('profil.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}
