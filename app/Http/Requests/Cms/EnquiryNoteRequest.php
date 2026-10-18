<?php

namespace App\Http\Requests\Cms;

use App\Models\Enquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class EnquiryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enquiry = $this->route('enquiry');

        return $enquiry instanceof Enquiry && Gate::allows('update', $enquiry);
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:10000'], 'user_id' => ['prohibited'], 'enquiry_id' => ['prohibited']];
    }
}
