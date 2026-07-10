<?php

namespace App\Http\Requests;

use App\Services\ActiveAgentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyTypeRequest extends FormRequest
{
    private const IMAGE_MAX_KB = 2048;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $propertyTypeId = $this->route('propertyType');
        $agentId = app(ActiveAgentService::class)->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:45',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('asset_types', 'code')
                    ->where(fn ($query) => $query
                        ->where('agent_id', $agentId)
                        ->whereNull('deleted_at'))
                    ->ignore($propertyTypeId),
            ],
            'pic' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:'.self::IMAGE_MAX_KB],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ชื่อประเภททรัพย์สิน',
            'code' => 'รหัส',
            'pic' => 'รูปหน้าปก',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = ini_get('upload_max_filesize') ?: '2M';

        return [
            'code.regex' => 'รหัสต้องเป็นภาษาอังกฤษหรือตัวเลขเท่านั้น และห้ามมีช่องว่าง',
            'pic.uploaded' => "อัปโหลดรูปไม่สำเร็จ ขนาดไฟล์ต้องไม่เกิน {$limit} (ค่าจำกัดของเซิร์ฟเวอร์)",
            'pic.max' => 'ขนาดรูปต้องไม่เกิน :max KB',
            'pic.image' => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
            'pic.mimes' => 'รองรับเฉพาะ JPG, PNG, GIF, WebP',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('code')) {
            return;
        }

        $this->merge([
            'code' => trim((string) $this->input('code')),
        ]);
    }

    public static function imageMaxKilobytes(): int
    {
        return self::IMAGE_MAX_KB;
    }
}
