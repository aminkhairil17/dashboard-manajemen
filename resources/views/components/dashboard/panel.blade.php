@props(['title' => null])
<div {{ $attributes->class(['panel']) }}>
    @if($title || isset($legend))
        <div class="panel-head">
            @if($title)<div class="panel-title">{{ $title }}</div>@endif
            @isset($legend){{ $legend }}@endisset
        </div>
    @endif
    {{ $slot }}
</div>
