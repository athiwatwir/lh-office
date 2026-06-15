<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyIsrecommendRequest extends FormRequest
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
            'isrecommend' => ['required', 'boolean'],
        ];
    }

    public function isrecommendValue(): string
    {
        return $this->boolean('isrecommend') ? 'Y' : 'N';
    }
}
