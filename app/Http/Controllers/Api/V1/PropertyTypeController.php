<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AssetTypeResource;
use App\Models\AssetType;
use App\Services\PropertyApiImageWarmer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyTypeController extends Controller
{
    public function __construct(
        private readonly PropertyApiImageWarmer $imageWarmer,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $types = AssetType::query()
            ->with('image')
            ->orderBy('seq')
            ->orderBy('name')
            ->get();

        $this->imageWarmer->warmAssetTypeImages($types);

        return AssetTypeResource::collection($types);
    }
}
