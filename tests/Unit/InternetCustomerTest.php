<?php

namespace Tests\Unit;

use App\Models\InternetCustomer;
use App\Schemas\ParamSchema;
use PHPUnit\Framework\TestCase;

class InternetCustomerTest extends TestCase
{
    /** @test */
    public function closed_customer_can_be_deleted(): void
    {
        $customer = new InternetCustomer(['status' => ParamSchema::CLOSED]);

        $this->assertTrue($customer->canBeDeleted());
    }

    /** @test */
    public function customer_with_non_closed_status_cannot_be_deleted(): void
    {
        foreach ([
            ParamSchema::PENDING,
            ParamSchema::ACTIVE,
            ParamSchema::SUSPENDED,
            ParamSchema::DISCONNECTED,
            ParamSchema::CANCELLED,
        ] as $status) {
            $customer = new InternetCustomer(['status' => $status]);

            $this->assertFalse($customer->canBeDeleted(), "Status {$status} tidak boleh dihapus");
        }
    }
}
