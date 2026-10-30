@extends('layouts.app')

@section('page_title', 'Pengaturan')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Pengaturan</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Profile & User Settings Form -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Profil Pengguna</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola informasi nama, alamat email, dan keamanan akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('settings.profile.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $user ? $user->name : 'Administrator') }}" required
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                    Alamat Email <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $user ? $user->email : 'admin@example.com') }}" required
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
            </div>

            <div class="pt-2 border-t border-slate-100 space-y-4">
                <h3 class="text-xs font-semibold text-slate-800">Ubah Kata Sandi (Opsional)</h3>

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-xs font-medium text-slate-600 mb-1">
                        Kata Sandi Saat Ini
                    </label>
                    <input type="password" name="current_password" id="current_password"
                           placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
                </div>

                <!-- New Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-xs font-medium text-slate-600 mb-1">
                            Kata Sandi Baru
                        </label>
                        <input type="password" name="new_password" id="new_password"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
                    </div>
                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-medium text-slate-600 mb-1">
                            Konfirmasi Sandi Baru
                        </label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Application Environment Info Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Informasi Lingkungan Sistem</h3>
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs font-mono">
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                <dt class="text-slate-400 text-[10px]">APLIKASI</dt>
                <dd class="text-slate-800 font-semibold mt-0.5 truncate">{{ $appInfo['app_name'] }}</dd>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                <dt class="text-slate-400 text-[10px]">LARAVEL VERSION</dt>
                <dd class="text-slate-800 font-semibold mt-0.5">v{{ $appInfo['laravel_version'] }}</dd>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                <dt class="text-slate-400 text-[10px]">PHP RUNTIME</dt>
                <dd class="text-slate-800 font-semibold mt-0.5">{{ $appInfo['php_version'] }}</dd>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                <dt class="text-slate-400 text-[10px]">DATABASE ENGINE</dt>
                <dd class="text-slate-800 font-semibold mt-0.5 uppercase">{{ $appInfo['database_driver'] }} (MySQL)</dd>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                <dt class="text-slate-400 text-[10px]">QUEUE DRIVER</dt>
                <dd class="text-slate-800 font-semibold mt-0.5 uppercase">{{ $appInfo['queue_driver'] }}</dd>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                <dt class="text-slate-400 text-[10px]">CACHE DRIVER</dt>
                <dd class="text-slate-800 font-semibold mt-0.5 uppercase">{{ $appInfo['cache_driver'] }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
