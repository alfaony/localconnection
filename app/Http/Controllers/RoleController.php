<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

use App\Models\Role;
use App\Models\Permission;
use App\Helpers\Access;

/**
 * RoleController dengan SAVE PER ACCORDION/MENU
 * Solusi robust untuk handle unlimited permissions
 */
class RoleController extends Controller
{
    public function index()
    {
        $role = Role::orderBy('id','desc')->get();
        return view('role.index',compact('role'));
    }

    public function create()
    {
        $is_editable = true;
        $permission = Permission::orderBy('table')->get();
        $dataPermission = $permission->groupBy('table')->toArray();

        $mainMenus = $this->getMainMenus();
        $checked = array_merge($mainMenus);

        return view('role.createOrEdit',compact('permission','is_editable','dataPermission','mainMenus','checked'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
        ]);
        
        DB::beginTransaction();
        try {
            $role = new Role();
            $role->name = $request->name;
            $role->save();
            
            DB::commit();
            
            // Redirect ke edit untuk assign permissions per section
            return redirect()->route('role.edit', $role)
                ->with('success', 'Role created successfully. Now assign permissions.');
                
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            return redirect()->route('role.index')
                ->with('error', 'Create failed: ' . $th->getMessage());
        }
    }

    public function show(Role $role)
    {
        $is_editable = false;
        $role = Role::find($role->id);
        $permission = Permission::orderBy('table')->get();
        $dataPermission = $permission->groupBy('table')->toArray();

        $rolePermissions = DB::table("permission_role")
            ->where("permission_role.role_id", $role->id)
            ->pluck('permission_role.permission_id','permission_role.permission_id')
            ->all();
        
        $mainMenus = $this->getMainMenus();
        $checked = array_merge($mainMenus);

        return view('role.createOrEdit',compact('role','permission','rolePermissions','dataPermission','is_editable','mainMenus','checked'));
    }

    public function edit(Role $role)
    {
        $is_editable = true;
        $role = Role::find($role->id);
        $permission = Permission::orderBy('table')->get();
        $dataPermission = $permission->groupBy('table')->toArray();

        $rolePermissions = DB::table("permission_role")
            ->where("permission_role.role_id", $role->id)
            ->pluck('permission_role.permission_id','permission_role.permission_id')
            ->all();
        
        $mainMenus = $this->getMainMenus();
        $checked = array_merge($mainMenus);

        return view('role.createOrEdit',compact('role','permission','rolePermissions','dataPermission','is_editable','mainMenus','checked'));
    }

