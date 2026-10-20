<?php
// Synchronize only the About introduction; never publish or alter other sections.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$page = App\Models\Page::where('key', 'about')->first();
if (!$page) { echo "No CMS About page: the public fallback uses the supplied profile.\n"; exit(0); }
$section = $page->sections()->where('key', 'introduction')->first();
if (!$section) { echo "No CMS introduction section; no database changes.\n"; exit(1); }
if (!in_array('--apply', $argv, true)) { echo "CMS introduction found. Use --apply to update only its two text columns.\n"; exit(0); }
$section->update(['content' => array_replace($section->content ?? [], [
    'column_one' => config('about.sections.introduction.content.column_one'),
    'column_two' => config('about.sections.introduction.content.column_two'),
])]);
echo "Updated only the About introduction. Publication status and other content preserved.\n";