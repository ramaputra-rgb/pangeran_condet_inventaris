@props([
    'title',
    'subtitle',
    'value',
    'unit',
    'variant' => null,
    'iconTone' => null,
])

@php
    $resolvedTone = $iconTone ?? match($variant) {
        'alert' => 'alert',
        'warm' => 'warm',
        default => 'neutral',
    };
@endphp

<article {{ $attributes->merge(['class' => 'stub-card' . ($variant ? ' ' . $variant : '')]) }}>
    <div class="stub-main">
        @if(isset($icon))
            <span class="stub-icon is-{{ $resolvedTone }}" aria-hidden="true">{{ $icon }}</span>
        @endif
        <b>{{ $title }}</b>
        <small>{{ $subtitle }}</small>
    </div>
    <div class="stub-val">
        <strong>{{ $value }}</strong>
        <span>{{ $unit }}</span>
    </div>
</article>
