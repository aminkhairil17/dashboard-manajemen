<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use App\Services\KpiCalculationService;
use Carbon\CarbonImmutable;
use Livewire\Component;

class Ringkasan extends Component
{
    public array $bsc;

    public array $payerMix;

    public array $topDiagnosa;

    public array $unitStatus;

    public function mount(HospitalDataRepository $repo, KpiCalculationService $kpi): void
    {
        $extras = $repo->getRingkasanExtras();
        $end = CarbonImmutable::now();
        $start = $end->subDays(29);

        $this->bsc = $extras['bsc'];
        // BOR dihitung live lewat KpiCalculationService (bukan angka statis) supaya selalu
        // sinkron dengan angka yang tampil di halaman Operasional.
        $this->bsc['proses']['rows'][0]['val'] = number_format($kpi->bor($start, $end), 1, ',', '.').'%';

        $this->payerMix = $extras['payer_mix'];
        $this->topDiagnosa = $extras['top_diagnosa'];
        $this->unitStatus = $extras['unit_status'];
    }

    public function render()
    {
        return view('livewire.dashboard.ringkasan')->layout('layouts.app');
    }
}
