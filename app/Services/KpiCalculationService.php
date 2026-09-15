<?php

namespace App\Services;

use App\Contracts\HospitalDataRepository;
use Carbon\CarbonImmutable;

/**
 * Rumus KPI efisiensi tempat tidur (BOR, LOS, BTO, TOI) dipisahkan dari cara
 * data mentah diambil, supaya bisa diuji langsung dengan input yang diketahui
 * dan tidak berubah walau sumber data (mock vs SIMRS GOS) berganti.
 */
class KpiCalculationService
{
    public function __construct(private readonly HospitalDataRepository $repository)
    {
    }

    /** Bed Occupancy Ratio (%) — persentase tempat tidur terisi. */
    public function bor(CarbonImmutable $start, CarbonImmutable $end): float
    {
        $c = $this->repository->getBedCensus($start, $end);

        if ($c['total_beds'] <= 0 || $c['period_days'] <= 0) {
            return 0.0;
        }

        return round(($c['patient_days'] / ($c['total_beds'] * $c['period_days'])) * 100, 1);
    }

    /** Length of Stay (hari) — rata-rata lama rawat. */
    public function los(CarbonImmutable $start, CarbonImmutable $end): float
    {
        $c = $this->repository->getBedCensus($start, $end);

        if ($c['discharges'] <= 0) {
            return 0.0;
        }

        return round($c['patient_days'] / $c['discharges'], 1);
    }

    /** Bed Turn Over (kali) — jumlah pasien keluar per tempat tidur. */
    public function bto(CarbonImmutable $start, CarbonImmutable $end): float
    {
        $c = $this->repository->getBedCensus($start, $end);

        if ($c['total_beds'] <= 0) {
            return 0.0;
        }

        return round($c['discharges'] / $c['total_beds'], 1);
    }

    /** Turn Over Interval (hari) — rata-rata TT kosong antar pasien. */
    public function toi(CarbonImmutable $start, CarbonImmutable $end): float
    {
        $c = $this->repository->getBedCensus($start, $end);

        if ($c['discharges'] <= 0) {
            return 0.0;
        }

        $availableBedDays = $c['total_beds'] * $c['period_days'];

        return round(($availableBedDays - $c['patient_days']) / $c['discharges'], 1);
    }
}
