<?php

namespace App\Http\Requests\Cms;

use App\Models\ContentModel;
use App\Models\ContentRevision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ContentRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $revision = $this->route('revision');
        $content = $this->route('content');

        return $revision instanceof ContentRevision ? Gate::allows('update', $revision)
            : ($content instanceof ContentModel && Gate::allows('revise', $content));
    }

    public function rules(): array
    {
        // Section-specific payload validation and sanitation belong to the CMS editor service.
        return [
            'payload' => ['required', 'array'],
            'status' => ['sometimes', Rule::in(['draft', 'review'])],
            'version' => ['prohibited'], 'revisionable_id' => ['prohibited'],
            'revisionable_type' => ['prohibited'], 'created_by' => ['prohibited'],
            'published_by' => ['prohibited'], 'published_at' => ['prohibited'],
        ];
    }
}
