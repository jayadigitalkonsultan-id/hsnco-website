<?php

namespace App\Http\Requests;

use App\Models\PracticeArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EnquirySubmissionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [];
        if (! $this->filled('area_label') && $this->has('area')) {
            $data['area_label'] = $this->input('area');
        }
        if (! $this->filled('source')) {
            $data['source'] = 'contact';
        }

        $this->merge($data);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (['first_name', 'last_name', 'message'] as $field) {
                $value = $this->input($field);
                if (is_string($value) && trim($value) === '') {
                    $validator->errors()->add($field, 'Kolom ini wajib diisi.');
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'submission_key' => ['required', 'ulid'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:40'],
            'practice_area_id' => [
                'nullable',
                'ulid',
                Rule::exists('practice_areas', 'id')->whereNull('deleted_at')->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now()),
            ],
            'area_label' => ['required_without:practice_area_id', 'nullable', 'string', 'max:255'],
            'area' => ['sometimes', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:1', 'max:3000'],
            'source' => ['required', Rule::in(['home', 'contact'])],
            'website' => ['prohibited'],
            'status' => ['prohibited'],
            'assigned_to' => ['prohibited'],
            'ip_address' => ['prohibited'],
            'user_agent' => ['prohibited'],
            'notification_sent_at' => ['prohibited'],
        ];
    }
}
