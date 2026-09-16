<?php

namespace App\Repositories;

use App\Contracts\HospitalDataRepository;
use App\Support\CurrentCompany;
use Carbon\CarbonImmutable;

/**
 * Data contoh yang realistis secara pola & volume untuk RS ukuran menengah
 * (±120 tempat tidur). Dipakai sampai akses SIMRS GOS RS Syifa Medika yang
 * sesungguhnya tersedia — lihat App\Contracts\HospitalDataRepository.
 *
 * Angka yang berhubungan langsung dengan kapasitas tempat tidur (BOR/LOS/
 * BTO/TOI, ketersediaan kamar, pendapatan) diskalakan mengikuti bed_capacity
 * company yang sedang aktif, supaya berpindah company di dashboard multi-RS
 * benar-benar menampilkan angka yang berbeda. Indikator lain (mutu, SDM,
 * konversi, dll — murni rasio/persentase) sengaja dibiarkan sama dulu.
 */
class MockHospitalDataRepository implements HospitalDataRepository
{
    private const DEFAULT_TOTAL_BEDS = 120;

    private const PATIENT_DAYS_PER_DAY = 88.33;

    private const DISCHARGES_PER_DAY = 21.0;

    private readonly int $totalBeds;

    private readonly float $scale;

    private readonly int $seedOffset;

    public function __construct(private CurrentCompany $currentCompany)
    {
        $company = $this->currentCompany->get();
        $this->totalBeds = $company?->bed_capacity ?? self::DEFAULT_TOTAL_BEDS;
        $this->scale = $this->totalBeds / self::DEFAULT_TOTAL_BEDS;
        $this->seedOffset = $company?->id ?? 0;
    }

    public function getBedCensus(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $periodDays = max(1, $start->diffInDays($end) + 1);

        return [
            'total_beds' => $this->totalBeds,
            'patient_days' => round(self::PATIENT_DAYS_PER_DAY * $this->scale * $periodDays, 1),
            'discharges' => (int) round(self::DISCHARGES_PER_DAY * $this->scale * $periodDays),
            'period_days' => $periodDays,
        ];
    }

    public function getBorTrend(CarbonImmutable $end, int $days): array
    {
        mt_srand(20260904 + $this->seedOffset);
        $base = (self::PATIENT_DAYS_PER_DAY / self::DEFAULT_TOTAL_BEDS) * 100;
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            if ($i === 0) {
                // Titik hari ini disamakan persis dengan BOR yang dihitung KpiCalculationService,
                // supaya angka di kartu KPI dan ujung grafik tren tidak pernah beda.
                $bor = round($base, 1);
            } else {
                $noise = (mt_rand(-60, 60) / 10);
                $drift = ($days - $i) * 0.05;
                $bor = round(min(95, max(55, $base + $noise + $drift)), 1);
            }

            $trend[] = [
                'date' => $end->subDays($i)->toDateString(),
                'bor' => $bor,
            ];
        }

