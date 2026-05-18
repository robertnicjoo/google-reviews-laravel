<?php

namespace Nicxonsolutions\GoogleReviews\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Nicxonsolutions\GoogleReviews\Contracts\ReviewsDriver;
use Nicxonsolutions\GoogleReviews\Data\ReviewsResult;
use Throwable;

class NicxonConnectorClient implements ReviewsDriver
{
    public function __construct(
        protected array $config,
        protected array $packageConfig
    ) {
    }

    public function reviews(?string $source = null, array $options = []): ReviewsResult
    {
        if (empty($this->config['endpoint']) || empty($this->config['site_token'])) {
            return ReviewsResult::failed('Nicxon Google Reviews Connector is not configured yet.');
        }

        try {
            $response = Http::timeout((int) ($this->packageConfig['timeout'] ?? 5))
                ->acceptJson()
                ->withToken($this->config['site_token'])
                ->get(rtrim($this->config['endpoint'], '/') . '/reviews', array_filter([
                    'location' => $source ?: ($options['location'] ?? null) ?: ($this->config['location'] ?? null),
                    'limit' => $options['max_reviews'] ?? $this->packageConfig['max_reviews'] ?? 5,
                ]));

            if ($response->failed()) {
                return ReviewsResult::failed('Nicxon Google Reviews Connector could not load reviews.', $response->status());
            }

            return ReviewsResult::fromConnector($response->json());
        } catch (ConnectionException) {
            return ReviewsResult::failed('Nicxon Google Reviews Connector is temporarily unavailable.');
        } catch (Throwable) {
            return ReviewsResult::failed('Nicxon Google Reviews Connector could not load reviews.');
        }
    }
}
