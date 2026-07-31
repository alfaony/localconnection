<?php

namespace Tests\Unit;

use App\Models\InternetPackage;
use Tests\TestCase;

class InternetPackageRegionPriceTest extends TestCase
{
    /** @test */
    public function it_uses_the_most_specific_custom_region_price(): void
    {
        $package = $this->packageWithRegions([
            $this->region('province', 1, 100000, 90000),
            $this->region('district', 30, 80000, 70000),
        ]);

        $price = $package->getPriceForRegion(1, 20, 30, 40);

        $this->assertSame(80000.0, $price['price']);
        $this->assertSame(70000.0, $price['price_nett']);
        $this->assertSame('district', $price['region_type']);
        $this->assertTrue($price['is_custom_price']);
    }

    /** @test */
    public function empty_price_on_a_more_specific_region_inherits_parent_region_price(): void
    {
        $package = $this->packageWithRegions([
            $this->region('province', 1, 100000, 90000),
            $this->region('district', 30, 80000, 70000),
            $this->region('subdistrict', 40, null, null),
        ]);

        $price = $package->getPriceForRegion(1, 20, 30, 40);

        $this->assertSame(80000.0, $price['price']);
        $this->assertSame(70000.0, $price['price_nett']);
        $this->assertSame('district', $price['region_type']);
    }

    /** @test */
    public function gross_and_nett_prices_can_inherit_from_different_region_levels(): void
    {
        $package = $this->packageWithRegions([
            $this->region('province', 1, 100000, 90000),
            $this->region('district', 30, 80000, null),
        ]);

        $price = $package->getPriceForRegion(1, 20, 30, 40);

        $this->assertSame(80000.0, $price['price']);
        $this->assertSame(90000.0, $price['price_nett']);
        $this->assertSame('district', $price['price_region_type']);
        $this->assertSame('province', $price['price_nett_region_type']);
    }

    /** @test */
    public function it_falls_back_to_global_prices_when_no_active_custom_price_matches(): void
    {
        $package = $this->packageWithRegions([
            $this->region('district', 30, 80000, 70000, false),
        ]);

        $price = $package->getPriceForRegion(1, 20, 30, 40);

        $this->assertSame(120000.0, $price['price']);
        $this->assertSame(110000.0, $price['price_nett']);
        $this->assertSame('global', $price['region_type']);
        $this->assertFalse($price['is_custom_price']);
    }

    private function packageWithRegions(array $regions): InternetPackage
    {
        $package = new InternetPackage([
            'price' => 120000,
            'price_nett' => 110000,
        ]);
        $package->setRelation('regions', collect($regions));

        return $package;
    }

    private function region(
        string $type,
        int $id,
        ?int $price,
        ?int $priceNett,
        bool $isActive = true
    ): object {
        return (object) [
            'region_type' => $type,
            'region_id' => $id,
            'price' => $price,
            'price_nett' => $priceNett,
            'is_active' => $isActive,
            'region_label' => ucfirst($type) . ' ' . $id,
        ];
    }
}
