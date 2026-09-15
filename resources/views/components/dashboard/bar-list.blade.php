@props(['items', 'max' => null])
@php($m = $max ?? collect($items)->max('val') ?: 1)
@foreach($items as $item)
    <div class="bar-row">
        <div class="bar-label">{{ $item['label'] }}</div>
        <div class="bar-track"><div class="bar-fill" style="width:{{ min(100, ($item['val'] / $m) * 100) }}%; {{ isset($item['color']) ? 'background:'.$item['color'].';' : '' }}"></div></div>
        <div class="bar-val num tabular">{{ $item['fmt'] ?? $item['val'] }}</div>
    </div>
@endforeach
