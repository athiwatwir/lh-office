<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyIndexRequest extends FormRequest
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
            'asset_type_id' => ['nullable', 'uuid'],
            'agent_id' => ['nullable', 'uuid'],
            'user_id' => ['nullable', 'uuid'],
            'isrecommend' => ['nullable', Rule::in(['0', '1', 'true', 'false', 'Y', 'N', 'y', 'n'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function assetTypeId(): ?string
    {
        $value = $this->query('asset_type_id');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function agentId(): ?string
    {
        $value = $this->query('agent_id');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function userId(): ?string
    {
        $value = $this->query('user_id');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function isRecommendFilter(): ?bool
    {
        if (! $this->has('isrecommend')) {
            return null;
        }

        $value = $this->query('isrecommend');

        if (is_bool($value)) {
            return $value;
        }

        return match (strtoupper((string) $value)) {
            '1', 'TRUE', 'Y' => true,
            '0', 'FALSE', 'N' => false,
            default => null,
        };
    }

    public function perPage(): int
    {
        return min(max((int) $this->query('per_page', 20), 1), 100);
    }
}
