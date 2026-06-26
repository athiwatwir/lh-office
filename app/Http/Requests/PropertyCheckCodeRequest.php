<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyCheckCodeRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:255'],
            'exclude' => ['nullable', 'uuid'],
        ];
    }

    public function code(): string
    {
        return trim($this->query('code', ''));
    }

    public function excludeId(): ?string
    {
        $value = $this->query('exclude');

        return is_string($value) && $value !== '' ? $value : null;
    }
}
