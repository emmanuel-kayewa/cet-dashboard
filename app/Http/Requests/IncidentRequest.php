<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidentRequest extends FormRequest
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
            'description' => 'nullable|string|max:5000',
            'type' => 'required|in:outage,safety,security,environmental,equipment_failure,operational,other',
            'severity' => 'required|in:critical,high,medium,low',
            'status' => 'required|in:reported,investigating,mitigating,resolved,closed',
            'root_cause' => 'nullable|string|max:5000',
            'resolution' => 'nullable|string|max:5000',
            'lessons_learned' => 'nullable|string|max:5000',
            'affected_area' => 'nullable|string|max:255',
            'affected_customers' => 'nullable|integer|min:0',
            'occurred_at' => 'nullable|date',
            'resolved_at' => 'nullable|date|after_or_equal:occurred_at',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
