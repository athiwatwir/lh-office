<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Province;

class PropertyAddressService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function sync(array $data, ?string $addressId = null): ?string
    {
        $hasAny = collect($data)->filter(fn ($value) => filled($value))->isNotEmpty();

        if (! $hasAny) {
            return $addressId;
        }

        $provinceId = $this->resolveProvinceId($data['province_name'] ?? null);

        $payload = [
            'address1' => $data['address1'] ?? null,
            'address2' => $data['address2'] ?? null,
            'moo' => $data['moo'] ?? null,
            'soi' => $data['soi'] ?? null,
            'street' => $data['street'] ?? null,
            'district' => $data['district'] ?? null,
            'amphur' => $data['amphur'] ?? null,
            'province_id' => $provinceId,
            'zipcode' => $data['zipcode'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        if ($addressId) {
            Address::query()->whereKey($addressId)->update($payload);

            return $addressId;
        }

        $address = Address::query()->create([
            ...$payload,
            'created' => now(),
        ]);

        return $address->id;
    }

    private function resolveProvinceId(?string $provinceName): ?string
    {
        if (! filled($provinceName)) {
            return null;
        }

        return Province::query()
            ->whereRaw('TRIM(province_name) = ?', [trim($provinceName)])
            ->value('id');
    }
}
