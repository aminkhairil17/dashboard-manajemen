<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Operasional</div>
    <div class="app-page-meta" style="margin-bottom:18px;">BOR, LOS, dan perputaran tempat tidur untuk 30 hari terakhir.</div>

    <div class="kpi-grid">
        @foreach($kpis as $k)
            <x-dashboard.kpi-tile :label="$k['label']" :value="$k['disp']" :unit="$k['unit']" :target="$k['target']" :status="$k['status']" :trend="$k['trend']" />
        @endforeach
    </div>

    <div class="two-col">
        <x-dashboard.panel title="Tren BOR — 30 hari terakhir">
            <x-slot:legend>
                <div class="panel-legend">
                    <span class="legend-item"><span class="legend-swatch"></span> BOR harian</span>
                    <span class="legend-item"><span class="legend-swatch band"></span> Target 60–85%</span>
                </div>
            </x-slot:legend>
            {!! \App\Support\ChartSvg::trend($borTrend, 'var(--accent)', 55, 95, [60, 85], number_format(end($borTrend), 1, ',', '.').'%') !!}
        </x-dashboard.panel>

        <x-dashboard.panel title="Grafik Barber-Johnson">
            <x-slot:legend>
                <div class="panel-legend"><span class="legend-item"><span class="legend-swatch band"></span> Zona ideal</span></div>
            </x-slot:legend>
            {!! \App\Support\ChartSvg::barberJohnson($barberJohnson, 'var(--accent)') !!}
            <div class="chart-foot">Standar Kemenkes/Barber-Johnson: BOR 70–85%, LOS 3–12 hari, TOI 1–3 hari, BTO ≥30x/tahun. Titik bulanan bergerak masuk ke zona ideal sejak April.</div>
        </x-dashboard.panel>
    </div>

    <x-dashboard.panel title="Ketersediaan Kamar / Tempat Tidur">
        <x-slot:legend>
            <div class="panel-legend">
                <span class="legend-item"><span class="legend-dot" style="background:var(--accent);"></span> Terisi</span>
                <span class="legend-item"><span class="legend-dot" style="background:color-mix(in srgb, var(--ink-2) 22%, transparent);"></span> Kosong</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--warn);"></span> Perbaikan</span>
            </div>
        </x-slot:legend>
        <table class="dtable">
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th style="min-width:140px;">Ketersediaan</th>
                    <th>Terisi</th>
                    <th>Kosong</th>
                    <th>Perbaikan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $r)
                    <tr>
                        <td class="strong">{{ $r['kelas'] }} <span style="color:var(--muted); font-weight:400;">({{ $r['total'] }})</span></td>
                        <td>
                            <div class="room-stack">
                                <span class="room-seg terisi" style="width:{{ $r['total'] ? round($r['terisi'] / $r['total'] * 100) : 0 }}%"></span>
                                <span class="room-seg kosong" style="width:{{ $r['total'] ? round($r['kosong'] / $r['total'] * 100) : 0 }}%"></span>
                                <span class="room-seg perbaikan" style="width:{{ $r['total'] ? round($r['perbaikan'] / $r['total'] * 100) : 0 }}%"></span>
                            </div>
                        </td>
                        <td class="num tabular">{{ $r['terisi'] }}</td>
                        <td class="num tabular">{{ $r['kosong'] }}</td>
                        <td class="num tabular">{{ $r['perbaikan'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="strong">Total ({{ $roomTotals['total'] }})</td>
                    <td>
                        <div class="room-stack">
                            <span class="room-seg terisi" style="width:{{ round($roomTotals['terisi'] / $roomTotals['total'] * 100) }}%"></span>
                            <span class="room-seg kosong" style="width:{{ round($roomTotals['kosong'] / $roomTotals['total'] * 100) }}%"></span>
                            <span class="room-seg perbaikan" style="width:{{ round($roomTotals['perbaikan'] / $roomTotals['total'] * 100) }}%"></span>
                        </div>
                    </td>
                    <td class="num tabular strong">{{ $roomTotals['terisi'] }}</td>
                    <td class="num tabular strong">{{ $roomTotals['kosong'] }}</td>
                    <td class="num tabular strong">{{ $roomTotals['perbaikan'] }}</td>
                </tr>
            </tbody>
        </table>
        <div class="chart-foot">Posisi kamar saat ini (snapshot per jenis kelas) — berbeda dari BOR yang dihitung dari rata-rata hari rawat 30 hari terakhir, jadi wajar kalau persentasenya sedikit berbeda.</div>
    </x-dashboard.panel>
</div>
