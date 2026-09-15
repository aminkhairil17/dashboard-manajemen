<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $sections = [
        'Utama' => [
            ['route' => 'dashboard.ringkasan', 'label' => 'Ringkasan Eksekutif', 'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>'],
            ['route' => 'dashboard.operasional', 'label' => 'Operasional', 'icon' => '<path d="M3 11l9-7 9 7"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/>'],
            ['route' => 'dashboard.rawat-jalan', 'label' => 'Rawat Jalan', 'icon' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1z"/><path d="M9 11h6M9 15h4"/>'],
            ['route' => 'dashboard.efisiensi', 'label' => 'Efisiensi Layanan', 'icon' => '<path d="M13 2 L4 14h6l-1 8 9-12h-6z"/>'],
            ['route' => 'dashboard.mutu', 'label' => 'Mutu & Keselamatan', 'icon' => '<path d="M12 21s7-3.5 7-9V5l-7-3-7 3v7c0 5.5 7 9 7 9z"/><path d="M9 12l2 2 4-4"/>'],
            ['route' => 'dashboard.konversi', 'label' => 'Conversion Rate', 'icon' => '<path d="M4 6h16M4 6l4-3M4 6l4 3M20 18H4M20 18l-4-3M20 18l-4 3"/>'],
        ],
        'Internal' => [
            ['route' => 'dashboard.keuangan', 'label' => 'Keuangan', 'icon' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.2"/>', 'lock' => true],
            ['route' => 'dashboard.sdm', 'label' => 'SDM', 'icon' => '<circle cx="9" cy="7" r="3"/><path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/><path d="M17 11a3 3 0 1 0 0-6"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>', 'lock' => true],
        ],
        'Lainnya' => [
            ['route' => 'dashboard.glosarium', 'label' => 'Kamus Istilah', 'icon' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'],
            ['route' => 'dashboard.tv-kiosk', 'label' => 'Link TV Kiosk', 'icon' => '<rect x="3" y="5" width="18" height="13" rx="2"/><path d="M8 21h8M12 18v3"/>'],
        ],
    ];

    $initials = collect(explode(' ', auth()->user()->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('');
@endphp

<div style="display:contents">
{{-- Top bar mobile: hamburger + brand + avatar --}}
<div class="app-topbar-mobile">
    <button class="menu-btn" @click="sidebarOpen = true" aria-label="Buka menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <a href="{{ route('dashboard.ringkasan') }}" wire:navigate class="app-brand">
        <img src="{{ asset('branding-logo.png') }}" alt="Logo RS Syifa Medika">
        <span>Syifa Medika</span>
    </a>
    <div class="util-avatar">{{ $initials }}</div>
</div>

<aside class="app-sidebar" :class="{ 'sidebar-open': sidebarOpen }">
    <button type="button" class="sidebar-collapse-btn" title="Ciutkan/lebarkan menu" aria-label="Ciutkan atau lebarkan menu"
        onclick="var h=document.documentElement, c=h.classList.toggle('sidebar-collapsed'); localStorage.setItem('sidebarCollapsed', c ? '1' : '0');">
        <svg class="icon-collapse" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        <svg class="icon-expand" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </button>
    <a href="{{ route('dashboard.ringkasan') }}" wire:navigate class="app-brand">
        <img src="{{ asset('branding-logo.png') }}" alt="Logo RS Syifa Medika">
        <span>Syifa Medika<span class="app-brand-sub">Dashboard Manajemen</span></span>
    </a>
    <div class="ribbon-line"></div>

    <nav class="app-sidebar-scroll">
        @foreach($sections as $sectionLabel => $items)
            <div class="nav-section">
                <div class="nav-section-label">{{ $sectionLabel }}</div>
                @foreach($items as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false"
                       class="sidebar-nav-item {{ request()->routeIs($item['route']) ? 'current' : '' }}">
                        <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                        <span>{{ $item['label'] }}</span>
                        @if(!empty($item['lock']))
                            <svg class="lock-badge" viewBox="0 0 24 24" fill="currentColor" title="Internal, tidak tampil di TV"><path d="M12 2a4 4 0 0 0-4 4v3H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V6a4 4 0 0 0-4-4zm-2 7V6a2 2 0 1 1 4 0v3z"/></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="app-sidebar-footer" x-data="{ open: false }">
        <div class="sidebar-footer-row">
            <div class="sidebar-user" @click="open = !open">
                <div class="util-avatar">{{ $initials }}</div>
                <div style="min-width:0;">
                    <div class="sidebar-user-name" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-email" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <button type="button" class="theme-toggle" title="Ganti terang/gelap" aria-label="Ganti tema terang/gelap"
                onclick="var h=document.documentElement, c=h.getAttribute('data-theme'), n=c==='dark'?'light':'dark'; h.setAttribute('data-theme', n); localStorage.setItem('theme', n);">
                <svg class="icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                <svg class="icon-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/></svg>
            </button>
        </div>
        <div x-show="open" @click.outside="open = false" x-cloak style="margin-top:6px; background:var(--surface-2); border-radius:10px; padding:6px;">
            <a href="{{ route('profile') }}" wire:navigate style="display:block; padding:8px 10px; border-radius:8px; font-size:.82rem; color:var(--ink-2); text-decoration:none;">Profil Saya</a>
            <button wire:click="logout" style="display:block; width:100%; text-align:left; padding:8px 10px; border-radius:8px; font-size:.82rem; color:var(--ink-2); background:none; border:none; cursor:pointer;">Keluar</button>
        </div>
    </div>
</aside>
</div>
