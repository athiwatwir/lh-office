<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleIndexRequest extends FormRequest
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
            'category_id' => ['nullable', 'uuid', Rule::exists('categories', 'id')],
            'q' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'ประเภทบทความ',
            'q' => 'คำค้นหา',
            'per_page' => 'จำนวนต่อหน้า',
        ];
    }

    public function categoryId(): ?string
    {
        $value = $this->query('category_id');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function keyword(): ?string
    {
        $value = trim((string) $this->query('q', ''));

        return $value !== '' ? $value : null;
    }

    public function perPage(): int
    {
        return min(max((int) $this->query('per_page', 20), 1), 100);
    }
}
