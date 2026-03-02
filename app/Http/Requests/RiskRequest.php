<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiskRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'required|in:operational,financial,strategic,compliance,technical,environmental,reputational',
            'likelihood' => 'required|integer|min:1|max:5',
            'impact' => 'required|integer|min:1|max:5',
            'status' => 'required|in:identified,assessed,mitigating,resolved,accepted',
            'mitigation_plan' => 'nullable|string|max:5000',
            'owner' => 'nullable|string|max:255',
            'review_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
