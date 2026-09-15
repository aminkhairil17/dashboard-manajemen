<?php

namespace App\Livewire\TvKiosk;

use App\Contracts\HospitalDataRepository;
use App\Models\TvDisplayToken;
use App\Services\KpiCalculationService;
use Carbon\CarbonImmutable;
use Livewire\Component;

class Show extends Component
{
    public bool $valid = false;

    public bool $isDirektur = false;

    // ---- Mode Lobby Publik (berputar) ----
    public array $operasionalKpis = [];

    public array $borTrend = [];

    public array $barberJohnson = [];

    public array $tat = [];

    public array $otRooms = [];

    public array $kematian = [];

    public array $hais = [];

    public array $konversiGroups = [];

    // ---- Mode Ruangan Direktur (satu halaman tetap) ----
    public array $direkturKpis = [];

    public array $revenueTrend = [];

    public array $unitStatus = [];

    public array $perluPerhatian = [];

    public function mount(string $token, HospitalDataRepository $repo, KpiCalculationService $kpi): void
    {
        $record = TvDisplayToken::where('token', $token)->first();

        if (! $record || ! $record->isActive()) {
            $this->valid = false;

            return;
        }

        $record->update(['last_used_at' => now()]);
        $this->valid = true;
        $this->isDirektur = $record->isDirektur();

        $end = CarbonImmutable::now();
        $start = $end->subDays(29);

        $bor = $kpi->bor($start, $end);

        if ($this->isDirektur) {
            $this->mountDirektur($repo, $bor);

            return;
        }

        $los = $kpi->los($start, $end);
        $bto = $kpi->bto($start, $end);
        $extras = $repo->getOperationalExtras();

        // TV hanya menampilkan 4 KPI paling inti — TOI, Boarding & Discharge Time
        // disimpan untuk dashboard biasa supaya layar TV tetap ringkas dari kejauhan.
        $this->operasionalKpis = [
            ['label' => 'BOR', 'unit' => '%', 'disp' => number_format($bor, 1, ',', '.'), 'target' => 'Target 60–85%', 'status' => ($bor >= 60 && $bor <= 85) ? 'good' : 'warn', 'trend' => array_column(array_slice($repo->getBorTrend($end, 7), -7), 'bor')],
            ['label' => 'LOS', 'unit' => 'hari', 'disp' => number_format($los, 1, ',', '.'), 'target' => 'Target ≤ 12 hari', 'status' => $los <= 12 ? 'good' : 'warn', 'trend' => [$los + 0.6, $los + 0.4, $los + 0.3, $los + 0.2, $los + 0.1, $los, $los]],
            ['label' => 'BTO', 'unit' => 'x/bln', 'disp' => number_format($bto, 1, ',', '.'), 'target' => 'Target ≥ 2,5x/bln', 'status' => $bto >= 2.5 ? 'good' : 'warn', 'trend' => [$bto - 0.4, $bto - 0.3, $bto - 0.2, $bto - 0.1, $bto - 0.1, $bto, $bto]],
            ['label' => 'New Patient', 'unit' => '%', 'disp' => number_format($extras['new_patient_pct']['value'], 1, ',', '.'), 'target' => $extras['new_patient_pct']['target'], 'status' => $extras['new_patient_pct']['status'], 'trend' => $extras['new_patient_pct']['trend']],
        ];
        $this->borTrend = array_column($repo->getBorTrend($end, 30), 'bor');
        $this->barberJohnson = $repo->getBarberJohnsonPoints();

        $efisiensi = $repo->getEfisiensiData();
        $this->tat = $efisiensi['tat'];
        $this->otRooms = $efisiensi['ot_rooms'];

        $mutu = $repo->getMutuData();
        $this->kematian = $mutu['kematian'];
        $this->hais = $mutu['hais'];

        $this->konversiGroups = $repo->getKonversiData();
    }

    private function mountDirektur(HospitalDataRepository $repo, float $bor): void
    {
        $keuangan = $repo->getKeuanganData();
        $ringkasan = $repo->getRingkasanExtras();
        $rawatJalan = $repo->getRawatJalanData();
        $pctTarget = round(($keuangan['pendapatan_bulan_berjalan'] / $keuangan['pendapatan_target']) * 100, 1);

        $this->direkturKpis = [
            ['label' => 'BOR', 'unit' => '%', 'disp' => number_format($bor, 1, ',', '.'), 'target' => 'Target 60–85%', 'status' => ($bor >= 60 && $bor <= 85) ? 'good' : 'warn'],
            ['label' => 'Pendapatan Bulan Ini', 'unit' => null, 'disp' => 'Rp '.number_format($keuangan['pendapatan_bulan_berjalan'], 2, ',', '.').' M', 'target' => $pctTarget.'% dari target Rp '.number_format($keuangan['pendapatan_target'], 2, ',', '.').' M', 'status' => $pctTarget >= 95 ? 'good' : 'warn'],
            ['label' => 'Kepuasan Pasien', 'unit' => '/ 100', 'disp' => $ringkasan['bsc']['pelanggan']['rows'][0]['val'] ?? '—', 'target' => 'Target ≥ 80', 'status' => 'good'],
            ['label' => 'Kunjungan Rawat Jalan', 'unit' => 'pasien', 'disp' => (string) $rawatJalan['total_kunjungan'], 'target' => 'Hari ini, semua poli', 'status' => 'good'],
            ['label' => 'Insiden Bulan Ini', 'unit' => 'kejadian', 'disp' => '3', 'target' => '0 sentinel', 'status' => 'warn'],
            ['label' => 'Piutang BPJS >90 Hari', 'unit' => null, 'disp' => 'Rp 310 jt', 'target' => 'Risiko write-off', 'status' => 'warn'],
        ];

        $this->revenueTrend = $repo->getRevenueTrend(30);
        $this->unitStatus = $ringkasan['unit_status'];
        $this->perluPerhatian = $repo->getPerluPerhatian();
    }

    public function render()
    {
        $view = $this->isDirektur ? 'livewire.tv-kiosk.show-direktur' : 'livewire.tv-kiosk.show';

        return view($view)->layout('layouts.tv');
    }
}
