<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $user = auth()->user() ?? User::first();

        $appInfo = [
            'app_name' => config('app.name', 'Market Basket Analysis'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_driver' => config('database.default'),
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
        ];

        return view('settings.index', compact('user', 'appInfo'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user() ?? User::first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('new_password')) {
            if ($user->password && !Hash::check($request->input('current_password'), $user->password)) {
                return back()->withInput()->with('error', 'Password saat ini salah.');
            }
            $user->password = Hash::make($request->input('new_password'));
        }

        $user->save();

        return back()->with('success', 'Pengaturan profil berhasil diperbarui.');
    }
}
