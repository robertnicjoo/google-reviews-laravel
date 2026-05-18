<?php

namespace Nicxonsolutions\GoogleReviews;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Nicxonsolutions\GoogleReviews\Contracts\ReviewsDriver;
use Nicxonsolutions\GoogleReviews\Data\ReviewsResult;

class GoogleReviews
{
    public function __construct(
        protected array $drivers,
        protected CacheRepository $cache,
        protected array $config
    ) {
    }

    public function reviews(?string $source = null, array $options = []): ReviewsResult
    {
        $driverName = $options['driver'] ?? $this->config['driver'] ?? 'business_profile';
        $driver = $this->drivers[$driverName] ?? null;

        if (! $driver instanceof ReviewsDriver) {
            return ReviewsResult::failed("Google Reviews driver [{$driverName}] is not configured.");
        }

        $ttl = (int) ($options['cache_ttl'] ?? $this->config['cache_ttl'] ?? 1800);
        $cacheOptions = $options;
        unset($cacheOptions['cache_ttl']);

        $cacheKey = 'nicxon_google_reviews:' . md5($driverName . ':' . ($source ?: 'default') . ':' . serialize($cacheOptions));

        if ($ttl <= 0) {
            return $driver->reviews($source, $options);
        }

        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $result = $driver->reviews($source, $options);

        if ($result->ok) {
            $this->cache->put($cacheKey, $result, $ttl);
            $this->cache->put($cacheKey . ':stale', $result, (int) ($this->config['stale_ttl'] ?? 86400));

            return $result;
        }

        return $this->cache->get($cacheKey . ':stale') ?: $result;
    }
}
