<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return view('registrasi');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated) {
            // Password di-hash bcrypt otomatis oleh cast 'hashed' pada model User.
            // Role selalu 'customer' — tidak pernah diambil dari input pengguna.
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
            ]);

            $user->customerProfile()->create([]);
        });

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Silakan login.');
    }
}
