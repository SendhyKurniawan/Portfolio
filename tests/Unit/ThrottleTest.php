<?php

use PHPUnit\Framework\TestCase;

final class ThrottleTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (glob(throttle_dir() . '/*.json') ?: [] as $file) {
            unlink($file);
        }
    }

    public function testEvaluateAllowsUntilMaxIsReached(): void
    {
        $this->assertSame(0, throttle_evaluate([100, 110], 120, 3, 60)['retry_after']);
    }

    public function testEvaluateReportsWaitFromOldestAttemptInWindow(): void
    {
        $result = throttle_evaluate([100, 110, 115], 120, 3, 60);

        // Oldest attempt (100) leaves the 60s window at 160
        $this->assertSame(40, $result['retry_after']);
    }

    public function testEvaluateDropsExpiredAndInvalidEntries(): void
    {
        $result = throttle_evaluate([10, 'x', 110, null, 115], 120, 3, 60);

        $this->assertSame([110, 115], $result['attempts']);
        $this->assertSame(0, $result['retry_after']);
    }

    public function testRecordThenRetryAfterThenClear(): void
    {
        throttle_record('login', '10.0.0.1', 900, 1000);
        throttle_record('login', '10.0.0.1', 900, 1001);

        $this->assertSame(0, throttle_retry_after('login', '10.0.0.1', 3, 900, 1002));
        $this->assertSame(898, throttle_retry_after('login', '10.0.0.1', 2, 900, 1002));
        $this->assertSame(0, throttle_retry_after('login', '10.0.0.2', 2, 900, 1002), 'other clients are unaffected');
        $this->assertSame(0, throttle_retry_after('guestbook', '10.0.0.1', 2, 900, 1002), 'other buckets are unaffected');

        throttle_clear('login', '10.0.0.1');
        $this->assertSame(0, throttle_retry_after('login', '10.0.0.1', 2, 900, 1002));
    }

    public function testRecordPrunesAttemptsOutsideWindow(): void
    {
        throttle_record('login', 'ip', 60, 1000);
        throttle_record('login', 'ip', 60, 2000);

        $stored = json_decode((string) file_get_contents(throttle_file('login', 'ip')), true);
        $this->assertSame([2000], $stored);
    }

    public function testCorruptStateFileIsTreatedAsEmpty(): void
    {
        throttle_record('login', 'ip', 60, 1000);
        file_put_contents(throttle_file('login', 'ip'), '{not json');

        $this->assertSame(0, throttle_retry_after('login', 'ip', 1, 60, 1001));
    }

    public function testMinutesTextRoundsUpAndPluralises(): void
    {
        $this->assertSame('1 minute', minutes_text(5));
        $this->assertSame('1 minute', minutes_text(60));
        $this->assertSame('2 minutes', minutes_text(61));
    }
}
