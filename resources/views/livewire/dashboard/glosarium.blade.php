<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Kamus Istilah Dashboard</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Pengertian dan contoh kegunaan tiap indikator, ditulis untuk direktur.</div>

    <div style="position:sticky; top:0; z-index:10; background:var(--bg); padding:14px 0; margin-bottom:8px;">
        <input type="text" wire:model.live.debounce.300ms="search" class="glossary-search" placeholder='Cari istilah, mis. "BOR", "klaim", atau "perawat"…'>
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:12px;">
            @foreach($allCategories as $c)
                <a href="#sec-{{ $c['cat'] }}" class="cat-chip">{{ $c['title'] }}</a>
            @endforeach
        </div>
        @if($search !== '')
            <div style="font-size:.78rem; color:var(--muted); margin-top:10px;">{{ $resultCount }} istilah cocok dengan &quot;{{ $search }}&quot;</div>
        @endif
    </div>

    @forelse($sections as $i => $section)
        <section id="sec-{{ $section['cat'] }}" style="margin-top:30px; scroll-margin-top:130px;">
            <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:12px; padding-bottom:8px; border-bottom:2px solid var(--accent-soft);">
                <span class="num" style="font-size:.78rem; color:var(--accent); font-weight:700;">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span style="font-family:'Manrope',sans-serif; font-weight:800; font-size:1.05rem;">{{ $section['title'] }}</span>
            </div>
            <p style="font-size:.82rem; color:var(--muted); margin:-4px 0 14px;">{{ $section['desc'] }}</p>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:14px;">
                @foreach($section['terms'] as $t)
                    <div class="term-card">
                        <div>
                            <div class="term-name">{{ $t['name'] }}</div>
                            @if(!empty($t['abbr']))<span class="term-abbr">{{ $t['abbr'] }}</span>@endif
                        </div>
                        <div class="term-def">{{ $t['def'] }}</div>
                        @if(!empty($t['formula']) || !empty($t['standard']))
                            <div class="term-meta">
                                @if(!empty($t['formula']))<span class="meta-pill">Rumus: <b>{{ $t['formula'] }}</b></span>@endif
                                @if(!empty($t['standard']))<span class="meta-pill">Standar: <b>{{ $t['standard'] }}</b></span>@endif
                            </div>
                        @endif
                        <div class="term-example">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.6 10.8c.5.4.8 1 .8 1.7V16h5.6v-.5c0-.7.3-1.3.8-1.7A6 6 0 0 0 12 3z"/></svg>
                            <div><b>Contoh kegunaan:</b> {{ $t['example'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <p style="color:var(--muted); margin-top:30px;">Tidak ada istilah yang cocok. Coba kata kunci lain.</p>
    @endforelse
</div>
