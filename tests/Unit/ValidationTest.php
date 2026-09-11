<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
    }

    public static function urlProvider(): array
    {
        return [
            'https' => ['https://example.com/path?q=1', true],
            'http' => ['http://example.com', true],
            'uppercase scheme' => ['HTTPS://example.com', true],
            'javascript' => ['javascript:alert(1)', false],
            'data' => ['data:text/html;base64,PHNjcmlwdD4=', false],
            'ftp' => ['ftp://example.com/file', false],
            'relative path' => ['uploads/a.png', false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('urlProvider')]
    public function testIsHttpUrlOnlyAcceptsHttpAndHttps(string $url, bool $expected): void
    {
        $this->assertSame($expected, is_http_url($url));
    }

    public function testSafeUrlCollapsesNonHttpToHash(): void
    {
        $this->assertSame('https://example.com', safe_url('https://example.com'));
        $this->assertSame('#', safe_url('javascript:alert(1)'));
        $this->assertSame('#', safe_url(null));
    }

    public function testIsValidDateRequiresRealIsoDate(): void
    {
        $this->assertTrue(is_valid_date('2026-02-28'));
        $this->assertFalse(is_valid_date('2026-02-30'));
        $this->assertFalse(is_valid_date('28/02/2026'));
        $this->assertFalse(is_valid_date('2026-2-8'));
        $this->assertFalse(is_valid_date(''));
    }

    public function testExcerptLeavesShortTextAlone(): void
    {
        $this->assertSame('Short post.', excerpt('Short post.'));
    }

    public function testExcerptCollapsesWhitespace(): void
    {
        $this->assertSame('a b c', excerpt("a \n\n b\t c"));
    }

    public function testExcerptCutsAtWordBoundaryAndAddsEllipsis(): void
    {
        $text = str_repeat('word ', 40);
        $result = excerpt($text, 23);

        $this->assertSame('word word word word...', $result);
    }

    public function testExcerptNeverSplitsMultibyteCharacters(): void
    {
        $text = str_repeat('é', 150);
        $result = excerpt($text, 100);

        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
        $this->assertSame(str_repeat('é', 100) . '...', $result);
    }

    public function testValidateLengthsReportsMissingAndTooLong(): void
    {
        $errors = validate_lengths(
            ['title' => '', 'body' => str_repeat('x', 11), 'note' => ''],
            [
                'title' => ['label' => 'Title', 'max' => 10],
                'body' => ['label' => 'Body', 'max' => 10],
                'note' => ['label' => 'Note', 'max' => 10, 'required' => false],
            ]
        );

        $this->assertSame([
            'title' => 'Title is required.',
            'body' => 'Body must be 10 characters or fewer.',
        ], $errors);
    }

    public function testValidateLengthsCountsCharactersNotBytes(): void
    {
        $errors = validate_lengths(['name' => str_repeat('é', 10)], ['name' => ['label' => 'Name', 'max' => 10]]);

        $this->assertSame([], $errors);
    }

    public function testPostStringIgnoresArraysAndMissingKeys(): void
    {
        $_POST = ['name' => 'Ann', 'tags' => ['a', 'b']];

        $this->assertSame('Ann', post_string('name'));
        $this->assertSame('', post_string('tags'));
        $this->assertSame('', post_string('missing'));
    }
}
