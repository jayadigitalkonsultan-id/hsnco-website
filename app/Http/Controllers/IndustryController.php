<?php

namespace App\Http\Controllers;

use App\Services\Cms\IndustryCatalog;

class IndustryController extends Controller
{
    public function index(IndustryCatalog $catalog)
    {
        return view('frontend.industries', ['industries' => $catalog->published()]);
    }

    public function show(string $sector, IndustryCatalog $catalog)
    {
        $industries = $catalog->published();
        $item = $industries->firstWhere('slug', $sector);
        abort_unless($item, 404);
        $profileModalData = $item['key_contacts']->mapWithKeys(fn ($person) => [$person->slug => [
            'name' => $person->name, 'position' => $person->position, 'summary' => $person->summary, 'biography' => $person->biography,
            'qualifications' => $person->qualifications ?? [], 'languages' => $person->languages ?? [],
            'practiceAreas' => $person->publishedPracticeAreas->pluck('name')->all(),
            'photo' => $person->public_photo_url, 'photoAlt' => $person->publicPhoto?->alt_text,
        ]]);

        return view('frontend.industry', ['industry' => $item, 'relatedIndustries' => $industries->where('slug', '!=', $sector)->take(3), 'profileModalData' => $profileModalData]);
    }
}
