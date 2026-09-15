<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Link TV Kiosk</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Buat &amp; cabut link tampilan TV tanpa login untuk layar lobby atau ruangan direktur.</div>

    @if(session('status'))
        <x-dashboard.panel>
            <div style="color:var(--accent); font-weight:600; font-size:.85rem;">{{ session('status') }}</div>
        </x-dashboard.panel>
    @endif

    <x-dashboard.panel title="Buat Link Baru">
        <form wire:submit="create" style="display:flex; flex-direction:column; gap:14px;">
            <div style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;">
                <div style="flex:1; min-width:220px;">
                    <input type="text" wire:model="label" placeholder="Label, mis. TV Lobby Utama" class="glossary-search">
                    @error('label')<div style="color:var(--crit); font-size:.75rem; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn-solid">+ Buat Link</button>
            </div>

            <div>
                <div style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); margin-bottom:8px;">Jenis Tampilan</div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <label style="flex:1; min-width:240px; display:flex; gap:10px; align-items:flex-start; padding:12px 14px; border-radius:12px; border:1px solid var(--border); cursor:pointer; background:{{ $type === 'publik' ? 'var(--accent-soft)' : 'transparent' }};">
                        <input type="radio" wire:model="type" value="publik" style="margin-top:3px;">
                        <span>
                            <span style="display:block; font-weight:700; font-size:.85rem;">Lobby Publik</span>
                            <span style="display:block; font-size:.75rem; color:var(--ink-2); margin-top:2px;">Berputar 4 halaman (Operasional, Efisiensi, Mutu, Conversion Rate). Tanpa data keuangan/SDM — aman dilihat pengunjung.</span>
                        </span>
                    </label>
                    <label style="flex:1; min-width:240px; display:flex; gap:10px; align-items:flex-start; padding:12px 14px; border-radius:12px; border:1px solid var(--border); cursor:pointer; background:{{ $type === 'direktur' ? 'var(--accent-soft)' : 'transparent' }};">
                        <input type="radio" wire:model="type" value="direktur" style="margin-top:3px;">
                        <span>
                            <span style="display:block; font-weight:700; font-size:.85rem;">Ruangan Direktur</span>
                            <span style="display:block; font-size:.75rem; color:var(--ink-2); margin-top:2px;">Satu halaman tetap (tidak berputar), termasuk data keuangan &amp; SDM. Khusus ruangan privat.</span>
                        </span>
                    </label>
                </div>
            </div>
        </form>
    </x-dashboard.panel>

    <x-dashboard.panel title="Link Aktif & Riwayat">
        <table class="dtable">
            <thead><tr><th>Label</th><th>Jenis</th><th>Link</th><th>Dibuat</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($tokens as $t)
                    <tr>
                        <td class="strong">{{ $t->label }}</td>
                        <td>
                            @if($t->type === 'direktur')
                                <span class="chip warn">🔒 Ruangan Direktur</span>
                            @else
                                <span class="chip good">Lobby Publik</span>
                            @endif
                        </td>
                        <td class="num" style="font-size:.75rem;">{{ $t->revoked_at ? '—' : url('/tv/'.$t->token) }}</td>
                        <td>{{ $t->created_at->translatedFormat('d M Y') }}</td>
                        <td>
                            @if($t->revoked_at)
                                <span class="chip crit">▼ Dicabut</span>
                            @else
                                <span class="chip good">▲ Aktif</span>
                            @endif
                        </td>
                        <td>
                            @unless($t->revoked_at)
                                <button wire:click="revoke({{ $t->id }})" wire:confirm="Cabut link TV \"{{ $t->label }}\"? TV yang memakainya akan langsung berhenti menampilkan data." style="background:none; border:none; color:var(--crit); font-size:.78rem; font-weight:700; cursor:pointer;">Cabut</button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:var(--muted);">Belum ada link TV yang dibuat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-dashboard.panel>

    <p style="font-size:.8rem; color:var(--muted); line-height:1.6; max-width:70ch;">
        Buka link di atas sekali di browser TV (bisa dijadikan halaman utama/bookmark) — tidak ada layar login yang muncul.
        <b>Lobby Publik</b> berganti otomatis tiap ±7 detik antara Operasional → Efisiensi Layanan → Mutu &amp; Keselamatan → Conversion Rate,
        indikator yang memang wajib dipublikasikan untuk transparansi akreditasi KARS. <b>Ruangan Direktur</b> menampilkan satu halaman ringkasan
        eksekutif tetap (termasuk keuangan &amp; SDM) — hanya buat link ini untuk layar di ruangan privat, jangan untuk area yang bisa dilihat umum.
    </p>
</div>
