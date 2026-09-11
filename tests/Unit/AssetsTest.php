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

    public function testAbsoluteUrlUsesTheHostTheVisitorAskedFor(): void
    {
        $this->assertSame('http://localhost:8095/img/og.jpg', absolute_url('img/og.jpg', ['HTTP_HOST' => 'localhost:8095']));
    }

    public function testAbsoluteUrlSwitchesToHttpsBehindTls(): void
    {
        $server = ['HTTP_HOST' => 'kurse.test', 'HTTPS' => 'on'];

        $this->assertSame('https://kurse.test/img/og.jpg', absolute_url('img/og.jpg', $server));
    }

    public function testHttpsOffIsNotTreatedAsHttps(): void
    {
        $server = ['HTTP_HOST' => 'kurse.test', 'HTTPS' => 'off'];

        $this->assertStringStartsWith('http://', absolute_url('img/og.jpg', $server));
    }

    public function testAHostHeaderCannotSmuggleAnotherSiteIn(): void
    {
        $server = ['HTTP_HOST' => 'evil.example.com/"><script>alert(1)</script>'];

        $this->assertSame('http://evil.example.com/img/og.jpg', absolute_url('img/og.jpg', $server));
    }

    public function testWithoutAHostHeaderThereIsNoAbsoluteUrl(): void
    {
        $this->assertSame('', absolute_url('img/og.jpg', []));
    }
}
