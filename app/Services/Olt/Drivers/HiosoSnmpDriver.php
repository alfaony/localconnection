<?php

namespace App\Services\Olt\Drivers;

/**
 * Vendor HiOSO — saat ini tidak ada behavior khusus di luar yang generic,
 * jadi class ini cuma alias/extension point. Semua logic ada di
 * GenericSnmpDriver (oid_map-driven). Kalau suatu saat HiOSO ternyata
 * punya kuirk beda (index encoding, status value non-standar, dll),
 * override method yang relevan di sini tanpa mengubah vendor lain.
 */
class HiosoSnmpDriver extends GenericSnmpDriver
{
}
