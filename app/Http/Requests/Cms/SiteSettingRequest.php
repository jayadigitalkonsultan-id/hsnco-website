<?php

namespace App\Http\Requests\Cms;

use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', SiteSetting::class);
    }

    public function rules(): array
    {
        $group = explode('.', (string) $this->input('key'))[0];

        return ['group' => ['required', Rule::in([$group]), Rule::in(config('cms.setting_groups'))], 'key' => ['required', Rule::in(config('cms.setting_keys'))], 'value' => ['required', 'array'], 'updated_by' => ['prohibited']];
    }
}
