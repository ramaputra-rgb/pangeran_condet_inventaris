@props([
    'title',
    'subtitle',
    'value',
    'unit',
    'variant' => null,
])

<article {{ $attributes->merge(['class' => 'stub-card' . ($variant ? ' ' . $variant : '')]) }}>
    <div>
        <b>{{ $title }}</b>
        <small>{{ $subtitle }}</small>
    </div>
    <div class="stub-val">
        <strong>{{ $value }}</strong>
        <span>{{ $unit }}</span>
    </div>
</article>
