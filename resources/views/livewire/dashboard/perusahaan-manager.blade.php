<div>
    <div class="app-page-eyebrow">{{ now()->translatedFormat('l, d F Y · H:i') }}</div>
    <div class="app-page-title">Kelola Perusahaan</div>
    <div class="app-page-meta" style="margin-bottom:18px;">Buat entitas RS/perusahaan baru di bawah grup, dan atur siapa saja yang punya akses ke masing-masing.</div>

    @if(session('status'))
        <x-dashboard.panel>
            <div style="color:var(--accent); font-weight:600; font-size:.85rem;">{{ session('status') }}</div>
        </x-dashboard.panel>
    @endif

    @if(session('error'))
        <x-dashboard.panel>
            <div style="color:var(--crit); font-weight:600; font-size:.85rem;">{{ session('error') }}</div>
        </x-dashboard.panel>
    @endif

    <x-dashboard.panel title="Tambah Perusahaan Baru">
        <form wire:submit="create" style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;">
            <div style="flex:2; min-width:220px;">
                <input type="text" wire:model="name" placeholder="Nama perusahaan, mis. RS Syifa Medika Cabang B" class="glossary-search">
                @error('name')<div style="color:var(--crit); font-size:.75rem; margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div style="flex:1; min-width:140px;">
                <input type="number" wire:model="bedCapacity" placeholder="Kapasitas tempat tidur" class="glossary-search">
                @error('bedCapacity')<div style="color:var(--crit); font-size:.75rem; margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-solid">+ Buat Perusahaan</button>
        </form>
    </x-dashboard.panel>

    @foreach($myCompanies as $company)
        <x-dashboard.panel :title="$company->name">
            <div style="font-size:.8rem; color:var(--muted); margin-bottom:12px;">Kapasitas {{ $company->bed_capacity }} tempat tidur</div>

            <table class="dtable">
                <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th></th></tr></thead>
                <tbody>
                    @foreach($company->users as $member)
                        <tr>
                            <td class="strong">{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->pivot->role }}</td>
                            <td>
                                @if($company->users->count() > 1)
                                    <button wire:click="removeMember({{ $company->id }}, {{ $member->id }})" wire:confirm="Hapus {{ $member->name }} dari {{ $company->name }}?" style="background:none; border:none; color:var(--crit); font-size:.78rem; font-weight:700; cursor:pointer;">Hapus</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form wire:submit="addMember({{ $company->id }})" style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap; margin-top:14px;">
                <div style="flex:2; min-width:200px;">
                    <input type="email" wire:model="memberEmail.{{ $company->id }}" placeholder="Email user yang sudah terdaftar" class="glossary-search">
                    @error('memberEmail.'.$company->id)<div style="color:var(--crit); font-size:.75rem; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div style="flex:1; min-width:140px;">
                    <input type="text" wire:model="memberRole.{{ $company->id }}" placeholder="Peran, mis. direktur" class="glossary-search">
                </div>
                <button type="submit" class="btn-solid">+ Tambah Anggota</button>
            </form>
        </x-dashboard.panel>
    @endforeach

    <p style="font-size:.8rem; color:var(--muted); line-height:1.6; max-width:70ch;">
        User yang ditambahkan harus sudah pernah mendaftar/login (lewat form biasa atau SSO) di aplikasi ini — cari berdasarkan email yang sama.
        Kapasitas tempat tidur dipakai untuk menyesuaikan skala data contoh (BOR/LOS/kamar/pendapatan) sampai integrasi SIMRS GOS per perusahaan tersedia.
    </p>
</div>
