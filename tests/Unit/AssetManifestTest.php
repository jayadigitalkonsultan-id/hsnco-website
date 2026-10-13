<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class AssetManifestTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'hsnco-asset-test-'.bin2hex(random_bytes(8));
        mkdir($this->directory);
        file_put_contents($this->directory.'/style.css', "body { color: black; }\r\n");
        file_put_contents($this->directory.'/image.bin', "\x00\x89PNG\r\n\xff");
        file_put_contents($this->directory.'/manifest.json', json_encode([
            'version' => 2,
            'files' => [
                ['file' => 'style.css', 'mode' => 'lf-text', 'sha256' => hash('sha256', "body { color: black; }\n")],
                ['file' => 'image.bin', 'mode' => 'binary', 'sha256' => hash('sha256', "\x00\x89PNG\r\n\xff")],
            ],
        ]));
    }

    protected function tearDown(): void
    {
        foreach (['style.css', 'image.bin', 'manifest.json'] as $file) {
            if (file_exists($this->directory.'/'.$file)) {
                unlink($this->directory.'/'.$file);
            }
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function check(): Process
    {
        $process = new Process([PHP_BINARY, dirname(__DIR__, 2).'/docker/check-assets.php', $this->directory, $this->directory.'/manifest.json']);
        $process->run();
        return $process;
    }

    public function test_windows_crlf_and_linux_lf_text_both_validate(): void
    {
        $this->assertSame(0, $this->check()->getExitCode());
        file_put_contents($this->directory.'/style.css', "body { color: black; }\n");
        $this->assertSame(0, $this->check()->getExitCode());
    }

    public function test_real_css_change_is_rejected(): void
    {
        file_put_contents($this->directory.'/style.css', "body { color: red; }\n");
        $result = $this->check();
        $this->assertSame(1, $result->getExitCode());
        $this->assertStringContainsString('Checksum mismatch: style.css', $result->getErrorOutput());
    }

    public function test_binary_bytes_are_never_line_ending_normalized(): void
    {
        file_put_contents($this->directory.'/image.bin', "\x00\x89PNG\n\xff");
        $result = $this->check();
        $this->assertSame(1, $result->getExitCode());
        $this->assertStringContainsString('Checksum mismatch: image.bin', $result->getErrorOutput());
    }

    public function test_missing_asset_is_rejected_with_a_specific_message(): void
    {
        unlink($this->directory.'/style.css');
        $result = $this->check();
        $this->assertSame(1, $result->getExitCode());
        $this->assertStringContainsString('Missing asset', $result->getErrorOutput());
    }

    public function test_outdated_manifest_is_rejected(): void
    {
        $manifest = json_decode(file_get_contents($this->directory.'/manifest.json'), true);
        $manifest['version'] = 1;
        file_put_contents($this->directory.'/manifest.json', json_encode($manifest));
        $result = $this->check();
        $this->assertSame(1, $result->getExitCode());
        $this->assertStringContainsString('version 2', $result->getErrorOutput());
    }
}