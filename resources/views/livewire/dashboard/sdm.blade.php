<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">SDM</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Ketenagaan dan rasio perawat. Khusus manajemen, tidak tampil di TV.</div>

    <div class="kpi-grid compact">
        @foreach($staffCounts as $s)
            <div class="kpi-tile">
                <div class="kpi-label">{{ $s['label'] }}</div>
                <div class="kpi-value-row"><span class="kpi-value num tabular">{{ $s['val'] }}</span><span class="kpi-unit">orang</span></div>
            </div>
        @endforeach
    </div>

    <x-dashboard.panel title="Rasio Perawat : Pasien per Shift">
        <x-dashboard.indicator-table :rows="collect($nurseRatio)->map(fn($n) => ['label' => $n['unit'], 'val' => $n['val'], 'target' => $n['target'], 'status' => $n['status']])->all()" label-header="Unit / Shift" value-header="Rasio" />
    </x-dashboard.panel>

    <x-dashboard.panel title="Indikator SDM Lainnya">
        <x-dashboard.indicator-table :rows="$metrics" />
    </x-dashboard.panel>
</div>
