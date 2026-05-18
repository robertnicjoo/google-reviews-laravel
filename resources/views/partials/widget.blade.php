@php
    $place = $result->place;
    $reviews = $result->reviews;
    $rating = $place['rating'] ?? 0;
    $reviewCount = $place['review_count'] ?? 0;
    $mapsUri = $place['google_maps_uri'] ?? 'https://www.google.com/maps';
@endphp

@if (! $result->ok)
    @if ($showErrors)
        <div class="nxgr nxgr-error" role="status">{{ $result->message }}</div>
    @endif
@else
    <section class="nxgr nxgr-widget nxgr-theme-{{ $theme }}" data-nxgr-widget>
        <div class="nxgr-summary">
            <div class="nxgr-place-mark" aria-hidden="true">
                <span></span>
            </div>

            <div class="nxgr-summary-copy">
                <h2>{{ $place['name'] ?? 'Google Reviews' }}</h2>

                <div class="nxgr-rating-line" aria-label="{{ number_format($rating, 1) }} out of 5 stars">
                    <strong>{{ number_format($rating, 1) }}</strong>
                    @include('google-reviews::partials.stars', ['rating' => $rating])
                </div>

                <p>Based on {{ number_format($reviewCount) }} reviews</p>

                <div class="nxgr-powered">
                    <span>powered by</span>
                    <b><span class="nxgr-g-blue">G</span><span class="nxgr-g-red">o</span><span class="nxgr-g-yellow">o</span><span class="nxgr-g-blue">g</span><span class="nxgr-g-green">l</span><span class="nxgr-g-red">e</span></b>
                </div>

                <a class="nxgr-review-button" href="{{ $mapsUri }}" target="_blank" rel="noopener nofollow">
                    {{ config('google-reviews.review_button_label', 'review us on Google') }}
                    <span>G</span>
                </a>
            </div>
        </div>

        @if (count($reviews) > 0)
            <div class="nxgr-carousel" data-nxgr-carousel>
                <button class="nxgr-nav nxgr-nav-prev" type="button" data-nxgr-prev aria-label="Previous review">‹</button>

                <div class="nxgr-review-track">
                    @foreach ($reviews as $index => $review)
                        <article class="nxgr-review {{ $index === 0 ? 'is-active' : '' }}" data-nxgr-slide>
                            <div class="nxgr-review-head">
                                <div class="nxgr-avatar">
                                    @if ($review['avatar'])
                                        <img src="{{ $review['avatar'] }}" alt="">
                                    @else
                                        <span>{{ mb_substr($review['author'], 0, 1) }}</span>
                                    @endif
                                </div>

                                <div>
                                    <h3>{{ $review['author'] }}</h3>
                                    <time>{{ $review['relative_time'] }}</time>
                                </div>

                                <a class="nxgr-google-mark" href="{{ $review['google_maps_uri'] ?? $mapsUri }}" target="_blank" rel="noopener nofollow" aria-label="Open review on Google">G</a>
                            </div>

                            @include('google-reviews::partials.stars', ['rating' => $review['rating']])

                            <p>{{ $review['text'] }}</p>
                        </article>
                    @endforeach
                </div>

                <button class="nxgr-nav nxgr-nav-next" type="button" data-nxgr-next aria-label="Next review">›</button>

                <div class="nxgr-dots" role="tablist" aria-label="Google reviews">
                    @foreach ($reviews as $index => $review)
                        <button type="button" class="{{ $index === 0 ? 'is-active' : '' }}" data-nxgr-dot="{{ $index }}" aria-label="Show review {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    @once
        <script>
            document.addEventListener('click', function (event) {
                const control = event.target.closest('[data-nxgr-prev], [data-nxgr-next], [data-nxgr-dot]');
                if (! control) return;

                const carousel = control.closest('[data-nxgr-carousel]');
                const slides = Array.from(carousel.querySelectorAll('[data-nxgr-slide]'));
                const dots = Array.from(carousel.querySelectorAll('[data-nxgr-dot]'));
                const active = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
                let next = active;

                if (control.matches('[data-nxgr-prev]')) next = active === 0 ? slides.length - 1 : active - 1;
                if (control.matches('[data-nxgr-next]')) next = active === slides.length - 1 ? 0 : active + 1;
                if (control.matches('[data-nxgr-dot]')) next = Number(control.dataset.nxgrDot);

                slides.forEach((slide, index) => slide.classList.toggle('is-active', index === next));
                dots.forEach((dot, index) => dot.classList.toggle('is-active', index === next));
            });
        </script>
    @endonce
@endif
