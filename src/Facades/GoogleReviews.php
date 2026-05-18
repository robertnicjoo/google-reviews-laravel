<?php

namespace Nicxonsolutions\GoogleReviews\Facades;

use Illuminate\Support\Facades\Facade;

class GoogleReviews extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'google-reviews';
    }
}
