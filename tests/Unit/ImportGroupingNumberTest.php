<?php

namespace Tests\Unit;

use App\Support\ImportGroupingNumber;
use PHPUnit\Framework\TestCase;

class ImportGroupingNumberTest extends TestCase
{
    /** @test */
    public function sequence_continues_from_the_largest_existing_number(): void
    {
        $this->assertSame(12, ImportGroupingNumber::startingSequence(
            'BKN',
            4,
            ['BKN001', 'BKN0012', 'OTHER9999']
        ));

        $this->assertSame(20, ImportGroupingNumber::startingSequence(
            'BKN',
            20,
            ['BKN0012']
        ));
    }

    /** @test */
    public function selected_group_generates_sequence_and_overrides_csv_value(): void
    {
        $this->assertSame(
            'BKN0002',
            ImportGroupingNumber::resolve('BKN', 2, 'BKN001')
        );
    }

    /** @test */
    public function csv_grouping_is_preserved_when_no_group_is_selected(): void
    {
        $this->assertSame('BKN001', ImportGroupingNumber::resolve(null, 1, ' BKN001 '));
        $this->assertNull(ImportGroupingNumber::resolve(null, 1, ''));
    }
}
