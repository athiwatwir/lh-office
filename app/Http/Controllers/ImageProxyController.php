<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\ImageProxyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImageProxyController extends Controller
{
    public function show(Request $request, Image $image, ImageProxyService $proxy): Response|RedirectResponse
    {
        $width = $request->filled('w') ? max(1, min(2000, $request->integer('w'))) : null;
        $height = $request->filled('h') ? max(1, min(2000, $request->integer('h'))) : null;
        $quality = $request->filled('q')
            ? max(1, min(100, $request->integer('q')))
            : null;

        $redirect = $proxy->redirectUrlForDimensions($image, $width, $height);

        if ($redirect !== null) {
            return redirect($redirect, 301);
        }

        $content = $proxy->render($image, $width, $height, $quality);

        if ($content === null) {
            abort(404);
        }

        return response($content, 200, $proxy->responseHeaders());
    }
}
