<?php


namespace App\Schemas;


class RoleSchema
{
    const ROOT = 'Root'; // all access
    const ADMIN = 'Administrator'; // all except role & permission (his company only)
    const FINANCE = 'Staff Finance';
    const PROCUREMENT = 'Procurement';
    const PM = 'Project Manager';
    const HR = 'Human Resource';
    const SALES = 'Sales';
    const OB = 'Office Boy';
    const BM = 'Building Manager';
    const SECURITY = 'Security';
    const DIRECTOR = 'Director';
    const MANAGER = 'Manager';
    const STAFF = 'Staff';
    const SYSTEM = 'System Admin';
    const SPRINTER = "Sprinter";
    const STAFF_FINANCE = "Staff Finance";
    const MANAGER_FINANCE = "Manager Finance";
    const TECKNICIAN_INTERNET = "Teknisi Internet";
    const CUSTOMER_INTERNET = "Internet Customer";
    const SYSTEM_BOS = "Sistem";
    const SYSTEM_ADMIN = "System Admin";
}
