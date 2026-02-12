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
use App\Models\InternetPackage;
use App\Models\Router;

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

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->package_id) {
            $query->where('internet_package_id', $request->package_id);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List customer berhasil diambil',
            'data'    => $customers
        ]);
    }

    public function show($id)
    {
        $customer = InternetCustomer::with([
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

            $customer->update([
                'status' => ParamSchema::APPROVED,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Customer {$customer->code} berhasil di-approve",
                'data'    => $customer
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve gagal', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal approve customer',
                'data'    => null
            ], 500);
        }
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
}
