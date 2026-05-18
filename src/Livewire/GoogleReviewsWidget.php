<?php

namespace Nicxonsolutions\GoogleReviews\Livewire;

use Livewire\Component;
use Nicxonsolutions\GoogleReviews\GoogleReviews;

class GoogleReviewsWidget extends Component
{
    public ?string $location = null;
    public ?string $source = null;
    public ?string $placeId = null;
    public string $theme = 'light';
    public bool $showErrors = false;
    public int $maxReviews = 5;

    public function render()
    {
        return view('google-reviews::livewire.google-reviews-widget', [
            'result' => app(GoogleReviews::class)->reviews($this->source ?: $this->location ?: $this->placeId, [
                'max_reviews' => $this->maxReviews,
            ]),
            'showErrors' => $this->showErrors || (bool) config('google-reviews.show_errors'),
        ]);
    }
}
