# Nicxon Google Reviews for Laravel

Laravel package for showing a Google Reviews widget. The default driver uses **Google Business Profile OAuth**, so website owners do not need a Places API key, a Place ID, or Google Maps Platform billing/credit-card setup.

The package also keeps two future paths open:

- `places` for the official Places API key + Place ID method.
- `nicxon_connector` for a future Nicxon-hosted connector if we decide to provide the easiest possible setup.

## Requirements

- PHP 8.2+
- Laravel 10+
- A Google account that owns or manages the Google Business Profile location
- OAuth credentials/token access for Google Business Profile APIs

No `GOOGLE_MAPS_API_KEY` is required for the default `business_profile` driver.

Official docs:

- [Google Business Profile review data](https://developers.google.com/my-business/content/review-data)
- [accounts.locations.reviews.list](https://developers.google.com/my-business/reference/rest/v4/accounts.locations.reviews/list)

## Install

```bash
composer require nicxonsolutions/google-reviews
php artisan vendor:publish --tag=google-reviews-config
```

For a full localhost/path-repository install and Business Profile OAuth walkthrough, see [docs/MANUAL_INSTALL_BUSINESS_PROFILE.md](docs/MANUAL_INSTALL_BUSINESS_PROFILE.md).

## Default Driver: Business Profile

```dotenv
GOOGLE_REVIEWS_DRIVER=business_profile
GOOGLE_BUSINESS_PROFILE_CLIENT_ID=your-oauth-client-id
GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET=your-oauth-client-secret
GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN=google-refresh-token
GOOGLE_BUSINESS_PROFILE_LOCATION=
GOOGLE_REVIEWS_CACHE_TTL=1800
GOOGLE_REVIEWS_STALE_TTL=86400
GOOGLE_REVIEWS_SHOW_ERRORS=false
```

`GOOGLE_BUSINESS_PROFILE_LOCATION` is optional. If it is empty, the package uses the first accessible location from the connected Google Business Profile account.

For multi-location accounts, set it to:

```dotenv
GOOGLE_BUSINESS_PROFILE_LOCATION=accounts/123456789/locations/987654321
```

That is a Google Business Profile location name, not a Google Maps Place ID.

## Blade

```blade
<x-google-reviews-widget />
```

With a specific Business Profile location:

```blade
<x-google-reviews-widget location="accounts/123456789/locations/987654321" />
```

## Livewire

If `livewire/livewire` is installed, the package registers:

```blade
<livewire:google-reviews-widget />
```

## JSON Endpoint

The package exposes a resilient endpoint for Vue, React, Alpine, or any other frontend:

```text
/nicxon-google-reviews/data
/nicxon-google-reviews/data?source=accounts/123456789/locations/987654321
```

The endpoint always returns JSON and does not throw Google API errors into the page:

```json
{
  "ok": false,
  "message": "Connect a Google Business Profile account to show Google Reviews.",
  "status": null,
  "place": [],
  "reviews": []
}
```

## Vue and React Starters

Publish starter components when you want to copy them into your app build:

```bash
php artisan vendor:publish --tag=google-reviews-assets
```

Published files:

- `resources/js/vendor/nicxon-google-reviews/vue/GoogleReviewsWidget.vue`
- `resources/js/vendor/nicxon-google-reviews/react/GoogleReviewsWidget.jsx`
- `resources/js/vendor/nicxon-google-reviews/web-component/nicxon-google-reviews.js`

## Custom Element

After importing the web component in your app bundle:

```html
<nicxon-google-reviews></nicxon-google-reviews>
```

With a specific Business Profile location:

```html
<nicxon-google-reviews location="accounts/123456789/locations/987654321"></nicxon-google-reviews>
```

## Optional Driver: Places API

This remains available, but it is no longer the default because Google Maps Platform billing is required.

```dotenv
GOOGLE_REVIEWS_DRIVER=places
GOOGLE_MAPS_API_KEY=your-google-cloud-api-key
GOOGLE_REVIEWS_PLACE_ID=your-place-id
```

Use this only when the site owner is comfortable enabling `Places API (New)` and Google billing.

## Future Driver: Nicxon Connector

The package includes a placeholder `nicxon_connector` driver so we can later add a hosted Nicxon service without changing the widget API:

```dotenv
GOOGLE_REVIEWS_DRIVER=nicxon_connector
NICXON_GOOGLE_REVIEWS_CONNECTOR_ENDPOINT=https://reviews.nicxonsolutions.com
NICXON_GOOGLE_REVIEWS_SITE_TOKEN=site-token
```

This driver currently returns a friendly setup error until a real hosted connector exists.

## Styling

The Blade and Livewire widgets load:

```text
/nicxon-google-reviews/assets/widget.css
```

This is a package-owned scoped CSS file, so the host website does not need Tailwind configured. The source Tailwind entry is included at `resources/css/widget.css`; the distributed CSS is scoped under `.nxgr` to avoid leaking styles into the user's website.

## Error Handling

The package handles missing OAuth tokens, missing locations, unauthorized Google accounts, request timeouts, quota limits, and Google outages. Successful responses are cached briefly, and the most recent successful response can be used as a stale fallback when Google is temporarily unavailable. By default, production pages fail quietly. Set this when you want visible diagnostics:

```dotenv
GOOGLE_REVIEWS_SHOW_ERRORS=true
```
