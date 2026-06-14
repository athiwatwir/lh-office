<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditorImageUploadRequest;
use App\Services\ImageUploadOptions;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;

class EditorImageController extends Controller
{
    public const UPLOAD_DIRECTORY = 'upload/editor';

    public function __construct(
        private readonly ImageUploadService $imageUpload,
    ) {}

    public function store(EditorImageUploadRequest $request): JsonResponse
    {
        $filename = $this->imageUpload->store(
            $request->file('file'),
            new ImageUploadOptions(
                directory: self::UPLOAD_DIRECTORY,
                quality: 85,
                maxWidth: 1200,
                maxHeight: 1200,
            ),
        );

        return response()->json([
            'location' => $this->imageUpload->url($filename, self::UPLOAD_DIRECTORY),
        ]);
    }
}
