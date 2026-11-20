<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\ImageProxyService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageCacheController extends Controller
{
    public function show(string $image, string $variant, ImageProxyService $proxy): BinaryFileResponse
    {
        if (! in_array($variant, $proxy->variants(), true)) {
            abort(404);
        }

        $path = $proxy->publicCachePathFor($image, $variant);

        // Fast path: serve existing WebP without DB / session / freshness checks.
        if (is_file($path)) {
            return $this->fileResponse($path, $proxy);
        }

        $imageModel = Image::query()->findOrFail($image);
        $response = $proxy->serveVariant($imageModel, $variant);

        if ($response === null) {
            abort(404);
        }

        return $response;
    }

    private function fileResponse(string $path, ImageProxyService $proxy): BinaryFileResponse
    {
        return response()->file($path, $proxy->responseHeaders());
    }
}
