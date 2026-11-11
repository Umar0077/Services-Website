<?php

namespace Tests\Unit;

use App\Support\TargetingCheck;
use PHPUnit\Framework\TestCase;

class TargetingCheckTest extends TestCase
{
    private function claims(string $text): array
    {
        return array_values(array_filter(TargetingCheck::run($text), fn ($w) => str_contains($w, 'unsupported claim')));
    }

    public function test_negated_guarantees_are_not_flagged(): void
    {
        $this->assertSame([], $this->claims('Same-day and 24/7 service cannot be guaranteed.'));
        $this->assertSame([], $this->claims('Availability is not guaranteed and varies by market.'));
        $this->assertSame([], $this->claims('There is no guarantee of arrival times.'));
    }

    public function test_real_claims_are_still_flagged(): void
    {
        $this->assertNotEmpty($this->claims('Same-day service guaranteed!'));
        $this->assertNotEmpty($this->claims('Our technicians are licensed and insured.'));
        $this->assertNotEmpty($this->claims('Not guaranteed elsewhere, but we guarantee a fix.'));
    }

    public function test_commercial_targeting_is_flagged(): void
    {
        $this->assertNotEmpty(TargetingCheck::run('HVAC for office buildings and warehouse facilities'));
    }
}
