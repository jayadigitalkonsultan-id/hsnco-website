<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Cms\MediaRequest;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Services\SecurityAudit;

class MediaController extends AdminController
{
    public function index(Request $request)
    {
        $this->authorizeRead($request, 'viewAny', Media::class);
        $filters = $this->listingInput($request, []);
        $query = Media::query()->with(['usages.usable']);
        if ($this->preview($request)) {
            $query->where('visibility', 'public');
        } elseif (! $request->user()->managesContent()) {
            $query->where(fn ($q) => $q->where('visibility', 'public')->orWhere('uploaded_by', $request->user()->id));
        }
        if ($search = trim($filters['q'] ?? '')) {
            $query->where(fn ($q) => $q
                ->where('original_name', 'like', '%'.$search.'%')
                ->orWhere('alt_text', 'like', '%'.$search.'%')
                ->orWhere('caption', 'like', '%'.$search.'%'));
        }
        $records = $query->orderByDesc('updated_at')->orderBy('id')->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();

        $records->getCollection()->transform(function (Media $media) {
            $media->setAttribute('usage_references', $this->usageReferences($media));

            return $media;
        });

        return view('admin.media.index', compact('records'));
    }

    public function create(Request $request)
    {
        $this->authorizeRead($request, 'create', Media::class);

        return view('admin.media.form', ['record' => new Media, 'editable' => ! $this->preview($request)]);
    }

    public function edit(Request $request, Media $media)
    {
        if ($this->preview($request)) {
            abort_unless($media->visibility === 'public', 403);
        } else {
            Gate::authorize('view', $media);
        }

        $media->load('usages.usable');

        return view('admin.media.form', [
            'record' => $media,
            'editable' => ! $this->preview($request) && Gate::allows('update', $media),
            'usageReferences' => $this->usageReferences($media),
        ]);
    }

    public function store(MediaRequest $request)
    {
        $data = $request->validated();
        $file = $request->file('file');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $image = @getimagesize($file->getRealPath());
        if (! isset($extensions[$mime]) || ! $image || ($image['mime'] ?? null) !== $mime
            || $image[0] > 8000 || $image[1] > 8000) {
            throw ValidationException::withMessages(['file' => 'File harus berupa gambar JPEG, PNG, atau WebP yang valid dengan dimensi maksimal 8000 × 8000 piksel.']);
        }

        $disk = ($data['visibility'] ?? 'public') === 'private' ? 'local' : 'public';
        $path = $file->storeAs('media', Str::ulid().'.'.$extensions[$mime], $disk);
        if (! $path) {
            throw ValidationException::withMessages(['file' => 'Gambar belum dapat disimpan. Silakan coba kembali.']);
        }
        try {
            $media = DB::transaction(function () use ($request, $file, $data, $disk, $path, $mime, $image) {
                return Media::create([
                    'disk' => $disk, 'path' => $path, 'original_name' => basename($file->getClientOriginalName()),
                    'mime_type' => $mime, 'size' => $file->getSize(), 'width' => $image[0], 'height' => $image[1],
                    'alt_text' => $data['alt_text'] ?? null, 'caption' => $data['caption'] ?? null,
                    'visibility' => $data['visibility'] ?? 'public', 'uploaded_by' => $request->user()->id,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
        app(SecurityAudit::class)->record('media_uploaded', 'success', $request->user(), $request, null, ['visibility' => $media->visibility, 'mime_type' => $media->mime_type], $media);

        return redirect()->route('admin.media.edit', $media)->with('success', 'Gambar berhasil diunggah.');
    }

    public function update(MediaRequest $request, Media $media)
    {
        $media->update($request->safe()->only(['alt_text', 'caption']));
        app(SecurityAudit::class)->record('media_updated', 'success', $request->user(), $request, null, [], $media);

        return redirect()->route('admin.media.edit', $media)->with('success', 'Informasi gambar berhasil disimpan.');
    }

    public function destroy(Request $request, Media $media)
    {
        Gate::authorize('delete', $media);
        try {
            $media->delete();
        } catch (\LogicException $exception) {
            throw ValidationException::withMessages(['media' => 'Gambar masih digunakan oleh konten atau riwayat revisi. Lepaskan referensinya terlebih dahulu.']);
        }
        app(SecurityAudit::class)->record('media_deleted', 'success', $request->user(), $request, null, ['visibility' => $media->visibility], $media);

        return redirect()->route('admin.media.index')->with('success', 'Gambar dipindahkan ke arsip. File tetap disimpan untuk pemulihan.');
    }

    public function file(Request $request, Media $media)
    {
        if ($this->preview($request)) {
            abort_unless($media->visibility === 'public', 403);
        } else {
            Gate::authorize('view', $media);
        }
        abort_unless(in_array($media->disk, ['frontend', 'public', 'local'], true)
            && in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true), 404);
        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($media->path), 404);

        return response()->file($disk->path($media->path), [
            'Content-Type' => $media->mime_type, 'Content-Security-Policy' => "default-src 'none'; sandbox",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function usageReferences(Media $media): array
    {
        $references = [];
        $labels = [
            'page' => ['Halaman', 'title'],
            'page_section' => ['Section halaman', 'key'],
            'practice_area' => ['Bidang praktik', 'name'],
            'professional' => ['Profesional', 'name'],
            'insight' => ['Publikasi', 'title'],
            'site_setting' => ['Pengaturan situs', 'key'],
            'content_revision' => ['Revisi konten', 'version'],
        ];

        foreach ($media->usages as $usage) {
            $usable = $usage->usable;
            [$typeLabel, $titleField] = $labels[$usage->usable_type] ?? ['Konten', null];
            $title = $usable ? data_get($usable, $titleField) : null;
            if ($usage->usable_type === 'content_revision' && $usable) {
                $title = trim(($usable->revisionable?->title ?? $usable->revisionable?->name ?? 'Konten').' · v'.$usable->version);
            }
            $references[] = [
                'type' => $typeLabel,
                'title' => $title ?: 'Item '.$usage->usable_id,
                'field' => $usage->field,
            ];
        }

        foreach ([
            ['model' => \App\Models\Page::class, 'column' => 'og_media_id', 'type' => 'Halaman · gambar SEO', 'field' => 'title'],
            ['model' => \App\Models\PracticeArea::class, 'column' => 'image_id', 'type' => 'Bidang praktik · gambar', 'field' => 'name'],
            ['model' => \App\Models\Professional::class, 'column' => 'photo_id', 'type' => 'Profesional · foto', 'field' => 'name'],
            ['model' => \App\Models\Insight::class, 'column' => 'cover_media_id', 'type' => 'Publikasi · sampul', 'field' => 'title'],
        ] as $reference) {
            foreach ($reference['model']::withTrashed()->where($reference['column'], $media->id)->get([$reference['field'], 'id']) as $record) {
                $references[] = [
                    'type' => $reference['type'],
                    'title' => $record->{$reference['field']} ?: 'Item '.$record->id,
                    'field' => $reference['column'],
                ];
            }
        }

        return $references;
    }
}
