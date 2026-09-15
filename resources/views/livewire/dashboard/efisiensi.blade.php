<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Efisiensi Layanan</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Seberapa cepat farmasi, lab, radiologi, dan kamar operasi melayani pasien.</div>

    <x-dashboard.panel title="Turn Around Time (TAT)">
        <div class="tat-grid">
            @foreach($tat as $t)
                <div class="tat-card">
                    <div class="tat-name">{{ $t['name'] }}</div>
                    <div class="kpi-value num tabular" style="font-size:1.3rem;">{{ $t['disp'] }}@if($t['unit'])<span class="kpi-unit"> {{ $t['unit'] }}</span>@endif</div>
                    <div class="tat-bar-track"><div class="tat-bar-fill" style="width:{{ min(100, ($t['val'] / $t['max']) * 100) }}%"></div></div>
                    <div class="tat-foot"><span>Aktual</span><span>{{ $t['target'] }}</span></div>
                </div>
            @endforeach
        </div>
    </x-dashboard.panel>

    <x-dashboard.panel title="OT Room Management — Utilisasi Kamar Operasi">
        @foreach($otRooms as $o)
            <div class="ot-row">
                <div class="ot-name">{{ $o['name'] }}</div>
                <div class="ot-track"><div class="ot-fill" style="width:{{ $o['util'] }}%"></div></div>
                <div class="ot-val num tabular">{{ $o['util'] }}%</div>
            </div>
        @endforeach
    </x-dashboard.panel>

    <x-dashboard.panel title="Waktu Tunggu per Unit">
        <x-dashboard.indicator-table :rows="collect($waitTimes)->map(fn($w) => ['label' => $w['unit'], 'val' => $w['val'], 'target' => $w['target'], 'status' => $w['status']])->all()" label-header="Unit" value-header="Rata-rata" />
    </x-dashboard.panel>
</div>
