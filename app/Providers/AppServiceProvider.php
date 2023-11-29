<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Helpers\Access;


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
                'setting_companies',
                'roles'
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
                    'icon' => 'fa fa-briefcase',
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
                'roles' => [
                    'text'        => 'Role Akses',
                    'route'         => 'role.index',
                    'icon' => 'fa fa-cog',
                ],
            ];

            // dd(Access::can("index", "rol"));
            foreach ($listMenu as $role) 
            {
                if(Access::can("index", $role))
                {
                    $event->menu->add($menus[$role]);
                }
            }
        });

        // die;
        Blade::if('canAccess', function($method, $table){
            return Access::can($method, $table);
        });
    }
}
