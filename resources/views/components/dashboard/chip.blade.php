@php
    $meta = [
        'good' => ['icon' => '▲', 'text' => 'Sesuai target'],
        'warn' => ['icon' => '●', 'text' => 'Mendekati batas'],
        'serious' => ['icon' => '●', 'text' => 'Perlu perhatian'],
        'crit' => ['icon' => '▼', 'text' => 'Di luar target'],
    ][$status] ?? ['icon' => '●', 'text' => $status];
@endphp
<span class="chip {{ $status }}">{{ $meta['icon'] }} {{ $meta['text'] }}</span>