    /**
     * Update role name only
     */
    public function updateName(Request $request, Role $role)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        
        try {
            $role->name = $request->name;
            $role->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Role name updated successfully'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update permissions untuk 1 menu/accordion saja
     * 
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMenuPermissions(Request $request, Role $role)
    {
        $this->validate($request, [
            'menu' => 'required|string',
            'permissions' => 'array', // Bisa kosong (uncheck all)
        ]);
        
        $menu = $request->menu;
        $selectedPermissions = $request->permissions ?? [];
        
        Log::info("Updating permissions for menu '{$menu}' in role {$role->id}");
        
        DB::beginTransaction();
        try {
            // 1. Get all permission IDs untuk menu ini
            $menuPermissionIds = Permission::where('table', $menu)
                ->pluck('id')
                ->toArray();
            
            // 2. Delete existing permissions untuk menu ini saja
            DB::table('permission_role')
                ->where('role_id', $role->id)
                ->whereIn('permission_id', $menuPermissionIds)
                ->delete();
            
            // 3. Insert selected permissions dengan UUID
            $insertData = [];
            foreach ($selectedPermissions as $permissionId) {
                $insertData[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('permission_role')->insert($insertData);
            }
            
            // 4. Clear cache
            Access::clearCacheForRole($role->id);
            
            DB::commit();
            
            Log::info("✅ Menu '{$menu}' updated with " . count($selectedPermissions) . " permissions");
            
            return response()->json([
                'success' => true,
                'message' => "Menu '{$menu}' updated successfully",
                'menu' => $menu,
                'permissions_count' => count($selectedPermissions)
            ]);
            
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error("❌ Error updating menu '{$menu}': " . $th->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update - select all permissions untuk role
     */
    public function selectAll(Request $request, Role $role)
    {
        DB::beginTransaction();
        try {
            // Get all permission IDs
            $allPermissionIds = Permission::pluck('id')->toArray();
            
            // Delete all existing
            DB::table('permission_role')->where('role_id', $role->id)->delete();
            
            // Insert all dengan UUID
            $insertData = [];
            foreach ($allPermissionIds as $permissionId) {
                $insertData[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Batch insert (500 per batch untuk safety)
            $chunks = array_chunk($insertData, 500);
            foreach ($chunks as $chunk) {
                DB::table('permission_role')->insert($chunk);
            }
            
            Access::clearCacheForRole($role->id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'All permissions selected (' . count($allPermissionIds) . ' permissions)',
                'count' => count($allPermissionIds)
            ]);
            
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Bulk update - deselect all permissions untuk role
     */
    public function deselectAll(Request $request, Role $role)
    {
        DB::beginTransaction();
        try {
            DB::table('permission_role')->where('role_id', $role->id)->delete();
            
            Access::clearCacheForRole($role->id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'All permissions removed'
            ]);
            
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, Role $role)
    {
        // Legacy method - redirect to edit with message
        return redirect()->route('role.edit', $role)
            ->with('info', 'Please update permissions per menu section.');
    }

    public function destroy(Role $role)
    {
        $role = Role::find($role->id)->delete();
        return redirect()->route('role.index')
            ->with('success','Role deleted successfully');
    }

    private function getMainMenus()
    {
        return [
            // a
            'agreement_letters', 'ask_bos', 'asset_assigns', 'assets', 'attendances',

            // b
            'badges', 'barcodes', 'basts', 'brand_product_stores',

            // c
            'category_product_stores', 'cctv_checks', 'challenges', 'chat_messages', 'cities',
            'companies', 'coverage_services', 'customer_checkouts', 'customer_software', 'customer_subscriptions',
            'customers',

            // d
            'daily_task_categories', 'daily_task_projects', 'dailytasks', 'dashboard_weekly_reports', 'data_centers',
            'dayoffs', 'decisions', 'devices', 'districts', 'division_budgets',
            'divisions',

            // e
            'employee_checkings', 'employees', 'equipment', 'equipment_reductions','events',

            // f
            'flowcharts',

            // h
            'homes',

            // i
            'inboxes', 'internet_customers', 'internet_packages', 'invoices', 'ip_rights',
            'item_purchases', 'item_requests',

            // k
            'kyes',

            // l
            'letter_submissions',

            // m
            'managers', 'master_accounts', 'master_check_items', 'meetings', 'missions',
            'moms',

            // n
            'national_holidays',

            // o
            'objectives', 'office_attendances', 'office_media', 'optical_distributions',

            // p
            'partner_parameter_types', 'partner_types', 'partners', 'partnership_agreements', 'pass_checkings',
            'pops', 'positions', 'postal_codes', 'pricelists', 'product_categories',
            'product_stores', 'product_suppliers', 'products', 'project_dashboards', 'projects',
            'promos', 'providers', 'provinces', 'punishment_users',

            // q
            'quotes',

            // r
            'racks', 'report_points', 'report_productivities', 'report_projects', 'reports',
            'roles', 'routers',

            // s
            'sales', 'sales_achievements', 'schedule_obs', 'security_checks', 'sensors',
            'setting_companies', 'shifting_obs', 'shipping_calculations', 'shipping_rates', 'software',
            'software_dashboards', 'software_packages', 'store_sellings', 'subdistricts', 'subscribe_letters',
            'subscription_payments', 'subscriptions', 'supliers', 'supplier_categories', 'supplier_types',

            // t
            'task_assigns', 'tasks', 'tickets', 'trainings',

            // u
            'used_items', 'used_laptops', 'users',

            // v
            'vehicles', 'visions',

            // w
            'warehouses', 'webhook_settings', 'weekly_reports', 'wfo_rules', 'wilayahs',
            'work_orders',

            // x
            'xeros', 'xp_configs',

            // z
            'zones',
        ];
    }
}