@if(!$valid)
    <div class="tv-page tv-invalid">
        <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
        <h1>Link tidak valid</h1>
        <p>Link TV ini sudah dicabut atau tidak pernah ada. Minta link baru ke admin lewat menu &ldquo;Link TV Kiosk&rdquo; di dashboard.</p>
    </div>
@else
    <div class="tv-page" id="tv-page">
        <div class="tv-glow g1"></div>
        <div class="tv-glow g2"></div>

        <div class="tv-topbar">
            <div class="tv-live">
                <img src="{{ asset('branding-logo.png') }}" alt="">
                RS SYIFA MEDIKA <span class="led"></span> LIVE
            </div>
            <div class="tv-clock" id="tv-clock"></div>
        </div>
        <div class="ribbon-line" style="position:relative; z-index:1; margin:14px 0 0;"></div>
        <div class="tv-progress-track"><div class="tv-progress-fill" id="tv-progress"></div></div>

        <div class="tv-slide-wrap">
            <div class="tv-slide" data-slide="0" style="display:block;">
                <div class="tv-slide-title">Operasional</div>
                <div class="kpi-grid">
                    @foreach($operasionalKpis as $k)
                        <x-dashboard.kpi-tile :label="$k['label']" :value="$k['disp']" :unit="$k['unit']" :target="$k['target']" :status="$k['status']" :trend="$k['trend']" />
                    @endforeach
                </div>
                <x-dashboard.panel title="Tren BOR — 30 hari terakhir">
                    {!! \App\Support\ChartSvg::trend($borTrend, 'var(--accent)', 55, 95, [60, 85], number_format(end($borTrend), 1, ',', '.').'%') !!}
                </x-dashboard.panel>
            </div>

            <div class="tv-slide" data-slide="1" style="display:none;">
                <div class="tv-slide-title">Efisiensi Layanan</div>
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
                <x-dashboard.panel title="OT Room Management">
                    @foreach($otRooms as $o)
                        <div class="ot-row">
                            <div class="ot-name">{{ $o['name'] }}</div>
                            <div class="ot-track"><div class="ot-fill" style="width:{{ $o['util'] }}%"></div></div>
                            <div class="ot-val num tabular">{{ $o['util'] }}%</div>
                        </div>
                    @endforeach
                </x-dashboard.panel>
            </div>

            <div class="tv-slide" data-slide="2" style="display:none;">
                <div class="tv-slide-title">Mutu &amp; Keselamatan Pasien</div>
                <x-dashboard.panel title="Angka Kematian">
                    <x-dashboard.indicator-table :rows="$kematian" />
                </x-dashboard.panel>
                <x-dashboard.panel title="Infeksi Terkait Layanan Kesehatan (HAIs)">
                    <x-dashboard.indicator-table :rows="$hais" />
                </x-dashboard.panel>
            </div>

            <div class="tv-slide" data-slide="3" style="display:none;">
                <div class="tv-slide-title">Conversion Rate</div>
                @foreach($konversiGroups as $g)
                    <x-dashboard.panel>
                        <div class="bar-group-title">{{ $g['group'] }}</div>
                        <x-dashboard.bar-list :items="collect($g['items'])->map(fn($i) => ['label' => $i['label'], 'val' => $i['val'], 'fmt' => number_format($i['val'], 1, ',', '.').'%'])->all()" :max="100" />
                    </x-dashboard.panel>
                @endforeach
            </div>
        </div>

        <div class="tv-dots" id="tv-dots">
            <span class="on"></span><span></span><span></span><span></span>
        </div>
    </div>

    <script>
        (function(){
            const slides = document.querySelectorAll('.tv-slide');
            const dots = document.querySelectorAll('#tv-dots span');
            const progress = document.getElementById('tv-progress');
            let idx = 0;

            function show(i){
                slides.forEach((s, si) => s.style.display = si === i ? 'block' : 'none');
                dots.forEach((d, di) => d.classList.toggle('on', di === i));
                progress.classList.remove('run');
                void progress.offsetWidth;
                progress.classList.add('run');
            }
            show(0);
            setInterval(() => { idx = (idx + 1) % slides.length; show(idx); }, 7000);

            function tickClock(){
                document.getElementById('tv-clock').textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
            }
            tickClock();
            setInterval(tickClock, 1000);
        })();
    </script>
@endif
