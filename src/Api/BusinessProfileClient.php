<?php

namespace Nicxonsolutions\GoogleReviews\Api;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Nicxonsolutions\GoogleReviews\Contracts\ReviewsDriver;
use Nicxonsolutions\GoogleReviews\Data\ReviewsResult;
use Throwable;

class BusinessProfileClient implements ReviewsDriver
{
    public function __construct(
        protected array $config,
        protected array $packageConfig,
        protected CacheRepository $cache
    ) {
    }

    public function reviews(?string $source = null, array $options = []): ReviewsResult
    {
        $token = $this->accessToken();

        if (! $token) {
            return ReviewsResult::failed('Connect a Google Business Profile account to show Google Reviews.');
        }

        try {
            $location = $source
                ?: ($options['location'] ?? null)
                ?: ($this->config['location'] ?? null)
                ?: $this->firstLocation($token);

            if (! $location) {
                return ReviewsResult::failed('No verified Google Business Profile location was found for this account.');
            }

            $response = Http::timeout((int) ($this->packageConfig['timeout'] ?? 5))
                ->acceptJson()
                ->withToken($token)
                ->get($this->reviewsUrl($location), [
                    'pageSize' => max(1, min((int) ($options['max_reviews'] ?? $this->packageConfig['max_reviews'] ?? 5), 50)),
                    'orderBy' => $options['order_by'] ?? 'updateTime desc',
                ]);

            if ($response->failed()) {
                return ReviewsResult::failed($this->messageForStatus($response->status()), $response->status());
            }

            return ReviewsResult::fromBusinessProfile($response->json(), $location);
        } catch (ConnectionException) {
            return ReviewsResult::failed('Google Business Profile is temporarily unavailable.');
        } catch (Throwable) {
            return ReviewsResult::failed('Google Business Profile reviews could not be loaded.');
        }
    }

    protected function accessToken(): ?string
    {
        if ($token = $this->cache->get('nicxon_google_reviews:business_profile:access_token')) {
            return $token;
        }

        if (! empty($this->config['access_token'])) {
            return $this->config['access_token'];
        }

        if (empty($this->config['refresh_token']) || empty($this->config['client_id']) || empty($this->config['client_secret'])) {
            return null;
        }

        $response = Http::asForm()
            ->timeout((int) ($this->packageConfig['timeout'] ?? 5))
            ->post($this->config['token_endpoint'] ?? 'https://oauth2.googleapis.com/token', [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'refresh_token' => $this->config['refresh_token'],
                'grant_type' => 'refresh_token',
            ]);

        if ($response->failed() || ! $response->json('access_token')) {
            return null;
        }

        $expiresIn = max(60, ((int) $response->json('expires_in', 3600)) - 60);
        $this->cache->put('nicxon_google_reviews:business_profile:access_token', $response->json('access_token'), $expiresIn);

        return $response->json('access_token');
    }

    protected function firstLocation(string $token): ?string
    {
        $account = $this->firstAccount($token);

        if (! $account) {
            return null;
        }

        $response = Http::timeout((int) ($this->packageConfig['timeout'] ?? 5))
            ->acceptJson()
            ->withToken($token)
            ->get($this->locationsUrl($account), [
                'readMask' => 'name,title,metadata',
                'pageSize' => 1,
            ]);

        return $response->successful() ? $response->json('locations.0.name') : null;
    }

    protected function firstAccount(string $token): ?string
    {
        $response = Http::timeout((int) ($this->packageConfig['timeout'] ?? 5))
            ->acceptJson()
            ->withToken($token)
            ->get($this->config['accounts_endpoint'] ?? 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts');

        return $response->successful() ? $response->json('accounts.0.name') : null;
    }

    protected function locationsUrl(string $account): string
    {
        return rtrim($this->config['locations_endpoint'] ?? 'https://mybusinessbusinessinformation.googleapis.com/v1', '/') . '/' . trim($account, '/') . '/locations';
    }

    protected function reviewsUrl(string $location): string
    {
        return rtrim($this->config['reviews_endpoint'] ?? 'https://mybusiness.googleapis.com/v4', '/') . '/' . trim($location, '/') . '/reviews';
    }

    protected function messageForStatus(int $status): string
    {
        return match ($status) {
            401, 403 => 'Google Business Profile is not authorized. Reconnect the business account.',
            404 => 'Google Business Profile location was not found.',
            429 => 'Google Business Profile quota has been reached.',
            500, 502, 503, 504 => 'Google Business Profile is temporarily unavailable.',
            default => 'Google Business Profile reviews could not be loaded.',
        };
    }
}
