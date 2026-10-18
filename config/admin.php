<?php

use App\Http\Requests\Cms\InsightRequest;
use App\Http\Requests\Cms\PageRequest;
use App\Http\Requests\Cms\PracticeAreaRequest;
use App\Http\Requests\Cms\ProfessionalRequest;
use App\Models\Insight;
use App\Models\Page;
use App\Models\PracticeArea;
use App\Models\Professional;

return [
    // Read-only preview is additionally restricted to APP_ENV=local and a loopback peer.
    'local_preview' => env('ADMIN_LOCAL_PREVIEW', false),
    'modules' => [
        'pages' => ['label' => 'Halaman website', 'singular' => 'Halaman', 'parameter' => 'page', 'model' => Page::class, 'request' => PageRequest::class, 'title_field' => 'title', 'icon' => 'file'],
        'practice-areas' => ['label' => 'Praktik hukum', 'singular' => 'Praktik', 'parameter' => 'practice_area', 'model' => PracticeArea::class, 'request' => PracticeAreaRequest::class, 'title_field' => 'name', 'icon' => 'scales'],
        'professionals' => ['label' => 'Profesional', 'singular' => 'Profesional', 'parameter' => 'professional', 'model' => Professional::class, 'request' => ProfessionalRequest::class, 'title_field' => 'name', 'icon' => 'people'],
        'insights' => ['label' => 'Publikasi', 'singular' => 'Publikasi', 'parameter' => 'insight', 'model' => Insight::class, 'request' => InsightRequest::class, 'title_field' => 'title', 'icon' => 'news'],
    ],
];
