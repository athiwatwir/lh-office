<?php

namespace App\Http\Requests;

use App\Services\ActiveAgentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyTypeReorderRequest extends FormRequest
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
        $agentId = app(ActiveAgentService::class)->id();

        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => [
                'required',
                'string',
                Rule::exists('asset_types', 'id')
                    ->where(fn ($query) => $query
                        ->where('agent_id', $agentId)
                        ->whereNull('deleted_at')),
            ],
        ];
    }
}
