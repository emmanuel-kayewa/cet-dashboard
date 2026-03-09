<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PpSafeguardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canInputData();
    }

    public function rules(): array
    {
        $uniqueRule = 'unique:pp_safeguards,record_code';
        if ($this->route('pp_safeguard')) {
            $uniqueRule .= ',' . $this->route('pp_safeguard');
        }

        return [
            'record_code'       => "required|string|max:30|{$uniqueRule}",
            'scope'             => 'required|string|max:255',
            'pp_project_id'     => 'nullable|exists:pp_projects,id',
            'wayleave_received' => 'nullable|integer|min:0',
            'wayleave_cleared'  => 'nullable|integer|min:0',
            'wayleave_pending'  => 'nullable|integer|min:0',
            'survey_received'   => 'nullable|integer|min:0',
            'survey_cleared'    => 'nullable|integer|min:0',
            'survey_pending'    => 'nullable|integer|min:0',
            'paps'              => 'nullable|integer|min:0',
            'comp_paid_zmw'     => 'nullable|numeric|min:0',
            'report_period'     => 'nullable|string|max:30',
            'notes'             => 'nullable|string|max:2000',
        ];
    }
}
