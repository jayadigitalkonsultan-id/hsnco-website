<?php

namespace App\Services\Cms;

use Illuminate\Validation\Rule;

class SectionPayloadSchema
{
    public function fields(string $type): array
    {
        return config('cms.section_fields.'.$type, []);
    }

    public function rules(string $type): array
    {
        $fields = $this->fields($type);
        $rules = ['content' => ['present', 'array:'.implode(',', array_keys($fields))]];

        foreach ($fields as $key => $field) {
            $path = 'content.'.$key;
            $max = $field['max'] ?? 5000;
            $presence = ($field['required'] ?? false) ? 'required' : 'nullable';
            $rules[$path] = match ($field['type']) {
                'text', 'rich_text' => [$presence, 'string', 'max:'.$max],
                'url' => [$presence, 'string', 'max:'.$max, 'regex:/^(?:https?:\/\/|mailto:|tel:|\/(?!\/)|#)[^\s]*$/i'],
                'number' => [$presence, 'integer', 'min:'.($field['min'] ?? 0), 'max:'.($field['max'] ?? 100000)],
                'date' => [$presence, 'date_format:Y-m-d'],
                'boolean' => ['nullable', 'boolean'],
                'media' => [$presence, 'ulid', Rule::exists('media', 'id')->whereNull('deleted_at')->where('visibility', 'public')],
                'ids' => array_merge([$presence, 'array'], isset($field['min']) ? ['min:'.$field['min']] : [], isset($field['max']) ? ['max:'.$field['max']] : ['max:100']),
                'items' => array_merge([$presence, 'array'], isset($field['min']) ? ['min:'.$field['min']] : [], isset($field['max']) ? ['max:'.$field['max']] : ['max:100']),
                default => ['prohibited'],
            };

            if ($field['type'] === 'ids') {
                $rules[$path.'.*'] = ['ulid', 'distinct', Rule::exists($field['table'], 'id')->whereNull('deleted_at')];
            }
            if ($field['type'] === 'items') {
                $itemFields = $field['items'];
                $allowed = implode(',', array_keys($itemFields));
                $rules[$path.'.*'] = ['required', 'array:'.$allowed];
                foreach ($itemFields as $itemKey => $itemField) {
                    $itemPath = $path.'.*.'.$itemKey;
                    $itemMax = $itemField['max'] ?? 5000;
                    $itemPresence = ($itemField['required'] ?? false) ? 'required' : 'nullable';
                    $rules[$itemPath] = match ($itemField['type']) {
                        'text', 'rich_text' => [$itemPresence, 'string', 'max:'.$itemMax],
                        'boolean' => ['nullable', 'boolean'],
                        'media' => [$itemPresence, 'ulid', Rule::exists('media', 'id')->whereNull('deleted_at')->where('visibility', 'public')],
                        'url' => [$itemPresence, 'string', 'max:2048', 'regex:/^(?:https?:\/\/|mailto:|tel:|\/(?!\/)|#)[^\s]*$/i'],
                        'date' => [$itemPresence, 'date_format:Y-m-d'],
                        default => ['prohibited'],
                    };
                }
            }
        }

        return $rules;
    }

    public function normalize(string $type, array $payload): array
    {
        $fields = $this->fields($type);
        $sanitizer = app(RichTextSanitizer::class);
        $normalized = [];
        foreach ($fields as $key => $field) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }
            $value = $payload[$key];
            if ($field['type'] === 'items' && is_string($value)) {
                $decoded = json_decode($value, true);
                $value = is_array($decoded) ? $decoded : $value;
            }
            if ($field['type'] === 'rich_text' && is_string($value)) {
                $value = $sanitizer->sanitize($value);
            }
            if ($field['type'] === 'items' && is_array($value)) {
                foreach ($value as $index => $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    foreach ($field['items'] as $itemKey => $itemField) {
                        if ($itemField['type'] === 'rich_text' && is_string($item[$itemKey] ?? null)) {
                            $value[$index][$itemKey] = $sanitizer->sanitize($item[$itemKey]);
                        }
                    }
                }
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
