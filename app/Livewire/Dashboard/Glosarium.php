<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Url;
use Livewire\Component;

class Glosarium extends Component
{
    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function render()
    {
        $sections = $this->allSections();
        $q = trim(mb_strtolower($this->search));

        if ($q !== '') {
            $sections = collect($sections)->map(function ($section) use ($q) {
                $section['terms'] = array_values(array_filter($section['terms'], function ($term) use ($q) {
                    $haystack = mb_strtolower($term['name'].' '.($term['abbr'] ?? '').' '.$term['def']);

                    return str_contains($haystack, $q);
                }));

                return $section;
            })->filter(fn ($s) => count($s['terms']) > 0)->values()->all();
        }

        $resultCount = collect($sections)->sum(fn ($s) => count($s['terms']));

        return view('livewire.dashboard.glosarium', [
            'sections' => $sections,
            'resultCount' => $resultCount,
            'allCategories' => collect($this->allSections())->map(fn ($s) => ['cat' => $s['cat'], 'title' => $s['title']])->all(),
        ])->layout('layouts.app');
    }

    private function allSections(): array
    {
        return [
            ['cat' => 'ringkasan', 'title' => 'Ringkasan Eksekutif', 'desc' => 'Cara direktur melihat seluruh kinerja RS sekaligus dalam satu halaman.', 'terms' => [
                ['name' => 'Balanced Scorecard (BSC)', 'def' => 'Kerangka pengukuran kinerja yang menilai organisasi dari 4 sudut pandang sekaligus — Keuangan, Pelanggan, Proses Bisnis Internal, dan Pembelajaran & Pertumbuhan — bukan cuma untung-rugi semata.', 'example' => 'Dipakai direktur di rapat manajemen bulanan supaya diskusi tidak melulu soal uang, tapi juga soal kualitas layanan, kepuasan pasien, dan kesiapan SDM secara berimbang.'],
                ['name' => 'Payer Mix', 'def' => 'Komposisi sumber pembayaran pasien: BPJS Kesehatan, asuransi swasta, atau umum/pribadi.', 'example' => 'Kalau BPJS terlalu dominan (>70%), direktur bisa dorong strategi menambah pasien asuransi/umum supaya arus kas lebih sehat, karena klaim BPJS cair lebih lambat.'],
                ['name' => '10 Besar Diagnosa', 'def' => 'Daftar penyakit dengan jumlah kasus rawat inap terbanyak dalam suatu periode.', 'example' => 'Kalau DBD naik drastis di suatu bulan, direktur bisa siapkan tambahan tempat tidur & kampanye pencegahan ke masyarakat sekitar RS.'],
                ['name' => 'Status Unit (RAG)', 'abbr' => 'Red - Amber - Green', 'def' => 'Ringkasan visual kondisi tiap unit kerja dalam 3 warna: hijau (baik), kuning (perlu perhatian), merah (kritis).', 'example' => 'Dipakai untuk rapat pagi 5 menit — direktur langsung tahu unit mana yang perlu perhatian hari itu tanpa membaca laporan panjang.'],
            ]],
            ['cat' => 'operasional', 'title' => 'Operasional', 'desc' => 'Efisiensi pemakaian tempat tidur dan alur pasien rawat inap.', 'terms' => [
                ['name' => 'BOR', 'abbr' => 'Bed Occupancy Ratio — Angka Penggunaan Tempat Tidur', 'def' => 'Persentase tempat tidur yang terisi pasien dibanding total tempat tidur tersedia, dalam periode tertentu.', 'formula' => '(Hari rawat ÷ (Jml TT × Jml hari periode)) × 100%', 'standard' => 'Ideal 60–85%', 'example' => 'BOR terlalu rendah berarti banyak TT menganggur (boros biaya); BOR terlalu tinggi berisiko kekurangan tempat saat lonjakan pasien.'],
                ['name' => 'LOS', 'abbr' => 'Length of Stay — Rata-rata Lama Rawat', 'def' => 'Rata-rata jumlah hari seorang pasien dirawat inap sampai pulang.', 'standard' => 'Ideal 3–12 hari', 'example' => 'LOS yang memanjang bisa jadi tanda proses klinis lambat atau menunggu hasil penunjang — direktur bisa minta audit ke komite medik.'],
                ['name' => 'BTO', 'abbr' => 'Bed Turn Over — Angka Perputaran Tempat Tidur', 'def' => 'Berapa kali satu tempat tidur dipakai bergantian oleh pasien berbeda dalam periode tertentu.', 'standard' => 'Minimal 30x/tahun', 'example' => 'Dipakai menilai efisiensi pemakaian aset tempat tidur — makin sering berputar (dengan LOS tetap wajar) makin efisien.'],
                ['name' => 'TOI', 'abbr' => 'Turn Over Interval — Tenggang Perputaran TT', 'def' => 'Rata-rata hari tempat tidur "kosong" antara satu pasien pulang sampai pasien berikutnya masuk.', 'standard' => 'Ideal 1–3 hari', 'example' => 'TOI yang kepanjangan menandakan keterlambatan proses admisi atau pembersihan kamar yang perlu dibenahi.'],
                ['name' => 'Grafik Barber-Johnson', 'def' => 'Grafik kuadran yang memetakan BOR, LOS, TOI, dan BTO sekaligus dalam satu visual untuk menilai efisiensi pengelolaan tempat tidur secara menyeluruh.', 'example' => 'Dipakai bagian rekam medis untuk laporan bulanan ke direktur — posisi titik di dalam atau di luar "zona ideal" langsung menunjukkan RS efisien atau tidak.'],
                ['name' => 'Boarding Time', 'def' => 'Waktu tunggu pasien IGD sejak diputuskan perlu rawat inap sampai benar-benar pindah ke ruang rawat.', 'standard' => 'Target ≤120 menit', 'example' => 'Boarding time lama menandakan bottleneck kapasitas ruang rawat — direktur bisa evaluasi ulang alur discharge planning.'],
                ['name' => 'Discharge Time', 'def' => 'Jam rata-rata pasien resmi keluar/pulang dari ruang rawat inap.', 'example' => 'Kalau rata-rata jam pulang terlalu siang, tempat tidur baru bisa dipakai pasien berikutnya lebih lambat — memperlambat seluruh alur rawat inap hari itu.'],
                ['name' => 'New Patient Proportion', 'abbr' => 'Proporsi Pasien Baru', 'def' => 'Persentase pasien baru dibanding total kunjungan pada suatu periode.', 'example' => 'Indikator pertumbuhan RS — kalau proporsinya terus turun, bisa jadi sinyal awal ada masalah citra/kualitas yang perlu ditelusuri tim marketing.'],
            ]],
            ['cat' => 'efisiensi', 'title' => 'Efisiensi Layanan', 'desc' => 'Kecepatan proses penunjang medis dan pemanfaatan kamar operasi.', 'terms' => [
                ['name' => 'TAT', 'abbr' => 'Turn Around Time', 'def' => 'Waktu proses dari permintaan diajukan sampai hasil selesai — dipakai untuk layanan Farmasi, Laboratorium, dan Radiologi.', 'example' => 'TAT laboratorium yang lambat membuat dokter menunggu lebih lama untuk memutuskan terapi, yang akhirnya ikut memperpanjang LOS pasien.'],
                ['name' => 'OT Room Management', 'abbr' => 'Utilisasi Kamar Operasi', 'def' => 'Persentase jam operasional kamar operasi yang benar-benar terpakai untuk tindakan, dibanding jam yang tersedia.', 'example' => 'Utilisasi rendah berarti jadwal kamar operasi bisa dipadatkan lagi supaya lebih banyak tindakan elektif terlayani tanpa perlu menambah kamar baru.'],
                ['name' => 'Waktu Tunggu per Unit', 'def' => 'Rata-rata waktu tunggu pasien di tiap titik layanan (IGD, poli rawat jalan, MCU, dll).', 'standard' => 'Mengacu SPM Kemenkes', 'example' => 'Dipantau rutin untuk memastikan RS tetap patuh terhadap Standar Pelayanan Minimal — sekaligus jadi penyebab komplain pasien yang paling sering.'],
            ]],
            ['cat' => 'mutu', 'title' => 'Mutu & Keselamatan Pasien', 'desc' => 'Indikator wajib akreditasi KARS terkait keselamatan dan kepuasan pasien.', 'terms' => [
                ['name' => 'GDR', 'abbr' => 'Gross Death Rate — Angka Kematian Umum', 'def' => 'Jumlah pasien meninggal per 1000 pasien keluar (hidup maupun meninggal), termasuk yang meninggal dalam <48 jam perawatan.', 'standard' => '≤ 45‰', 'example' => 'Dipantau sebagai indikator umum mutu pelayanan RS secara keseluruhan, dilaporkan rutin ke Kemenkes/KARS.'],
                ['name' => 'NDR', 'abbr' => 'Net Death Rate — Angka Kematian Bersih', 'def' => 'Jumlah pasien meninggal ≥48 jam setelah dirawat per 1000 pasien keluar — lebih mencerminkan kualitas perawatan.', 'standard' => '≤ 25‰', 'example' => 'NDR yang tinggi memicu direktur meminta audit medik kasus-per-kasus ke komite medik untuk mencari akar masalah.'],
                ['name' => 'HAIs', 'abbr' => 'Healthcare-Associated Infections', 'def' => 'Infeksi yang didapat pasien selama dirawat, bukan dari sebelum masuk RS: Plebitis, ISK, IDO, dan Dekubitus.', 'example' => 'Dipantau ketat oleh Komite PPI karena jadi salah satu indikator utama penilaian akreditasi KARS.'],
                ['name' => 'Kepatuhan Kebersihan Tangan', 'abbr' => 'Hand Hygiene Compliance', 'def' => 'Persentase petugas yang benar-benar cuci tangan sesuai 5 momen standar WHO saat diaudit langsung.', 'standard' => '≥ 85%', 'example' => 'Angka yang rendah di sini biasanya berkorelasi langsung dengan naiknya kasus HAIs bulan berikutnya.'],
                ['name' => 'Kepatuhan Identifikasi Pasien', 'def' => 'Persentase proses identifikasi pasien (nama + tanggal lahir/no. RM) yang dilakukan benar sebelum tindakan.', 'standard' => 'Target 100%', 'example' => 'Indikator "zero tolerance" — kesalahan identifikasi bisa berujung salah obat atau salah tindakan.'],
                ['name' => 'Readmisi 30 Hari', 'def' => 'Persentase pasien yang harus dirawat inap kembali dalam 30 hari setelah pulang, dengan diagnosa yang berkaitan.', 'standard' => '< 5%', 'example' => 'Readmisi tinggi bisa menandakan pasien dipulangkan terlalu dini atau edukasi pulang yang kurang matang.'],
                ['name' => 'KTD / KNC / KPC / Sentinel', 'abbr' => 'Insiden Keselamatan Pasien', 'def' => 'KTD = sudah terjadi & merugikan pasien. KNC = hampir terjadi, keburu dicegah. KPC = kondisi berisiko sebelum insiden. Sentinel = insiden serius berujung kematian/cacat permanen.', 'example' => 'Dipakai Komite Mutu & Keselamatan Pasien untuk root cause analysis (RCA), bukan mencari siapa yang salah.'],
                ['name' => 'IKM', 'abbr' => 'Indeks Kepuasan Masyarakat/Pasien', 'def' => 'Skor survei kepuasan pasien terhadap pelayanan RS, biasanya dalam skala 0–100.', 'example' => 'Dipakai menilai performa unit-unit yang berhadapan langsung dengan pasien, dan jadi bahan evaluasi kinerja tahunan.'],
                ['name' => 'Top Kategori Komplain', 'def' => 'Pengelompokan keluhan pasien berdasarkan penyebab yang paling sering muncul.', 'example' => 'Kalau "waktu tunggu" jadi kategori terbesar, direktur tahu prioritas perbaikan ada di alur pendaftaran/poli — bukan di kualitas medisnya.'],
            ]],
            ['cat' => 'keuangan', 'title' => 'Keuangan', 'desc' => 'Kesehatan arus kas dan performa pendapatan RS — halaman internal, tidak dipublikasikan ke TV.', 'terms' => [
                ['name' => 'Pendapatan per Lini Layanan', 'def' => 'Rincian pendapatan RS dipecah berdasarkan sumbernya: rawat inap, rawat jalan, IGD, penunjang, dan farmasi.', 'example' => 'Dipakai melihat lini mana yang paling berkontribusi, dan mana yang perlu didorong lebih jauh.'],
                ['name' => 'Piutang BPJS (Aging)', 'def' => 'Jumlah tagihan ke BPJS Kesehatan yang belum dibayar, dikelompokkan berdasarkan usia tagihan.', 'example' => 'Piutang >90 hari yang menumpuk adalah sinyal bahaya arus kas — perlu eskalasi ke tim klaim/casemix.'],
                ['name' => 'BOPO', 'abbr' => 'Rasio Biaya Operasional terhadap Pendapatan', 'def' => 'Persentase biaya operasional dibanding total pendapatan yang diterima RS.', 'example' => 'BOPO yang makin kecil berarti RS makin efisien menghasilkan laba dari setiap rupiah pendapatan.'],
                ['name' => 'Status Klaim BPJS', 'def' => 'Tahapan proses klaim: Diajukan, Disetujui/Layak, Pending Verifikasi, atau Dispute/Perlu Koreksi.', 'example' => 'Klaim berstatus "Dispute" tinggi menandakan masalah kelengkapan berkas/koding yang perlu dibenahi tim casemix.'],
            ]],
            ['cat' => 'sdm', 'title' => 'SDM', 'desc' => 'Ketenagaan, beban kerja, dan kesiapan staf — halaman internal, tidak dipublikasikan ke TV.', 'terms' => [
                ['name' => 'Rasio Perawat : Pasien', 'def' => 'Perbandingan jumlah perawat yang berjaga dengan jumlah pasien yang dirawat dalam satu shift.', 'standard' => 'Ranap umum 1:8–10, ICU 1:1–2', 'example' => 'Rasio terlalu longgar meningkatkan risiko kesalahan dan menurunkan mutu asuhan keperawatan.'],
                ['name' => 'Tingkat Kehadiran', 'def' => 'Persentase kehadiran staf sesuai jadwal kerja yang ditetapkan.', 'standard' => 'Target ≥ 95%', 'example' => 'Dipantau untuk mendeteksi dini masalah kedisiplinan atau beban kerja berlebih sebelum berdampak ke pelayanan.'],
                ['name' => 'Turnover Staf', 'def' => 'Persentase staf yang keluar/resign dalam suatu periode, biasanya dihitung tahunan.', 'standard' => 'Target ≤ 10%', 'example' => 'Turnover tinggi berarti biaya rekrutmen & pelatihan ulang membengkak, dan kualitas layanan bisa menurun.'],
                ['name' => 'Pemenuhan Jadwal Jaga', 'def' => 'Persentase slot jadwal jaga yang berhasil terisi sesuai kebutuhan unit.', 'example' => 'Kalau angka ini turun, ada risiko kekurangan tenaga di shift tertentu yang perlu solusi cepat.'],
                ['name' => 'Kepatuhan Pelatihan Wajib', 'def' => 'Persentase staf yang sudah menyelesaikan pelatihan wajib seperti K3RS, BHD, dan PPI dasar.', 'example' => 'Salah satu syarat penilaian akreditasi KARS — kalau rendah, RS berisiko temuan saat surveior datang.'],
            ]],
            ['cat' => 'konversi', 'title' => 'Conversion Rate', 'desc' => 'Perpindahan pasien antar unit layanan sebagai indikator alur klinis.', 'terms' => [
                ['name' => 'Conversion Rate antar Unit', 'def' => 'Persentase pasien yang berpindah/dirujuk dari satu unit layanan ke unit lain.', 'example' => 'OPD→IPD rendah bisa berarti kasus yang perlu rawat inap malah dipulangkan; ER→OT memantau kecukupan kapasitas kamar operasi darurat.'],
                ['name' => 'OPD / IPD / ER / OT / MCU', 'abbr' => 'Singkatan unit layanan', 'def' => 'OPD = Rawat Jalan. IPD = Rawat Inap. ER = IGD. OT = Kamar Operasi. MCU = Medical Check-Up.', 'example' => 'Dipakai konsisten di seluruh dashboard supaya laporan lintas unit mudah dibaca.'],
            ]],
            ['cat' => 'tv', 'title' => 'Mode TV Kiosk', 'desc' => 'Cara dashboard ini ditampilkan ke publik.', 'terms' => [
                ['name' => 'TV Kiosk / Mode Tanpa Login', 'def' => 'Tampilan dashboard yang dipasang permanen di layar TV area publik (lobby), berisi indikator yang wajib dipublikasikan untuk transparansi akreditasi KARS.', 'example' => 'Diakses lewat link berisi token rahasia yang dibuka sekali di browser TV — tanpa perlu mengetik username/password tiap hari lewat remote.'],
            ]],
        ];
    }
}
