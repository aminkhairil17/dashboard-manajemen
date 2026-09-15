<?php

namespace App\Console\Commands;

use App\Models\KpiSnapshot;
use App\Services\KpiCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class KpiSnapshotCommand extends Command
{
    protected $signature = 'kpi:snapshot';

    protected $description = 'Hitung KPI efisiensi tempat tidur (BOR, LOS, BTO, TOI) untuk hari ini dan simpan ke kpi_snapshots';

    public function handle(KpiCalculationService $kpi): int
    {
        $end = CarbonImmutable::now();
        $start = $end->subDays(29);
        $today = $end->toDateString();

        $values = [
            'bor' => $kpi->bor($start, $end),
            'los' => $kpi->los($start, $end),
            'bto' => $kpi->bto($start, $end),
            'toi' => $kpi->toi($start, $end),
        ];

        foreach ($values as $key => $value) {
            KpiSnapshot::updateOrCreate(
                ['kpi_key' => $key, 'date' => $today],
                ['category' => 'operasional', 'value' => $value]
            );
        }

        $this->info("Snapshot KPI operasional untuk {$today} tersimpan: ".json_encode($values));

        return self::SUCCESS;
    }
}
