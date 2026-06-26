<?php

namespace App\Services;

class ApiImageUrlService
{
    public function full(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $base = rtrim((string) config('app.img_url', ''), '/');

        if ($base === '') {
            $base = rtrim((string) config('app.url', ''), '/');
        }

        if ($base === '') {
            return $url;
        }

        return $base.'/'.ltrim($url, '/');
    }
}