        return $trend;
    }

    public function getBarberJohnsonPoints(): array
    {
        $months = ['Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'];
        $toi = [2.4, 2.1, 1.9, 1.7, 1.6, 1.5];
        $los = [5.1, 4.8, 4.6, 4.4, 4.3, 4.2];

        $points = [];
        foreach ($months as $i => $m) {
            $points[] = ['month' => $m, 'toi' => $toi[$i], 'los' => $los[$i]];
        }

        return $points;
    }

    public function getOperationalExtras(): array
    {
        return [
            'boarding_time' => ['value' => 96, 'unit' => 'menit', 'target' => 'Target ≤ 120 menit', 'status' => 'good', 'trend' => [118, 110, 104, 101, 99, 92, 96]],
            'discharge_time' => ['value' => '13:42', 'unit' => null, 'target' => 'Target sebelum 12:00', 'status' => 'warn', 'trend' => [12.9, 13.1, 13.4, 13.6, 13.3, 13.5, 13.7]],
            'new_patient_pct' => ['value' => 34.0, 'unit' => '%', 'target' => 'Target ≥ 30%', 'status' => 'good', 'trend' => [28, 29, 30, 31, 32, 33, 34]],
        ];
    }

    /**
     * Posisi kamar saat ini (snapshot per jenis kelas), diskalakan dari
     * proporsi dasar untuk RS 120 tempat tidur mengikuti bed_capacity company
     * yang aktif — totalnya akan selalu sama dengan $this->totalBeds.
     */
    public function getRoomAvailability(): array
    {
        $base = [
            ['kelas' => 'VIP', 'total' => 10, 'terisi' => 6, 'kosong' => 3, 'perbaikan' => 1],
            ['kelas' => 'Kelas 1', 'total' => 20, 'terisi' => 15, 'kosong' => 4, 'perbaikan' => 1],
            ['kelas' => 'Kelas 2', 'total' => 30, 'terisi' => 23, 'kosong' => 7, 'perbaikan' => 0],
            ['kelas' => 'Kelas 3', 'total' => 40, 'terisi' => 32, 'kosong' => 8, 'perbaikan' => 0],
            ['kelas' => 'ICU', 'total' => 10, 'terisi' => 9, 'kosong' => 0, 'perbaikan' => 1],
            ['kelas' => 'HCU', 'total' => 6, 'terisi' => 3, 'kosong' => 3, 'perbaikan' => 0],
            ['kelas' => 'Isolasi', 'total' => 4, 'terisi' => 0, 'kosong' => 4, 'perbaikan' => 0],
        ];

        if ($this->scale === 1.0) {
            return $base;
        }

        $scaled = array_map(function (array $row) {
            $row['total'] = (int) round($row['total'] * $this->scale);
            $row['perbaikan'] = min($row['total'], (int) round($row['perbaikan'] * $this->scale));
            $row['terisi'] = min($row['total'] - $row['perbaikan'], (int) round($row['terisi'] * $this->scale));
            $row['kosong'] = max(0, $row['total'] - $row['terisi'] - $row['perbaikan']);

            return $row;
        }, $base);

        // Rapikan sisa pembulatan supaya total kelas persis sama dengan $this->totalBeds.
        $diff = $this->totalBeds - array_sum(array_column($scaled, 'total'));
        if ($diff !== 0) {
            $scaled[array_key_last($scaled)]['total'] += $diff;
            $scaled[array_key_last($scaled)]['kosong'] = max(0, $scaled[array_key_last($scaled)]['total'] - $scaled[array_key_last($scaled)]['terisi'] - $scaled[array_key_last($scaled)]['perbaikan']);
        }

        return $scaled;
    }

    public function getRawatJalanData(): array
    {
        return [
            'total_kunjungan' => 218,
            'pasien_baru_pct' => 34.0,
            'waktu_tunggu' => ['value' => '48 menit', 'target' => '≤ 60 menit', 'status' => 'good'],
            'no_show_rate' => ['value' => '6,4%', 'target' => '< 10%', 'status' => 'good'],
            'top_poli' => [
                ['label' => 'Poli Umum', 'val' => 42],
                ['label' => 'Poli Anak', 'val' => 35],
                ['label' => 'Poli Penyakit Dalam', 'val' => 31],
                ['label' => 'Poli Kandungan & Kebidanan', 'val' => 28],
                ['label' => 'Poli Gigi', 'val' => 24],
            ],
            'jam_sibuk' => [
                ['label' => '07:00–08:00', 'val' => 18],
                ['label' => '08:00–09:00', 'val' => 34],
                ['label' => '09:00–10:00', 'val' => 41],
                ['label' => '10:00–11:00', 'val' => 38],
                ['label' => '11:00–12:00', 'val' => 29],
                ['label' => '12:00–13:00', 'val' => 15],
                ['label' => '13:00–14:00', 'val' => 22],
                ['label' => '14:00–15:00', 'val' => 21],
            ],
            'dokter_praktik' => [
                ['poli' => 'Poli Umum', 'dokter' => 'dr. Ahmad Fauzi', 'jam' => '08:00–12:00', 'pasien' => 42],
                ['poli' => 'Poli Anak', 'dokter' => 'dr. Siti Rahayu, Sp.A', 'jam' => '08:00–11:00', 'pasien' => 35],
                ['poli' => 'Poli Penyakit Dalam', 'dokter' => 'dr. Bambang Wijaya, Sp.PD', 'jam' => '09:00–13:00', 'pasien' => 31],
                ['poli' => 'Poli Kandungan & Kebidanan', 'dokter' => 'dr. Rina Kusuma, Sp.OG', 'jam' => '08:00–12:00', 'pasien' => 28],
                ['poli' => 'Poli Gigi', 'dokter' => 'drg. Dewi Anggraini', 'jam' => '08:00–14:00', 'pasien' => 24],
            ],
        ];
    }

    public function getEfisiensiData(): array
    {
        return [
            'tat' => [
                ['name' => 'TAT Farmasi — Non-Racikan', 'val' => 24, 'max' => 40, 'disp' => '24', 'unit' => 'menit', 'target' => '≤ 30 menit'],
                ['name' => 'TAT Farmasi — Racikan', 'val' => 52, 'max' => 75, 'disp' => '52', 'unit' => 'menit', 'target' => '≤ 60 menit'],
                ['name' => 'TAT Laboratorium', 'val' => 105, 'max' => 160, 'disp' => '105', 'unit' => 'menit', 'target' => '≤ 140 menit'],
                ['name' => 'TAT Radiologi', 'val' => 160, 'max' => 200, 'disp' => '2j 40m', 'unit' => '', 'target' => '≤ 3 jam'],
            ],
            'ot_rooms' => [
                ['name' => 'OK 1', 'util' => 82],
                ['name' => 'OK 2', 'util' => 74],
                ['name' => 'OK 3', 'util' => 61],
            ],
            'wait_times' => [
                ['unit' => 'IGD (triase → dokter)', 'val' => '14 menit', 'target' => '≤ 15 menit', 'status' => 'good'],
                ['unit' => 'Poli Rawat Jalan (daftar → periksa)', 'val' => '48 menit', 'target' => '≤ 60 menit', 'status' => 'good'],
                ['unit' => 'MCU', 'val' => '1j 12m', 'target' => '≤ 90 menit', 'status' => 'warn'],
            ],
        ];
    }

    public function getMutuData(): array
    {
        return [
            'kematian' => [
                ['label' => 'GDR (Gross Death Rate)', 'val' => '18,4‰', 'target' => '≤ 45‰', 'status' => 'good'],
                ['label' => 'NDR (Net Death Rate)', 'val' => '9,1‰', 'target' => '≤ 25‰', 'status' => 'good'],
            ],
            'hais' => [
                ['label' => 'Plebitis', 'val' => '4,2‰', 'target' => '< 5‰', 'status' => 'good'],
                ['label' => 'Infeksi Saluran Kemih (ISK)', 'val' => '3,1‰', 'target' => '< 4,7‰', 'status' => 'good'],
                ['label' => 'Infeksi Daerah Operasi (IDO)', 'val' => '1,8%', 'target' => '< 2%', 'status' => 'good'],
                ['label' => 'Dekubitus', 'val' => '0,9‰', 'target' => '< 1,5‰', 'status' => 'good'],
            ],
            'kepatuhan' => [
                ['label' => 'Kepatuhan Kebersihan Tangan', 'val' => '89%', 'target' => '≥ 85%', 'status' => 'good'],
                ['label' => 'Kepatuhan Identifikasi Pasien', 'val' => '99,2%', 'target' => '100%', 'status' => 'warn'],
                ['label' => 'Readmisi 30 Hari', 'val' => '4,1%', 'target' => '< 5%', 'status' => 'good'],
            ],
            'insiden' => [
                ['label' => 'KTD — Kejadian Tidak Diharapkan', 'val' => 1],
                ['label' => 'KNC — Kejadian Nyaris Cedera', 'val' => 2],
                ['label' => 'KPC — Kejadian Potensial Cedera', 'val' => 4],
                ['label' => 'Sentinel', 'val' => 0],
            ],
            'komplain' => [
                ['label' => 'Waktu Tunggu', 'val' => 38],
                ['label' => 'Kebersihan', 'val' => 22],
                ['label' => 'Keramahan Petugas', 'val' => 18],
                ['label' => 'Biaya', 'val' => 14],
                ['label' => 'Lainnya', 'val' => 8],
            ],
        ];
    }

    public function getKeuanganData(): array
    {
        $rupiah = fn (float $miliar): string => 'Rp '.number_format($miliar, 2, ',', '.').' M';

        $revenueByLine = [
            ['label' => 'Rawat Inap', 'val' => 1.92],
            ['label' => 'Rawat Jalan', 'val' => 1.14],
            ['label' => 'Penunjang (Lab/Radiologi)', 'val' => 0.71],
            ['label' => 'IGD', 'val' => 0.68],
            ['label' => 'Farmasi', 'val' => 0.37],
        ];

        foreach ($revenueByLine as &$line) {
            $line['val'] = round($line['val'] * $this->scale, 2);
            $line['fmt'] = $rupiah($line['val']);
        }
        unset($line);

        return [
            'pendapatan_bulan_berjalan' => round(4.82 * $this->scale, 2),
            'pendapatan_target' => round(5.50 * $this->scale, 2),
            'bopo' => 82.4,
            'revenue_by_line' => $revenueByLine,
            'piutang_aging' => [
                ['label' => '0–30 hari', 'val' => 640, 'status' => 'good'],
                ['label' => '31–60 hari', 'val' => 290, 'status' => 'good'],
                ['label' => '61–90 hari', 'val' => 180, 'status' => 'warn'],
                ['label' => '>90 hari', 'val' => 310, 'status' => 'crit'],
            ],
            'klaim_bpjs' => [
                ['label' => 'Diajukan', 'val' => '1.240 klaim', 'status' => 'good'],
                ['label' => 'Disetujui (Layak)', 'val' => '1.050 klaim (84,7%)', 'status' => 'good'],
                ['label' => 'Pending Verifikasi', 'val' => '140 klaim', 'status' => 'warn'],
                ['label' => 'Dispute / Perlu Koreksi', 'val' => '50 klaim', 'status' => 'serious'],
            ],
        ];
    }

    public function getSdmData(): array
    {
        return [
            'staff_counts' => [
                ['label' => 'Dokter Spesialis', 'val' => 24],
                ['label' => 'Dokter Umum', 'val' => 18],
                ['label' => 'Perawat', 'val' => 186],
                ['label' => 'Bidan', 'val' => 22],
                ['label' => 'Penunjang Medis', 'val' => 64],
                ['label' => 'Non-Medis', 'val' => 58],
            ],
            'nurse_ratio' => [
                ['unit' => 'Rawat Inap Umum — Pagi', 'val' => '1 : 8', 'target' => 'Standar 1:8–10', 'status' => 'good'],
                ['unit' => 'Rawat Inap Umum — Siang', 'val' => '1 : 9', 'target' => 'Standar 1:8–10', 'status' => 'good'],
                ['unit' => 'Rawat Inap Umum — Malam', 'val' => '1 : 10', 'target' => 'Standar 1:8–10', 'status' => 'good'],
                ['unit' => 'ICU — Semua Shift', 'val' => '1 : 1,5', 'target' => 'Standar 1:1–2', 'status' => 'good'],
            ],
            'metrics' => [
                ['label' => 'Tingkat Kehadiran', 'val' => '96,8%', 'target' => '≥ 95%', 'status' => 'good'],
                ['label' => 'Turnover Staf (tahunan)', 'val' => '6,8%', 'target' => '≤ 10%', 'status' => 'good'],
                ['label' => 'Pemenuhan Jadwal Jaga', 'val' => '98,2%', 'target' => '100%', 'status' => 'warn'],
                ['label' => 'Kepatuhan Pelatihan Wajib (K3RS/BHD/PPI)', 'val' => '87%', 'target' => '100%', 'status' => 'warn'],
            ],
        ];
    }

    public function getKonversiData(): array
    {
        return [
            ['group' => 'Admisi & Rujukan Internal', 'items' => [
                ['label' => 'OPD → IPD', 'val' => 8.2],
                ['label' => 'ER → IPD', 'val' => 21.5],
                ['label' => 'ER → OT', 'val' => 6.4],
                ['label' => 'MCU → OPD', 'val' => 11.4],
            ]],
            ['group' => 'Pemanfaatan Penunjang', 'items' => [
                ['label' => 'OPD/IPD → Farmasi', 'val' => 88.0],
                ['label' => 'OPD/IPD → Laboratorium', 'val' => 42.3],
                ['label' => 'OPD/IPD → Radiologi', 'val' => 19.1],
            ]],
        ];
    }

    public function getRingkasanExtras(): array
    {
        return [
            'bsc' => [
                'keuangan' => ['title' => 'Perspektif Keuangan', 'rows' => [
                    ['label' => 'Pendapatan Bulan Berjalan', 'val' => 'Rp 4,82 M', 'status' => 'warn'],
                    ['label' => 'Rasio Biaya Operasional (BOPO)', 'val' => '82,4%', 'status' => 'good'],
                    ['label' => 'Piutang BPJS >90 Hari', 'val' => 'Rp 310 jt', 'status' => 'warn'],
                ]],
                'pelanggan' => ['title' => 'Perspektif Pelanggan', 'rows' => [
                    ['label' => 'Indeks Kepuasan Pasien', 'val' => '88,2 / 100', 'status' => 'good'],
                    ['label' => 'Komplain Selesai <3 Hari', 'val' => '92%', 'status' => 'good'],
                    ['label' => 'Kunjungan Bulan Ini', 'val' => '8.420 (+6,2%)', 'status' => 'good'],
                ]],
                'proses' => ['title' => 'Perspektif Proses Bisnis Internal', 'rows' => [
                    ['label' => 'BOR', 'val' => '73,6%', 'status' => 'good'],
                    ['label' => 'Kepatuhan Clinical Pathway', 'val' => '91%', 'status' => 'good'],
                    ['label' => 'Insiden Keselamatan Pasien', 'val' => '3 kejadian', 'status' => 'warn'],
                ]],
                'sdm' => ['title' => 'Perspektif Pembelajaran & Pertumbuhan', 'rows' => [
                    ['label' => 'Rasio Perawat : Pasien (Ranap)', 'val' => '1 : 8', 'status' => 'good'],
                    ['label' => 'Kepatuhan Pelatihan Wajib', 'val' => '87%', 'status' => 'warn'],
                    ['label' => 'Turnover Staf (tahunan)', 'val' => '6,8%', 'status' => 'good'],
                ]],
            ],
            'payer_mix' => [
                ['label' => 'BPJS Kesehatan', 'val' => 68, 'color' => 'var(--accent)'],
                ['label' => 'Asuransi Swasta', 'val' => 14, 'color' => 'var(--brand-magenta)'],
                ['label' => 'Umum / Pribadi', 'val' => 18, 'color' => 'var(--muted)'],
            ],
            'top_diagnosa' => [
                ['label' => 'Demam Berdarah Dengue', 'val' => 142],
                ['label' => 'Diabetes Melitus Tipe 2', 'val' => 118],
                ['label' => 'Hipertensi', 'val' => 104],
                ['label' => 'ISPA', 'val' => 97],
                ['label' => 'Gastroenteritis Akut', 'val' => 88],
                ['label' => 'Dispepsia', 'val' => 74],
                ['label' => 'Pneumonia', 'val' => 61],
                ['label' => 'Anemia', 'val' => 53],
                ['label' => 'Cedera Kepala Ringan', 'val' => 47],
                ['label' => 'Observasi Febris', 'val' => 41],
            ],
            'unit_status' => [
                ['name' => 'IGD', 'status' => 'good', 'note' => 'Respons time sesuai target'],
                ['name' => 'Rawat Inap', 'status' => 'good', 'note' => 'BOR dalam rentang ideal'],
                ['name' => 'ICU', 'status' => 'warn', 'note' => 'Okupansi 95%, mendekati kapasitas'],
                ['name' => 'Kamar Operasi', 'status' => 'good', 'note' => 'Utilisasi 82%, terjadwal baik'],
                ['name' => 'Farmasi', 'status' => 'warn', 'note' => '3 item stok kritis menipis'],
                ['name' => 'Laboratorium', 'status' => 'good', 'note' => 'TAT sesuai target'],
                ['name' => 'Radiologi', 'status' => 'good', 'note' => 'TAT sesuai target'],
                ['name' => 'Rawat Jalan', 'status' => 'good', 'note' => 'Waktu tunggu sesuai target'],
            ],
        ];
    }

    public function getRevenueTrend(int $days): array
    {
        mt_srand(20260905 + $this->seedOffset);
        // Rp 4,82 M bulan berjalan ÷ 30 hari ≈ rata-rata harian, dengan sedikit noise.
        $dailyAvg = (4.82 * $this->scale) / 30;
        $trend = [];

        for ($i = 0; $i < $days; $i++) {
            $noise = (mt_rand(-30, 30) / 100) * $dailyAvg;
            $trend[] = round(max(0.05, $dailyAvg + $noise), 3);
        }

        return $trend;
    }

    public function getPerluPerhatian(): array
    {
        return [
            ['kategori' => 'Keuangan', 'label' => 'Pendapatan Bulan Berjalan', 'nilai' => '87,6% dari target', 'status' => 'warn'],
            ['kategori' => 'Keuangan', 'label' => 'Piutang BPJS &gt;90 Hari', 'nilai' => 'Rp 310 jt', 'status' => 'warn'],
            ['kategori' => 'Operasional', 'label' => 'Discharge Time', 'nilai' => '13:42 (target &lt;12:00)', 'status' => 'warn'],
            ['kategori' => 'ICU', 'label' => 'Okupansi Tempat Tidur', 'nilai' => '95%, mendekati kapasitas', 'status' => 'warn'],
            ['kategori' => 'Farmasi', 'label' => 'Stok Obat', 'nilai' => '3 item stok kritis menipis', 'status' => 'warn'],
            ['kategori' => 'Mutu', 'label' => 'Kepatuhan Identifikasi Pasien', 'nilai' => '99,2% (target 100%)', 'status' => 'warn'],
            ['kategori' => 'SDM', 'label' => 'Pemenuhan Jadwal Jaga', 'nilai' => '98,2% (target 100%)', 'status' => 'warn'],
            ['kategori' => 'SDM', 'label' => 'Kepatuhan Pelatihan Wajib', 'nilai' => '87% (target 100%)', 'status' => 'warn'],
        ];
    }
}
