<?php

return
[
    /**
     * default payment mode
     */

     'paymentMode' =>
     [
        'daily' => 'Harian',
        'monthly' => 'Bulanan'
     ],

     'agreementTemplate' =>
     [
        'templateBos3' => 'Template Bos 3',
        'templateBos1' => 'Template Bos 1'
     ],

     'month' =>
     [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
     ],

     'statusApproval' =>
     [
        'in review' => 'In Review',
        'complete' => 'Complete'
     ],

     'statusTask' =>
     [
        'overdue' => 'Kemarin',
        'today' => 'Hari ini',
        'upcomming' => 'Yang Akan datang',
     ],

     'statusSelect' =>
     [
        'single_select' => 'Single Select',
        'multi_select' => 'Multi Select',
     ],


     'leads_from' =>
     [
        '1' => 'Internal',
        '2' => 'External',
     ],

     'days' =>
       [
         'monday' => 'Senin',
         'tuesday' => 'Selasa',
         'wednesday' => 'Rabu',
         'thursday' => 'Kamis',
         'friday' => 'Jumat',
         'saturday' => 'Sabtu',
         'sunday' => 'Minggu',
       ],

       'customerSignature'=>
       [
        'pic' => 'PIC',
        'director' => 'Direktur',
        'penanggung_jawab' => 'Penanggung Jawab'
       ],

       'status_invoice' => 
       [
          'DRAFT' => 'Draft',
          'SUBMITTED' => 'Submitted',
          'AUTHORISED' => 'Waiting payment',
       ],
       'status_invoice_search' => 
       [
          'DRAFT' => 'Draft',
          'SUBMITTED' => 'Submitted',
          'AUTHORISED' => 'Waiting payment',
          'PAID' => 'Paid',
       ],

       'daysOfWeek' =>
       [
         'Monday' => 'monday',
         'Tuesday' => 'tuesday',
         'Wednesday' => 'wednesday',
         'Thursday' => 'thursday',
         'Friday' => 'friday',
         'Saturday' => 'saturday',
         'Sunday ' => 'sunday',
      ],
      
      'bast_template' =>
      [
       'template1' => 'Template 1',
       'template2' => 'Template 2',
      ],

      'status_kye' => 
      [
         'approved' => "Approved",
         'rejected' => 'Rejected'
      ],

      'sensorType' => 
      [
         'boolean' => 'On / Off',
         'integer' => 'Angka',
      ],

      "type_vehicle" => 
      [
         'motor' => 'Motor',
         'mobil' => 'Mobil',
      ],
      
      "request_order_step" => 
      [
         'REQUESTED' => 'Requested',
         'WAITING_PAYMENT' => 'Waiting Payment',
         'WAITING_DELIVERY_CONFIRMATION' => 'Waiting Delivery Confirmation',
         'DELIVERED' => 'Delivered',
         'CLOSED' => 'Closed',
      ],

      "meeting_type" =>
      [
         'online' => 'Online',
         'offline' => 'Offline',
      ],

      "day_name_code" => 
      [
         'MO' => 'Senin',
         'TU' => 'Selasa',
         'WE' => 'Rabu',
         'TH' => 'Kamis',
         'FR' => 'Jumat',
         'SA' => 'Sabtu',
         'SU' => 'Minggu',
      ],
      'master_type_check' => 
      [
         'laptop_type' => 'Laptop',
         'item_type' => 'Barang Lain',
      ],
      'promo_type' => 
      [
         'free_months' => 'Gratis Bulan',
      ],
      'postion_latpop' =>
      [
         'Dijual' => 0,
         'Inventory' => null
      ],
      'merital_status' => 
      [
         'single' => 'Lajang',
         'married' => 'Menikah',
         'widow' => 'Janda',
         'divorced' => 'Duda'
      ],

    'partner_types' => [
        'distributor' => 'Distributor',
        'reseller' => 'Reseller',
        'technology_partner' => 'Technology Partner',
        'solution_partner' => 'Solution Partner',
        'oem' => 'OEM',
        'system_integrator' => 'System Integrator',
        'value_added_reseller' => 'Value Added Reseller (VAR)',
        'affiliate' => 'Affiliate',
        'agency_partner' => "Agency Partner",
      ],

    'partner_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ],

    'target_status' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'completed' => 'Completed',
    ],

    'certification_levels' => [
        'basic' => 'Basic',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'expert' => 'Expert',
        'master' => 'Master',
    ],

    'months' => [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ],
    
];
