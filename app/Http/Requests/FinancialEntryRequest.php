<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()->canInputData()) {
            return false;
        }

        // Directorate heads can only submit data for their own directorate
        if ($this->user()->isDirectorateHead() && $this->directorate_id) {
            return (int) $this->directorate_id === (int) $this->user()->directorate_id;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'directorate_id' => 'required|exists:directorates,id',
            'category' => 'required|in:revenue,expense,budget,capex,opex',
            'sub_category' => 'nullable|string|max:255',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'budgeted_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'period_date' => 'required|date',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
