<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Conversion Rate</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Berapa persen pasien berpindah ke layanan berikutnya, bulan berjalan.</div>

    @foreach($groups as $g)
        <x-dashboard.panel>
            <div class="bar-group-title">{{ $g['group'] }}</div>
            <x-dashboard.bar-list :items="collect($g['items'])->map(fn($i) => ['label' => $i['label'], 'val' => $i['val'], 'fmt' => number_format($i['val'], 1, ',', '.').'%'])->all()" :max="100" />
        </x-dashboard.panel>
    @endforeach
</div>
