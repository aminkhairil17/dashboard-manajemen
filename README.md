# Dashboard Manajemen RS Syifa Medika

Dashboard KPI untuk manajemen/direksi RS Syifa Medika — Ringkasan Eksekutif (Balanced Scorecard), Operasional (BOR/LOS/BTO/TOI, Grafik Barber-Johnson), Efisiensi Layanan, Mutu & Keselamatan Pasien, Keuangan, SDM, Conversion Rate, Kamus Istilah, dan mode TV Kiosk tanpa login.

<!-- deploy test: 2026-09-15 -->

## Menjalankan secara lokal

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Buat database MySQL kosong bernama sesuai `DB_DATABASE` di `.env` (default `dashboard_manajemen`), lalu:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

Login dengan akun yang dibuat seeder: `direktur@syifamedika.id` / `password`.

## Arsitektur data — penting untuk dibaca sebelum mengubah sumber data

Seluruh angka di dashboard saat ini berasal dari `App\Repositories\MockHospitalDataRepository` — data contoh yang realistis, **belum** terhubung ke SIMRS GOS. Ini sengaja dipisah lewat interface `App\Contracts\HospitalDataRepository` supaya sumber data bisa diganti tanpa mengubah halaman/komponen manapun:

1. Buat class baru, mis. `App\Repositories\SimrsGosRepository`, yang mengimplementasikan `HospitalDataRepository`.
2. Ganti binding di `App\Providers\HospitalDataServiceProvider` dari `MockHospitalDataRepository::class` ke `SimrsGosRepository::class`.

Rumus KPI efisiensi tempat tidur (BOR, LOS, BTO, TOI) dihitung oleh `App\Services\KpiCalculationService` dari data sensus mentah (`getBedCensus()`), bukan angka jadi — sudah ada unit test-nya di `tests/Unit/KpiCalculationServiceTest.php`.

### Yang perlu diminta ke tim IT RS untuk integrasi SIMRS GOS

- Host, port, nama database, dan **user MySQL read-only** untuk SIMRS GOS (isi di `.env` sebagai `SIMRS_DB_*` — koneksi keduanya sudah disiapkan di `config/database.php` sebagai koneksi `simrs`, belum dipakai kode manapun).
- Nama tabel & kolom untuk: kunjungan/registrasi pasien, sensus rawat inap (tanggal masuk/keluar, kelas/ruang, tempat tidur), data kamar operasi/tindakan, order laboratorium & radiologi (waktu order vs waktu hasil), resep farmasi (waktu terima vs waktu obat siap), data kematian pasien, dan data klaim BPJS.
- Konfirmasi jumlah tempat tidur aktif per unit (dipakai untuk BOR/BTO/TOI) — saat ini di-hardcode 120 di `MockHospitalDataRepository`.

## Mode TV Kiosk

Halaman `/tv/{token}` sengaja **tanpa** middleware auth — proteksinya lewat token acak yang dibuat/dicabut dari menu "Link TV Kiosk" di dashboard (`App\Models\TvDisplayToken`). Halaman ini hanya menampilkan indikator publik (Operasional, Efisiensi Layanan, Mutu & Keselamatan, Conversion Rate); Ringkasan Eksekutif, Keuangan, dan SDM sengaja tidak ditampilkan karena memuat data internal.

## Snapshot KPI harian

```bash
php artisan kpi:snapshot
```

Menghitung BOR/LOS/BTO/TOI hari berjalan dan menyimpannya ke tabel `kpi_snapshots` (dijadwalkan otomatis tiap hari jam 06:00 lewat `routes/console.php` — jalankan `php artisan schedule:work` saat development untuk mengetesnya, atau pasang cron `* * * * * php artisan schedule:run` di production).
