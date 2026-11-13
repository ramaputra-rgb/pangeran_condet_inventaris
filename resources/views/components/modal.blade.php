@props([
    'id',
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeClass' => 'rank',
    'badgeId' => null,
    'wide' => false,
    'titleId' => null,
    'subtitleId' => null,
])

@php
    $headingId = $titleId ?? ($id . '-title');
@endphp

<dialog id="{{ $id }}" {{ $attributes->merge(['class' => 'modal' . ($wide ? ' modal-wide' : '')]) }} aria-labelledby="{{ $headingId }}">
    <div class="modal-head">
        <div>
            @if($badge)
                <span class="{{ $badgeClass }}" @if($badgeId) id="{{ $badgeId }}" @endif>{{ $badge }}</span>
            @endif
            <h2 id="{{ $headingId }}">{{ $title }}</h2>
            @if($subtitle || $subtitleId)
                <p @if($subtitleId) id="{{ $subtitleId }}" @endif>{{ $subtitle }}</p>
            @endif
        </div>
        <button type="button" class="modal-close" data-close aria-label="Tutup dialog">&times;</button>
    </div>

    {{ $slot }}
</dialog>
