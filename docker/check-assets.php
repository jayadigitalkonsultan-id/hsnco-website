<?php

$base = realpath($argv[1] ?? '/app/public/assets');
$manifestPath = $argv[2] ?? '/usr/local/etc/hsnco-assets.json';
if ($base === false || !is_readable($manifestPath)) {
    fwrite(STDERR, "Asset root or manifest is missing.\n");
    exit(1);
}
$manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
foreach ($manifest['files'] as $entry) {
    $relative = $entry['file'];
    if (str_contains($relative, '..') || !preg_match('~^[a-zA-Z0-9_./-]+$~', $relative)) {
        throw new RuntimeException('Invalid asset path in manifest.');
    }
    $file = realpath($base.DIRECTORY_SEPARATOR.$relative);
    if ($file === false || !str_starts_with($file, $base.DIRECTORY_SEPARATOR)
        || !is_file($file) || !is_readable($file) || filesize($file) === 0
        || hash_file('sha256', $file) !== $entry['sha256']) {
        fwrite(STDERR, "Missing, unreadable or changed asset: ".$relative."\n");
        exit(1);
    }
}
fwrite(STDOUT, count($manifest['files'])." frontend assets verified.\n");