<?php

namespace App\Services\Olt\Drivers;

/**
 * Vendor HSGQ — sama seperti HiosoSnmpDriver, ini cuma alias/extension
 * point selama belum ada kuirk khusus yang ditemukan di lapangan. Semua
 * logic ada di GenericSnmpDriver (oid_map-driven).
 */
class HsgqSnmpDriver extends GenericSnmpDriver
{
}
