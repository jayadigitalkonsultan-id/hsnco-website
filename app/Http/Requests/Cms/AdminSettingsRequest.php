<?php

namespace App\Http\Requests\Cms;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', SiteSetting::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'office_address' => ['nullable', 'string', 'max:1000'],
            'email' => ['nullable', 'email', 'max:254'], 'phone' => ['nullable', 'string', 'max:40'],
            'contact_verified' => ['required', 'boolean'], 'tagline' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo_media_id' => ['nullable', 'string', 'ulid', Rule::exists('media', 'id')->where('visibility', 'public')->whereNull('deleted_at')],
            'favicon_path' => ['required', Rule::in(['assets/icons/favicon.svg'])],
            'navigation' => ['sometimes', 'required', 'array:home,about,practice-areas,team,insights,contact'],
            'navigation.home' => ['required_with:navigation', 'string', 'max:80'],
            'navigation.about' => ['required_with:navigation', 'string', 'max:80'],
            'navigation.practice-areas' => ['required_with:navigation', 'string', 'max:80'],
            'navigation.team' => ['required_with:navigation', 'string', 'max:80'],
            'navigation.insights' => ['required_with:navigation', 'string', 'max:80'],
            'navigation.contact' => ['required_with:navigation', 'string', 'max:80'],
        ];
    }
}
