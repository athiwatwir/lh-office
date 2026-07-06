<?php

namespace App\Http\Requests;

use App\Models\Agent;
use App\Models\Asset;
use App\Services\ActiveAgentService;
use App\Services\SiteConfigService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyRequest extends FormRequest
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
        $agent = $this->resolveAgent();
        $propertyId = $this->route('property');
        $agentId = $agent?->id ?? ($propertyId
            ? Asset::query()->whereKey($propertyId)->value('agent_id')
            : app(ActiveAgentService::class)->id());

        $siteConfig = app(SiteConfigService::class);
        $zoneEnabled = $siteConfig->enabledForAgent($agent, 'zone');
        $specialPriceEnabled = $siteConfig->enabledForAgent($agent, 'special_price');

        $rules = [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('assets', 'code')
                    ->where(fn ($query) => $query->where('agent_id', $agentId))
                    ->ignore($propertyId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'asset_type_id' => [
                'required',
                'string',
                Rule::exists('asset_types', 'id')->where(
                    fn ($query) => $query->where('agent_id', $agentId),
                ),
            ],
            'user_id' => ['required', 'string', Rule::exists('users', 'id')],
            'price_amounnt' => ['nullable', 'numeric', 'min:0'],
            'price_per_wah' => ['nullable', 'numeric', 'min:0'],
            'price_rent' => ['nullable', 'numeric', 'min:0'],
            'price_amounnt_lower' => ['nullable', 'numeric', 'min:0'],
            'bedroom' => ['nullable', 'integer', 'min:0'],
            'bathroom' => ['nullable', 'integer', 'min:0'],
            'parking' => ['nullable', 'integer', 'min:0'],
            'floor' => ['nullable', 'numeric', 'min:0'],
            'floor_total' => ['nullable', 'numeric', 'min:0'],
            'area_rai' => ['nullable', 'numeric', 'min:0'],
            'area_ngan' => ['nullable', 'numeric', 'min:0'],
            'area_wah' => ['nullable', 'numeric', 'min:0'],
            'area_meter' => ['nullable', 'numeric', 'min:0'],
            'direction' => ['nullable', 'string', 'max:255'],
            'youtube_link' => ['nullable', 'string', 'max:255', 'url'],
            'tag_names' => ['nullable', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address.address1' => ['nullable', 'string', 'max:255'],
            'address.address2' => ['nullable', 'string', 'max:255'],
            'address.moo' => ['nullable', 'string', 'max:50'],
            'address.soi' => ['nullable', 'string', 'max:255'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:255'],
            'address.amphur' => ['nullable', 'string', 'max:255'],
            'address.province' => ['nullable', 'string', 'max:255'],
            'address.zipcode' => ['nullable', 'string', 'max:10'],
            'address.description' => ['nullable', 'string', 'max:1000'],
            'issale' => ['nullable', Rule::in(['Y', 'N'])],
            'isrent' => ['nullable', Rule::in(['Y', 'N'])],
            'issalerent' => ['nullable', Rule::in(['Y', 'N'])],
            'issellout' => ['nullable', Rule::in(['Y', 'N'])],
            'issaledown' => ['nullable', Rule::in(['Y', 'N'])],
            'iscovering' => ['nullable', Rule::in(['Y', 'N'])],
            'isdweller' => ['nullable', Rule::in(['Y', 'N'])],
        ];

        if ($zoneEnabled) {
            $rules['zone_id'] = ['required', 'string', Rule::exists('zones', 'id')];
        } else {
            $rules['zone_id'] = ['nullable', 'string', Rule::exists('zones', 'id')];
        }

        if ($specialPriceEnabled) {
            $rules['isspecial_marketprice'] = ['nullable', Rule::in(['Y', 'N'])];
            $rules['price_special'] = [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(fn () => $this->boolean('isspecial_marketprice')),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'รหัสทรัพย์',
            'name' => 'ชื่อทรัพย์',
            'description' => 'รายละเอียด',
            'asset_type_id' => 'ประเภททรัพย์',
            'zone_id' => 'โซน',
            'user_id' => 'ตัวแทน',
            'price_amounnt' => 'ราคาขาย',
            'price_special' => 'ราคาพิเศษ',
            'price_per_wah' => 'ราคาต่อ ตร.ว.',
            'price_rent' => 'ราคาเช่า',
            'price_amounnt_lower' => 'ราคาต่ำสุด',
            'isspecial_marketprice' => 'ราคาพิเศษ',
            'bedroom' => 'ห้องนอน',
            'bathroom' => 'ห้องน้ำ',
            'parking' => 'ที่จอดรถ',
            'floor' => 'ชั้น',
            'floor_total' => 'จำนวนชั้น',
            'area_rai' => 'ไร่',
            'area_ngan' => 'งาน',
            'area_wah' => 'ตารางวา',
            'area_meter' => 'ตารางเมตร',
            'direction' => 'ทิศ',
            'youtube_link' => 'ลิงก์ YouTube',
            'tag_names' => 'แท็กทำเล',
            'latitude' => 'ละติจูด',
            'longitude' => 'ลองจิจูด',
            'address.address1' => 'บ้านเลขที่',
            'address.address2' => 'ที่อยู่เพิ่มเติม',
            'address.moo' => 'หมู่ที่',
            'address.soi' => 'ซอย',
            'address.street' => 'ถนน',
            'address.district' => 'ตำบล/แขวง',
            'address.amphur' => 'อำเภอ/เขต',
            'address.province' => 'จังหวัด',
            'address.zipcode' => 'รหัสไปรษณีย์',
            'address.description' => 'รายละเอียดที่อยู่',
        ];
    }

    protected function prepareForValidation(): void
    {
        $agent = $this->resolveAgent();
        $specialPriceEnabled = app(SiteConfigService::class)->enabledForAgent($agent, 'special_price');

        if ($specialPriceEnabled && ! $this->has('isspecial_marketprice')) {
            $this->merge(['isspecial_marketprice' => 'N']);
        }

        foreach (['issale', 'isrent', 'issalerent', 'issellout', 'issaledown', 'iscovering', 'isdweller', 'isspecial_marketprice'] as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $this->merge([
                $field => $this->boolean($field) ? 'Y' : 'N',
            ]);
        }

        if ($specialPriceEnabled && $this->input('isspecial_marketprice') !== 'Y') {
            $this->merge(['price_special' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function assetData(): array
    {
        $agent = $this->resolveAgent();
        $siteConfig = app(SiteConfigService::class);

        $fields = [
            'code',
            'name',
            'description',
            'asset_type_id',
            'user_id',
            'price_amounnt',
            'price_per_wah',
            'price_rent',
            'price_amounnt_lower',
            'bedroom',
            'bathroom',
            'parking',
            'floor',
            'floor_total',
            'area_rai',
            'area_ngan',
            'area_wah',
            'area_meter',
            'direction',
            'youtube_link',
            'issale',
            'isrent',
            'issalerent',
            'issellout',
            'issaledown',
            'iscovering',
            'isdweller',
            'latitude',
            'longitude',
        ];

        if ($siteConfig->enabledForAgent($agent, 'zone')) {
            $fields[] = 'zone_id';
        }

        if ($siteConfig->enabledForAgent($agent, 'special_price')) {
            $fields[] = 'isspecial_marketprice';
            $fields[] = 'price_special';
        }

        return $this->safe()->only($fields);
    }

    /**
     * @return array<string, mixed>
     */
    public function addressData(): array
    {
        return $this->input('address', []);
    }

    private function resolveAgent(): ?Agent
    {
        $propertyId = $this->route('property');

        if ($propertyId) {
            $agentId = Asset::query()->whereKey($propertyId)->value('agent_id');

            return $agentId ? Agent::query()->find($agentId) : null;
        }

        return app(ActiveAgentService::class)->agent();
    }
}
