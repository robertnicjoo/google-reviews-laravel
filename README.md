# Nicxon Google Reviews for Laravel

Production-ready Laravel package for displaying Google Reviews using multiple drivers, including **Google Business Profile OAuth** as the default integration method.

Unlike traditional Google Places integrations, the default driver does **not** require:

- Google Maps Platform billing
- A Google Maps API key
- A Google Place ID
- Credit card setup

The package is designed for:

- Laravel Blade
- Livewire
- Vue
- React
- Alpine.js
- Web Components
- Headless/API usage

---

# Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Drivers](#drivers)
  - [Business Profile Driver](#business-profile-driver-default)
  - [Places API Driver](#places-api-driver)
  - [Nicxon Connector Driver](#nicxon-connector-driver-future)
- [Blade Usage](#blade-usage)
- [Livewire Usage](#livewire-usage)
- [JSON API Endpoint](#json-api-endpoint)
- [Vue Integration](#vue-integration)
- [React Integration](#react-integration)
- [Web Component Integration](#web-component-integration)
- [Styling](#styling)
- [Caching](#caching)
- [Error Handling](#error-handling)
- [Security Notes](#security-notes)
- [Production Recommendations](#production-recommendations)
- [Publishing Assets](#publishing-assets)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

# Features

- Google Business Profile OAuth integration
- No Google Maps billing required by default
- Automatic caching and stale fallback support
- Blade component support
- Livewire component support
- Vue starter component
- React starter component
- Web Component support
- JSON API endpoint
- Scoped CSS styling
- Graceful error handling
- Multi-location support
- Laravel 10, 11, 12, and 13 compatibility

---

# Requirements

- PHP 8.2+
- Laravel 10+
- A Google account with access to a Google Business Profile
- OAuth credentials for Google Business Profile APIs

Official Google documentation:

- https://developers.google.com/my-business/content/review-data
- https://developers.google.com/my-business/reference/rest/v4/accounts.locations.reviews/list

---

# Installation

Install the package:

```bash
composer require nicxonsolutions/google-reviews
```

Publish configuration:

```bash
php artisan vendor:publish --tag=google-reviews-config
```

Optional asset publishing:

```bash
php artisan vendor:publish --tag=google-reviews-assets
```

For local/path repository installation and OAuth walkthroughs, see:

```text
docs/MANUAL_INSTALL_BUSINESS_PROFILE.md
```

---

# Configuration

Add the following variables to your `.env` file.

---

# Drivers

## Business Profile Driver (Default)

Recommended for most websites.

```dotenv
GOOGLE_REVIEWS_DRIVER=business_profile

GOOGLE_BUSINESS_PROFILE_CLIENT_ID=
GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET=
GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN=

GOOGLE_BUSINESS_PROFILE_LOCATION=

GOOGLE_REVIEWS_CACHE_TTL=1800
GOOGLE_REVIEWS_STALE_TTL=86400

GOOGLE_REVIEWS_SHOW_ERRORS=false
```

### Notes

`GOOGLE_BUSINESS_PROFILE_LOCATION` is optional.

If omitted, the package automatically uses the first accessible Business Profile location.

For multi-location accounts:

```dotenv
GOOGLE_BUSINESS_PROFILE_LOCATION=accounts/123456789/locations/987654321
```

This value is a Google Business Profile location identifier, not a Google Maps Place ID.

---

## Places API Driver

Optional official Google Places API integration.

```dotenv
GOOGLE_REVIEWS_DRIVER=places

GOOGLE_MAPS_API_KEY=
GOOGLE_REVIEWS_PLACE_ID=
```

### Important

This driver requires:

- Google Cloud Platform
- Places API enabled
- Billing enabled on the Google Cloud account

---

## Nicxon Connector Driver (Future)

Reserved for future hosted integrations.

```dotenv
GOOGLE_REVIEWS_DRIVER=nicxon_connector

NICXON_GOOGLE_REVIEWS_CONNECTOR_ENDPOINT=https://reviews.nicxonsolutions.com
NICXON_GOOGLE_REVIEWS_SITE_TOKEN=
```

Currently this driver returns a friendly setup message until the hosted service becomes available.

---

# Blade Usage

Render the default widget:

```blade
<x-google-reviews-widget />
```

Specify a Business Profile location:

```blade
<x-google-reviews-widget
    location="accounts/123456789/locations/987654321"
/>
```

---

# Livewire Usage

If `livewire/livewire` is installed, the package automatically registers:

```blade
<livewire:google-reviews-widget />
```

---

# JSON API Endpoint

The package exposes a resilient frontend endpoint.

Endpoints:

```text
/nicxon-google-reviews/data
/nicxon-google-reviews/data?source=accounts/123456789/locations/987654321
```

Example response:

```json
{
    "ok": true,
    "message": null,
    "status": 200,
    "place": {
        "name": "Business Name",
        "rating": 4.9
    },
    "reviews": []
}
```

Failure response example:

```json
{
    "ok": false,
    "message": "Connect a Google Business Profile account to show Google Reviews.",
    "status": null,
    "place": [],
    "reviews": []
}
```

The endpoint never exposes raw Google API exceptions directly to frontend users.

---

# Vue Integration

Publish assets:

```bash
php artisan vendor:publish --tag=google-reviews-assets
```

Published Vue component:

```text
resources/js/vendor/nicxon-google-reviews/vue/GoogleReviewsWidget.vue
```

Usage example:

```vue
<GoogleReviewsWidget />
```

---

# React Integration

Published React component:

```text
resources/js/vendor/nicxon-google-reviews/react/GoogleReviewsWidget.jsx
```

Usage example:

```jsx
<GoogleReviewsWidget />
```

---

# Web Component Integration

Published web component:

```text
resources/js/vendor/nicxon-google-reviews/web-component/nicxon-google-reviews.js
```

Usage:

```html
<nicxon-google-reviews></nicxon-google-reviews>
```

With a specific location:

```html
<nicxon-google-reviews
    location="accounts/123456789/locations/987654321">
</nicxon-google-reviews>
```

---

# Styling

The package automatically serves scoped widget CSS:

```text
/nicxon-google-reviews/assets/widget.css
```

### Benefits

- No Tailwind dependency required on host applications
- Styles are scoped under `.nxgr`
- Prevents style leakage into the host website

Source CSS entry:

```text
resources/css/widget.css
```

---

# Caching

The package caches successful review responses automatically.

Environment variables:

```dotenv
GOOGLE_REVIEWS_CACHE_TTL=1800
GOOGLE_REVIEWS_STALE_TTL=86400
```

### Cache Strategy

- Fresh cache reduces Google API requests
- Stale fallback improves uptime during outages
- Improves performance and page speed

---

# Error Handling

The package gracefully handles:

- Missing OAuth credentials
- Invalid refresh tokens
- Unauthorized Google accounts
- Missing Business Profile locations
- Google API outages
- Timeouts
- Rate limits
- Empty review responses

By default, production pages fail quietly.

Enable visible diagnostics during development:

```dotenv
GOOGLE_REVIEWS_SHOW_ERRORS=true
```

---

# Security Notes

Never commit sensitive credentials into source control.

Recommended:

- Store OAuth credentials in `.env`
- Rotate refresh tokens periodically
- Restrict Google OAuth applications properly
- Use HTTPS in production environments

---

# Production Recommendations

Recommended production setup:

- Enable Laravel cache
- Use Redis or Memcached
- Cache reviews aggressively
- Serve widgets via CDN if needed
- Enable stale fallback support
- Queue review refreshes for high-traffic websites

Recommended cache drivers:

```dotenv
CACHE_STORE=redis
```

---

# Publishing Assets

Publish package assets:

```bash
php artisan vendor:publish --tag=google-reviews-assets
```

Publish configuration:

```bash
php artisan vendor:publish --tag=google-reviews-config
```

---

# Troubleshooting

## Route Not Defined

If you encounter:

```text
Route [google-reviews.assets.css] not defined
```

Ensure package routes are loaded before route generation.

Run:

```bash
php artisan optimize:clear
composer dump-autoload
```

---

## Widget Not Showing Reviews

Verify:

- OAuth credentials are valid
- Refresh token is active
- Business Profile access exists
- Location identifier is correct

---

## Livewire Component Missing

Install Livewire:

```bash
composer require livewire/livewire
```

---

# License

Licensed under GPL-2.0-or-later.

Copyright © PT. Nicxon International Solutions
