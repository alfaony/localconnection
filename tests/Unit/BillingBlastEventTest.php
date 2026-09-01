<?php

namespace Tests\Unit;

use App\Console\Commands\BlastBillingByStatusCommand;
use App\Models\UserCustomer;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class BillingBlastEventTest extends TestCase
{
    /** @dataProvider eventProvider */
    public function test_it_resolves_one_event_using_the_expected_priority(
        ?string $startDate,
        ?string $endDate,
        ?string $expectedEvent
    ): void {
        $customer = new UserCustomer([
            'start_billing_date' => $startDate,
            'end_billing_date' => $endDate,
        ]);

        $event = BlastBillingByStatusCommand::resolveEvent(
            $customer,
            Carbon::createFromFormat('Y-m-d', '2026-09-01')
        );

        $this->assertSame($expectedEvent, $event);
    }

    public function eventProvider(): array
    {
        return [
            'H-0 has priority over billing created' => ['2026-09-01', '2026-09-01', 'h_0'],
            'H-1 has priority over billing created' => ['2026-09-01', '2026-09-02', 'h_1'],
            'H-3 has priority over billing created' => ['2026-09-01', '2026-09-04', 'h_3'],
            'billing created' => ['2026-09-01', '2026-09-06', 'billing_created'],
            'no matching event' => ['2026-08-31', '2026-09-03', null],
        ];
    }
}
