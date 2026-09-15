@php
    $piutangItems = collect($data['piutang_aging'])->map(fn($p) => [
        'label' => $p['label'], 'val' => $p['val'], 'fmt' => 'Rp '.$p['val'].' jt',
        'color' => match($p['status']) { 'crit' => 'var(--crit)', 'warn' => 'var(--warn)', default => 'var(--accent)' },
    ])->all();
    $pctTarget = round(($data['pendapatan_bulan_berjalan'] / $data['pendapatan_target']) * 100, 1);
@endphp
<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Keuangan</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Pendapatan dan piutang BPJS. Khusus manajemen, tidak tampil di TV.</div>

    <div class="split-2">
        <x-dashboard.panel title="Pendapatan per Lini Layanan">
            <x-dashboard.bar-list :items="$data['revenue_by_line']" />
            <div class="chart-foot">Total bulan berjalan: Rp {{ number_format($data['pendapatan_bulan_berjalan'], 2, ',', '.') }} M dari target Rp {{ number_format($data['pendapatan_target'], 2, ',', '.') }} M ({{ number_format($pctTarget, 1, ',', '.') }}%).</div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Piutang BPJS — Aging">
            <x-dashboard.bar-list :items="$piutangItems" />
            <div class="chart-foot">Piutang &gt;90 hari (Rp 310 jt) berisiko write-off — perlu eskalasi ke tim klaim.</div>
        </x-dashboard.panel>
    </div>

    <x-dashboard.panel title="Status Klaim BPJS — Bulan Berjalan">
        <table class="dtable">
            <thead><tr><th>Status Klaim</th><th>Jumlah</th><th>Kondisi</th></tr></thead>
            <tbody>
                @foreach($data['klaim_bpjs'] as $k)
                    <tr>
                        <td class="strong">{{ $k['label'] }}</td>
                        <td class="num tabular">{{ $k['val'] }}</td>
                        <td><x-dashboard.chip :status="$k['status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dashboard.panel>
</div>
