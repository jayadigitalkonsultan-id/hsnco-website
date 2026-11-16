<?php

namespace App\Services\Cms;

use App\Models\Insight;
use App\Models\News;
use App\Models\ContentModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicationFeed
{
    private function query()
    {
        $insights = Insight::published()->select('id', 'published_at')->selectRaw("'insight' as kind");
        $news = News::published()->select('id', 'published_at')->selectRaw("'news' as kind");
        return DB::query()->fromSub($insights->unionAll($news), 'publications')
            ->orderByDesc('published_at')->orderByDesc('id')->orderBy('kind');
    }

    public function paginate(int $perPage = 9)
    {
        $page = $this->query()->paginate($perPage)->withQueryString();
        $page->setCollection($this->hydrate($page->getCollection()));
        return $page;
    }

    public function latest(int $limit = 3)
    {
        return $this->hydrate($this->query()->limit($limit)->get());
    }

    private function hydrate($rows)
    {
        $items = collect();
        foreach (['insight' => Insight::class, 'news' => News::class] as $kind => $model) {
            $ids = $rows->where('kind', $kind)->pluck('id');
            foreach ($model::published()->with(['publicCoverMedia', 'publishedAuthor'])->whereIn('id', $ids)->get() as $item) {
                $items->put($kind.':'.$item->id, $this->decorate($item));
            }
        }
        return $rows->map(fn ($row) => $items->get($row->kind.':'.$row->id))->filter()->values();
    }

    public function decorate(ContentModel $item): ContentModel
    {
        $kind = $item instanceof News ? 'news' : 'insight';
        $item->publication_kind = $kind;
        $item->publication_label = $kind === 'news' ? 'News' : 'Insight';
        $item->article_url = route('publications.show', ['kind' => $kind, 'slug' => $item->slug]);
        $media = $item->publicCoverMedia;
        $item->cover_url = $media && in_array($media->disk, ['frontend', 'public', 's3'], true)
            ? ($media->disk === 'frontend' ? asset('assets/'.ltrim($media->path, '/')) : Storage::disk($media->disk)->url($media->path)) : null;
        return $item;
    }

    public function recommended(?ContentModel $current = null)
    {
        return Insight::published()->with(['publicCoverMedia', 'publishedAuthor'])
            ->when($current instanceof Insight, fn ($q) => $q->whereKeyNot($current->id))
            ->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get()
            ->map(fn ($item) => $this->decorate($item));
    }
}
