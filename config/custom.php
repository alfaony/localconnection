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
];
