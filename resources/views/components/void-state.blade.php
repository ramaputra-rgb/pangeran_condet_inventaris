@props([
    'title',
    'description',
])

<div {{ $attributes->merge(['class' => 'void']) }}>
    <strong>{{ $title }}</strong>
    <p>{{ $description }}</p>
    @if(isset($action))
        {{ $action }}
    @endif
</div>
