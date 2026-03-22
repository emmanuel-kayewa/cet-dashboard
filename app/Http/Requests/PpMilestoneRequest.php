<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PpMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canInputData();
    }

    public function rules(): array
    {
        return [
            'milestone_code' => [
                'required', 'string', 'max:30',
                Rule::unique('pp_milestones', 'milestone_code')->ignore($this->route('milestone')),
            ],
            'pp_project_id'  => 'required|exists:pp_projects,id',
            'milestone'      => 'required|string|max:255',
            'category'       => 'nullable|string|in:Contract,Construction,Procurement,Engineering,Commissioning',
            'baseline_date'  => 'nullable|date',
            'forecast_date'  => 'nullable|date',
            'actual_date'    => 'nullable|date',
            'weight_pct'     => 'nullable|numeric|min:0|max:100',
            'delay_days'     => 'nullable|integer',
            'owner'          => 'nullable|string|max:255',
            'status'         => 'required|string|in:Completed,In Progress,Pending,Overdue,At Risk,Not Started',
            'notes'          => 'nullable|string|max:2000',
        ];
    }
}
