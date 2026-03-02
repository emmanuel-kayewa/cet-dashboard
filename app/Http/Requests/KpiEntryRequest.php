<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KpiEntryRequest extends FormRequest
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
            'kpi_id' => 'required|exists:kpis,id',
            'directorate_id' => 'required|exists:directorates,id',
            'value' => 'required|numeric',
            'period_date' => 'required|date',
            'period_type' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'directorate_id' => 'You can only submit data for your own directorate.',
        ];
    }
}
