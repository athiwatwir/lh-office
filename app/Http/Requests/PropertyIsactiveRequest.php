<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyIsactiveRequest extends FormRequest
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
            'isactive' => ['required', 'boolean'],
        ];
    }

    public function isactiveValue(): string
    {
        return $this->boolean('isactive') ? 'Y' : 'N';
    }
}
