<?php

use PHPUnit\Framework\TestCase;

final class AssetsTest extends TestCase
{
    public function testAssetUrlAppendsFileModificationTime(): void
    {
        $expected = 'css/main.css?v=' . filemtime(__DIR__ . '/../../css/main.css');

        $this->assertSame($expected, asset_url('css/main.css'));
    }

    public function testAssetUrlKeepsTheRelativePrefix(): void
    {
        $this->assertStringStartsWith('../css/admin.css?v=', asset_url('css/admin.css', '../'));
    }

    public function testMissingAssetFallsBackToThePlainPath(): void
    {
        $this->assertSame('css/missing.css', asset_url('css/missing.css'));
    }

    public function testPathsOutsideTheProjectAreNotResolved(): void
    {
        $this->assertSame('../secrets.txt', asset_url('../secrets.txt'));
    }
}
