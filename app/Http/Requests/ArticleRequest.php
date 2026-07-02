<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
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
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'agent_id' => ['nullable', 'uuid', Rule::exists('agents', 'id')],
            'isactive' => ['required', 'in:Y,N'],
            'text' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:'.self::IMAGE_MAX_KB],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'หัวข้อบทความ',
            'category_id' => 'ประเภทบทความ',
            'agent_id' => 'Agent',
            'isactive' => 'สถานะ',
            'text' => 'เนื้อหาบทความ',
            'cover' => 'รูปหน้าปก',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = ini_get('upload_max_filesize') ?: '2M';

        return [
            'cover.uploaded' => "อัปโหลดรูปไม่สำเร็จ ขนาดไฟล์ต้องไม่เกิน {$limit} (ค่าจำกัดของเซิร์ฟเวอร์)",
            'cover.max' => 'ขนาดรูปต้องไม่เกิน :max KB',
            'cover.image' => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
            'cover.mimes' => 'รองรับเฉพาะ JPG, PNG, GIF, WebP',
        ];
    }

    public static function imageMaxKilobytes(): int
    {
        return self::IMAGE_MAX_KB;
    }
}
