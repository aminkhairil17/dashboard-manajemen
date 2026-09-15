<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use App\Services\KpiCalculationService;
use Carbon\CarbonImmutable;
use Livewire\Component;

class Operasional extends Component
{
    public array $kpis;

    public array $borTrend;

    public array $barberJohnson;

    public array $rooms;

    public array $roomTotals;

    public function mount(HospitalDataRepository $repo, KpiCalculationService $kpi): void
    {
        $end = CarbonImmutable::now();
        $start = $end->subDays(29);

        $bor = $kpi->bor($start, $end);
        $los = $kpi->los($start, $end);
        $bto = $kpi->bto($start, $end);
        $toi = $kpi->toi($start, $end);

        $extras = $repo->getOperationalExtras();

        $this->kpis = [
            ['label' => 'BOR', 'unit' => '%', 'disp' => number_format($bor, 1, ',', '.'), 'target' => 'Target 60–85%', 'status' => $this->rangeStatus($bor, 60, 85), 'trend' => array_column(array_slice($repo->getBorTrend($end, 7), -7), 'bor')],
            ['label' => 'LOS', 'unit' => 'hari', 'disp' => number_format($los, 1, ',', '.'), 'target' => 'Target ≤ 12 hari', 'status' => $los <= 12 ? 'good' : 'warn', 'trend' => [$los + 0.6, $los + 0.4, $los + 0.3, $los + 0.2, $los + 0.1, $los, $los]],
            ['label' => 'BTO', 'unit' => 'x/bln', 'disp' => number_format($bto, 1, ',', '.'), 'target' => 'Target ≥ 2,5x/bln', 'status' => $bto >= 2.5 ? 'good' : 'warn', 'trend' => [$bto - 0.4, $bto - 0.3, $bto - 0.2, $bto - 0.1, $bto - 0.1, $bto, $bto]],
            ['label' => 'TOI', 'unit' => 'hari', 'disp' => number_format($toi, 1, ',', '.'), 'target' => 'Target 1–3 hari', 'status' => $this->rangeStatus($toi, 1, 3), 'trend' => [$toi + 0.9, $toi + 0.7, $toi + 0.5, $toi + 0.3, $toi + 0.1, $toi, $toi]],
            ['label' => 'Boarding Time', 'unit' => $extras['boarding_time']['unit'], 'disp' => (string) $extras['boarding_time']['value'], 'target' => $extras['boarding_time']['target'], 'status' => $extras['boarding_time']['status'], 'trend' => $extras['boarding_time']['trend']],
            ['label' => 'Discharge Time', 'unit' => null, 'disp' => $extras['discharge_time']['value'], 'target' => $extras['discharge_time']['target'], 'status' => $extras['discharge_time']['status'], 'trend' => $extras['discharge_time']['trend']],
            ['label' => 'New Patient', 'unit' => '%', 'disp' => number_format($extras['new_patient_pct']['value'], 1, ',', '.'), 'target' => $extras['new_patient_pct']['target'], 'status' => $extras['new_patient_pct']['status'], 'trend' => $extras['new_patient_pct']['trend']],
        ];

        $this->borTrend = array_column($repo->getBorTrend($end, 30), 'bor');
        $this->barberJohnson = $repo->getBarberJohnsonPoints();

        $this->rooms = $repo->getRoomAvailability();
        $this->roomTotals = [
            'total' => array_sum(array_column($this->rooms, 'total')),
            'terisi' => array_sum(array_column($this->rooms, 'terisi')),
            'kosong' => array_sum(array_column($this->rooms, 'kosong')),
            'perbaikan' => array_sum(array_column($this->rooms, 'perbaikan')),
        ];
    }

    private function rangeStatus(float $value, float $min, float $max): string
    {
        return ($value >= $min && $value <= $max) ? 'good' : 'warn';
    }

    public function render()
    {
        return view('livewire.dashboard.operasional')->layout('layouts.app');
    }
}
