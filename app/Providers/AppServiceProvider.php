<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Helpers\Access;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

use App\Models\EquipmentReduction;
use App\Models\TaskAssign;
use App\Observers\EquipmentReductionObserver;
use App\Observers\TaskAssignObserver;

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

        Schema::defaultStringLength(191);
        if ($this->app->environment('production') || $this->app->environment('development')) 
        {
            URL::forceScheme('https');
        }

        $events->listen(BuildingMenu::class, function (BuildingMenu $event) 
        {

            $listMenu = [
                'homes',
                'pricelists',
                'roles',
            ];

            $managementCompanyArray = array();
            $managementSalesArray = array();
            $managementObArray = array();
            $equipmentMenuArray = array();
            $taskMenuArray = array();
            $securityMenuArray = array();
            $productivityMenuArray = array();

            $equipmentMenu = ['devices','equipment','equipment_reductions'];
            $taskMenu = ['report_points','tasks','task_assigns'];
            $securityMenu = ['assets','security_checks','cctv_checks','tickets'];
            $productivityMenu = ['report_productivities','project_dashboards','division_budgets','visions','divisions','objectives', 'daily_task_projects','daily_task_categories', 'dailytasks','trainings','ip_rights','sales_achievements'];
            $managementCompanyMenu = 
            [
                'national_holidays',
                'employee_checkings',
                'letter_submissions',
                'positions',
                'employees',
                'users',
                'companies',
                'setting_companies',
            ];
            $managementSalesMenu = 
            [
                'managers',
                'products',
                'product_categories',
                'customers',
                'quotes',
                'work_orders',
                'agreement_letters',
                'projects',
                'supliers',
                'report_projects',
                'basts',
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

            $securityMenu = 
            [
                'text'    => 'Manajemen Keamanan',
                'submenu' => $securityMenuArray
            ];

            if($managementCompanyMenu['submenu'] )
            {
                $event->menu->add($managementCompanyMenu);
            }
            
            if($managementSalesMenu['submenu'] )
            {
                $event->menu->add($managementSalesMenu);
            }

            if($productivityMenu['submenu'] )
            {
                $event->menu->add($productivityMenu);
            }

            if($managementObMenu['submenu'] )
            {
                $event->menu->add($managementObMenu);
            }

            if($equipmentMenu['submenu'] )
            {
                $event->menu->add($equipmentMenu);
            }

            if($taskMenu['submenu'] )
            {
                $event->menu->add($taskMenu);
            }
            
            if($securityMenu['submenu'] )
            {
                $event->menu->add($securityMenu);
            }
            
        });

        // die;
        Blade::if('canAccess', function($method, $table){
            return Access::can($method, $table);
        });
    }
}
