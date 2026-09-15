@php
    $revMin = 0.08; $revMax = 0.22;
    $lastRev = end($revenueTrend);
@endphp
<div class="tv-page" id="tv-page">
    <div class="tv-glow g1"></div>
    <div class="tv-glow g2"></div>

    <div class="tv-topbar">
        <div class="tv-live">
            <img src="{{ asset('branding-logo.png') }}" alt="">
            RS SYIFA MEDIKA · RUANGAN DIREKTUR <span class="led"></span> LIVE
        </div>
        <div class="tv-clock" id="tv-clock"></div>
    </div>
    <div class="ribbon-line" style="position:relative; z-index:1; margin:14px 0 0;"></div>

    <div class="tv-slide-wrap" style="position:relative; z-index:1;">
        <div class="kpi-grid">
            @foreach($direkturKpis as $k)
                <x-dashboard.kpi-tile :label="$k['label']" :value="$k['disp']" :unit="$k['unit']" :target="$k['target']" :status="$k['status']" />
            @endforeach
        </div>

        <div class="split-2">
            <x-dashboard.panel title="Tren Pendapatan Harian — 30 Hari">
                {!! \App\Support\ChartSvg::trend($revenueTrend, 'var(--accent)', $revMin, $revMax, [0.15, 0.20], 'Rp '.number_format($lastRev, 2, ',', '.').' M') !!}
                <div class="chart-foot">Target harian ±Rp 0,18 M (setara Rp 5,50 M/bulan). Pita hijau menandai rentang "on track".</div>
            </x-dashboard.panel>

            <x-dashboard.panel title="Perlu Perhatian">
                @foreach($perluPerhatian as $p)
                    <div class="stat-row">
                        <div class="stat-row-label">
                            <span class="meta-pill" style="margin-right:6px;">{{ $p['kategori'] }}</span>
                            {!! $p['label'] !!}
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="stat-row-val" style="font-size:.78rem;">{!! $p['nilai'] !!}</span>
                            <x-dashboard.chip :status="$p['status']" />
                        </div>
                    </div>
                @endforeach
            </x-dashboard.panel>
        </div>

        <x-dashboard.panel title="Status Semua Unit">
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
</div>

<script>
    (function(){
        function tickClock(){
            document.getElementById('tv-clock').textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        }
        tickClock();
        setInterval(tickClock, 1000);
    })();
</script>
