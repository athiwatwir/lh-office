<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Http\Resources\Api\AssetTypeResource;
use App\Models\Agent;
use App\Models\AssetType;
use App\Services\PropertyApiImageWarmer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyTypeController extends Controller
{
    public function __construct(
        private readonly PropertyApiImageWarmer $imageWarmer,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        $types = AssetType::query()
            ->with('image')
            ->forAgent($agent->id)
            ->orderedForDisplay()
            ->get();

        $this->imageWarmer->warmAssetTypeImages($types);

        return AssetTypeResource::collection($types);
    }
}
