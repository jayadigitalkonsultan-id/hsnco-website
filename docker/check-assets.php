<?php

function failAssetCheck(string $message): never
{
    fwrite(STDERR, $message."\n");
    exit(1);
}

$base = realpath($argv[1] ?? '/app/public/assets');
$manifestPath = $argv[2] ?? '/usr/local/etc/hsnco-assets.json';
if ($base === false || !is_readable($manifestPath)) {
    failAssetCheck('Asset root or manifest is missing/unreadable.');
}
$manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
if (($manifest['version'] ?? null) !== 2 || empty($manifest['files']) || !is_array($manifest['files'])) {
    failAssetCheck('Expected asset manifest version 2. Regenerate with tools/update-asset-manifest.mjs.');
}
$seen = [];
foreach ($manifest['files'] as $entry) {
    $relative = $entry['file'] ?? '';
    $mode = $entry['mode'] ?? '';
    $expected = $entry['sha256'] ?? '';
    if ($relative === '' || str_starts_with($relative, '/') || str_contains($relative, '..')
        || !preg_match('~^[a-zA-Z0-9_./-]+$~', $relative)
        || !in_array($mode, ['lf-text', 'binary'], true) || !preg_match('/^[a-f0-9]{64}$/', $expected)) {
        failAssetCheck('Invalid entry in asset manifest.');
    }
    if (isset($seen[$relative])) {
        failAssetCheck('Duplicate asset in manifest: '.$relative);
    }
    $seen[$relative] = true;
    $file = realpath($base.DIRECTORY_SEPARATOR.$relative);
    if ($file === false || !str_starts_with($file, $base.DIRECTORY_SEPARATOR) || !is_file($file)) {
        failAssetCheck('Missing asset or path outside asset root: '.$relative);
    }
    if (!is_readable($file)) {
        failAssetCheck('Unreadable asset: '.$relative.' (check runtime UID permissions).');
    }
    $data = file_get_contents($file);
    if ($data === false || $data === '') {
        failAssetCheck('Empty/unreadable asset: '.$relative);
    }
    if ($mode === 'lf-text') {
        if (!preg_match('//u', $data)) {
            failAssetCheck('Invalid UTF-8 text asset: '.$relative);
        }
        $data = str_replace("\r\n", "\n", $data);
    }
    $actual = hash('sha256', $data);
    if (!hash_equals($expected, $actual)) {
        failAssetCheck('Checksum mismatch: '.$relative.'; expected='.$expected.' actual='.$actual.' mode='.$mode);
    }
}
fwrite(STDOUT, count($seen)." frontend assets verified (manifest v2).\n");