<?php

namespace App\Http\Requests\Cms;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

class MediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $media = $this->route('media');

        return $media instanceof Media ? Gate::allows('update', $media) : Gate::allows('create', Media::class);
    }

    public function rules(): array
    {
        $media = $this->route('media');

        return [
            'file' => [
                $media ? 'prohibited' : 'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                'dimensions:max_width=8000,max_height=8000',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof UploadedFile || ! $value->isValid()) {
                        return;
                    }

                    $path = $value->getRealPath();
                    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
                    $image = @getimagesize($path);
                    $supported = ['image/jpeg', 'image/png', 'image/webp'];

                    if (! in_array($mime, $supported, true) || ! $image || ($image['mime'] ?? null) !== $mime) {
                        $fail('File harus berupa gambar JPEG, PNG, atau WebP yang valid.');
                    }
                },
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['sometimes', Rule::in($media instanceof Media ? [$media->visibility] : ['public', 'private'])],
            'path' => ['prohibited'],
            'disk' => ['prohibited'],
            'uploaded_by' => ['prohibited'],
        ];
    }
}
