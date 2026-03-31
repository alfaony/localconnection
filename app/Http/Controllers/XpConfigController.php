<?php

namespace App\Http\Controllers;

use App\Helpers\XpHelper;
use App\Models\Company;
use App\Models\EmployeeXpHistory;
use App\Models\User;
use App\Models\XpConfig;
use App\Models\XpConfigModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class XpConfigController extends Controller
{
    /**
     * Daftar semua XP Config.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $configs = XpConfig::withCount('companies')->latest()->paginate(10);

        $stats = [
            'total_configs'    => XpConfig::count(),
            'active_configs'   => XpConfig::where('is_enabled', true)->count(),
            'companies_with_xp'=> Company::byCompany($companyId)->whereNotNull('xp_config_id')->count(),
            'total_xp_awarded' => EmployeeXpHistory::where('company_id', $companyId)->where('xp', '>', 0)->sum('xp'),
            'users_with_xp'    => User::byCompany($companyId)->where('total_xp', '>', 0)->count(),
            'top_xp'           => User::byCompany($companyId)->max('total_xp') ?? 0,
        ];

        return view('xp_config.index', compact('configs', 'stats'));
    }

    /**
     * Form buat config baru.
     */
    public function create()
    {
        $defaultModels = [
            'ALL'         => 'Default (Semua Aksi)',
            'DailyTask'   => 'Daily Task',
            'Meeting'     => 'Meeting',
            'Mom'     => 'Task MoM',
            'ItemRequest' => 'Item Request',
            'WeeklyReport'=> 'Weekly Report',
            'Project'     => 'Project',
            'Decision'    => 'Keputusan',
        ];
        return view('xp_config.createOrEdit', compact('defaultModels'));
    }

    /**
     * Simpan config baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_enabled'  => 'boolean',
            'models'      => 'required|array|min:1',
            'models.*.source_type' => 'required|string',
            'models.*.xp'          => 'required|integer',
            'models.*.label'       => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $config = XpConfig::create([
                'name'        => $request->name,
                'description' => $request->description,
                'is_enabled'  => $request->boolean('is_enabled', true),
            ]);

            foreach ($request->models as $m) {
                XpConfigModel::create([
                    'xp_config_id' => $config->id,
                    'source_type'  => $m['source_type'],
                    'xp'           => (int) $m['xp'],
                    'label'        => $m['label'] ?? $m['source_type'],
                ]);
            }

            DB::commit();
            return redirect()->route('xp-config.index')->with('store', true);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan config: ' . $th->getMessage());
        }
    }

    /**
     * Form edit config.
     */
    public function edit(XpConfig $xpConfig)
    {
        $xpConfig->load('models');
        $defaultModels = [];
        return view('xp_config.createOrEdit', compact('xpConfig', 'defaultModels'));
    }

    /**
     * Update config.
     */
    public function update(Request $request, XpConfig $xpConfig)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_enabled'  => 'boolean',
            'models'      => 'required|array|min:1',
            'models.*.source_type' => 'required|string',
            'models.*.xp'          => 'required|integer',
            'models.*.label'       => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $xpConfig->update([
                'name'        => $request->name,
                'description' => $request->description,
                'is_enabled'  => $request->boolean('is_enabled', true),
            ]);

            // Hapus semua models lama, ganti dengan yang baru
            $xpConfig->models()->delete();
            foreach ($request->models as $m) {
                XpConfigModel::create([
                    'xp_config_id' => $xpConfig->id,
                    'source_type'  => $m['source_type'],
                    'xp'           => (int) $m['xp'],
                    'label'        => $m['label'] ?? $m['source_type'],
                ]);
            }

            // Clear cache semua company yang pakai config ini
            $xpConfig->companies->each(function ($company) {
                XpHelper::clearConfigCache($company->id);
            });

            DB::commit();
            return redirect()->route('xp-config.index')->with('update', true);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update config: ' . $th->getMessage());
        }
    }

    /**
     * Hapus config.
     */
    public function destroy(XpConfig $xpConfig)
    {
        if ($xpConfig->companies()->count() > 0) {
            return redirect()->back()->with('error', 'Config ini masih digunakan oleh ' . $xpConfig->companies()->count() . ' company. Lepaskan dulu sebelum menghapus.');
        }

        $xpConfig->delete();
        return redirect()->route('xp-config.index')->with('delete', true);
    }

    /**
     * Halaman assign config ke company.
     */
    public function assignIndex()
    {
        $companies = Company::with('xpConfig')->byCompany(Auth::user()->company_id)->get();
        $configs   = XpConfig::where('is_enabled', true)->get();
        return view('xp_config.assign', compact('companies', 'configs'));
    }

    /**
     * Proses assign/lepas config ke company.
     */
    public function assignUpdate(Request $request)
    {
        $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'xp_config_id'  => 'nullable|exists:xp_configs,id',
        ]);

        $company = Company::findOrFail($request->company_id);
        $company->update(['xp_config_id' => $request->xp_config_id ?: null]);

        // Clear cache
        XpHelper::clearConfigCache($company->id);

        return redirect()->back()->with('update', true);
    }
}
