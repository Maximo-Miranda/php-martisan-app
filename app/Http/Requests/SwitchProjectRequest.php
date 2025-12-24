<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->whereNull('deleted_at'),
            ],
        ];

        // Super Admin can switch to any project
        if (! $this->user()->isSuperAdmin()) {
            $rules['project_id'][] = Rule::exists('project_user', 'project_id')
                ->where('user_id', $this->user()->id);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'You are not a member of this project.',
        ];
    }
}
