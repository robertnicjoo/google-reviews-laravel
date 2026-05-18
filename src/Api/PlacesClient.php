<?php

namespace Nicxonsolutions\GoogleReviews\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Nicxonsolutions\GoogleReviews\Contracts\ReviewsDriver;
use Nicxonsolutions\GoogleReviews\Data\ReviewsResult;
use Throwable;

class PlacesClient implements ReviewsDriver
{
    protected const FIELD_MASK = 'id,displayName,googleMapsUri,rating,userRatingCount,reviews';

    public function __construct(protected array $config)
    {
    }

    public function reviews(?string $source = null, array $options = []): ReviewsResult
    {
        $placeId = $source ?: ($this->config['place_id'] ?? null);

        if (! $placeId) {
            return ReviewsResult::failed('Google Reviews place ID is not configured for the Places driver.');
        }

        return $this->placeDetails($this->normalizePlaceId($placeId), $options);
    }

    public function placeDetails(string $placeId, array $options = []): ReviewsResult
    {
        $apiKey = $options['api_key'] ?? $this->config['api_key'] ?? null;

        if (! $apiKey) {
            return ReviewsResult::failed('Google Maps API key is not configured.');
        }

        try {
            $response = Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => self::FIELD_MASK,
                ])
                ->get($this->endpoint($placeId), array_filter([
                    'languageCode' => $options['language'] ?? $this->config['language'] ?? null,
                    'regionCode' => $options['region'] ?? $this->config['region'] ?? null,
                ]));

            if ($response->failed()) {
                return ReviewsResult::failed($this->messageForStatus($response->status()), $response->status());
            }

            return ReviewsResult::fromGooglePlace($response->json(), (int) ($options['max_reviews'] ?? $this->config['max_reviews'] ?? 5));
        } catch (ConnectionException) {
            return ReviewsResult::failed('Google Reviews is temporarily unavailable.');
        } catch (RequestException $exception) {
            return ReviewsResult::failed($this->messageForStatus($exception->response?->status() ?? 500), $exception->response?->status() ?? 500);
        } catch (Throwable) {
            return ReviewsResult::failed('Google Reviews could not be loaded.');
        }
    }

    protected function endpoint(string $placeId): string
    {
        return rtrim($this->config['endpoint'] ?? 'https://places.googleapis.com/v1/places', '/') . '/' . rawurlencode($placeId);
    }

    protected function messageForStatus(int $status): string
    {
        return match ($status) {
            400 => 'Google Reviews request is invalid. Check the Place ID and Places API (New) setup.',
            401, 403 => 'Google Reviews is not authorized. Check GOOGLE_MAPS_API_KEY and Places API (New).',
            429 => 'Google Reviews quota has been reached.',
            500, 502, 503, 504 => 'Google Reviews is temporarily unavailable.',
            default => 'Google Reviews could not be loaded.',
        };
    }

    protected function normalizePlaceId(string $placeId): string
    {
        return str_starts_with($placeId, 'places/') ? substr($placeId, 7) : $placeId;
    }
}
