<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerRequest extends FormRequest
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
        $sellerId = $this->route('seller');

        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($sellerId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'lineid' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:255'],
            'pic' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'firstname' => 'ชื่อ',
            'lastname' => 'นามสกุล',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'lineid' => 'Line ID',
            'position' => 'ตำแหน่ง',
            'pic' => 'รูปโปรไฟล์',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        return [
            'firstname' => $this->validated('firstname'),
            'lastname' => $this->validated('lastname'),
            'email' => $this->input('email') ?? '',
            'phone' => $this->validated('phone'),
            'lineid' => $this->validated('lineid'),
            'position' => $this->validated('position'),
            'isseller' => 'Y',
            'isactive' => 'N',
        ];
    }
}
