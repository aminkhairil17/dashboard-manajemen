<?php

namespace Tests\Unit;

use App\Contracts\HospitalDataRepository;
use App\Services\KpiCalculationService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class KpiCalculationServiceTest extends TestCase
{
    private function serviceWithCensus(array $census): KpiCalculationService
    {
        $repo = new class($census) implements HospitalDataRepository
        {
            public function __construct(private array $census) {}

            public function getBedCensus(CarbonImmutable $start, CarbonImmutable $end): array
            {
                return $this->census;
            }

            public function getBorTrend(CarbonImmutable $end, int $days): array { return []; }
            public function getBarberJohnsonPoints(): array { return []; }
            public function getOperationalExtras(): array { return []; }
            public function getRoomAvailability(): array { return []; }
            public function getRawatJalanData(): array { return []; }
            public function getRevenueTrend(int $days): array { return []; }
            public function getPerluPerhatian(): array { return []; }
            public function getEfisiensiData(): array { return []; }
            public function getMutuData(): array { return []; }
            public function getKeuanganData(): array { return []; }
            public function getSdmData(): array { return []; }
            public function getKonversiData(): array { return []; }
            public function getRingkasanExtras(): array { return []; }
        };

        return new KpiCalculationService($repo);
    }

    public function test_bor_dihitung_dari_hari_rawat_dibagi_kapasitas_tempat_tidur(): void
    {
        $kpi = $this->serviceWithCensus([
            'total_beds' => 100, 'patient_days' => 2400, 'discharges' => 300, 'period_days' => 30,
        ]);

        $this->assertSame(80.0, $kpi->bor(CarbonImmutable::parse('2026-08-01'), CarbonImmutable::parse('2026-08-30')));
    }

    public function test_los_dihitung_dari_hari_rawat_dibagi_jumlah_pasien_keluar(): void
    {
        $kpi = $this->serviceWithCensus([
            'total_beds' => 100, 'patient_days' => 2400, 'discharges' => 300, 'period_days' => 30,
        ]);

        $this->assertSame(8.0, $kpi->los(CarbonImmutable::parse('2026-08-01'), CarbonImmutable::parse('2026-08-30')));
    }

    public function test_bto_dihitung_dari_jumlah_pasien_keluar_dibagi_tempat_tidur(): void
    {
        $kpi = $this->serviceWithCensus([
            'total_beds' => 100, 'patient_days' => 2400, 'discharges' => 300, 'period_days' => 30,
        ]);

        $this->assertSame(3.0, $kpi->bto(CarbonImmutable::parse('2026-08-01'), CarbonImmutable::parse('2026-08-30')));
    }

    public function test_toi_dihitung_dari_selisih_hari_tempat_tidur_tersedia_dan_terisi(): void
    {
        $kpi = $this->serviceWithCensus([
            'total_beds' => 100, 'patient_days' => 2400, 'discharges' => 300, 'period_days' => 30,
        ]);

        $this->assertSame(2.0, $kpi->toi(CarbonImmutable::parse('2026-08-01'), CarbonImmutable::parse('2026-08-30')));
    }

    public function test_kpi_mengembalikan_nol_saat_tidak_ada_tempat_tidur_atau_pasien_keluar(): void
    {
        $kpi = $this->serviceWithCensus([
            'total_beds' => 0, 'patient_days' => 0, 'discharges' => 0, 'period_days' => 30,
        ]);

        $start = CarbonImmutable::parse('2026-08-01');
        $end = CarbonImmutable::parse('2026-08-30');

        $this->assertSame(0.0, $kpi->bor($start, $end));
        $this->assertSame(0.0, $kpi->los($start, $end));
        $this->assertSame(0.0, $kpi->bto($start, $end));
        $this->assertSame(0.0, $kpi->toi($start, $end));
    }
}
