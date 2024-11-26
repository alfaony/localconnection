<?php


namespace App\Schemas;


class RoleSchema
{
    const ROOT = 'Root'; // all access
    const ADMIN = 'Administrator'; // all except role & permission (his company only)
    const FINANCE = 'Finance';
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
}
