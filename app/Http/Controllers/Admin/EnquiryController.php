<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Cms\EnquiryNoteRequest;
use App\Http\Requests\Cms\EnquiryRequest;
use App\Models\Enquiry;
use App\Models\User;
use App\Enums\EnquiryStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnquiryController extends AdminController
{
    public function index(Request $request)
    {
        $restricted = $this->preview($request);
        $records = null;
        if (! $restricted) {
            Gate::authorize('viewAny', Enquiry::class);
            $filters = $this->listingInput($request, ['new', 'read', 'in_progress', 'closed', 'spam']);
            $query = Enquiry::query();
            if ($filters['status'] ?? null) {
                $query->where('status', $filters['status']);
            }
            if ($search = trim($filters['q'] ?? '')) {
                $query->where(fn ($q) => $q
                    ->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('area_label', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%'));
            }
            $records = $query->orderByDesc('created_at')->orderBy('id')->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
        }

        return view('admin.enquiries.index', compact('records', 'restricted'));
    }

    public function show(Request $request, Enquiry $enquiry)
    {
        abort_if($this->preview($request), 403);
        Gate::authorize('view', $enquiry);
        if ($enquiry->status === EnquiryStatus::New) {
            $enquiry->forceFill(['status' => EnquiryStatus::Read, 'read_at' => now()])->save();
        }
        $enquiry->load('notes.user', 'assignee');
        $assignees = User::where('status', 'active')->whereIn('role', ['admin', 'super_admin'])->orderBy('name')->get();

        return view('admin.enquiries.show', compact('enquiry', 'assignees'));
    }

    public function update(EnquiryRequest $request, Enquiry $enquiry)
    {
        $data = $request->validated();
        $enquiry->fill($data);
        if ($data['status'] !== 'new' && $enquiry->read_at === null) {
            $enquiry->read_at = now();
        }
        $enquiry->closed_at = $data['status'] === 'closed' ? ($enquiry->closed_at ?? now()) : null;
        $enquiry->save();

        return redirect()->route('admin.enquiries.show', $enquiry)->with('success', 'Status permintaan berhasil diperbarui.');
    }

    public function note(EnquiryNoteRequest $request, Enquiry $enquiry)
    {
        $enquiry->notes()->create(['body' => $request->validated('body'), 'user_id' => $request->user()->id]);

        return redirect()->route('admin.enquiries.show', $enquiry)->with('success', 'Catatan internal berhasil ditambahkan.');
    }
}
