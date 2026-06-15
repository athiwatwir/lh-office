<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PropertyIndexRequest;
use App\Http\Resources\Api\PropertyDetailResource;
use App\Http\Resources\Api\PropertyListResource;
use App\Models\Asset;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function index(PropertyIndexRequest $request): AnonymousResourceCollection
    {
        $properties = Asset::query()
            ->active()
            ->with([
                'asset_type:id,name',
                'agent:id,name,code',
                'zone:id,name',
                'asset_images' => fn ($query) => $query
                    ->orderByRaw("CASE WHEN isdefault = 'Y' THEN 0 ELSE 1 END")
                    ->orderBy('seq')
                    ->limit(1)
                    ->with('image'),
            ])
            ->withCount('asset_images')
            ->when(
                $request->assetTypeId(),
                fn ($query) => $query->where('asset_type_id', $request->assetTypeId()),
            )
            ->when(
                $request->agentId(),
                fn ($query) => $query->where('agent_id', $request->agentId()),
            )
            ->when(
                $request->isRecommendFilter() === true,
                fn ($query) => $query->where('isrecommend', 'Y'),
            )
            ->when(
                $request->isRecommendFilter() === false,
                fn ($query) => $query->where(fn ($builder) => $builder
                    ->where('isrecommend', '!=', 'Y')
                    ->orWhereNull('isrecommend')),
            )
            ->latestFirst()
            ->paginate($request->perPage());

        return PropertyListResource::collection($properties);
    }

    public function show(string $property): PropertyDetailResource
    {
        $item = Asset::query()
            ->active()
            ->with([
                'asset_type:id,name',
                'agent:id,name,code,logo',
                'zone:id,name',
                'address.province',
                'asset_images' => fn ($query) => $query->orderBy('seq')->with('image'),
            ])
            ->findOrFail($property);

        return new PropertyDetailResource($item);
    }
}
