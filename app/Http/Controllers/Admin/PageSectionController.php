<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Cms\PageSectionRequest;
use App\Models\Page;
use App\Services\Admin\DraftContentService;
use App\Services\SecurityAudit;

class PageSectionController extends AdminController
{
    public function store(PageSectionRequest $request, Page $page, DraftContentService $drafts)
    {
        $drafts->savePageSection($page, null, $request->validated(), $request->user());
        app(SecurityAudit::class)->record('page_section_created', 'success', $request->user(), $request, null, ['key' => $request->validated('key')], $page);

        return back()->with('success', 'Bagian halaman tersimpan sebagai revisi draft.');
    }

    public function update(PageSectionRequest $request, Page $page, string $section, DraftContentService $drafts)
    {
        $drafts->savePageSection($page, $section, $request->validated(), $request->user());
        app(SecurityAudit::class)->record('page_section_updated', 'success', $request->user(), $request, null, ['key' => $request->validated('key')], $page);

        return back()->with('success', 'Perubahan bagian tersimpan sebagai revisi draft.');
    }

    public function destroy(Page $page, string $section, DraftContentService $drafts)
    {
        $drafts->savePageSection($page, $section, [], request()->user(), delete: true);
        app(SecurityAudit::class)->record('page_section_deleted', 'success', request()->user(), request(), null, ['section_id' => $section], $page);

        return back()->with('success', 'Penghapusan bagian tersimpan sebagai revisi draft.');
    }
}
