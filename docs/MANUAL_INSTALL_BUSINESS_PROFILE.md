# Manual Laravel Localhost Install + Business Profile Setup

This guide shows how to install the local package into a Laravel app and how to obtain the default `business_profile` driver values.

The default driver does **not** use Google Places API, `GOOGLE_MAPS_API_KEY`, Google Maps Place ID, or Google Maps Platform billing. It uses Google Business Profile OAuth instead.

Important: Google Business Profile APIs are protected APIs. You still need a Google Cloud project, OAuth credentials, and a Google account that owns or manages the business profile. Google may require Business Profile API access approval before the APIs appear in your Cloud project.

Official references:

- Google Business Profile basic setup: https://developers.google.com/my-business/content/basic-setup
- Google Business Profile OAuth: https://developers.google.com/my-business/content/implement-oauth
- Google review list endpoint: https://developers.google.com/my-business/reference/rest/v4/accounts.locations.reviews/list
- Google OAuth web server flow: https://developers.google.com/identity/protocols/oauth2/web-server

## 1. Install Into A Laravel App On Localhost

Assume your folders look like this:

```text
/Users/robert/Documents/New project/google-reviews-laravel
/Users/robert/Documents/New project/my-laravel-app
```

Open the Laravel app folder:

```bash
cd "/Users/robert/Documents/New project/my-laravel-app"
```

Add the local package as a Composer path repository:

```bash
composer config repositories.nicxon-google-reviews path "../google-reviews-laravel"
composer require nicxonsolutions/google-reviews:"*"
```

Publish the config:

```bash
php artisan vendor:publish --tag=google-reviews-config
```

Clear cached config:

```bash
php artisan optimize:clear
```

Add the widget to any Blade page:

```blade
<x-google-reviews-widget show-errors="true" />
```

Start Laravel:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

For JSON testing, open:

```text
http://127.0.0.1:8000/nicxon-google-reviews/data
```

Before OAuth is configured, the widget should fail gracefully with a setup message instead of breaking the page.

## 2. What Values The Business Profile Driver Needs

Your `.env` needs:

```dotenv
GOOGLE_REVIEWS_DRIVER=business_profile
GOOGLE_BUSINESS_PROFILE_CLIENT_ID=
GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET=
GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN=
GOOGLE_BUSINESS_PROFILE_LOCATION=
GOOGLE_REVIEWS_SHOW_ERRORS=true
```

`GOOGLE_BUSINESS_PROFILE_LOCATION` is optional. If empty, the package tries to use the first Google Business Profile location accessible by the connected account.

For multi-location accounts, set it later to something like:

```dotenv
GOOGLE_BUSINESS_PROFILE_LOCATION=accounts/123456789/locations/987654321
```

This is **not** a Google Maps Place ID.

## 3. Prepare Google Business Profile API Access

Use the Google account that owns or manages the business profile.

1. Go to Google Cloud Console.
2. Create or select a project.
3. Make sure your project/account has Business Profile API access approval if Google requires it.
4. Enable the Business Profile APIs Google lists in their basic setup docs. For this package, the important ones are:
   - Google My Business API
   - My Business Account Management API
   - My Business Business Information API
5. Configure the OAuth consent screen.
6. Add this OAuth scope:

```text
https://www.googleapis.com/auth/business.manage
```

For local testing, the app can stay in testing mode, but add your own Google account as a test user.

Note: Google’s docs mention multiple Business Profile APIs and project approval. If an API is not visible in Cloud Console, the issue is usually access approval, account eligibility, or using the wrong Google account.

## 4. Create OAuth Client ID And Secret

In Google Cloud Console:

1. Go to **APIs & Services > Credentials**.
2. Click **Create credentials > OAuth client ID**.
3. Choose **Web application**.
4. Add this authorized redirect URI:

```text
https://developers.google.com/oauthplayground
```

5. Save.
6. Copy the Client ID and Client Secret.

Put them in Laravel:

