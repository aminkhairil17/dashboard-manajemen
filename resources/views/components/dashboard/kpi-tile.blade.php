@props(['label', 'value', 'unit' => null, 'target' => null, 'status' => null, 'trend' => null])
<div class="kpi-tile">
    <div class="kpi-label">{{ $label }}</div>
    <div class="kpi-value-row">
        <span class="kpi-value num tabular">{{ $value }}</span>
        @if($unit)<span class="kpi-unit">{{ $unit }}</span>@endif
    </div>
    @if($target)<div class="kpi-target">{{ $target }}</div>@endif
    @if($trend)
        {!! \App\Support\ChartSvg::sparkline($trend, 'var(--accent)') !!}
    @endif
    @if($status)<x-dashboard.chip :status="$status" />@endif
</div>
