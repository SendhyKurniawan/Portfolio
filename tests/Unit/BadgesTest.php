<?php

use PHPUnit\Framework\TestCase;

final class BadgesTest extends TestCase
{
    private int $now;

    protected function setUp(): void
    {
        $this->now = strtotime('2026-09-12 12:00:00');
    }

    public function testItemsAddedWithinTheWindowAreNew(): void
    {
        $this->assertTrue(is_recent('2026-09-10 08:00:00', $this->now));
        $this->assertTrue(is_recent('2026-09-12', $this->now), 'date without time');
    }

    public function testTheWindowEdgeIsStillNew(): void
    {
        $edge = date('Y-m-d H:i:s', $this->now - NEW_BADGE_DAYS * 86400);

        $this->assertTrue(is_recent($edge, $this->now));
    }

    public function testOlderItemsAreNotNew(): void
    {
        $this->assertFalse(is_recent('2026-01-04 07:55:20', $this->now));
        $this->assertFalse(is_recent('2023-10-27', $this->now));
    }

    public function testCustomWindow(): void
    {
        $this->assertTrue(is_recent('2026-09-06', $this->now, 7));
        $this->assertFalse(is_recent('2026-09-01', $this->now, 7));
    }

    public function testMissingOrBrokenDatesAreNotNew(): void
    {
        $this->assertFalse(is_recent(null, $this->now));
        $this->assertFalse(is_recent('', $this->now));
        $this->assertFalse(is_recent('not a date', $this->now));
    }
}
