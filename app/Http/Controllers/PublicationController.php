<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\News;
use App\Services\Cms\PublicationFeed;

class PublicationController extends Controller
{
    public function index(PublicationFeed $feed)
    {
        return view('frontend.publications.index', ['publications' => $feed->paginate()]);
    }

    public function show(string $kind, string $slug, PublicationFeed $feed)
    {
        abort_unless(in_array($kind, ['news', 'insight'], true), 404);
        $model = $kind === 'news' ? News::class : Insight::class;
        $article = $model::published()->with(['publicCoverMedia', 'publishedAuthor'])->where('slug', $slug)->firstOrFail();
        return view('frontend.publications.show', ['article' => $feed->decorate($article), 'recommended' => $feed->recommended($article)]);
    }
}
