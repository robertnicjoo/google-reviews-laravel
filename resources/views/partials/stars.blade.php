@php
    $filled = (int) floor($rating);
    $half = ($rating - $filled) >= 0.5;
@endphp

<span class="nxgr-stars" aria-hidden="true">
    @for ($i = 1; $i <= 5; $i++)
        <span class="{{ $i <= $filled ? 'is-filled' : ($i === $filled + 1 && $half ? 'is-half' : '') }}">★</span>
    @endfor
</span>
