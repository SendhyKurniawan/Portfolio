<?php

use PHPUnit\Framework\TestCase;

final class GuestbookTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('CREATE TABLE guestbook (id INTEGER PRIMARY KEY, name TEXT, email TEXT, message TEXT)');
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        throttle_clear('guestbook', client_ip());
    }

    private function post(array $fields): void
    {
        $_POST = array_merge(['csrf_token' => csrf_token(), 'nama' => 'Ann', 'email' => 'ann@example.com', 'pesan' => 'Hello!'], $fields);
    }

    private function storedRows(): array
    {
        return $this->pdo->query('SELECT name, email, message FROM guestbook')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testValidateGuestbookTrimsAndAcceptsGoodInput(): void
    {
        $result = validate_guestbook(['name' => '  Ann ', 'email' => 'ann@example.com', 'message' => " Hi \n"]);

        $this->assertSame([], $result['errors']);
        $this->assertSame(['name' => 'Ann', 'email' => 'ann@example.com', 'message' => 'Hi'], $result['data']);
    }

    public function testValidateGuestbookReportsEachProblem(): void
    {
        $result = validate_guestbook(['name' => '   ', 'email' => 'nope', 'message' => str_repeat('x', 2001)]);

        $this->assertSame([
            'name' => 'Name is required.',
            'message' => 'Message must be 2000 characters or fewer.',
            'email' => 'Email address is not valid.',
        ], $result['errors']);
    }

    public function testCooldown(): void
    {
        $this->assertSame(0, guestbook_cooldown_left(null, 1000));
        $this->assertSame(45, guestbook_cooldown_left(985, 1000));
        $this->assertSame(0, guestbook_cooldown_left(900, 1000));
    }

    public function testValidPostIsStoredRawAndStartsCooldown(): void
    {
        $this->post(['nama' => '<b>Ann</b>', 'pesan' => 'Tom & Jerry']);

        $result = handle_guestbook_post($this->pdo);

        $this->assertSame('success', $result['type']);
        $this->assertSame([['name' => '<b>Ann</b>', 'email' => 'ann@example.com', 'message' => 'Tom & Jerry']], $this->storedRows());
        $this->assertIsInt($_SESSION['guestbook_last_sent']);
    }

    public function testMissingCsrfTokenIsRejectedAndInputKept(): void
    {
        $this->post(['csrf_token' => 'forged']);

        $result = handle_guestbook_post($this->pdo);

        $this->assertSame('error', $result['type']);
        $this->assertSame('Ann', $result['old']['name']);
        $this->assertSame([], $this->storedRows());
    }

    public function testHoneypotPretendsSuccessButStoresNothing(): void
    {
        $this->post(['website' => 'http://spam.test']);

        $this->assertSame('success', handle_guestbook_post($this->pdo)['type']);
        $this->assertSame([], $this->storedRows());
    }

    public function testSecondMessageWithinCooldownIsRejected(): void
    {
        $_SESSION['guestbook_last_sent'] = time();
        $this->post([]);

        $result = handle_guestbook_post($this->pdo);

        $this->assertSame('error', $result['type']);
        $this->assertStringStartsWith('Please wait', $result['message']);
        $this->assertSame([], $this->storedRows());
    }

    public function testPerIpCapStillAppliesWithFreshSession(): void
    {
        for ($i = 0; $i < GUESTBOOK_MAX_PER_HOUR; $i++) {
            throttle_record('guestbook', client_ip(), 3600);
        }
        $this->post([]);

        $result = handle_guestbook_post($this->pdo);

        $this->assertSame('error', $result['type']);
        $this->assertStringStartsWith('Too many messages from your network', $result['message']);
        $this->assertSame([], $this->storedRows());
    }

    public function testSuccessfulPostCountsTowardsIpCap(): void
    {
        $this->post([]);
        handle_guestbook_post($this->pdo);

        $this->assertSame(0, throttle_retry_after('guestbook', client_ip(), 2, 3600));
        $this->assertGreaterThan(0, throttle_retry_after('guestbook', client_ip(), 1, 3600));
    }

    public function testValidationErrorsAreReturned(): void
    {
        $this->post(['email' => 'bad']);

        $result = handle_guestbook_post($this->pdo);

        $this->assertSame(['type' => 'error', 'message' => 'Email address is not valid.', 'old' => ['name' => 'Ann', 'email' => 'bad', 'message' => 'Hello!']], $result);
    }

    public function testOfflineDatabaseIsReported(): void
    {
        $this->post([]);

        $result = handle_guestbook_post(null);

        $this->assertSame('error', $result['type']);
        $this->assertStringContainsString('database is offline', $result['message']);
    }

    public function testInsertFailureIsReportedNotThrown(): void
    {
        $this->pdo->exec('DROP TABLE guestbook');
        $this->post([]);

        $result = handle_guestbook_post($this->pdo);

        $this->assertSame('Transmission failed. Please try again later.', $result['message']);
        $this->assertArrayNotHasKey('guestbook_last_sent', $_SESSION);
    }
}
