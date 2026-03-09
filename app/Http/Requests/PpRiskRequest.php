<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PpRiskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canInputData();
    }

    public function rules(): array
    {
        $uniqueRule = 'unique:pp_risks,risk_code';
        if ($this->route('pp_risk')) {
            $uniqueRule .= ',' . $this->route('pp_risk');
        }

        return [
            'risk_code'        => "required|string|max:20|{$uniqueRule}",
            'pp_project_id'    => 'nullable|exists:pp_projects,id',
            'risk_category'    => 'required|string|max:255',
            'risk_description' => 'required|string|max:5000',
            'likelihood'       => 'required|integer|min:1|max:5',
            'impact'           => 'required|integer|min:1|max:5',
            'risk_level'       => 'required|string|in:Red,Amber,Green',
            'mitigation'       => 'nullable|string|max:5000',
            'owner'            => 'nullable|string|max:255',
            'due_date'         => 'nullable|date',
            'status'           => 'required|string|in:Open,Mitigating,Closed',
            'notes'            => 'nullable|string|max:2000',
        ];
    }
}
