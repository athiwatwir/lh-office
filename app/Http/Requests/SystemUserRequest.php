<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SystemUserRequest extends FormRequest
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
        $systemUserId = $this->route('system_user');

        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($systemUserId),
            ],
            'isactive' => ['nullable', Rule::in(['Y', 'N'])],
            'password' => [
                Rule::requiredIf(fn () => $this->isMethod('POST') && $this->input('isactive') === 'Y'),
                Rule::prohibitedIf(fn () => $this->isMethod('PUT')),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
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
            'isactive' => 'สถานะใช้งาน',
            'password' => 'รหัสผ่าน',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'isactive' => $this->boolean('isactive') ? 'Y' : 'N',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        return [
            ...$this->safe()->only([
                'firstname',
                'lastname',
                'email',
                'isactive',
            ]),
            'isseller' => 'N',
        ];
    }
}
