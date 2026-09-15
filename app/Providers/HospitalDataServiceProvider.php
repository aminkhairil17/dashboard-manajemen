<?php

namespace App\Providers;

use App\Contracts\HospitalDataRepository;
use App\Repositories\MockHospitalDataRepository;
use Illuminate\Support\ServiceProvider;

class HospitalDataServiceProvider extends ServiceProvider
{
    /**
     * Satu-satunya tempat sumber data dashboard diikat.
     *
     * Ganti baris di bawah ke SimrsGosRepository begitu akses & skema
     * SIMRS GOS RS Syifa Medika tersedia — tidak ada bagian lain dari
     * aplikasi yang perlu diubah.
     */
    public function register(): void
    {
        $this->app->singleton(HospitalDataRepository::class, MockHospitalDataRepository::class);
    }
}
