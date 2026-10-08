<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:0', 'max:10'],
            'filled_at' => ['required', 'string', 'max:40'],
            'price' => ['required', 'integer', 'min:0'],
        ];
    }
}
