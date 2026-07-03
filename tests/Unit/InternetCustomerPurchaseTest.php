<?php

namespace Tests\Unit;

use App\Models\InternetCustomerPurchase;
use App\Schemas\ParamSchema;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InternetCustomerPurchaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @test */
    public function purchase_is_expired_when_it_is_marked_expired(): void
    {
        Carbon::setTestNow('2026-07-04 12:00:00');

        $purchase = new InternetCustomerPurchase([
            'payment_method' => ParamSchema::EXPIRED,
            'period_end' => '2026-08-01',
        ]);

        $this->assertTrue($purchase->isExpired());
    }

    /** @test */
    public function purchase_is_expired_after_its_period_end_date(): void
    {
        Carbon::setTestNow('2026-07-04 12:00:00');

        $purchase = new InternetCustomerPurchase([
            'period_end' => '2026-07-03',
        ]);

        $this->assertTrue($purchase->isExpired());
    }

    /** @test */
    public function purchase_is_not_expired_during_its_period_end_date(): void
    {
        Carbon::setTestNow('2026-07-04 23:59:59');

        $purchase = new InternetCustomerPurchase([
            'period_end' => '2026-07-04',
        ]);

        $this->assertFalse($purchase->isExpired());
    }

    /** @test */
    public function purchase_without_period_end_is_not_expired(): void
    {
        Carbon::setTestNow('2026-07-04 12:00:00');

        $purchase = new InternetCustomerPurchase();

        $this->assertFalse($purchase->isExpired());
    }
}
