<?php

use PHPUnit\Framework\TestCase;

final class UploadTest extends TestCase
{
    private string $dir;
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/portfolio-upload-test-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
        foreach ($this->tmpFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function tmpFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $contents);
        $this->tmpFiles[] = $path;
        return $path;
    }

    private function pngBytes(): string
    {
        // 1x1 transparent PNG
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    }

    public function testNoFileMeansNoChange(): void
    {
        $this->assertSame(['path' => null, 'error' => null], store_image_upload([], $this->dir));
        $this->assertSame(['path' => null, 'error' => null], store_image_upload(['error' => UPLOAD_ERR_NO_FILE], $this->dir));
    }

    public function testPhpIniSizeLimitIsReportedAsTooLarge(): void
    {
        $result = store_image_upload(['error' => UPLOAD_ERR_INI_SIZE], $this->dir);

        $this->assertSame('Image must be 5 MB or smaller.', $result['error']);
    }

    public function testOtherUploadErrorsAreGeneric(): void
    {
        $result = store_image_upload(['error' => UPLOAD_ERR_PARTIAL, 'tmp_name' => 'x'], $this->dir);

        $this->assertSame('Image upload failed. Please try again.', $result['error']);
    }

    public function testOversizedFileIsRejected(): void
    {
        $file = ['error' => UPLOAD_ERR_OK, 'tmp_name' => $this->tmpFile($this->pngBytes()), 'size' => UPLOAD_MAX_BYTES + 1];

        $this->assertSame('Image must be 5 MB or smaller.', store_image_upload($file, $this->dir, 'rename')['error']);
    }

    public function testPhpDisguisedAsImageIsRejected(): void
    {
        $file = ['error' => UPLOAD_ERR_OK, 'tmp_name' => $this->tmpFile('<?php system($_GET["c"]);'), 'size' => 30, 'name' => 'cat.jpg'];

        $result = store_image_upload($file, $this->dir, 'rename');

        $this->assertNull($result['path']);
        $this->assertSame('Only JPG, PNG, GIF or WebP images are allowed.', $result['error']);
        $this->assertDirectoryDoesNotExist($this->dir);
    }

    public function testRealImageIsStoredUnderRandomNameWithSniffedExtension(): void
    {
        $file = ['error' => UPLOAD_ERR_OK, 'tmp_name' => $this->tmpFile($this->pngBytes()), 'size' => 70, 'name' => '../../evil.php'];

        $result = store_image_upload($file, $this->dir, 'rename');

        $this->assertNull($result['error']);
        $this->assertMatchesRegularExpression('#^uploads/[a-f0-9]{32}\.png$#', $result['path']);
        $this->assertFileExists($this->dir . '/' . basename($result['path']));
    }

    public function testFailedMoveIsReported(): void
    {
        $file = ['error' => UPLOAD_ERR_OK, 'tmp_name' => $this->tmpFile($this->pngBytes()), 'size' => 70];

        $result = store_image_upload($file, $this->dir, fn () => false);

        $this->assertSame('Image could not be saved.', $result['error']);
    }

    public function testNewUploadWinsOverEverythingElse(): void
    {
        $this->assertSame(
            ['path' => 'uploads/new.png', 'error' => null],
            resolve_image_choice('uploads/old.png', true, 'https://x.test/a.png', 'uploads/new.png')
        );
    }

    public function testRemoveCheckboxBeatsUrlField(): void
    {
        $this->assertSame(['path' => '', 'error' => null], resolve_image_choice('https://x.test/old.png', true, 'https://x.test/old.png', null));
    }

    public function testUrlFieldMustBeHttp(): void
    {
        $this->assertSame(['path' => 'https://x.test/b.png', 'error' => null], resolve_image_choice('', false, ' https://x.test/b.png ', null));

        $result = resolve_image_choice('uploads/old.png', false, 'javascript:alert(1)', null);
        $this->assertSame('uploads/old.png', $result['path']);
        $this->assertSame('Image URL must start with http:// or https://.', $result['error']);
    }

    public function testClearingPrefilledUrlRemovesRemoteImage(): void
    {
        $this->assertSame(['path' => '', 'error' => null], resolve_image_choice('https://x.test/old.png', false, '', null));
    }

    public function testEmptyUrlKeepsLocalUpload(): void
    {
        $this->assertSame(['path' => 'uploads/old.png', 'error' => null], resolve_image_choice('uploads/old.png', false, '', null));
    }

    public function testDeleteStoredUploadRemovesOnlyOwnFiles(): void
    {
        mkdir($this->dir);
        file_put_contents($this->dir . '/abc.png', 'x');
        $outside = $this->tmpFile('keep me');

        delete_stored_upload('uploads/abc.png', $this->dir);
        delete_stored_upload('uploads/../' . basename($outside), $this->dir);
        delete_stored_upload('https://example.com/abc.png', $this->dir);
        delete_stored_upload('', $this->dir);

        $this->assertFileDoesNotExist($this->dir . '/abc.png');
        $this->assertFileExists($outside);
    }
}
