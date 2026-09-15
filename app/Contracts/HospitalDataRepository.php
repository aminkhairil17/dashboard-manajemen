<?php

namespace App\Contracts;

use Carbon\CarbonImmutable;

/**
 * Satu titik integrasi ke sumber data rumah sakit.
 *
 * Implementasi saat ini (MockHospitalDataRepository) memakai data contoh.
 * Begitu akses & skema SIMRS GOS milik RS tersedia, buat SimrsGosRepository
 * yang mengimplementasikan interface ini dan ganti binding-nya di
 * HospitalDataServiceProvider — tanpa perlu mengubah service/komponen lain.
 */
interface HospitalDataRepository
{
    /**
     * Data sensus tempat tidur mentah untuk satu periode, dipakai
     * KpiCalculationService menghitung BOR, LOS, BTO, dan TOI.
     *
     * @return array{total_beds:int, patient_days:float, discharges:int, period_days:int}
     */
    public function getBedCensus(CarbonImmutable $start, CarbonImmutable $end): array;

    /**
     * Tren BOR harian, dipakai grafik "Tren BOR — 30 hari terakhir".
     *
     * @return array<int, array{date:string, bor:float}>
     */
    public function getBorTrend(CarbonImmutable $end, int $days): array;

    /**
     * Titik bulanan (TOI, LOS) untuk Grafik Barber-Johnson.
     *
     * @return array<int, array{month:string, toi:float, los:float}>
     */
    public function getBarberJohnsonPoints(): array;

    /** KPI tambahan halaman Operasional: boarding/discharge time, new patient. */
    public function getOperationalExtras(): array;

    /**
     * Ketersediaan kamar/tempat tidur saat ini per kelas: total, terisi, kosong, perbaikan.
     *
     * @return array<int, array{kelas:string, total:int, terisi:int, kosong:int, perbaikan:int}>
     */
    public function getRoomAvailability(): array;

    /** Data halaman Rawat Jalan: kunjungan hari ini, poli terbanyak, jam sibuk, dokter praktik. */
    public function getRawatJalanData(): array;

    /** Data halaman Efisiensi Layanan: TAT, utilisasi OT, waktu tunggu. */
    public function getEfisiensiData(): array;

    /** Data halaman Mutu & Keselamatan Pasien. */
    public function getMutuData(): array;

    /** Data halaman Keuangan. */
    public function getKeuanganData(): array;

    /** Data halaman SDM. */
    public function getSdmData(): array;

    /** Data halaman Conversion Rate. */
    public function getKonversiData(): array;

    /** Data pelengkap Ringkasan Eksekutif: BSC, payer mix, top diagnosa, status unit. */
    public function getRingkasanExtras(): array;

    /**
     * Tren pendapatan harian (30 hari) vs target, dipakai grafik TV Ruangan Direktur.
     *
     * @return array<int, float>
     */
    public function getRevenueTrend(int $days): array;

    /**
     * Daftar item berstatus warn/crit dari semua kategori (keuangan, mutu, SDM,
     * operasional), dipakai panel "Perlu Perhatian" di TV Ruangan Direktur.
     */
    public function getPerluPerhatian(): array;
}
