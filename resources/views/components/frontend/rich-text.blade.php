@props(['html' => ''])
<div {{ $attributes }}>{!! app(\App\Services\Cms\RichTextSanitizer::class)->sanitize($html) !!}</div>
