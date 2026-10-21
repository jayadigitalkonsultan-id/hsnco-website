<?php

namespace App\Services\Cms;

class EnglishCopy
{
    private array $copy;

    private string $pattern;

    private array $biographies = [];

    public function __construct()
    {
        $this->copy = [];
        foreach (file(resource_path('content/frontend-english.tsv'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            [$source, $target] = explode('|||', $line, 2);
            $this->copy[trim($source)] = str_replace("'", '’', trim($target));
        }
        foreach (json_decode(file_get_contents(resource_path('content/professionals-en.json')), true, 512, JSON_THROW_ON_ERROR) as $profile) {
            $this->biographies[str_replace("\r\n", "\n", $profile['source_biography'])] = $profile['biography'];

        }
        uksort($this->copy, fn ($a, $b) => strlen($b) <=> strlen($a));
        $this->pattern = '~(?<![\p{L}\p{N}_])(?:'.implode('|', array_map(fn ($key) => preg_quote($key, '~'), array_keys($this->copy))).')(?![\p{L}\p{N}_])~u';
        // Recognise copies partially converted by an earlier content update, without matching arbitrary edits.
        foreach ($this->biographies as $source => $english) {
            $partial = preg_replace_callback($this->pattern, fn ($match) => $this->copy[$match[0]], $source);
            $this->biographies[$partial] = $english;
            $this->biographies[preg_replace('/\byang\b/u', 'that', $partial)] = $english;
        }
        $normalised = [];
        foreach ($this->biographies as $source => $english) {
            $normalised[preg_replace('/\s+/u', ' ', trim($source))] = $english;
        }
        $this->biographies = $normalised;
    }

    public function text(?string $text): ?string
    {
        if ($text !== null && in_array($text, $this->copy, true)) {
            return $text;
        }
        if ($text !== null && isset($this->biographies[preg_replace('/\s+/u', ' ', trim($text))])) {
            return $this->biographies[preg_replace('/\s+/u', ' ', trim($text))];
        }
        if ($text !== null && isset($this->copy[$text])) {
            return $this->copy[$text];
        }

        return $text === null ? null : preg_replace_callback($this->pattern, fn ($match) => $this->copy[$match[0]], $text);
    }

    public function structured(mixed $value): mixed
    {
        if (! is_array($value)) {
            return is_string($value) ? $this->text($value) : $value;
        }
        $protected = ['id', 'slug', 'key', 'type', 'source_text', 'source_view', 'source_note', 'path', 'image', 'illustration_path', 'button_url', 'href', 'route', 'url', 'email', 'phone', 'office_address', 'address', 'evidence', 'client_name', 'attribution', 'level', 'status', 'disk'];
        foreach ($value as $key => $item) {
            if (in_array($key, $protected, true) || str_ends_with((string) $key, '_id') || str_ends_with((string) $key, '_ids')) {
                continue;
            }
            $value[$key] = $this->structured($item);
        }
        if (($value['route'] ?? null) === 'team' && in_array($value['label'] ?? null, ['Professional', 'Profesional'], true)) {
            $value['label'] = 'Professionals';
        }

        return $value;
    }
}
