<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PpFinancialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canInputData();
    }

    public function rules(): array
    {
        $uniqueRule = 'unique:pp_financials,finance_code';
        if ($this->route('pp_financial')) {
            $uniqueRule .= ',' . $this->route('pp_financial');
        }

        return [
            'finance_code'     => "required|string|max:30|{$uniqueRule}",
            'pp_project_id'    => 'nullable|exists:pp_projects,id',
            'as_of_date'       => 'required|date',
            'committed_amount' => 'nullable|numeric|min:0',
            'paid_to_date'     => 'nullable|numeric|min:0',
            'currency'         => 'required|string|in:USD,ZMW',
            'notes'            => 'nullable|string|max:2000',
        ];
    }
}
