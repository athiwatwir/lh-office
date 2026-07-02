<?php

namespace App\Http\Requests;

use App\Support\TagNameParser;
use Illuminate\Foundation\Http\FormRequest;

class TagBulkStoreRequest extends FormRequest
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
            'names' => ['required', 'string', 'max:50000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'names' => 'รายชื่อแท็ก',
        ];
    }

    /**
     * @return list<string>
     */
    public function parsedNames(): array
    {
        return TagNameParser::parse($this->input('names', ''));
    }
}
