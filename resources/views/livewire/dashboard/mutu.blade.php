<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Mutu &amp; Keselamatan Pasien</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Indikator wajib akreditasi KARS: kematian, infeksi, dan keselamatan pasien.</div>

    <x-dashboard.panel title="Angka Kematian">
        <x-dashboard.indicator-table :rows="$kematian" />
    </x-dashboard.panel>

    <x-dashboard.panel title="Infeksi Terkait Layanan Kesehatan (HAIs)">
        <x-dashboard.indicator-table :rows="$hais" />
    </x-dashboard.panel>

    <div class="split-2">
        <x-dashboard.panel title="Kepatuhan & Keselamatan">
            <x-dashboard.indicator-table :rows="$kepatuhan" />
        </x-dashboard.panel>

        <x-dashboard.panel title="Insiden Keselamatan Pasien — Bulan Ini">
            <x-dashboard.bar-list :items="collect($insiden)->map(fn($i) => ['label' => $i['label'], 'val' => max($i['val'], 0.3), 'fmt' => $i['val'].' kejadian'])->all()" :max="4" />
        </x-dashboard.panel>
    </div>

    <x-dashboard.panel title="Top Kategori Komplain Pasien">
        <x-dashboard.bar-list :items="collect($komplain)->map(fn($c) => ['label' => $c['label'], 'val' => $c['val'], 'fmt' => $c['val'].'%'])->all()" />
    </x-dashboard.panel>
</div>
