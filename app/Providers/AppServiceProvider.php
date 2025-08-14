<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Helpers\Access;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

use App\Models\EquipmentReduction;
use App\Models\TaskAssign;
use App\Models\Bast;
use App\Models\DailyTask;

use App\Observers\EquipmentReductionObserver;
use App\Observers\TaskAssignObserver;
use App\Observers\BastObserver;
use App\Observers\DailyTaskObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        EquipmentReduction::observe(EquipmentReductionObserver::class);
        TaskAssign::observe(TaskAssignObserver::class);
        Bast::observe(BastObserver::class);
        DailyTask::observe(DailyTaskObserver::class);

        Schema::defaultStringLength(191);
        if ($this->app->environment('production') || $this->app->environment('development')) 
        {
            URL::forceScheme('https');
        }

        $events->listen(BuildingMenu::class, function (BuildingMenu $event) 
        {

            $listMenu = [
                // 'roles',
            ];
            $managementRequestItemArray = array();
            $managementCompanyArray = array();
            $managementUsedArray = array();
            $managementCompanyArray = array();
            $managementSalesArray = array();
            $managementObArray = array();
            $equipmentMenuArray = array();
            $taskMenuArray = array();
            $securityMenuArray = array();
            $productivityMenuArray = array();
            $warehouseMenuArray = array();
            $shippingMenuArray = array();
            $wilayahMenuArray = array();
            $productMenuArray = array();
            $subcribetionMenuArray = array();
            $internetManagementMenuArray = array();
            $settingMenuArray = array();

            $equipmentMenu = ['devices','equipment','equipment_reductions'];
            $taskMenu = ['report_points','tasks','task_assigns'];
            $securityMenu = ['assets','security_checks','cctv_checks','tickets'];
            $productivityMenu = ['report_productivities','project_dashboards','division_budgets','visions','divisions','objectives', 'daily_task_projects','daily_task_categories', 'dailytasks','trainings','ip_rights','sales_achievements'];
            $warehouseMenu = ['sensors','warehouses','zones','racks'];
            $shippingMenu = ['providers','shipping_rates','shipping_calculations'];
            $wilayahMenu = ['provinces','cities','districts','subdistricts','postal_codes'];
            $productMenu = ['pricelists','products','product_suppliers','supplier_categories'];
            $subcribetionMenu = ['vehicles','subscribe_letters'];
            $managementRequestItemMenu = ['item_requests'];
            $managementUsedMenu = ['master_check_items','used_laptops','used_items'];
            $internetManagementMenu = ['data_centers', 'pops','optical_distributions','coverage_services','internet_packages', 'internet_customers','promos'];
            $settingMenu = ['setting_companies','roles','webhook_settings'];
            
            $managementCompanyMenu = 
            [
                'meetings',
                'moms',
                'dashboard_weekly_reports',
                'weekly_reports',
                'flowcharts',
                'ask_bos',
                'decisions',
                'dayoffs',
                'kyes',
                'partnership_agreements',
                'national_holidays',
                'employee_checkings',
                'letter_submissions',
                'positions',
                'employees',
                'users',
                'companies',
            ];
            $managementSalesMenu = 
            [
                'managers',
                // 'products',
                'product_categories',
                'customers',
                'quotes',
                'work_orders',
                'agreement_letters',
                'projects',
                'supliers',
                'report_projects',
                'basts',
                'invoices',
                'reports',
            ];

            $managementObMenu = 
            [
                'attendances',
                'shifting_obs',
                'schedule_obs',
            ];
            


            $menus = [
                'homes' => [
                    'text'        => 'Dashboard',
                    'url'         => '/',
                    'icon'        => '',
                    'label_color' => 'success',
                ],
                'pricelists' => [
                    'text'        => 'Price List',
                    'route'         => 'pricelist.index',
                    'icon' => 'fa fa-list-alt',
                ],
                'employees' => [
                    'text'        => 'Data Pekerja',
                    'route'         => 'employee.index',
                    'icon' => 'fa fa-briefcase',
                ],
                'managers' => [
                    'text'        => 'Jumlah Hari Kerja',
                    'route'         => 'manager.index',
                    'icon'      => 'fa fa-tasks',
                ],
                'users' => [
                    'text'        => 'Data Pengguna',
                    'route'         => 'user.index',
                    'icon' => 'fa fa-users',
                ],
                'products' => [
                    'text'        => 'Produk',
                    'route'         => 'product.index',
                    'icon' => 'fa fa-tag',
                ],
                'customers' => [
                    'text'        => 'Customer',
                    'route'         => 'customer.index',
                    'icon' => 'fa fa-user-circle',
                ],
                'quotes' => [
                    'text'        => 'Quote',
                    'route'         => 'quote.index',
                    'icon' => 'fa fa-file-pdf',
                ],
                'work_orders' => [
                    'text'        => 'SPK',
                    'route'         => 'work-order.index',
                    'icon' => 'fa fa-clipboard-list',
                ],
                'agreement_letters' => [
                    'text'        => 'Perjanjian',
                    'route'         => 'agreement-letter.index',
                    'icon' => 'fa fa-file-signature',
                ],
                'projects' => [
                    'text'        => 'Data Proyek',
                    'route'         => 'project.index',
                    'icon' => 'fa fa-tasks',
                ],
                'supliers' => [
                    'text'        => 'Pembelian',
                    'route'         => 'suplier.index',
                    'icon'      => 'fa fa-check',
                ],
                'report_projects' => [
                    'text'        => 'Laporan Proyek',
                    'route'         => 'report-project.index',
                    'icon' => 'fa fa-file-signature',
                ],
                'basts' => [
                    'text'        => 'Daftar BAST',
                    'route'         => 'bast.index',
                    'icon' => 'fa fa-file-signature',
                ],
                'reports' => [
                    'text'        => 'Laporan',
                    'route'         => 'report.index',
                    'icon' => 'fa fa-file',
                ],
                'setting_companies' => [
                    'text'        => 'Setting Perusahaan',
                    'route'         => 'setting-company.index',
                    'icon' => 'fa fa-home',
                ],
                'companies' => [
                    'text'        => 'Company',
                    'route'         => 'company.index',
                    'icon' => 'fa fa-building',
                ],
                'roles' => [
                    'text'        => 'Role Akses',
                    'route'         => 'role.index',
                    'icon' => 'fa fa-cog',
                ],

                'equipment' => [
                    'text'        => 'Daftar Perlengkapan',
                    'route'         => 'equipment.index',
                    'icon' => 'fa fa-list-ul',
                ],

                'equipment_reductions' => [
                    'text'        => 'Pengeluaran',
                    'route'         => 'equipment-reduction.index',
                    'icon' => 'fa fa-check',
                ],

                'tasks' => [
                    'text'        => 'Pekerjaan',
                    'route'         => 'task.index',
                    'icon' => 'fa fa-tasks',
                ],

                'task_assigns' => [
                    'text'        => 'Penugasan',
                    'route'         => 'task-assign.index',
                    'icon' => 'fa fa-list-alt',
                ],

                'assets' => [
                    'text'        => 'Akses',
                    'route'         => 'asset.index',
                    'icon' => 'fa fa-briefcase',
                ],

                'attendances' => [
                    'text'        => 'Kehadiran',
                    'route'         => 'attendance.index',
                    'icon' => 'fa fa-calendar',
                ],

                'report_points' => [
                    'text'        => 'Laporan Poin',
                    'route'         => 'report-point.index',
                    'icon' => 'fa fa-book',
                ],
                'report_productivities' => [
                    'text'        => 'Laporan Poin',
                    'route'         => 'report-productivity.index',
                    'icon' => 'fa fa-book',
                ],
                'security_checks' => [
                    'text'        => 'Kontrol Keamanan',
                    'route'         => 'security-check.index',
                    'icon' => 'fa fa-check',
                ],
                'cctv_checks' => [
                    'text'        => 'Kontrol Cctv',
                    'route'         => 'cctv-check.index',
                    'icon' => 'fa fa-check',
                ],
                'tickets' => [
                    'text'        => 'Tiket',
                    'route'         => 'ticket.index',
                    'icon' => 'fa fa-envelope',
                ],
                'trainings' => [
                    'text'        => 'Pelatihan',
                    'route'         => 'training.index',
                    'icon' => 'fa fa-graduation-cap',
                ],
                'ip_rights' => [
                    'text'        => 'Hak Cipta',
                    'route'         => 'ip-right.index',
                    'icon' => 'fa fa-gavel',
                ],
                'sales_achievements' => [
                    'text'        => 'Pencapaian Penjualan',
                    'route'         => 'sales_achievement.index',
                    'icon' => 'fa fa-line-chart',
                ],
                'dailytasks' => [
                    'text'        => 'Tugas Harian',
                    'route'         => 'dailytask.index',
                    'icon' => 'fa fa-tasks',
                ],
                'daily_task_projects' => [
                    'text'        => 'Main Proyek Tugas',
                    'route'         => 'daily_task_project.index',
                    'icon' => 'fa fa-sitemap',
                ],
                'divisions' => [
                    'text'        => 'Divisi',
                    'route'         => 'division.index',
                    'icon' => 'fa fa-users',
                ],
                'objectives' => [
                    'text'        => 'Objective',
                    'route'         => 'objective.index',
                    'icon' => 'fa fa-bars',
                ],
                'visions' => [
                    'text'        => 'Visi & Misi',
                    'route'         => 'vision.index',
                    'icon' => 'fa fa-info-circle',
                ],
                'visions' => [
                    'text'        => 'Visi & Misi',
                    'route'         => 'vision.index',
                    'icon' => 'fa fa-info-circle',
                ],
                'project_dashboards' => [
                    'text'        => 'Dashboard Tracking Tugas',
                    'route'         => 'projectdashboard.index',
                    'icon' => 'fa fa-flag',
                ],

                'daily_task_categories' => [
                    'text'        => 'Kategori Tugas Harian',
                    'route'         => 'daily-task-category.index',
                    'icon' => 'fa fa-check',
                ],
                'shifting_obs' => [
                    'text' => 'Shifting OB',
                    'route' => 'shifting-ob.index',
                    'icon' => 'fa fa-calendar',
                ],
                'schedule_obs' => 
                [
                    'text' => 'Schedule OB',
                    'route' => 'schedule-ob.index',
                    'icon' => 'fa fa-clock',
                ],

                'division_budgets' => [
                    'text'        => 'Pengajuan Anggaran',
                    'route'         => 'division-budget.index',
                    'icon' => 'fa fa-money-bill',
                ],
                'product_categories' => [
                    'text' => 'Kategori Product',
                    'route' => 'product-category.index',
                    'icon' => 'fa fa-list',
                ],

                'letter_submissions' => [
                    'text' => 'Pengajuan Surat',
                    'route' => 'letter-submission.index',
                    'icon' => 'fa fa-paper-plane',
                ],

                'positions' => [
                    'text' => 'Daftar Posisi',
                    'route' => 'position.index',
                    'icon' => 'fa fa-list',
                ],
                'devices' => 
                [
                    'text' => 'Device',
                    'route' => 'device.index',
                    'icon' => 'fa fa-mobile-alt',
                ],
                'national_holidays' => 
                [
                    'text' => 'National Holiday',
                    'route' => 'national-holiday.index',
                    'icon' => 'fa fa-calendar-alt',
                ],
                'employee_checkings' => 
                [
                    'text' => 'Employee Check-In',
                    'route' => 'employee-checking.index',
                    'icon' => 'fa fa-user-check',
                ],
                'invoices' =>
                [
                    'text' => 'Invoice',
                    'route' => 'invoice.index',
                    'icon' => 'fa fa-file-invoice',
                ],
                'kyes' => [
                    'text' => 'Know Your Employee (KYE)',
                    'route' => 'kye.index',
                    'icon' => 'fa fa-id-card',
                ],
                'warehouses' => [
                    'text' => 'Manajemen Gudang',
                    'route' => 'warehouse.index',
                    'icon' => 'fa fa-warehouse',
                ],
                'sensors' => [
                    'text' => 'Sensor',
                    'route' => 'sensor.index',
                    'icon' => 'fa fa-microchip',
                ],
                'zones' => [
                    'text' => 'Zone',
                    'route' => 'zone.index',
                    'icon' => 'fa fa-box',
                ],

                'racks' => [
                    'text' => 'Rack',
                    'route' => 'rack.index',
                    'icon' => 'fa fa-list',
                ],
                'providers' => [
                    'text' => 'Provider Shipping',
                    'route' => 'provider.index',
                    'icon' => 'fa fa-truck',
                ],
                'provinces' => [
                    'text' => 'Provinsi',
                    'route' => 'province.index',
                    'icon' => 'fa fa-map',
                ],
                'cities' => [
                    'text' => 'Kabupaten/Kota',
                    'route' => 'city.index',
                    'icon' => 'fa fa-city',
                ],
                'districts' => [
                    'text' => 'Kecamatan',
                    'route' => 'district.index',
                    'icon' => 'fa fa-map-marker-alt',
                ],
                'subdistricts' => [
                    'text' => 'Kelurahan',
                    'route' => 'subdistrict.index',
                    'icon' => 'fa fa-map-signs',
                ],
                'postal_codes' => [
                    'text' => 'Kode Pos',
                    'route' => 'postal-code.index',
                    'icon' => 'fa fa-envelope',
                ],
                'shipping_types' => [
                    'text' => 'Jenis Pengiriman',
                    'route' => 'shipping-type.index',
                    'icon' => 'fa fa-shipping-fast',
                ],

                'shipping_rates' => [
                    'text' => 'Tarif Pengiriman',
                    'route' => 'shipping-rate.index',
                    'icon' => 'fa fa-dollar-sign',
                ],
                'shipping_calculations' => [
                    'text' => 'Hitung Pengiriman',
                    'route' => 'shipping-calculation.index',
                    'icon' => 'fa fa-calculator',
                ],
                'ask_bos' => [
                    'text' => 'Tanya Bos',
                    'route' => 'ask-bos.index',
                    'icon' => 'fa fa-comments',
                ],
                'decisions' => 
                [
                    'text' => 'Keputusan',
                    'route' => 'decision.index',
                    'icon' => 'fa fa-balance-scale',
                ],
                'partnership_agreements' => 
                [
                    'text' => 'Perjanjian Partnership',
                    'route' => 'partnership-agreement.index',
                    'icon' => 'fa fa-handshake',
                ],
                'product_suppliers' => [
                    'text' => 'Data Supplier',
                    'route' => 'product-supplier.index',
                    'icon' => 'fa fa-briefcase',
                ],
                'supplier_categories' => [
                    'text' => 'Kategori Supplier',
                    'route' => 'supplier-category.index',
                    'icon' => 'fa fa-list',
                ],
                'dayoffs' => 
                [
                    'text' => 'Cuti',
                    'route' => 'dayoff.index',
                    'icon' => 'fa fa-calendar-times',
                ],
                'weekly_reports' => 
                [
                    'text' => 'Laporan Mingguan',
                    'route' => 'weekly-report.index',
                    'icon' => 'fa fa-calendar',
                ],
                'dashboard_weekly_reports' => 
                [
                    'text' => 'Halaman Laporan Mingguan',
                    'route' => 'dashboard-weekly-report.index',
                    'icon' => 'fas fa-tachometer-alt',
                ],
                'vehicles' => 
                [
                    'text' => 'Kendaraan',
                    'route' => 'vehicle.index',
                    'icon' => 'fa fa-car',
                ],
                'subscribe_letters' => 
                [
                    'text' => 'Surat Berlangganan',
                    'route' => 'subscribe-letter.index',
                    'icon' => 'fa fa-envelope-open',
                ],
                'flowcharts' => 
                [
                    'text' => 'Alur Kerja',
                    'route' => 'flowchart.index',
                    'icon' => 'fa fa-sitemap',
                ],
                'item_requests' => 
                [
                    'text' => 'Pengajuan Barang',
                    'route' => 'item-request.index',
                    'icon' => 'fa fa-shopping-cart',
                ],
                'list_sprinter' =>
                [
                    'text' => 'List Sprinter',
                    'url' => url("item-request/list/".Auth::user()->company->slug),
                    'icon' => 'fa fa-list',
                ],
                'meetings' => 
                [
                    'text' => 'Rapat',
                    'route' => 'meeting.index',
                    'icon' => 'fas fa-chalkboard-teacher',
                ],
                'moms' => 
                [
                    'text' => 'Minutes of Meeting',
                    'route' => 'mom.index',
                    'icon' => 'fa fa-file',
                ],
                'used_laptops' =>
                [
                    'text' => 'Laptop Bekas',
                    'route' => 'used-laptop.index',
                    'icon' => 'fa fa-laptop',
                ],
                'master_check_items' =>
                [
                    'text' => 'Master Pemeriksaan',
                    'route' => 'master-check-item.index',
                    'icon' => 'fa fa-list',
                ],
                'used_items' =>
                [
                    'text' => 'Barang ',
                    'route' => 'used-item.index',
                    'icon' => 'fa fa-box-open',
                ],
                'data_centers' =>
                [
                    'text' => 'Data Center',
                    'route' => 'data-center.index',
                    'icon' => 'fa fa-server',
                ],
                'pops' =>
                [
                    'text' => 'POP',
                    'route' => 'pop.index',
                    'icon' => 'fa fa-hdd',
                ],
                'optical_distributions' =>
                [
                    'text' => 'Distribusi Optic',
                    'route' => 'optical-distribution.index',
                    'icon' => 'fas fa-network-wired',
                ],
                'coverage_services' =>
                [
                    'text' => 'Layanan Coverage',
                    'route' => 'coverage-service.index',
                    'icon' => 'fa fa-signal',
                ],
                'internet_packages' =>
                [
                    'text' => 'Paket Internet',
                    'route' => 'internet-package.index',
                    'icon' => 'fa fa-globe',
                ],
                'internet_customers' =>
                [
                    'text' => 'Pelanggan Internet',
                    'route' => 'internet-customer.index',
                    'icon' => 'fa fa-users',
                ],
                'promos' => 
                [
                    'text' => 'Promosi',
                    'route' => 'promo.index',
                    'icon' => 'fa fa-tags',
                ],
                'webhook_settings' => 
                [
                    'text' => 'Pengaturan Webhook',
                    'route' => 'webhook-setting',
                    'icon' => 'fa fa-cogs',
                ]

            ];

            foreach ($listMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    $event->menu->add($menus[$role]);
                }
            }

            foreach ($managementSalesMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($managementSalesArray,$menus[$role]);
                }
            }
            
            foreach ($managementCompanyMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($managementCompanyArray,$menus[$role]);
                }
            }

            foreach ($managementObMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($managementObArray,$menus[$role]);
                }
            }

            foreach ($equipmentMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($equipmentMenuArray,$menus[$role]);
                }
            }

            foreach ($taskMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($taskMenuArray,$menus[$role]);
                }
            }

            foreach ($securityMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($securityMenuArray,$menus[$role]);
                }
            }

            foreach ($productivityMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($productivityMenuArray,$menus[$role]);
                }
            }

            foreach ($warehouseMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($warehouseMenuArray,$menus[$role]);
                }
            }


            foreach ($shippingMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($shippingMenuArray,$menus[$role]);
                }
            }

            foreach ($wilayahMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($wilayahMenuArray,$menus[$role]);
                }
            }

            foreach ($productMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($productMenuArray,$menus[$role]);
                }
            }

            foreach ($subcribetionMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($subcribetionMenuArray,$menus[$role]);
                }
            }

            foreach ($managementUsedMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($managementUsedArray,$menus[$role]);
                }
            }

            foreach ($internetManagementMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($internetManagementMenuArray,$menus[$role]);
                }
            }

            foreach ($managementRequestItemMenu as $role) 
            {
                if(in_array($role,['item_requests']) && Access::can("index", $role))
                {
                    array_push($managementRequestItemArray,$menus["list_sprinter"]);
                }

                if(Access::can("index", $role))
                {
                    array_push($managementRequestItemArray,$menus[$role]);
                }
            }

            foreach ($settingMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    array_push($settingMenuArray,$menus[$role]);
                }
            }

            $managementRequestItemMenu = 
            [
                'text'    => 'Manajemen Pengajuan Barang',
                'submenu' => $managementRequestItemArray
            ];

            $managementSalesMenu = 
            [
                'text'    => 'Manajemen Penjualan',
                'submenu' => $managementSalesArray
            ];


            $productivityMenu = 
            [
                'text'    => 'Produktifitas',
                'submenu' => $productivityMenuArray
            ];

            $managementCompanyMenu = 
            [
                'text'    => 'Manajemen Perusahaan',
                'submenu' => $managementCompanyArray
            ];
            
            $managementObMenu = 
            [
                'text'    => 'Manajemen OB',
                'submenu' => $managementObArray
            ];

            $equipmentMenu = 
            [
                'text'    => 'Perlengkapan',
                'submenu' => $equipmentMenuArray
            ];

            $taskMenu = 
            [
                'text'    => 'Manajemen Pekerjaan',
                'submenu' => $taskMenuArray
            ];

            $usedMenu = 
            [
                'text'    => 'Manajemen Barang',
                'submenu' => $managementUsedArray
            ];

            $securityMenu = 
            [
                'text'    => 'Manajemen Keamanan',
                'submenu' => $securityMenuArray
            ];

            $warehouseMenu = [
                'text'      => 'Daftar Gudang',
                'submenu'   => $warehouseMenuArray
            ];

            $shippingMenu = [
                'text'      => 'Manajemen Pengiriman',
                'submenu'   => $shippingMenuArray
            ];

            $wilayahMenu = [
                'text'      => 'Wilayah',
                'submenu'   => $wilayahMenuArray    
            ];

            $productMenu = [
                'text'      => 'Manajemen Produk',
                'submenu'   => $productMenuArray    
            ];

            $subcribetionMenu = [
                'text'      => 'Manajemen Perpanjangan',
                'submenu'   => $subcribetionMenuArray    
            ];

            $internetManagementMenu = [
                'text'      => 'Manajemen Internet',
                'submenu'   => $internetManagementMenuArray    
            ];

            $settingMenu = [
                'text'      => 'Setting',
                'submenu'   => $settingMenuArray    
            ];


            // if($managementCompanyMenu['submenu'] )
            // {
            //     $event->menu->add($managementCompanyMenu);
            // }

            // if($productMenu['submenu'] )
            // {
            //     $event->menu->add($productMenu);
            // }
            
            // if($managementSalesMenu['submenu'] )
            // {
            //     $event->menu->add($managementSalesMenu);
            // }

            // if($managementRequestItemMenu['submenu'] )
            // {
            //     $event->menu->add($managementRequestItemMenu);
            // }

            // if($productivityMenu['submenu'] )
            // {
            //     $event->menu->add($productivityMenu);
            // }

            // if($managementObMenu['submenu'] )
            // {
            //     $event->menu->add($managementObMenu);
            // }

            // if($equipmentMenu['submenu'] )
            // {
            //     $event->menu->add($equipmentMenu);
            // }

            // if($taskMenu['submenu'] )
            // {
            //     $event->menu->add($taskMenu);
            // }

            // if($usedMenu['submenu'] )
            // {
            //     $event->menu->add($usedMenu);
            // }
            
            // if($securityMenu['submenu'] )
            // {
            //     $event->menu->add($securityMenu);
            // }

            // if($warehouseMenu['submenu'] )
            // {
            //     $event->menu->add($warehouseMenu);
            // }

            // if($shippingMenu['submenu'] )
            // {
            //     $event->menu->add($shippingMenu);
            // }

            // if($wilayahMenu['submenu'] )
            // {
            //     $event->menu->add($wilayahMenu);
            // }

            // if($subcribetionMenu['submenu'] )
            // {
            //     $event->menu->add($subcribetionMenu);
            // }

            // if($internetManagementMenu['submenu'] )
            // {
            //     $event->menu->add($internetManagementMenu);
            // }

            //refactor
            $masterMenu = [
                [
                    'text' => 'Dashboard',
                    'url'  => 'home',
                    'icon' => 'fa fa-home',
                ],
                [
                    'text' => 'Manajemen Umum',
                    'submenu' => [
                        $managementCompanyMenu,
                        $wilayahMenu,
                        $warehouseMenu,
                        $subcribetionMenu
                    ]
                ],
                [
                    'text' => 'Manajemen Produk & Barang',
                    'submenu' => [
                        $productMenu,
                        $usedMenu,
                        $managementRequestItemMenu,
                        $equipmentMenu
                    ]
                ],
                [
                    'text' => 'Operasional & Penjualan',
                    'submenu' => [
                        $managementSalesMenu,
                        $shippingMenu,
                        $managementObMenu,
                        $taskMenu,
                        $productivityMenu
                    ]
                ],
                [
                    'text' => 'Keamanan & Internet',
                    'submenu' => [
                        $securityMenu,
                        $internetManagementMenu
                    ]
                ],
                [
                    'text' => 'Setting',
                    'submenu' => [
                        $settingMenu
                    ]
                ]
            ];

            foreach ($masterMenu as $menuGroup) {
                // Cek apakah punya submenu
                if (isset($menuGroup['submenu'])) {
                    // Filter submenu yang tidak kosong
                    $filteredSubmenu = array_filter($menuGroup['submenu'], function ($subMenuItem) {
                        return isset($subMenuItem['submenu']) && !empty($subMenuItem['submenu']);
                    });
    
                    // Tambahkan hanya jika masih ada submenu yang valid
                    if (!empty($filteredSubmenu)) {
                        $menuGroup['submenu'] = $filteredSubmenu;
                        $event->menu->add($menuGroup);
                    }
                } else {
                    // Menu tanpa submenu (misalnya dashboard)
                    $event->menu->add($menuGroup);
                }
            }
        });


        // die;
        Blade::if('canAccess', function($method, $table){
            return Access::can($method, $table);
        });
    }
}
