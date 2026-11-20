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
            'frontend_url' => ['nullable', 'string', 'max:255', 'url'],
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
            'frontend_url' => 'Frontend URL',
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->filled('code')) {
            $payload['code'] = Str::upper(trim((string) $this->input('code')));
        }

        if ($this->exists('frontend_url')) {
            $frontendUrl = trim((string) $this->input('frontend_url'));
            $payload['frontend_url'] = $frontendUrl === '' ? null : rtrim($frontendUrl, '/');
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }
}
