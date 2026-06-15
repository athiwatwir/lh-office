<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyImageUploadRequest;
use App\Models\Asset;
use App\Models\AssetImage;
use App\Services\PropertyImageService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class PropertyImageController extends Controller
{
    public function __construct(
        private readonly PropertyImageService $propertyImages,
    ) {}

    public function store(PropertyImageUploadRequest $request, string $property): JsonResponse
    {
        $asset = Asset::query()->findOrFail($property);
        $uploaded = [];

        try {
            foreach ($request->file('images', []) as $file) {
                $assetImage = $this->propertyImages->upload($asset, $file);
                $uploaded[] = $this->propertyImages->formatForJson($assetImage);
            }
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'อัปโหลดรูปภาพเรียบร้อยแล้ว',
            'images' => $uploaded,
        ]);
    }

    public function setDefault(string $property, string $assetImage): JsonResponse
    {
        $asset = Asset::query()->findOrFail($property);
        $item = AssetImage::query()
            ->where('asset_id', $asset->id)
            ->findOrFail($assetImage);

        try {
            $this->propertyImages->setDefault($asset, $item);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'ตั้งค่ารูปหลักเรียบร้อยแล้ว',
            'id' => $item->id,
        ]);
    }

    public function destroy(string $property, string $assetImage): JsonResponse
    {
        $asset = Asset::query()->findOrFail($property);
        $item = AssetImage::query()
            ->where('asset_id', $asset->id)
            ->findOrFail($assetImage);

        try {
            $newDefaultId = $this->propertyImages->delete($asset, $item);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'ลบรูปภาพเรียบร้อยแล้ว',
            'id' => $item->id,
            'newDefaultId' => $newDefaultId,
        ]);
    }
}
