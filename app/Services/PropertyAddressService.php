<?php

namespace App\Services;

use App\Models\Address;

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

        $payload = [
            'address1' => $data['address1'] ?? null,
            'address2' => $data['address2'] ?? null,
            'moo' => $data['moo'] ?? null,
            'soi' => $data['soi'] ?? null,
            'street' => $data['street'] ?? null,
            'district' => $data['district'] ?? null,
            'amphur' => $data['amphur'] ?? null,
            'province' => $this->resolveProvince($data),
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveProvince(array $data): ?string
    {
        $value = $data['province'] ?? $data['province_name'] ?? null;

        if (! filled($value)) {
            return null;
        }

        return trim((string) $value);
    }
}
