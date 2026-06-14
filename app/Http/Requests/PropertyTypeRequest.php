<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'seq' => ['required', 'integer', 'min:0'],
            'pic' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
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
            'pic' => 'รูปไอคอน',
        ];
    }
}
