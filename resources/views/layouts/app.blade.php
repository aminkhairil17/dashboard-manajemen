<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <script>
            (function(){
                // wire:navigate mengganti isi halaman lewat AJAX tanpa reload penuh, jadi
                // <html> yang baru diambil dari server tidak bawa atribut ini — perlu
                // dipasang ulang tiap kali navigasi selesai, bukan cuma sekali di awal.
                function applyPreferences(){
                    var stored = localStorage.getItem('theme');
                    var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                    document.documentElement.setAttribute('data-theme', theme);
                    document.documentElement.classList.toggle('sidebar-collapsed', localStorage.getItem('sidebarCollapsed') === '1');
                }
                applyPreferences();
                document.addEventListener('livewire:navigated', applyPreferences);
            })();
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
    </head>
    <body class="app-body">
        <div x-data="{ sidebarOpen: false }" class="app-shell">
            <livewire:layout.navigation />

            <div class="app-sidebar-backdrop" :class="{ open: sidebarOpen }" @click="sidebarOpen = false" x-cloak></div>

            <div class="app-main-wrap">
                <div class="page-deco">
                    <div class="deco-a"></div>
                    <div class="deco-b"></div>
                    <div class="deco-c"></div>
                    <div class="deco-d"></div>
                </div>

                <svg class="app-mascot" viewBox="0 0 60 74" aria-hidden="true">
                    <ellipse cx="30" cy="70" rx="15" ry="3" fill="rgba(0,0,0,.12)"/>
                    <g class="m-body-grp">
                        <rect class="m-leg-l" x="20" y="50" width="8" height="16" rx="4" fill="var(--accent)"/>
                        <rect class="m-leg-r" x="32" y="50" width="8" height="16" rx="4" fill="var(--accent)"/>
                        <ellipse class="m-arm-l" cx="8" cy="30" rx="6" ry="10" fill="var(--accent)"/>
                        <ellipse class="m-arm-r" cx="52" cy="30" rx="6" ry="10" fill="var(--accent)"/>
                        <ellipse cx="30" cy="32" rx="22" ry="25" fill="var(--accent)"/>
                        <rect x="26" y="28" width="4" height="14" rx="1" fill="#ffffff"/>
                        <rect x="23" y="33" width="14" height="4" rx="1" fill="#ffffff"/>
                        <circle cx="22" cy="21" r="2.6" fill="var(--dark-ui)"/>
                        <circle cx="38" cy="21" r="2.6" fill="var(--dark-ui)"/>
                        <path d="M22 27 Q30 33 38 27" stroke="var(--dark-ui)" stroke-width="2" fill="none" stroke-linecap="round"/>
                    </g>
                </svg>

                <main class="app-content">
                    @if (isset($header))
                        <div class="app-page-title">{{ $header }}</div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
