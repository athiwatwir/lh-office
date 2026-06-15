<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyImageUploadRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.required' => 'กรุณาเลือกรูปภาพ',
            'images.*.image' => 'ไฟล์ต้องเป็นรูปภาพ',
            'images.*.mimes' => 'รองรับเฉพาะไฟล์ jpeg, png, gif, webp',
            'images.*.max' => 'ขนาดรูปภาพต้องไม่เกิน 10 MB',
        ];
    }
}
