<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role = Role::orderBy('id','desc')->get();
        return view('role.index',compact('role'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $is_editable = true;
        $permission = Permission::orderBy('table')->get();
        $dataPermission = $permission->groupBy('table')->toArray();

        $mainMenus = [
            'assets',
            'asset_assigns',
            'attendances',
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
            'equipment',
            'equipment_reductions',
            'tasks',
            'task_assigns',
            'report_points',
            'security_checks',
            'cctv_checks',
            'tickets',
            'trainings',
            'ip_rights',
            'sales_achievements',
            'report_points',
            'dailytasks',
            'daily_task_projects',
            'objectives',
            'divisions',
            'visions',
            'missions',
            'project_dashboards',
            'report_productivities',
            'daily_task_categories',
            'division_budgets',
            'shifting_obs',
            'schedule_obs',
            'inboxes',
            'letter_submissions',
            'devices',
            'positions',
            'national_holidays',
            'employee_checkings',
            'invoices',
            'xeros',
            'roles',
            'pass_checkings',
            'warehouses',
            'sensors',
            'racks',
            'wilayahs',
            'providers','shipping_rates',
            'provinces','cities','districts','subdistricts','postal_codes',
            'shipping_calculations',
            'ask_bos',
            'decisions',
            'kyes',
            'partnership_agreements',
            'product_suppliers',
            'office_media',
        ];

        $checked = array_merge($mainMenus);

        return view('role.createOrEdit',compact('permission','is_editable','dataPermission','mainMenus','checked'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);
        DB::beginTransaction();
        try {
            // $role = Role::create(['name' => $request->input('name')]);
            $role = new Role();
            $role->name = $request->name;
            $role->save();
            foreach ($request->permission as $key => $value) 
            {
                // PermissionRole::create(['role_id' => $role->id, 'permission_id' => $value]);
                $permissionRole = new PermissionRole();
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_id = $value;
                $permissionRole->save();
            }
            DB::commit();
            return redirect()->to(route('role.index'))
                            ->with('success','Role created successfully');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            // dd($th);
            Log::error($th);
            return redirect()->route('role.index')
            ->with('error','Role created unsuccessfully'.$th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $is_editable = false;
        $role = Role::find($role->id);
        $permission = Permission::orderBy('table')->get();
        $dataPermission = $permission->groupBy('table')->toArray();

        $rolePermissions = DB::table("permission_role")->where("permission_role.role_id",$role->id)
            ->pluck('permission_role.permission_id','permission_role.permission_id')
            ->all();
        
            $mainMenus = [
                'assets',
                'asset_assigns',
                'attendances',
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
                'equipment',
                'equipment_reductions',
                'tasks',
                'task_assigns',
                'report_points',
                'security_checks',
                'cctv_checks',
                'tickets',
                'trainings',
                'ip_rights',
                'sales_achievements',
                'report_points',
                'dailytasks',
                'daily_task_projects',
                'objectives',
                'divisions',
                'visions',
                'missions',
                'project_dashboards',
                'report_productivities',
                'daily_task_categories',
                'division_budgets',
                'shifting_obs',
                'schedule_obs',
                'inboxes',
                'letter_submissions',
                'devices',
                'positions',
                'national_holidays',
                'employee_checkings',
                'invoices',
                'xeros',
                'roles',
                'pass_checkings',
                'warehouses',
                'sensors',
                'racks',
                'wilayahs',
                'providers','shipping_rates',
                'provinces','cities','districts','subdistricts','postal_codes',
                'shipping_calculations',
                'ask_bos',
                'decisions',
                'kyes',
                'partnership_agreements',
                'product_suppliers',
                'office_media',
            ];
        $checked = array_merge($mainMenus);

        return view('role.createOrEdit',compact('role','permission','rolePermissions','dataPermission','is_editable','mainMenus','checked'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $is_editable = true;
        $role = Role::find($role->id);
        $permission = Permission::orderBy('table')->get();
        $dataPermission = $permission->groupBy('table')->toArray();

        $rolePermissions = DB::table("permission_role")->where("permission_role.role_id",$role->id)
            ->pluck('permission_role.permission_id','permission_role.permission_id')
            ->all();
        
            $mainMenus = [
                'assets',
                'asset_assigns',
                'attendances',
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
                'equipment',
                'equipment_reductions',
                'tasks',
                'task_assigns',
                'report_points',
                'security_checks',
                'cctv_checks',
                'tickets',
                'trainings',
                'ip_rights',
                'sales_achievements',
                'report_points',
                'dailytasks',
                'daily_task_projects',
                'objectives',
                'divisions',
                'visions',
                'missions',
                'project_dashboards',
                'report_productivities',
                'daily_task_categories',
                'division_budgets',
                'shifting_obs',
                'schedule_obs',
                'inboxes',
                'letter_submissions',
                'devices',
                'positions',
                'national_holidays',
                'employee_checkings',
                'invoices',
                'xeros',
                'roles',
                'pass_checkings',
                'warehouses',
                'sensors',
                'racks',
                'wilayahs',
                'providers','shipping_rates',
                'provinces','cities','districts','subdistricts','postal_codes',
                'shipping_calculations',
                'ask_bos',
                'decisions',
                'kyes',
                'partnership_agreements',
                'product_suppliers',
                'office_media',
            ];
        
        $checked = array_merge($mainMenus);

        return view('role.createOrEdit',compact('role','permission','rolePermissions','dataPermission','is_editable','mainMenus','checked'));

        // dd($rolePermissions);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
        
        DB::beginTransaction();
        try {
            //code...
            $role = Role::find($role->id);
            $role->name = $request->input('name');
            $role->save();
    
            DB::table("permission_role")->where("permission_role.role_id",$role->id)->delete();
            foreach ($request->permission as $key => $value) 
            {
                $permissionRole = new PermissionRole();
                $permissionRole->role_id = $role->id;
                $permissionRole->permission_id = $value;
                $permissionRole->save();
            }
            
            DB::commit();
            return redirect()->route('role.index')
                            ->with('success','Role updated successfully');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);
            // dd($th);
            return redirect()->route('role.index')
            ->with('success',false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role = Role::find($role->id)->delete();
        return redirect()->route('role.index')
        ->with('success','Role Delete successfully');
    }
}
