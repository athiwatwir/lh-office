<?php

namespace App\Http\Requests\Api;

use App\Http\Middleware\AuthenticateAgentApiKey;
use App\Models\Agent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerAssetRequest extends FormRequest
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
        /** @var Agent $agent */
        $agent = $this->attributes->get(AuthenticateAgentApiKey::REQUEST_ATTRIBUTE);

        return [
            'type' => ['required', Rule::in(['S', 'P', 'sell', 'buy'])],
            'asset_type_id' => [
                'required',
                'uuid',
                Rule::exists('asset_types', 'id')->where(
                    fn ($query) => $query->where('agent_id', $agent->id),
                ),
            ],
            'zone_id' => ['nullable', 'uuid', Rule::exists('zones', 'id')],
            'asset_type_des' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'floor_total' => ['nullable', 'integer', 'min:0'],
            'bedroom' => ['nullable', 'integer', 'min:0'],
            'bathroom' => ['nullable', 'integer', 'min:0'],
            'kitchen_room' => ['nullable', 'integer', 'min:0'],
            'reception_room' => ['nullable', 'integer', 'min:0'],
            'dining_room' => ['nullable', 'integer', 'min:0'],
            'maid_room' => ['nullable', 'integer', 'min:0'],
            'parking' => ['nullable', 'integer', 'min:0'],
            'area_rai' => ['nullable', 'numeric', 'min:0'],
            'area_ngan' => ['nullable', 'numeric', 'min:0'],
            'area_wah' => ['nullable', 'numeric', 'min:0'],
            'area_meter' => ['nullable', 'numeric', 'min:0'],
            'price_amount' => ['nullable', 'numeric', 'min:0'],
            'price_per_wah' => ['nullable', 'numeric', 'min:0'],
            'budgets' => ['nullable', 'string', 'max:255'],
            'isreqconsult' => ['nullable', Rule::in(['Y', 'N', 'y', 'n', true, false, 1, 0])],
            'customer' => ['required', 'array'],
            'customer.fullname' => ['required', 'string', 'max:255'],
            'customer.tel' => ['required', 'string', 'max:50'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.lineid' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'array'],
            'address.address1' => ['nullable', 'string', 'max:255'],
            'address.address2' => ['nullable', 'string', 'max:255'],
            'address.moo' => ['nullable', 'string', 'max:50'],
            'address.soi' => ['nullable', 'string', 'max:100'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:100'],
            'address.amphur' => ['nullable', 'string', 'max:100'],
            'address.province' => ['nullable', 'string', 'max:100'],
            'address.zipcode' => ['nullable', 'string', 'max:10'],
            'address.description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $type = strtoupper((string) $this->input('type'));

        if (in_array($type, ['SELL', 'S'], true)) {
            $this->merge(['type' => 'S']);
        }

        if (in_array($type, ['BUY', 'P', 'PURCHASE', 'FIND'], true)) {
            $this->merge(['type' => 'P']);
        }

        if ($this->has('price_amounnt') && ! $this->has('price_amount')) {
            $this->merge(['price_amount' => $this->input('price_amounnt')]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('type') === 'S' && blank($this->input('price_amount')) && blank($this->input('price_per_wah'))) {
                $validator->errors()->add('price_amount', 'กรุณาระบุราคาขายหรือราคาต่อตารางวาสำหรับคำขอฝากขาย');
            }

            if ($this->input('type') === 'P' && blank($this->input('budgets'))) {
                $validator->errors()->add('budgets', 'กรุณาระบุงบประมาณสำหรับคำขอฝากหา');
            }
        });
    }

    public function normalizedType(): string
    {
        return (string) $this->input('type');
    }

    public function isConsultRequested(): string
    {
        $value = $this->input('isreqconsult');

        if (is_bool($value)) {
            return $value ? 'Y' : 'N';
        }

        return in_array(strtoupper((string) $value), ['Y', '1', 'TRUE'], true) ? 'Y' : 'N';
    }

    /**
     * @return array<string, mixed>
     */
    public function customerData(): array
    {
        return (array) $this->input('customer', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function addressData(): array
    {
        return (array) $this->input('address', []);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'ประเภทคำขอ',
            'asset_type_id' => 'ประเภททรัพย์สิน',
            'zone_id' => 'โซน',
            'price_amount' => 'ราคาขาย',
            'budgets' => 'งบประมาณ',
            'customer.fullname' => 'ชื่อลูกค้า',
            'customer.tel' => 'เบอร์โทรลูกค้า',
            'customer.email' => 'อีเมลลูกค้า',
            'customer.lineid' => 'Line ID',
        ];
    }
}
