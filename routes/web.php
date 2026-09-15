<?php

use App\Livewire\Dashboard\Efisiensi;
use App\Livewire\Dashboard\Glosarium;
use App\Livewire\Dashboard\Keuangan;
use App\Livewire\Dashboard\Konversi;
use App\Livewire\Dashboard\Mutu;
use App\Livewire\Dashboard\Operasional;
use App\Livewire\Dashboard\RawatJalan;
use App\Livewire\Dashboard\Ringkasan;
use App\Livewire\Dashboard\Sdm;
use App\Livewire\Dashboard\TvKioskManager;
use App\Livewire\TvKiosk\Show as TvKioskShow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Ringkasan::class)->name('dashboard');
    Route::get('/dashboard/ringkasan', Ringkasan::class)->name('dashboard.ringkasan');
    Route::get('/dashboard/operasional', Operasional::class)->name('dashboard.operasional');
    Route::get('/dashboard/rawat-jalan', RawatJalan::class)->name('dashboard.rawat-jalan');
    Route::get('/dashboard/efisiensi', Efisiensi::class)->name('dashboard.efisiensi');
    Route::get('/dashboard/mutu', Mutu::class)->name('dashboard.mutu');
    Route::get('/dashboard/keuangan', Keuangan::class)->name('dashboard.keuangan');
    Route::get('/dashboard/sdm', Sdm::class)->name('dashboard.sdm');
    Route::get('/dashboard/konversi', Konversi::class)->name('dashboard.konversi');
    Route::get('/dashboard/glosarium', Glosarium::class)->name('dashboard.glosarium');
    Route::get('/dashboard/tv-kiosk', TvKioskManager::class)->name('dashboard.tv-kiosk');
});

// Mode TV kiosk — sengaja tanpa middleware auth, proteksi lewat token acak di URL (lihat App\Livewire\TvKiosk\Show).
Route::get('/tv/{token}', TvKioskShow::class)->name('tv.show');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
