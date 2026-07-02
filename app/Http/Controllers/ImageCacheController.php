<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\ImageProxyService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageCacheController extends Controller
{
    public function show(Image $image, string $variant, ImageProxyService $proxy): BinaryFileResponse|RedirectResponse
    {
        if (! in_array($variant, $proxy->variants(), true)) {
            abort(404);
        }

        $path = $proxy->publicCachePath($image, $variant);

        if (is_file($path) && $proxy->isCached($image, $variant)) {
            return response()->file($path, $proxy->responseHeaders());
        }

        $response = $proxy->serveVariant($image, $variant);

        if ($response === null) {
            abort(404);
        }

        return $response;
    }
}
