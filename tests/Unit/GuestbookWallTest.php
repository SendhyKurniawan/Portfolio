<?php

use PHPUnit\Framework\TestCase;

final class GuestbookWallTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('CREATE TABLE guestbook (id INTEGER PRIMARY KEY, name TEXT, email TEXT, message TEXT,
            created_at TEXT, approved INTEGER NOT NULL DEFAULT 0)');
    }

    private function add(string $name, string $message, string $createdAt, int $approved): void
    {
        $this->pdo->prepare('INSERT INTO guestbook (name, email, message, created_at, approved) VALUES (?, ?, ?, ?, ?)')
            ->execute([$name, $name . '@example.com', $message, $createdAt, $approved]);
    }

    public function testOnlyApprovedMessagesAreShown(): void
    {
        $this->add('Ann', 'Approved one', '2026-09-01 10:00:00', 1);
        $this->add('Bob', 'Waiting for approval', '2026-09-02 10:00:00', 0);

        $wall = fetch_public_guestbook($this->pdo);

        $this->assertCount(1, $wall);
        $this->assertSame('Ann', $wall[0]['name']);
    }

    public function testMessagesReadOldestFirstLikeAChat(): void
    {
        $this->add('Ann', 'First', '2026-09-01 10:00:00', 1);
        $this->add('Bob', 'Second', '2026-09-02 10:00:00', 1);

        $this->assertSame(['First', 'Second'], array_column(fetch_public_guestbook($this->pdo), 'message'));
    }

    public function testOnlyTheNewestMessagesUpToTheLimitAreKept(): void
    {
        foreach (range(1, 5) as $i) {
            $this->add("Guest $i", "Message $i", "2026-09-0$i 10:00:00", 1);
        }

        $this->assertSame(['Message 4', 'Message 5'], array_column(fetch_public_guestbook($this->pdo, 2), 'message'));
    }

    public function testEmailsNeverReachThePublicWall(): void
    {
        $this->add('Ann', 'Hello', '2026-09-01 10:00:00', 1);

        $this->assertArrayNotHasKey('email', fetch_public_guestbook($this->pdo)[0]);
    }

    public function testApprovalCanBeGivenAndTakenBack(): void
    {
        $this->add('Ann', 'Hello', '2026-09-01 10:00:00', 0);
        $id = (int) $this->pdo->lastInsertId();

        set_guestbook_approval($this->pdo, $id, true);
        $this->assertCount(1, fetch_public_guestbook($this->pdo));

        set_guestbook_approval($this->pdo, $id, false);
        $this->assertSame([], fetch_public_guestbook($this->pdo));
    }

    public function testWallIsEmptyWithoutADatabase(): void
    {
        $this->assertSame([], fetch_public_guestbook(null));
    }

    public function testWallIsEmptyOnDatabasesWithoutTheApprovalColumn(): void
    {
        $old = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $old->exec('CREATE TABLE guestbook (id INTEGER PRIMARY KEY, name TEXT, message TEXT)');

        $this->assertSame([], fetch_public_guestbook($old));
    }
}
