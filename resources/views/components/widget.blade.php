@once
    <link rel="stylesheet" href="{{ $googleReviewsAssetUrl ?? route('google-reviews.assets.css') }}">
@endonce

@include('google-reviews::partials.widget', [
    'result' => $result,
    'theme' => $theme,
    'showErrors' => $showErrors,
])
