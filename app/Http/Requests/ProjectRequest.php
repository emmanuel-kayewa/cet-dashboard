<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:planned,in_progress,on_hold,completed,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high,critical',
            'project_manager' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
