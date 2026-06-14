<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'usercode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'usercode')->ignore($userId),
            ],
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'lineid' => ['nullable', 'string', 'max:100'],
            'fax' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'isactive' => ['nullable', Rule::in(['Y', 'N'])],
            'isseller' => ['nullable', Rule::in(['Y', 'N'])],
            'password' => [$this->isMethod('POST') ? 'required' : 'prohibited', 'string', 'min:8', 'confirmed'],
            'pic' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'usercode' => 'รหัสพนักงาน',
            'firstname' => 'ชื่อ',
            'lastname' => 'นามสกุล',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'lineid' => 'Line ID',
            'fax' => 'แฟกซ์',
            'position' => 'ตำแหน่ง',
            'isactive' => 'สถานะใช้งาน',
            'isseller' => 'ตัวแทนขาย',
            'password' => 'รหัสผ่าน',
            'pic' => 'รูปโปรไฟล์',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'isactive' => $this->boolean('isactive') ? 'Y' : 'N',
            'isseller' => $this->boolean('isseller') ? 'Y' : 'N',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        return $this->safe()->only([
            'usercode',
            'firstname',
            'lastname',
            'email',
            'phone',
            'lineid',
            'fax',
            'position',
            'isactive',
            'isseller',
        ]);
    }
}
