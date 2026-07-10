<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Http\Requests\Api\PropertyIndexRequest;
use App\Http\Requests\Api\PropertySearchRequest;
use App\Http\Resources\Api\PropertyDetailResource;
use App\Http\Resources\Api\PropertyListResource;
use App\Models\Agent;
use App\Models\Asset;
use App\Services\AssetViewService;
use App\Services\PropertyApiImageWarmer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function __construct(
        private readonly AssetViewService $assetViews,
        private readonly PropertyApiImageWarmer $imageWarmer,
    ) {}

    public function index(PropertyIndexRequest $request): AnonymousResourceCollection
    {
        $agent = $this->apiAgent($request);

        $properties = Asset::query()
            ->active()
            ->where('agent_id', $agent->id)
            ->with($this->listRelations())
            ->withCount('asset_images')
            ->when(
                $request->assetTypeId(),
                fn($query) => $query->where('asset_type_id', $request->assetTypeId()),
            )
            ->when(
                $request->userId(),
                fn($query) => $query->where('user_id', $request->userId()),
            )
            ->when(
                $request->zoneId(),
                fn($query) => $query->where('zone_id', $request->zoneId()),
            )
            ->when(
                $request->tagId(),
                fn($query, $tagId) => $query->whereHas('tags', fn($tagQuery) => $tagQuery
                    ->where('tags.id', $tagId)),
            )
            ->when(
                $request->isRecommendFilter() === true,
                fn($query) => $query->where('isrecommend', 'Y'),
            )
            ->when(
                $request->isRecommendFilter() === false,
                fn($query) => $query->where(fn($builder) => $builder
                    ->where('isrecommend', '!=', 'Y')
                    ->orWhereNull('isrecommend')),
            )
            ->latestFirst()
            ->paginate($request->perPage());

        $this->imageWarmer->warmListThumbnails($properties);

        return PropertyListResource::collection($properties);
    }

    public function show(Request $request, string $property): PropertyDetailResource
    {
        $agent = $this->apiAgent($request);

        $item = $this->findActiveProperty($agent, $property)
            ->with($this->detailRelations())
            ->firstOrFail();

        $this->imageWarmer->warmDetailImages($item);

        return new PropertyDetailResource($item);
    }

    public function recordView(Request $request, string $property): JsonResponse
    {
        $agent = $this->apiAgent($request);

        $asset = $this->findActiveProperty($agent, $property)->firstOrFail();

        $result = $this->assetViews->record($asset);

        return response()->json($result);
    }

    public function search(PropertySearchRequest $request): AnonymousResourceCollection
    {
        $agent = $this->apiAgent($request);

        $properties = Asset::query()
            ->active()
            ->where('agent_id', $agent->id)
            ->with($this->listRelations())
            ->withCount('asset_images')
            ->when(
                $request->text(),
                fn($query, $text) => $query->where(function ($builder) use ($text) {
                    $builder
                        ->where('code', 'like', "%{$text}%")
                        ->orWhere('name', 'like', "%{$text}%")
                        ->orWhereHas('address', fn($addressQuery) => $addressQuery
                            ->where('address1', 'like', "%{$text}%"));
                }),
            )
            ->when(
                $request->assetTypeId(),
                fn($query, $assetTypeId) => $query->where('asset_type_id', $assetTypeId),
            )
            ->when(
                $request->province(),
                fn($query, $province) => $query->whereHas('address', fn($addressQuery) => $addressQuery
                    ->where('province', 'like', "%{$province}%")),
            )
            ->when(
                $request->district(),
                fn($query, $district) => $query->whereHas('address', fn($addressQuery) => $addressQuery
                    ->where('district', 'like', "%{$district}%")),
            )
            ->when(
                $request->amphur(),
                fn($query, $amphur) => $query->whereHas('address', fn($addressQuery) => $addressQuery
                    ->where('amphur', 'like', "%{$amphur}%")),
            )
            ->when(
                $request->priceMin() !== null,
                fn($query) => $query->where('price_amounnt', '>=', $request->priceMin()),
            )
            ->when(
                $request->priceMax() !== null,
                fn($query) => $query->where('price_amounnt', '<=', $request->priceMax()),
            )
            ->latestFirst()
            ->paginate($request->perPage());

        $this->imageWarmer->warmListThumbnails($properties);

        return PropertyListResource::collection($properties);
    }

    private function apiAgent(Request $request): Agent
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        return $agent;
    }

    /**
     * Resolve an active property by primary key or assets.code for the given agent.
     */
    private function findActiveProperty(Agent $agent, string $property): Builder
    {
        return Asset::query()
            ->active()
            ->where('agent_id', $agent->id)
            ->where(function ($query) use ($property) {
                $query->whereKey($property)->orWhere('code', $property);
            });
    }

    /**
     * @return array<int|string, mixed>
     */
    private function listRelations(): array
    {
        return [
            'asset_type:id,name,code',
            'agent:id,name,code',
            'zone:id,name',
            'user:id,firstname,lastname,phone',
            'address:id,amphur,address1,soi,street',
            'asset_images' => fn($query) => $query
                ->orderByRaw("CASE WHEN isdefault = 'Y' THEN 0 ELSE 1 END")
                ->orderBy('seq')
                ->limit(1)
                ->with('image'),
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function detailRelations(): array
    {
        return [
            'asset_type:id,name',
            'agent:id,name,code,logo',
            'zone:id,name',
            'tags:id,name',
            'address',
            'user:id,firstname,lastname,phone,email,lineid,image_id',
            'user.image',
            'asset_images' => fn($query) => $query->orderBy('seq')->with('image'),
        ];
    }
}
