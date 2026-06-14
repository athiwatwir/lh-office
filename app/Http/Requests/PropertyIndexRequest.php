<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'code' => ['nullable', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:255'],
            'asset_type_id' => ['nullable', 'uuid'],
            'zone_id' => ['nullable', 'uuid'],
            'user_id' => ['nullable', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function filters(): array
    {
        return [
            'code' => trim((string) $this->query('code', '')),
            'name' => trim((string) $this->query('name', '')),
            'asset_type_id' => trim((string) $this->query('asset_type_id', '')),
            'zone_id' => trim((string) $this->query('zone_id', '')),
            'user_id' => trim((string) $this->query('user_id', '')),
        ];
    }

    public function hasFilter(): bool
    {
        return collect($this->filters())->contains(fn (string $value) => $value !== '');
    }
}