```dotenv
GOOGLE_BUSINESS_PROFILE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET=your-client-secret
```

## 5. Get A Refresh Token With OAuth Playground

Go to:

```text
https://developers.google.com/oauthplayground
```

Click the gear icon in the top-right and set:

```text
Use your own OAuth credentials: enabled
OAuth Client ID: your client id
OAuth Client secret: your client secret
```

In **Step 1**, paste this custom scope:

```text
https://www.googleapis.com/auth/business.manage
```

Click **Authorize APIs**.

Choose the Google account that owns or manages the business profile.

After redirecting back to OAuth Playground, click **Exchange authorization code for tokens**.

Copy the `refresh_token`.

Put it in Laravel:

```dotenv
GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN=your-refresh-token
```

If OAuth Playground does not show a `refresh_token`, revoke access and try again:

```text
https://myaccount.google.com/permissions
```

Remove the test app, then repeat the OAuth Playground flow.

## 6. Find The Business Profile Location Name

After your `.env` has client ID, client secret, and refresh token, clear config:

```bash
php artisan optimize:clear
```

Try the package JSON endpoint:

```text
http://127.0.0.1:8000/nicxon-google-reviews/data
```

If the connected account has one location, you can leave:

```dotenv
GOOGLE_BUSINESS_PROFILE_LOCATION=
```

For multiple locations, use OAuth Playground to list accounts:

```text
GET https://mybusinessaccountmanagement.googleapis.com/v1/accounts
```

Then list locations for an account:

```text
GET https://mybusinessbusinessinformation.googleapis.com/v1/accounts/ACCOUNT_ID/locations?readMask=name,title,metadata
```

Copy the returned `name`, for example:

```text
accounts/123456789/locations/987654321
```

Add it to Laravel:

```dotenv
GOOGLE_BUSINESS_PROFILE_LOCATION=accounts/123456789/locations/987654321
```

Clear config again:

```bash
php artisan optimize:clear
```

## 7. Final Localhost `.env` Example

```dotenv
GOOGLE_REVIEWS_DRIVER=business_profile
GOOGLE_BUSINESS_PROFILE_CLIENT_ID=1234567890-example.apps.googleusercontent.com
GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET=GOCSPX-example
GOOGLE_BUSINESS_PROFILE_REFRESH_TOKEN=1//example-refresh-token
GOOGLE_BUSINESS_PROFILE_LOCATION=accounts/123456789/locations/987654321
GOOGLE_REVIEWS_CACHE_TTL=1800
GOOGLE_REVIEWS_STALE_TTL=86400
GOOGLE_REVIEWS_SHOW_ERRORS=true
```

In production, set:

```dotenv
GOOGLE_REVIEWS_SHOW_ERRORS=false
```

## 8. Common Problems

### 403 Permission Denied

The Google account may not own/manage the business profile, the API may not be enabled, the project may not have Business Profile API access, or the OAuth consent screen/test users may be wrong.

### No Refresh Token

OAuth only returns a refresh token during the right offline-consent flow. In OAuth Playground, use your own OAuth credentials. If needed, remove the app from Google Account permissions and authorize again.

### No Locations Returned

Make sure you used the Google account that manages a verified Google Business Profile. Personal Google accounts without profile access will not return business locations.

### Reviews Load Once Then Stop

Run:

```bash
php artisan optimize:clear
```

Then test the JSON endpoint again. The package caches successful responses and uses a stale fallback when Google is temporarily unavailable.

## 9. About Credit Card / Billing

The default `business_profile` driver does not use Google Maps Platform Places API, so it does not require users to provide:

```dotenv
GOOGLE_MAPS_API_KEY=
GOOGLE_REVIEWS_PLACE_ID=
```

It does require OAuth access to the Google Business Profile account.

If Google Cloud prompts for billing while enabling Business Profile APIs, that is a Google Cloud account/project policy outside this package. This package’s default driver is designed specifically to avoid Places API billing and Place ID setup.
