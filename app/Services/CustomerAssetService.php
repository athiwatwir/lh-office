<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\CustomerAsset;

class CustomerAssetService
{
    public function __construct(
        private readonly PropertyAddressService $addressService,
    ) {}

    /**
     * @param  array<string, mixed>  $customerData
     * @param  array<string, mixed>  $addressData
     */
    public function create(Agent $agent, array $payload, array $customerData, array $addressData = []): CustomerAsset
    {
        $customer = $this->resolveCustomer($customerData);
        $addressId = filled($addressData)
            ? $this->addressService->sync($addressData)
            : null;

        $customerAsset = CustomerAsset::query()->create([
            'customer_id' => $customer->id,
            'agent_id' => $agent->id,
            'type' => $payload['type'],
            'asset_type_id' => $payload['asset_type_id'],
            'zone_id' => $payload['zone_id'] ?? null,
            'asset_type_des' => $payload['asset_type_des'] ?? null,
            'description' => $payload['description'] ?? null,
            'floor_total' => $payload['floor_total'] ?? null,
            'bedroom' => $payload['bedroom'] ?? null,
            'bathroom' => $payload['bathroom'] ?? null,
            'kitchen_room' => $payload['kitchen_room'] ?? null,
            'reception_room' => $payload['reception_room'] ?? null,
            'dining_room' => $payload['dining_room'] ?? null,
            'maid_room' => $payload['maid_room'] ?? null,
            'parking' => $payload['parking'] ?? null,
            'area_rai' => $payload['area_rai'] ?? null,
            'area_ngan' => $payload['area_ngan'] ?? null,
            'area_wah' => $payload['area_wah'] ?? null,
            'area_meter' => $payload['area_meter'] ?? null,
            'price_per_wah' => $payload['price_per_wah'] ?? null,
            'price_amounnt' => $payload['price_amount'] ?? null,
            'budgets' => $payload['budgets'] ?? null,
            'isreqconsult' => $payload['isreqconsult'] ?? 'N',
            'address_id' => $addressId,
            'isread' => 'N',
            'created' => now(),
        ]);

        return $customerAsset->load([
            'customer:id,fullname,tel,email,lineid',
            'asset_type:id,name',
            'zone:id,name,description',
            'address.province',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveCustomer(array $data): Customer
    {
        $tel = trim((string) ($data['tel'] ?? ''));
        $fullname = trim((string) ($data['fullname'] ?? ''));

        $existing = Customer::query()
            ->where('tel', $tel)
            ->first();

        if ($existing !== null) {
            $existing->update([
                'fullname' => $fullname,
                'email' => $data['email'] ?? $existing->email,
                'lineid' => $data['lineid'] ?? $existing->lineid,
            ]);

            return $existing->fresh();
        }

        return Customer::query()->create([
            'fullname' => $fullname,
            'tel' => $tel,
            'email' => $data['email'] ?? null,
            'lineid' => $data['lineid'] ?? null,
            'created' => now(),
        ]);
    }
}
