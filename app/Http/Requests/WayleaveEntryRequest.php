<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WayleaveEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canInputData();
    }

    public function rules(): array
    {
        return [
            'category' => 'required|in:wayleave,survey',
            'aspect' => 'required|string|max:255',
            'received' => 'required|integer|min:0|max:1000000000',
            'cleared' => 'required|integer|min:0|max:1000000000|lte:received',
            'report_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
