<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Ringkasan Eksekutif</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Empat perspektif kinerja RS dalam satu pandangan.</div>

    <div class="bsc-grid">
        @foreach($bsc as $perspective)
            <x-dashboard.panel>
                <div class="bsc-eyebrow">{{ $perspective['title'] }}</div>
                @foreach($perspective['rows'] as $row)
                    <div class="stat-row">
                        <div class="stat-row-label">{{ $row['label'] }}</div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="stat-row-val num tabular">{{ $row['val'] }}</span>
                            <span class="chip {{ $row['status'] }}" style="padding:2px 6px;" title="{{ $row['status'] }}">
                                {{ ['good' => '▲', 'warn' => '●', 'serious' => '●', 'crit' => '▼'][$row['status']] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </x-dashboard.panel>
        @endforeach
    </div>

    <div class="split-2">
        <x-dashboard.panel title="Payer Mix — Bulan Ini">
            <div class="donut-wrap">
                {!! \App\Support\ChartSvg::donut($payerMix, 118) !!}
                <div class="donut-legend">
                    @foreach($payerMix as $p)
                        <div class="donut-legend-item"><span class="donut-dot" style="background:{{ $p['color'] }}"></span> {{ $p['label'] }} — <b>{{ $p['val'] }}%</b></div>
                    @endforeach
                </div>
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="10 Besar Diagnosa — Rawat Inap">
            <x-dashboard.bar-list :items="collect($topDiagnosa)->map(fn($d) => ['label' => $d['label'], 'val' => $d['val'], 'fmt' => $d['val'].' kasus'])->all()" />
        </x-dashboard.panel>
    </div>

    <x-dashboard.panel title="Status Unit — Ringkasan Operasional">
        <div class="rag-grid">
            @foreach($unitStatus as $u)
                <div class="rag-card {{ $u['status'] }}">
                    <div class="rag-name">{{ $u['name'] }}</div>
                    <div class="rag-note">{{ $u['note'] }}</div>
                </div>
            @endforeach
        </div>
    </x-dashboard.panel>
</div>
