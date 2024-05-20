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
                'employees',
                'managers',
                'users',
                'products',
                'customers',
                'quotes',
                'work_orders',
                'agreement_letters',
                'projects',
                'supliers',
                'report_projects',
                'basts',
                'reports',
                'companies',
                'setting_companies',
                'roles',
                'attendances',
            ];

            $equipmentMenuArray = array();
            $taskMenuArray = array();
            $securityMenuArray = array();
            $productivityMenuArray = array();

            $equipmentMenu = ['equipment','equipment_reductions'];
            $taskMenu = ['report_points','tasks','task_assigns'];
            $securityMenu = ['assets','security_checks','cctv_checks','tickets'];
            $productivityMenu = ['report_productivities','dailytasks','trainings','ip_rights','sales_achievements'];

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
            ];

            foreach ($listMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    $event->menu->add($menus[$role]);
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

            $productivityMenu = 
            [
                'text'    => 'Produktifitas',
                'submenu' => $productivityMenuArray
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

            if($productivityMenu['submenu'] )
            {
                $event->menu->add($productivityMenu);
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
