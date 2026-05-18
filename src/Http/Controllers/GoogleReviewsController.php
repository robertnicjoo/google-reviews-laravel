<?php

namespace Nicxonsolutions\GoogleReviews\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Nicxonsolutions\GoogleReviews\GoogleReviews;

class GoogleReviewsController extends Controller
{
    public function show(Request $request, GoogleReviews $googleReviews, ?string $source = null): JsonResponse
    {
        return response()->json($googleReviews->reviews($request->query('source', $source))->toArray());
    }

    public function css(): Response
    {
        return response(file_get_contents(__DIR__ . '/../../../resources/dist/nicxon-google-reviews.css'), 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
