<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PublicationController extends Controller
{
    public function __invoke(Request $request)
    {
        Gate::authorize('viewAny', Insight::class);
        $queries = [];
        foreach (['insights' => Insight::class, 'news' => News::class] as $module => $model) {
            $queries[] = $model::query()->select('id', 'title', 'status', 'updated_at')->selectRaw("'".$module."' as module")
                ->when(!$request->user()->managesContent(), fn ($q) => $q->where('created_by', $request->user()->id));
        }
        $records = DB::query()->fromSub($queries[0]->unionAll($queries[1]), 'publications')
            ->orderByDesc('updated_at')->orderByDesc('id')->orderBy('module')->paginate(15);
        return view('admin.publications', compact('records'));
    }
}
