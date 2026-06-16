<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PropertySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text' => ['nullable', 'string', 'max:255'],
            'asset_type_id' => ['nullable', 'uuid'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'amphur' => ['nullable', 'string', 'max:255'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function text(): ?string
    {
        $value = trim((string) $this->query('text', ''));

        return $value !== '' ? $value : null;
    }

    public function assetTypeId(): ?string
    {
        $assetTypeId = $this->query('asset_type_id');

        return is_string($assetTypeId) && $assetTypeId !== '' ? $assetTypeId : null;
    }

    public function province(): ?string
    {
        $value = trim((string) $this->query('province', ''));

        return $value !== '' ? $value : null;
    }

    public function district(): ?string
    {
        $value = trim((string) $this->query('district', ''));

        return $value !== '' ? $value : null;
    }

    public function amphur(): ?string
    {
        $value = trim((string) $this->query('amphur', ''));

        return $value !== '' ? $value : null;
    }

    public function perPage(): int
    {
        return min(max((int) $this->query('per_page', 20), 1), 100);
    }

    public function priceMin(): ?float
    {
        $value = $this->query('price_min');

        return is_numeric($value) ? (float) $value : null;
    }

    public function priceMax(): ?float
    {
        $value = $this->query('price_max');

        return is_numeric($value) ? (float) $value : null;
    }
}
