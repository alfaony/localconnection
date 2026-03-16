<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerInstallation;
use App\Models\InternetInstallationPhoto;
use App\Models\CoverageServiceDistribution;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Models\AddressPool;

use App\Jobs\ProvisionCustomerJob;
use App\Jobs\SyncInstalledCustomersJob;

use App\Schemas\ParamSchema;

class InternetCustomerApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = InternetCustomer::query()
            ->byCompany($user->company_id)
            ->with([
                'internetPackage:id,name',
                'installation:id,internet_customer_id,device_serial_number',
                'userCustomer:id,name,email,phone_number'
            ]);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%")
                ->orWhere('grouping_id', 'like', "%$search%");
            });
        }

        $allowedStatuses = ['pending', 'process_installation', 'installed'];

        if ($request->status) {
            if ($request->status == 'prosess') {
                $query->whereIn('status', ['pending', 'process_installation']);
            } else if ($request->status == 'installed') {
                $query->where('status', 'installed');
            }
        } else {
            $query->whereIn('status', $allowedStatuses);
        }

        if ($request->package_id) {
            $query->where('internet_package_id', $request->package_id);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        $customData = $customers->getCollection()->transform(function ($customer) {
            return [
                'id'               => $customer->id,
                'action_user_id'   => $customer->action_user_id,
                'user_customer_id' => $customer->user_customer_id,
                'company_id'       => $customer->company_id,
                'code'             => $customer->code,
                'code_cust'        => $customer->code_cust,
                'name'             => $customer->name,
                'address'          => $customer->address,
                'is_paid'          => $customer->is_paid,
                'status'           => $customer->status,
                'created_at'       => $customer->created_at,
                'internet_package' => $customer->internetPackage ? [
                    'id'   => $customer->internetPackage->id,
                    'name' => $customer->internetPackage->name,
                ] : null,
                'installation'     => $customer->installation,
                'user_customer'    => [
                    'id'   => $customer->userCustomer->id ?? null,
                    'name' => $customer->userCustomer->name ?? null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'List customer berhasil diambil',
            'data'    => [
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'data'         => $customData
            ]
        ]);
    }

    public function show($id)
    {
        $customer = InternetCustomer::with([
            'province:id,name',
            'city:id,name',
            'district:id,name',
            'subdistrict:id,name',
            'promo:id,name',
            'odp:id,name',
            'router:id,name',
            'internetPackage',
            'userCustomer',
            'installation',
            'installation.medias'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail customer ditemukan',
            'data'    => $customer
        ]);
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $customer = InternetCustomer::findOrFail($id);

            if ($customer->status !== ParamSchema::PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer tidak dalam status pending',
                    'data'    => null
                ], 422);
            }

            $this->installation($customer);

            DB::commit();

            Log::info('Customer pending approved via API', [
                'customer_id'   => $customer->id,
                'customer_code' => $customer->code,
                'approved_by'   => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pendaftaran pelanggan {$customer->code} telah disetujui",
                'data'    => $customer->load('internetPackage') // Reload data terbaru
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to approve pending customer via API', [
                'customer_id' => $id,
                'error'       => $th->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui pendaftaran: ' . $th->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    private function installation($customer)
    {
        try {
            $customer->update([
                'status'         => ParamSchema::PROCESS_INSTALLATION,
                'action_user_id' => Auth::id()
            ]);

            $userTechnical = optional($customer->subdistrict?->coverageService?->coverageServiceOds)
                ->pluck('ods.user_assign_id')
                ->unique()
                ->filter() 
                ->all();

            $from = \App\Models\User::where('company_id', $customer->company_id)
                    ->whereHas('role', function ($q) {
                        $q->whereIn('name', [\App\Schemas\RoleSchema::ROOT, \App\Schemas\RoleSchema::ADMIN]);
                    })
                    ->first();

            if (!empty($userTechnical) && $from) {
                $message = "Pembayaran Langganan Internet Untuk Kode " . $customer->code . " Telah di Setujui. Silahkan segera lakukan Pemasangan";
                
                $directUrl = config('app.url') . "/internet-customer/" . $customer->id;

                foreach ($userTechnical as $tech) {
                    $this->sentInbox($tech, $from->id, $message, $directUrl);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    private function sentInbox($to, $from, $message, $url)
    {

    }


    public function close($id)
    {
        DB::beginTransaction();
        try {
            $customer = InternetCustomer::findOrFail($id);

            if ($customer->status !== ParamSchema::PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer tidak dalam status pending',
                    'data'    => null
                ], 422);
            }

            $customer->update([
                'status' => ParamSchema::CLOSED,
                'action_user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Customer {$customer->code} berhasil ditutup",
                'data'    => $customer
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Close gagal', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup customer',
                'data'    => null
            ], 500);
        }
    }

    public function completeInstallation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'serial_number'            => 'required|string|max:255',
            'notes'                    => 'nullable|string',
            'router_id'                => 'required|exists:routers,id',
            'username'                 => 'required|unique:internet_customers,username',
            'password'                 => 'required',
            'local_address'            => 'nullable|ip|unique:internet_customers,local_address',
            'optical_distribution_id'  => 'required|exists:optical_distributions,id',
            'grouping_id'              => 'nullable|string|max:255',
            'photos.*'                 => 'required|image|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data'    => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $customer = InternetCustomer::findOrFail($id);

            $customer->update([
                'grouping_id'             => $request->grouping_id,
                'status'                  => ParamSchema::INSTALLED,
                'local_address'           => $request->local_address,
                'router_id'               => $request->router_id,
                'username'                => $request->username,
                'pass_hash'               => $request->password,
                'override_pool_id'        => $request->override_pool_id,
                'optical_distribution_id' => $request->optical_distribution_id,
            ]);

            dispatch(new ProvisionCustomerJob($customer->id));

            $installation = InternetCustomerInstallation::create([
                'internet_customer_id' => $customer->id,
                'device_serial_number' => $request->serial_number,
                'notes'                => $request->notes,
                'installed_at'         => now(),
                'technical_user_id'    => Auth::id(),
            ]);

            $photoCount = 0;
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $filename = uniqid().'_'.time().'_'.$index.'.'.$photo->getClientOriginalExtension();
                    $path = $photo->storeAs("installation-photos/{$customer->code}", $filename, 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');

                    InternetInstallationPhoto::create([
                        'internet_installation_id' => $installation->id,
                        'photo' => $path,
                        'caption' => 'Installation Photo '.($photoCount+1),
                    ]);

                    $photoCount++;
                }
            }

            if ($photoCount == 0) {
                throw new \Exception("Foto instalasi wajib diupload");
            }

            SyncInstalledCustomersJob::dispatch([$customer->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Instalasi berhasil disimpan ($photoCount foto)",
                'data'    => $installation
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Complete installation gagal', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan instalasi',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function getInstallationResources($id)
    {
        $cust = \App\Models\InternetCustomer::with([
            'internetPackage',
            'subdistrict.coverageService.coverageServiceOds.ods.pops.routers',
        ])->findOrFail($id);

        $routerIds = collect(
            $cust->subdistrict?->coverageService?->coverageServiceOds ?? []
        )
        ->flatMap(function ($csod) {
            return collect($csod->opticalDistribution?->pops ?? [])
                ->flatMap(fn($pop) => collect($pop->routers ?? [])->pluck('id'));
        })
        ->unique()
        ->values();

        if ($routerIds->isEmpty()) {
            $routersData = \App\Models\Router::query()
                ->whereHas('pppoeServers')
                ->orderBy('name')
                ->get(['id', 'name', 'active_status']);
        } else {
            $routersData = \App\Models\Router::query()
                ->whereIn('id', $routerIds)
                ->whereHas('pppoeServers', fn($q) => $q->whereNotNull('address_pool_id'))
                ->whereHas('addressPools')
                ->withCount(['pppoeServers' => fn($q) => $q->whereNotNull('address_pool_id')])
                ->orderBy('name')
                ->get(['id', 'name', 'active_status']);
        }

        $odps = [];
        $coverageService = $cust->subdistrict?->coverageService;
        if ($coverageService) {
            $odps = \App\Models\CoverageServiceDistribution::query()
                ->where('coverage_service_id', $coverageService->id)
                ->with('ods:id,name')
                ->get()
                ->pluck('ods')
                ->filter()
                ->unique('id')
                ->map(fn($odp) => [
                    'id' => $odp->id,
                    'name' => $odp->name
                ])
                ->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'odps' => $odps,
                'routers' => $routersData->map(fn($r) => [
                    'id' => $r->id,
                    'name' => $r->name . ' (PPPoE: ' . ($r->pppoe_servers_count ?? 0) . ')',
                    'is_online' => $r->active_status == 'online'
                ])
            ]
        ]);
    }

    public function getIpPoolsByRouter(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id'
        ]);

        $pools = \App\Models\AddressPool::query()
            ->where('router_id', $request->router_id)
            ->orderBy('name')
            ->get(['id', 'name', 'cidr', 'gateway'])
            ->map(fn($p) => [
                'id'    => $p->id,
                'label' => $p->name . ' — ' . $p->cidr . ($p->gateway ? ' (gw ' . $p->gateway . ')' : '')
            ]);

        return response()->json([
            'success' => true,
            'data' => $pools
        ]);
    }
}