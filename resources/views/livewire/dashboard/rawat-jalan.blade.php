@php
    $pasienBaru = (int) round($data['total_kunjungan'] * ($data['pasien_baru_pct'] / 100));
@endphp
<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Rawat Jalan</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Kunjungan poli hari ini dan poli mana yang paling sibuk.</div>

    <div class="kpi-grid">
        <div class="kpi-tile">
            <div class="kpi-label">Kunjungan Hari Ini</div>
            <div class="kpi-value-row"><span class="kpi-value num tabular">{{ $data['total_kunjungan'] }}</span><span class="kpi-unit">pasien</span></div>
            <div class="kpi-target">Rawat jalan, semua poli</div>
        </div>
        <div class="kpi-tile">
            <div class="kpi-label">Pasien Baru</div>
            <div class="kpi-value-row"><span class="kpi-value num tabular">{{ $pasienBaru }}</span><span class="kpi-unit">pasien</span></div>
            <div class="kpi-target">{{ number_format($data['pasien_baru_pct'], 1, ',', '.') }}% dari total kunjungan hari ini</div>
        </div>
        <x-dashboard.kpi-tile label="Waktu Tunggu Rata-rata" :value="$data['waktu_tunggu']['value']" :target="'Target '.$data['waktu_tunggu']['target']" :status="$data['waktu_tunggu']['status']" />
        <x-dashboard.kpi-tile label="Tingkat Tidak Hadir (No-Show)" :value="$data['no_show_rate']['value']" :target="'Target '.$data['no_show_rate']['target']" :status="$data['no_show_rate']['status']" />
    </div>

    <div class="two-col">
        <x-dashboard.panel title="5 Poli dengan Kunjungan Terbanyak">
            <x-dashboard.bar-list :items="collect($data['top_poli'])->map(fn($p) => ['label' => $p['label'], 'val' => $p['val'], 'fmt' => $p['val'].' pasien'])->all()" />
        </x-dashboard.panel>

        <x-dashboard.panel title="Jam Sibuk — Distribusi Kunjungan">
            <x-dashboard.bar-list :items="collect($data['jam_sibuk'])->map(fn($j) => ['label' => $j['label'], 'val' => $j['val'], 'fmt' => $j['val'].' pasien'])->all()" />
            <div class="chart-foot">Puncak kunjungan jam 09:00–10:00 — jadwal jaga pendaftaran &amp; kasir bisa ditambah di jam ini.</div>
        </x-dashboard.panel>
    </div>

    <x-dashboard.panel title="Dokter Praktik Hari Ini">
        <table class="dtable">
            <thead><tr><th>Poli</th><th>Dokter</th><th>Jam Praktik</th><th>Pasien Terlayani</th></tr></thead>
            <tbody>
                @foreach($data['dokter_praktik'] as $d)
                    <tr>
                        <td class="strong">{{ $d['poli'] }}</td>
                        <td>{{ $d['dokter'] }}</td>
                        <td class="num tabular">{{ $d['jam'] }}</td>
                        <td class="num tabular">{{ $d['pasien'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dashboard.panel>
</div>
