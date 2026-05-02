<?php

namespace App\Http\Requests\Student;

use App\Models\Job;
use App\Models\Resume;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'job_id'       => ['required', 'integer', 'exists:jobs,id'],
            'resume_id'    => ['nullable', 'integer', 'exists:resumes,id'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** Extra business-rule validation after field validation passes. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Job must be published
            if ($this->filled('job_id')) {
                $job = Job::find($this->input('job_id'));
                if (!$job || $job->status !== 'published') {
                    $v->errors()->add('job_id', 'This job is not available for applications.');
                }
            }

            // Resume must belong to this student
            if ($this->filled('resume_id')) {
                $student = $this->user()->student;
                $resume  = Resume::where('id', $this->input('resume_id'))
                    ->where('student_id', $student?->id)
                    ->first();
                if (!$resume) {
                    $v->errors()->add('resume_id', 'The selected resume does not belong to you.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ],
        ], 422));
    }
}
