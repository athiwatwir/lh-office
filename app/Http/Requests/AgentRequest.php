<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgentRequest extends FormRequest
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
        $agentId = $this->route('agent');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:45',
                Rule::unique('agents', 'code')->ignore($agentId),
            ],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'api_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อเอเจนต์',
            'code' => 'รหัส',
            'logo' => 'โลโก้',
            'api_key' => 'API Key',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('code')) {
            return;
        }

        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
        ]);
    }
}
