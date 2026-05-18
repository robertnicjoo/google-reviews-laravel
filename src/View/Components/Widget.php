<?php

namespace Nicxonsolutions\GoogleReviews\View\Components;

use Illuminate\View\Component;
use Nicxonsolutions\GoogleReviews\GoogleReviews;

class Widget extends Component
{
    public function __construct(
        public ?string $location = null,
        public ?string $source = null,
        public ?string $placeId = null,
        public string $theme = 'light',
        public bool $showErrors = false,
        public int $maxReviews = 5
    ) {
    }

    public function render()
    {
        $result = app(GoogleReviews::class)->reviews($this->source ?: $this->location ?: $this->placeId, [
            'max_reviews' => $this->maxReviews,
        ]);

        return view('google-reviews::components.widget', [
            'result' => $result,
            'theme' => $this->theme,
            'showErrors' => $this->showErrors || (bool) config('google-reviews.show_errors'),
        ]);
    }
}
