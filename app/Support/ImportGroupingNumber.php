<?php

namespace App\Support;

use App\Models\InternetCustomerGroup;

class ImportGroupingNumber
{
    public static function startingSequence(string $prefix, int $lastNumber, iterable $existingGroupingIds): int
    {
        $existingMax = 0;

        foreach ($existingGroupingIds as $groupingId) {
            $groupingId = (string) $groupingId;

            if (!str_starts_with($groupingId, $prefix)) {
                continue;
            }

            $existingMax = max(
                $existingMax,
                InternetCustomerGroup::parseSequence(substr($groupingId, strlen($prefix)))
            );
        }

        return max($lastNumber, $existingMax);
    }

    /**
     * Grup yang dipilih dari form selalu mengalahkan nilai grouping dari CSV.
     */
    public static function resolve(?string $selectedGroupPrefix, int $sequence, ?string $csvGrouping): ?string
    {
        if ($selectedGroupPrefix !== null && $selectedGroupPrefix !== '') {
            return $selectedGroupPrefix . InternetCustomerGroup::formatSequence($sequence);
        }

        $csvGrouping = trim((string) $csvGrouping);

        return $csvGrouping !== '' ? $csvGrouping : null;
    }
}
