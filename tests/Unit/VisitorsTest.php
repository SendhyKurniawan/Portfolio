<?php

use PHPUnit\Framework\TestCase;

final class VisitorsTest extends TestCase
{
    private const CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36';

    private PDO $pdo;

    protected function setUp(): void
    {
        $_SESSION = [];
        $_SERVER['HTTP_USER_AGENT'] = self::CHROME;
        $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testDigitsArePaddedLikeAnOdometer(): void
    {
        $this->assertSame('000042', visitor_counter_digits(42));
        $this->assertSame('000000', visitor_counter_digits(0));
        $this->assertSame('1234567', visitor_counter_digits(1234567));
    }

    public function testDigitsShowDashesWhenTheCountIsUnavailable(): void
    {
        $this->assertSame('------', visitor_counter_digits(null));
    }

    public function testBrowsersAreNotBots(): void
    {
        $this->assertFalse(is_probable_bot(self::CHROME));
        $this->assertFalse(is_probable_bot('Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1'));
    }

    public function testCrawlersScriptsAndEmptyAgentsAreBots(): void
    {
        $this->assertTrue(is_probable_bot('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'));
        $this->assertTrue(is_probable_bot('Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/128.0 Safari/537.36'));
        $this->assertTrue(is_probable_bot('curl/8.9.1'));
        $this->assertTrue(is_probable_bot(''));
        $this->assertTrue(is_probable_bot('   '));
    }

    public function testOnlyNewHumanNonAdminVisitsCount(): void
    {
        $this->assertTrue(visitor_should_count(false, self::CHROME, false));
        $this->assertFalse(visitor_should_count(true, self::CHROME, false), 'already counted this session');
        $this->assertFalse(visitor_should_count(false, self::CHROME, true), 'admin');
        $this->assertFalse(visitor_should_count(false, 'Googlebot/2.1', false), 'bot');
    }

    public function testIncrementCreatesTheTableOnFirstUse(): void
    {
        $this->assertSame(1, counter_increment($this->pdo, 'visitors'));
        $this->assertSame(2, counter_increment($this->pdo, 'visitors'));
        $this->assertSame(2, counter_read($this->pdo, 'visitors'));
    }

    public function testCountersAreIndependent(): void
    {
        counter_increment($this->pdo, 'visitors');

        $this->assertSame(1, counter_increment($this->pdo, 'other'));
        $this->assertSame(1, counter_read($this->pdo, 'visitors'));
    }

    public function testReadingAMissingCounterIsZero(): void
    {
        counter_increment($this->pdo, 'visitors');

        $this->assertSame(0, counter_read($this->pdo, 'never-used'));
    }

    public function testTrackVisitorCountsOncePerSession(): void
    {
        $this->assertSame(1, track_visitor($this->pdo));
        $this->assertSame(1, track_visitor($this->pdo), 'reload in the same session');

        $_SESSION = [];
        $this->assertSame(2, track_visitor($this->pdo), 'new session');
    }

    public function testTrackVisitorDoesNotCountBotsOrTheAdmin(): void
    {
        track_visitor($this->pdo);

        $_SESSION = [];
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1';
        $this->assertSame(1, track_visitor($this->pdo));

        $_SESSION = ['admin_logged_in' => true];
        $_SERVER['HTTP_USER_AGENT'] = self::CHROME;
        $this->assertSame(1, track_visitor($this->pdo));
    }

    public function testTrackVisitorReturnsNullWithoutADatabase(): void
    {
        $this->assertNull(track_visitor(null));
    }

    public function testTrackVisitorReturnsNullAndRetriesLaterWhenTheQueryFails(): void
    {
        $broken = new class ('sqlite::memory:') extends PDO {
            public function prepare(string $query, array $options = []): PDOStatement|false
            {
                throw new PDOException('database went away');
            }
        };

        $this->assertNull(track_visitor($broken));
        $this->assertArrayNotHasKey('visitor_counted', $_SESSION, 'a failed count is retried on the next page view');
    }
}
