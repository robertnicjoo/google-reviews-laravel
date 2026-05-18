<?php

namespace Nicxonsolutions\GoogleReviews\Contracts;

use Nicxonsolutions\GoogleReviews\Data\ReviewsResult;

interface ReviewsDriver
{
    public function reviews(?string $source = null, array $options = []): ReviewsResult;
}
