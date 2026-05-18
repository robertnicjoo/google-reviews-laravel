<?php

namespace Nicxonsolutions\GoogleReviews\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class ReviewsResult implements Arrayable, JsonSerializable
{
    public function __construct(
        public bool $ok,
        public ?string $message = null,
        public ?int $status = null,
        public array $place = [],
        public array $reviews = []
    ) {
    }

    public static function failed(string $message, ?int $status = null): self
    {
        return new self(false, $message, $status);
    }

    public static function fromGooglePlace(array $place, int $maxReviews = 5): self
    {
        $reviews = array_slice(array_map(fn (array $review) => [
            'author' => data_get($review, 'authorAttribution.displayName', 'Google user'),
            'author_uri' => data_get($review, 'authorAttribution.uri'),
            'avatar' => data_get($review, 'authorAttribution.photoUri'),
            'rating' => (float) data_get($review, 'rating', 0),
            'text' => data_get($review, 'text.text') ?: data_get($review, 'originalText.text'),
            'relative_time' => data_get($review, 'relativePublishTimeDescription'),
            'published_at' => data_get($review, 'publishTime'),
            'google_maps_uri' => data_get($review, 'googleMapsUri'),
        ], $place['reviews'] ?? []), 0, max(0, min($maxReviews, 5)));

        return new self(true, null, 200, [
            'id' => $place['id'] ?? null,
            'name' => data_get($place, 'displayName.text'),
            'rating' => (float) ($place['rating'] ?? 0),
            'review_count' => (int) ($place['userRatingCount'] ?? 0),
            'google_maps_uri' => $place['googleMapsUri'] ?? null,
        ], $reviews);
    }

    public static function fromBusinessProfile(array $payload, string $location): self
    {
        $reviews = array_map(fn (array $review) => [
            'author' => data_get($review, 'reviewer.displayName', 'Google user'),
            'author_uri' => null,
            'avatar' => data_get($review, 'reviewer.profilePhotoUrl'),
            'rating' => self::starRatingToNumber(data_get($review, 'starRating')),
            'text' => data_get($review, 'comment'),
            'relative_time' => null,
            'published_at' => data_get($review, 'createTime'),
            'google_maps_uri' => null,
        ], $payload['reviews'] ?? []);

        return new self(true, null, 200, [
            'id' => $location,
            'name' => data_get($payload, 'location.title', 'Google Reviews'),
            'rating' => (float) ($payload['averageRating'] ?? 0),
            'review_count' => (int) ($payload['totalReviewCount'] ?? count($reviews)),
            'google_maps_uri' => null,
        ], $reviews);
    }

    public static function fromConnector(array $payload): self
    {
        if (array_key_exists('ok', $payload) && ! $payload['ok']) {
            return self::failed($payload['message'] ?? 'Nicxon Google Reviews Connector could not load reviews.', $payload['status'] ?? null);
        }

        return new self(
            true,
            null,
            200,
            $payload['place'] ?? $payload['business'] ?? [],
            $payload['reviews'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'message' => $this->message,
            'status' => $this->status,
            'place' => $this->place,
            'reviews' => $this->reviews,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected static function starRatingToNumber(?string $rating): int
    {
        return match ($rating) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => 0,
        };
    }
}
