<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PpProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canInputData();
    }

    public function rules(): array
    {
        return [
            'project_code'      => [
                'required', 'string', 'max:20',
                Rule::unique('pp_projects', 'project_code')->ignore($this->route('project')),
            ],
            'project_name'      => 'required|string|max:255',
            'sector'            => 'required|string|in:Generation,Transmission,Distribution,IPP',
            'sub_sector'        => 'nullable|string|max:255',
            'project_stage'     => 'required|string|in:Execution,Preparation,Completed,Cancelled,Commissioned',
            'status'            => 'nullable|string|in:On Track,Delayed,At Risk',
            'programme'         => 'nullable|string|max:255',
            'province'          => 'nullable|string|max:255',
            'district'          => 'nullable|string|max:255',
            'contractor'              => 'nullable|string|max:255',
            'developer'               => 'nullable|string|max:255',
            'cost_usd'                => 'nullable|numeric|min:0',
            'cost_zmw'                => 'nullable|numeric|min:0',
            'capacity_mw'             => 'nullable|numeric|min:0',
            'commissioned_mw'         => 'nullable|numeric|min:0',
            'progress_pct'            => 'nullable|numeric|min:0|max:100',
            'cod_planned'             => 'nullable|date',
            'key_issue_summary'       => 'nullable|string|max:5000',
            'last_update_date'        => 'nullable|date',
            'notes'                   => 'nullable|string|max:5000',
            // REMs classification
            'energy_type'             => 'nullable|string|max:30',
            'renewable_flag'          => 'nullable|boolean',
            'market_segment'          => 'nullable|string|max:255',
            'ownership_model'         => 'nullable|string|max:255',
            'owner_group'             => 'nullable|string|max:255',
            'owner_entity'            => 'nullable|string|max:255',
            'is_ipp'                  => 'nullable|boolean',
            'commissioned_mw_to_date' => 'nullable|numeric|min:0',
            'grid_connected'          => 'nullable|boolean',
            'cod_actual'              => 'nullable|date',
            'commissioned_date'       => 'nullable|date',
            'owner_subsidiary_name'   => 'nullable|string|max:255',
            'owner_subsidiary_flag'   => 'nullable|boolean',
            'commissioned_capacity_mw'=> 'nullable|numeric|min:0',
            'lifecycle_phase'         => 'nullable|string|in:Implementation,Commissioning/Operational,Procurement,Contracting',
            // PMO
            'project_manager'         => 'nullable|string|max:255',
            'planned_start'           => 'nullable|date',
            'planned_finish'          => 'nullable|date',
            'forecast_finish'         => 'nullable|date',
            'approved_budget'         => 'nullable|numeric|min:0',
            'committed_cost'          => 'nullable|numeric|min:0',
            'actual_spend'            => 'nullable|numeric|min:0',
            'forecast_at_completion'  => 'nullable|numeric|min:0',
            'next_decision'           => 'nullable|string|max:5000',
        ];
    }
}
