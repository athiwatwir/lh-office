<?php

namespace App\Http\Resources\Api\Concerns;

use App\Services\ApiImageUrlService;

trait FormatsApiImageUrls
{
    protected function apiImageUrl(?string $url): ?string
    {
        return app(ApiImageUrlService::class)->full($url);
    }
}
