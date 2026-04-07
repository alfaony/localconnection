<?php


namespace App\Schemas;


class ParamSchema
{
    const TRUE = 1;
    
    const FALSE = 0;

    const DAILY = "daily";

    const MONTHLY = "monthly";

    const ONEMONTH = 30;

    const ONEDAY = 1;

    const LIMIT = 1;

    const PERCENTAGE = 100;

    const DOING = "doing";

    const INREVIEW = "in review";
    
    const NOTCOMPLATE = "not complete";
    
    const COMPLATE = "complete";

    const REGULAR = 'regular';

    const ZERO = 0;

    const STORAGE = "storage";

    const PIC = "pic";

    const CHECKIN = "check_in";

    const CHECKOUT = "check_out";

    const NEW = "new";

    const DONE = "done";

    const ONTIME = "ontime";

    const LATE = "late";

    const PINALTY_NOT_PROGRESS = "penalty_not_progress";

    const RECURRING = "Recurring";

    const MULTISELECT = "multi_select";

    const ALL = "all";

    const TODO = "todo";

    const BACKLOG="backlog";
    
    const FILEREPORT = "file_report";

    const FILETASK = "file_task";

    const CLOCKOUT = "17:00";

    const PERJANJIANKERJA = "Surat Perjanjian Kerja";

    const TEMPLATEMAGANG ="sk_magang_template";

    const STAFF = 1;

    const NONSTAFF = NULL;

    const APPROVE = "approve";

    const APPROVED = "approved";

    const TEMPLATEPERJANJIANKERJA = "perjanjian_kerja_template";

    const TEMPLATETUGAS = "sk_tugas_template";

    const TEMPLATEJABATAN = "sk_management_template";

    const CLOSED = "closed";

    const CLOSE = "close";

    const OPEN = "open";

    const AUTHORISED = "AUTHORISED";

    const DRAFT = "DRAFT";

    const SERVICEFEE = "Service Fee";

    const ADDTIONALCHARGES = "Additional Charges";

    const DISCOUNT="Discount";

    const DELETE="DELETED";

    const PENANGGUNGJAWAB = "penanggung_jawab";

    const TARGET_CHECKIN = 10;
    const TARGET_ABSENCE = 4;

    const DRAF = "draft";

    const SUBMIT = "submit";

    const SIGNATURE = "signature";

    const ONREVIEW = "onreview";

    const REJECTED = "rejected";

    const GOOGLE_MEET = "google_meet";

    const INTERNAL = "internal";

    const EXTERNAL = "external";

    const UP = 'UP';

    // Schemas
    const PERJANJIAN_INTERNET = "perjanjian_berlangganan_internet";

    // Status Interent
    const PENDING = "pending";
    const WAITING_PAYMENT_SUBSCRIPTION = "waiting_payment_subscription";
    const WAITING_PAYMENT_CONFIRMATION = "waiting_payment_confirmation";
    const PROCESS_INSTALLATION = "process_installation";
    const INSTALLED = "installed";
    const ACTIVE = "active";
    const EXPIRED = "expired";
    const CANCELLED = "cancelled";
    const SUSPENDED = "suspended";
    const REACTIVATED = "reactivated";
    const CUSTOMER_EXISTING = "customer_existing";
    const INACTIVE = "inactive";

    // Promo
    const PROMO_FREE_MONTH = "free_months";

    const WFO = "wfo";
    const WFH = "wfh";
    const SHIFT = "shift";

    // Platform
    const APK = "apk";
    const WEB = "web";

    // Internet Customer Type
    const BISNIS = "bisnis";
    const RUMAH = "rumah";
}