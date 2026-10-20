<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ViewEncodingTest extends TestCase
{
    public function test_application_views_and_custom_assets_are_utf8_without_mojibake(): void
    {
        $root = dirname(__DIR__, 2);
        $directories = ['resources/views', 'public/assets/css', 'public/assets/js', 'public/assets/plugins-init', 'public/admin-assets/css', 'public/admin-assets/js', 'app', 'database/seeders'];
        $checked = 0;

        foreach ($directories as $directory) {
            if (!is_dir($root.'/'.$directory)) {
                continue;
            }

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'css', 'js', 'json'], true)) {
                    continue;
                }

                $text = file_get_contents($file->getPathname());
                $label = substr($file->getPathname(), strlen($root) + 1);
                $this->assertTrue(mb_check_encoding($text, 'UTF-8'), $label.' must be valid UTF-8.');
                $this->assertDoesNotMatchRegularExpression('/\x{FFFD}|\x{00C2}[\x{0080}-\x{00BF}]|\x{00C3}[\x{0080}-\x{00BF}]|\x{00E2}[\x{0080}-\x{00BF}\x{20AC}\x{2020}]|\x{00F0}\x{0178}/u', $text, $label.' contains corrupted text.');
                $checked++;
            }
        }

        $this->assertGreaterThan(0, $checked);
    }
}
