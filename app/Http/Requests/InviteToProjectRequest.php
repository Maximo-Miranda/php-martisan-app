<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteToProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('invite', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'email' => [
                'required',
                'email',
                Rule::unique('project_invitations')
                    ->where('project_id', $project->id)
                    ->whereNull('accepted_at'),
                function ($attribute, $value, $fail) use ($project) {
                    $user = User::where('email', $value)->first();
                    if ($user && $user->belongsToProject($project)) {
                        $fail('This user is already a member of the project.');
                    }
                },
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['Project Admin', 'Project Editor', 'Project Viewer']),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An invitation has already been sent to this email address.',
            'role.in' => 'The selected role is invalid.',
        ];
    }
}
