<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => ['required', 'string', 'max:255'],
            'seq' => ['required', 'integer', 'min:0'],
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
            'seq' => 'ลำดับการแสดงผล',
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
            'pic.uploaded' => "อัปโหลดรูปไม่สำเร็จ ขนาดไฟล์ต้องไม่เกิน {$limit} (ค่าจำกัดของเซิร์ฟเวอร์)",
            'pic.max' => 'ขนาดรูปต้องไม่เกิน :max KB',
            'pic.image' => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
            'pic.mimes' => 'รองรับเฉพาะ JPG, PNG, GIF, WebP',
        ];
    }

    public static function imageMaxKilobytes(): int
    {
        return self::IMAGE_MAX_KB;
    }
}
