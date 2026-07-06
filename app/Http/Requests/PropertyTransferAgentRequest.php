<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyTransferAgentRequest extends FormRequest
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
        $targetAgentId = $this->input('agent_id');

        return [
            'agent_id' => ['required', 'string', Rule::exists('agents', 'id')],
            'asset_type_id' => [
                'required',
                'string',
                Rule::exists('asset_types', 'id')->where(
                    fn ($query) => $query->where('agent_id', $targetAgentId),
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'agent_id' => 'เอเจนต์ปลายทาง',
            'asset_type_id' => 'ประเภททรัพย์ในเอเจนต์ปลายทาง',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'agent_id.required' => 'กรุณาเลือกเอเจนต์ปลายทาง',
            'agent_id.exists' => 'ไม่พบเอเจนต์ที่เลือก',
            'asset_type_id.required' => 'กรุณาเลือกประเภททรัพย์ในเอเจนต์ปลายทาง',
            'asset_type_id.exists' => 'ประเภททรัพย์ที่เลือกไม่ถูกต้องสำหรับเอเจนต์ปลายทาง',
        ];
    }
}
