<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Models\ContentModel;
use App\Models\Insight;
use App\Models\Media;
use App\Models\Page;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Services\Admin\ContentWorkflowService;
use App\Services\Admin\DraftContentService;
use App\Services\Cms\AboutPageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\SecurityAudit;

class ContentController extends AdminController
{
    private function definition(Request $request): array
    {
        return config('admin.modules.'.$request->route('module'));
    }

    private function record(Request $request)
    {
        return $request->route($this->definition($request)['parameter']);
    }

    public function index(Request $request)
    {
        $definition = $this->definition($request);
        $this->authorizeRead($request, 'viewAny', $definition['model']);
        $filters = $this->listingInput($request, ['draft', 'review', 'published', 'archived']);
        $query = $this->contentQuery($request, $definition['model']);
        if ($search = trim($filters['q'] ?? '')) {
            $query->where($definition['title_field'], 'like', '%'.$search.'%');
        }
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }
        ($filters['sort'] ?? '') === 'title_asc' ? $query->orderBy($definition['title_field']) : $query->orderByDesc('updated_at');
        $records = $query->orderBy('id')->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
        $tree = [];
        if ($request->route('module') === 'pages') {
            $tree = $this->contentQuery($request, $definition['model'])->orderBy('title')->limit(1000)->get()
                ->map(fn ($page) => ['id' => $page->id, 'text' => $page->title, 'data' => [
                    'title' => $page->title, 'summary' => $page->sections()->count().' bagian halaman',
                    'url' => route('admin.pages.edit', $page),
                ]])->all();
        }

        return view('admin.content.index', compact('definition', 'records', 'tree', 'filters'));
    }

    public function create(Request $request)
    {
        $definition = $this->definition($request);
        $this->authorizeRead($request, 'create', $definition['model']);

        return $this->form($request, new $definition['model'], app(DraftContentService::class));
    }

    public function edit(Request $request)
    {
        $record = $this->record($request);
        $this->authorizeRead($request, 'view', $record);

        return $this->form($request, $record, app(DraftContentService::class));
    }

    public function showPreview(Request $request, DraftContentService $drafts)
    {
        abort_if($this->preview($request) || ! $request->user(), 403);
        $record = $this->record($request);
        Gate::authorize('view', $record);
        $revision = $drafts->previewRevision($record);
        abort_unless($revision, 404);
        Gate::authorize('view', $revision);

        $viewName = 'admin.content.preview';
        $viewData = [
            'record' => $record,
            'revision' => $revision,
            'payload' => $revision->payload,
            'definition' => $this->definition($request),
            'module' => $request->route('module'),
        ];
        if ($record instanceof Page && $record->key === 'about') {
            $about = app(AboutPageContent::class);
            $viewName = 'frontend.about-preview';
            $viewData = $about->viewData($about->fromPayload($record, $revision->payload)) + $viewData;
        }

        return response()->view($viewName, $viewData)->withHeaders([
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }

    private function form(Request $request, ContentModel $record, DraftContentService $drafts)
    {
        $definition = $this->definition($request);
        $workflowRecord = clone $record;
        $workingRevision = $record->exists ? $drafts->workingRevision($record) : null;
        $workingStatus = $workingRevision?->status
            ?? ($record->status === ContentStatus::Published ? ContentStatus::Draft : $record->status);
        $workingPayload = $workingRevision?->payload ?? [];
        $draftPracticeAreaIds = $workingPayload['practice_area_ids'] ?? null;
        $editable = ! $this->preview($request) && Gate::allows($record->exists ? 'update' : 'create', $record->exists ? $workflowRecord : $definition['model']);
        $record = $drafts->hydrateWorkingCopy($record);
        if ($record instanceof Page && $record->key === 'about') {
            $record = app(AboutPageContent::class)->prepare($record);
        }
        if ($record->exists && $workflowRecord->status === ContentStatus::Published && ! $workingRevision) {
            $record->setAttribute('status', $workingStatus);
        }
        $media = Media::where('visibility', 'public')->orderBy('original_name')->get(['id', 'original_name']);
        $practices = $this->contentQuery($request, PracticeArea::class)->orderBy('sort_order')->get(['id', 'name']);
        $authors = $this->contentQuery($request, Professional::class)->orderBy('name')->get(['id', 'name']);
        $insights = $this->contentQuery($request, Insight::class)->orderBy('title')->get(['id', 'title']);
        if ($record instanceof Professional && $record->exists) {
            if ($draftPracticeAreaIds !== null) {
                $record->setRelation('practiceAreas', $practices->whereIn('id', $draftPracticeAreaIds)->values());
            } else {
                $record->load('practiceAreas');
            }
        }

        $keyContactOptions = $record instanceof \App\Models\Industry ? Professional::published()->orderBy('name')->get(['id','name']) : collect();
        $selectedKeyContacts = $record instanceof \App\Models\Industry ? ($workingPayload['key_contact_ids'] ?? ($record->exists ? $record->keyContacts()->pluck('professionals.id')->all() : [])) : [];
        return view('admin.content.form', compact('keyContactOptions', 'selectedKeyContacts', 'definition', 'record', 'workflowRecord', 'workingRevision', 'workingStatus', 'media', 'practices', 'authors', 'insights', 'editable'));
    }

    public function store(Request $request, DraftContentService $service)
    {
        $definition = $this->definition($request);
        $validated = app($definition['request'])->validated();
        $record = $service->save(new $definition['model'], $validated, $request->user());
        app(SecurityAudit::class)->record('content_created', 'success', $request->user(), $request, null, ['module' => $request->route('module'), 'status' => $record->status->value], $record);

        return redirect()->route('admin.'.$request->route('module').'.edit', $record)->with('success', 'Draft berhasil dibuat.');
    }

    public function update(Request $request, DraftContentService $service)
    {
        $definition = $this->definition($request);
        $validated = app($definition['request'])->validated();
        $record = $service->save($this->record($request), $validated, $request->user());
        app(SecurityAudit::class)->record('content_updated', 'success', $request->user(), $request, null, ['module' => $request->route('module'), 'status' => $record->status->value], $record);

        return redirect()->route('admin.'.$request->route('module').'.edit', $record)->with('success', 'Perubahan draft berhasil disimpan.');
    }

    public function publish(Request $request, ContentWorkflowService $workflow)
    {
        $record = $this->record($request);
        $workflow->publish($record, $request->user());
        app(SecurityAudit::class)->record('content_published', 'success', $request->user(), $request, null, ['module' => $request->route('module')], $record);

        return redirect()->route('admin.'.$request->route('module').'.edit', $record)->with('success', 'Konten berhasil diterbitkan.');
    }

    public function archive(Request $request, ContentWorkflowService $workflow)
    {
        $record = $this->record($request);
        $workflow->archive($record, $request->user());
        app(SecurityAudit::class)->record('content_archived', 'success', $request->user(), $request, null, ['module' => $request->route('module')], $record);

        return redirect()->route('admin.'.$request->route('module').'.edit', $record)->with('success', 'Konten berhasil diarsipkan.');
    }

    public function destroy(Request $request)
    {
        $record = $this->record($request);
        Gate::authorize('delete', $record);
        $record->delete();
        app(SecurityAudit::class)->record('content_deleted', 'success', $request->user(), $request, null, ['module' => $request->route('module')], $record);

        return redirect()->route('admin.'.$request->route('module').'.index')->with('success', 'Konten dipindahkan ke arsip penghapusan.');
    }
}
