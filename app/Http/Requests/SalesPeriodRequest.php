<?php

namespace App\Http\Requests;

use App\Enums\SalesPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesPeriodRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->query->has('period')) {
            $this->merge(['period' => SalesPeriod::Jam->value]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['period' => ['required', 'string', Rule::enum(SalesPeriod::class)]];
    }
}
