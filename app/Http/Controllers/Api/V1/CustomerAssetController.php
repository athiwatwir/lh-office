<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Http\Requests\Api\StoreCustomerAssetRequest;
use App\Http\Resources\Api\CustomerAssetResource;
use App\Models\Agent;
use App\Services\CustomerAssetService;
use Illuminate\Http\JsonResponse;

class CustomerAssetController extends Controller
{
    public function __construct(
        private readonly CustomerAssetService $customerAssets,
    ) {}

    public function store(StoreCustomerAssetRequest $request): JsonResponse
    {
        /** @var Agent $agent */
        $agent = $request->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        $customerAsset = $this->customerAssets->create(
            $agent,
            [
                'type' => $request->normalizedType(),
                'asset_type_id' => $request->validated('asset_type_id'),
                'zone_id' => $request->validated('zone_id'),
                'asset_type_des' => $request->validated('asset_type_des'),
                'description' => $request->validated('description'),
                'floor_total' => $request->validated('floor_total'),
                'bedroom' => $request->validated('bedroom'),
                'bathroom' => $request->validated('bathroom'),
                'kitchen_room' => $request->validated('kitchen_room'),
                'reception_room' => $request->validated('reception_room'),
                'dining_room' => $request->validated('dining_room'),
                'maid_room' => $request->validated('maid_room'),
                'parking' => $request->validated('parking'),
                'area_rai' => $request->validated('area_rai'),
                'area_ngan' => $request->validated('area_ngan'),
                'area_wah' => $request->validated('area_wah'),
                'area_meter' => $request->validated('area_meter'),
                'price_amount' => $request->validated('price_amount'),
                'price_per_wah' => $request->validated('price_per_wah'),
                'budgets' => $request->validated('budgets'),
                'isreqconsult' => $request->isConsultRequested(),
            ],
            $request->customerData(),
            $request->addressData(),
        );

        return (new CustomerAssetResource($customerAsset))
            ->response()
            ->setStatusCode(201);
    }
}
