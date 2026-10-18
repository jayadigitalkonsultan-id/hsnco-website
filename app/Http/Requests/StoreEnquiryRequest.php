<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'submission_key' => ['required', 'ulid'],
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254'],
            'phone' => ['nullable', 'string', 'max:40'],
            'practice_area_id' => ['nullable', 'ulid', Rule::exists('practice_areas', 'id')->whereNull('deleted_at')->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())],
            'message' => ['required', 'string', 'max:3000'],
            'status' => ['prohibited'], 'assigned_to' => ['prohibited'],
            'ip_address' => ['prohibited'], 'user_agent' => ['prohibited'],
        ];
    }
}
