<?php

namespace App\Http\Requests\Cms;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enquiry = $this->route('enquiry');

        return $enquiry instanceof Enquiry && Gate::allows('update', $enquiry);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(EnquiryStatus::class)], 'assigned_to' => ['nullable', 'ulid', Rule::exists('users', 'id')->where('status', 'active')->whereIn('role', ['admin', 'super_admin'])]];
    }
}
