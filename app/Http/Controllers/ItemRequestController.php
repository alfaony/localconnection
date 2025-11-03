<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Events\ChatMessageSent;

use App\Models\User;
use App\Models\Company;
use App\Models\Delivery;
use App\Models\ItemRequest;
use App\Models\SupplierType;
use App\Models\SettingCompany;
use App\Models\ProductSupplier;
use App\Models\PotentialVendor;
use App\Models\SupplierCategory;

use App\Jobs\SentMessageToVendor;
use App\Jobs\ProcessItemRequestCreated;

use App\Helpers\Access;
use App\Helpers\InboxHelper;

use App\Services\WorkflowService;

use App\Services\Weblas\Device;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;


class ItemRequestController extends Controller
{
    public function index()
    {   
        $stepsRequest = config('custom.request_order_step');;
        return view('item_request.index', compact('stepsRequest'));
    }

    public function create()
    {                
        $settingCompany = SettingCompany::byCompany(Auth::user()->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');
        $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
        $types = SupplierType::byCompany(Auth::user()->company_id)->get();
        $productSuppliers = collect();

        $shareWa = $client->status() ?? false;

        $sprinters = User::where('company_id', Auth::user()->company_id)
            ->whereHas('role.permissions', function ($q) {
                $q->where('method', 'as_sprinter')
                ->where('table', 'item_requests');
            })
            ->get();

        $existsSprinter = $sprinters->count() > 0;

        
        // $statusShareWa = 
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('item_request.createOrEdit', compact('categories', 'shareWa', 'existsSprinter','types','sprinters', 'productSuppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty'=> "required|numeric|min:1"
        ]);

        // dd(collect($request->product_supplier_id));
        // dd($request->all());
        // $userCandidate = $this->findCandidate(Auth::user()->company_id);

        if ($request->hasFile('picture')) 
        {
            $validated['picture'] = $request->file('picture')->store('item_pictures');
        }

        // $validated['assigned_pic_id'] = $userCandidate->id;
        $validated['user_id'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'REQUESTED';
        $validated['supplier_type_id'] = $request->type;

        $item = ItemRequest::create($validated);

        dispatch(new ProcessItemRequestCreated($item->id, $request->assigned_pic_id, $request->product_supplier_id));

        $settingCompany = SettingCompany::byCompany(Auth::user()->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');

        if($request->shareWa && $settingCompany['server_wablas'] && $settingCompany['token_wablas'])
        {
            dispatch(new SentMessageToVendor($item));
        }
        return redirect()->route('item-request.show',$item->id)->with('success', 'Request submitted.');
    }

    public function edit(ItemRequest $itemRequest)
    {
        if(!$itemRequest->action_permission) 
        {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit data ini.');
        }

        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();

        $settingCompany = SettingCompany::byCompany(Auth::user()->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');
        $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
        $types = SupplierType::byCompany(Auth::user()->company_id)->get();
        $productSuppliers = ProductSupplier::byCompany(Auth::user()->company_id)->get();

        $shareWa = $client->status() ?? false;

        $sprinters = User::where('company_id', Auth::user()->company_id)
            ->whereHas('role.permissions', function ($q) {
                $q->where('method', 'as_sprinter')
                ->where('table', 'item_requests');
            })
            ->get();

        $existsSprinter = $sprinters->count() > 0;

        return view('item_request.createOrEdit', compact('itemRequest', 'categories', 'shareWa','existsSprinter','types', 'sprinters', 'productSuppliers'));
    }

    public function show(ItemRequest $itemRequest)
    {
        return view('item_request.show', compact('itemRequest'));
    }

    public function update(Request $request, ItemRequest $itemRequest)
    {
        $validated = $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty' => 'required|numeric|min:1',
        ]);
        
                // Kelola PotentialVendor berdasarkan product_supplier_id
        if (!empty($request->product_supplier_id)) 
        {
            $inputVendorIds = $request->product_supplier_id ?? [];

            // Step 1: Ambil ID existing dari relasi
            $existingVendorIds = $itemRequest->potentialVendors->pluck('product_supplier_id')->toArray();

            // Step 2: Simpan atau update data baru
            foreach ($inputVendorIds as $vendorId) {
                PotentialVendor::firstOrCreate([
                    'company_id' => $itemRequest->company_id,
                    'item_request_id' => $itemRequest->id,
                    'product_supplier_id' => $vendorId,
                ], [
                    'responded' => false
                ]);
            }

            // Step 3: Hapus data lama yang tidak ada dalam input baru
            $vendorsToDelete = array_diff($existingVendorIds, $inputVendorIds);

            if (!empty($vendorsToDelete)) {
                PotentialVendor::where('item_request_id', $itemRequest->id)
                    ->whereIn('product_supplier_id', $vendorsToDelete)
                    ->delete();
            }
        } 
        // else 
        // {
        //     $itemRequest->potentialVendors()->delete();
        // }
        
        $validated['supplier_type_id'] = $request->type;

        $settingCompany = SettingCompany::byCompany(Auth::user()->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');

        if($request->shareWa && $settingCompany['server_wablas'] && $settingCompany['token_wablas'])
        {
            dispatch(new SentMessageToVendor($itemRequest));
        }
        

        if ($request->hasFile('picture')) 
        {
            if ($itemRequest->picture) {
                Storage::delete($itemRequest->picture);
            }
            $validated['picture'] = $request->file('picture')->store('item_pictures');
        }

        // dispatch(new ProcessItemRequestCreated($itemRequest->id));
        $itemRequest->update($validated);

        return redirect()->route('item-request.show',$itemRequest->id)->with('success', 'Request updated.');
    }

    public function destroy(ItemRequest $itemRequest)
    {
        if (!$itemRequest->is_open) 
        {
            return redirect()->route('item-request.index')->with('error', 'Error: Request sudah selesai, tidak dapat dihapus.');
        }

        if(!$itemRequest->action_permission) 
        {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit data ini.');
        }
        
        $itemRequest->delete();
        return redirect()->route('item-request.index')->with('success', 'Request deleted.');
    }

    public function dataTableJson(Request $request)
    {
        // Fetch data for the DataTable
        $query = ItemRequest::query();
        $query->with('category')->byCompany(Auth::user()->company_id)->orderBy('updated_at', 'desc');

        if ($request->filled('status')) 
        {
            $query->where('status', $request->status);
        }
        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['item_name', 'category', 'estimated_price', 'qty', 'status'];

        // Define searchable columns
        $searchable = [
            'item_name',
            'category.name',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [];

        if(Access::can('show','item_requests') && Access::can('workflow','item_requests'))
        {
            $pdf = [
                'name' => 'Show',
                'route' => 'item-request.show',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','item_requests'))
        {
            $edit = [
                'name' => 'Edit',
                'route' => 'item-request.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','item_requests'))
        {
            $destroy = [
                'name' => 'Delete',
                'route' => 'item-request.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }


        $response =  datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();

        foreach ($data->data as $index => $item) 
        {
            $item->item_name = '<a href="'.route('item-request.show',$item->id).'">'.$item->item_name.'</a>';
            $item->estimated_price = 'Rp. '.number_format($item->estimated_price, 0,',','.'); // Format angka dengan 2 desimal
            $item->status = $item->status_badge;
            if(!$item->action_permission)
            {
                $item->action = '<span class="badge badge-danger"><i class="fas fa-times"></i></span>';
            }
        }

        
        return response()->json($data);
    }

    public function workflow($itemRequest)
    {
        try 
        {   
            $itemRequest = ItemRequest::find($itemRequest);

            // Generate array steps dari service atau manual
            $steps = WorkflowService::generateSteps($itemRequest); // asumsi kamu punya ini

            // Render HTML partial blade
            $htmlWorkflow = view('item_request._workflow_steps', compact('steps','itemRequest'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $htmlWorkflow,
                'status_badge' => $itemRequest->status_badge,
                'status_open' => $itemRequest->status_open,
                'status_closed' => $itemRequest->status == 'CLOSED' ? true : false,
                'reason_text' => $itemRequest->close_reason,
            ]);

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Workflow load error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat workflow.'
            ], 500);
        }
    }

    public function delivery(Request $request, $id)
    {
        $request->validate([
            'resi_number' => 'nullable|string',
            'airwillbill_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'delivery_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);
        
       $itemRequest = ItemRequest::findOrFail($id);

       DB::beginTransaction();
       try {
        if ($request->hasFile('airwillbill_photo')) {
            $airwillbillPath = $request->file('airwillbill_photo')->store('airwillbill');
        } else {
            $airwillbillPath = null;
        }
        $deliveryPhotoPath = $request->hasFile('delivery_photo') ? $request->file('delivery_photo')->store('delivery_photo') : null;

        if(!$itemRequest->delivery)
        {
            $delivery = Delivery::create(
            [
                'item_request_id' => $itemRequest->id,
                'company_id' => auth()->user()->company_id,
                'sprinter_id' => auth()->id(),
                'shipping_method' => $request->shipping_method,
                'resi_number' => $request->resi_number,
                'airwillbill_photo' => $airwillbillPath,
                'delivery_photo' => $deliveryPhotoPath,
            ]);
            
            $message = "Air Way Bill (Resi) Sudah Terbit Untuk Request #{$itemRequest->item_name} #id {$itemRequest->id}";
            $directUrl = route('item-request.show', $itemRequest->id);
            $this->sentInbox($itemRequest->user_id,$message, $directUrl, $itemRequest->id);
        }
        else
        {
            $itemRequest->delivery->update([
                'delivery_photo' => $deliveryPhotoPath,
                'delivered_at' => now(),
            ]);

            $delivery = $itemRequest->delivery;

            ItemRequest::where('id', $id)->update(['status' => 'DELIVERED']);

            $message = "Request selesai #{$itemRequest->item_name} #id {$itemRequest->id}";
            $directUrl = route('item-request.show', $itemRequest->id);
            $this->sentInbox($itemRequest->user_id,$message, $directUrl, $itemRequest->id);
        }

        DB::commit();
        return response()->json(['success' => true, 'delivery' => $delivery]);

       } catch (\Throwable $th) {
        //throw $th;
        // dd($th);
        DB::rollBack();
        Log::error($th);

        return response()->json(['success' => false, 'message' => 'Error']);
       }
    }

    public function publicIndex($companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();
        $settingCompany = SettingCompany::byCompany($company->id)->where('field_title','closed_time')->get()->pluck('field_value','field_title');

        return view('item_request.public_index', compact('company', 'settingCompany'));
    }

    public function loadByCompany($companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $requests = ItemRequest::with(['assignedPic', 'requester'])
            ->where('company_id', $company->id)
            ->where('status', '!=', 'DELIVERED')
            ->where('status', '!=', 'CLOSED')
            ->latest()
            ->get();

        $data = $requests->map(function ($request) {
            $created = \Carbon\Carbon::parse($request->created_at);
            $now = now();
            $target = $created->copy()->setTime(16, 0, 0); // target: 04:00 hari itu
            $todaySameDay = $created->isSameDay($now);

            if (!$todaySameDay || $now->greaterThan($target)) {
                $countdown = '00:00:00';
                $expired = true;
            } else {
                $diff = $now->diff($target);
                $countdown = $diff->format('%H:%I:%S');
                $expired = false;
            }

            return [
                'sprinter' => optional($request->assignedPic)->name ?? '-',
                'item'     => $request->item_name,
                'qty'      => $request->qty,
                'status'   => $request->status,
                'countdown'=> $countdown,
                'expired'  => $expired,
                'created_at' => $request->created_at,
                'status_badge' => $request->status_badge
            ];
        });

        return response()->json($data);
    }

    public function closed(Request $request, $id)
    {
        $itemRequest = ItemRequest::findOrFail($id);
        
        if (!$itemRequest->is_open) {
            return response()->json(['message' => 'Permintaan sudah ditutup'], 400);
        }

        $itemRequest->is_open = false;
        $itemRequest->status = 'CLOSED';
        $itemRequest->close_reason = $request->close_reason;
        $itemRequest->save();

        $message = "Permintaan #{$itemRequest->item_name} #id {$itemRequest->id} telah ditutup. Silakan cek detailnya";
        $directUrl = route('item-request.show', $itemRequest->id);
        $this->sentInbox($itemRequest->user_id,$message, $directUrl, $itemRequest->id);

        return response()->json(['message' => 'Permintaan ditutup']);
    }

    private function findCandidate($companyId)
    {
        $users = User::where('company_id', $companyId)
        ->withCount(['assignedRequests' => function ($query) {
            $query->whereIn('status', ['REQUESTED', 'WAITING_PAYMENT', 'PAID', 'READY_TO_SEND']);
        }])
        ->get();
    
        // Cari jumlah tugas terkecil
        $min = $users->min('assigned_requests_count');
    
        // Ambil semua user dengan jumlah tugas terkecil
        $candidates = $users->where('assigned_requests_count', $min);
    
        // Pilih satu secara acak
        $assignedUser = $candidates->random();
    
        // Assign jika ada
        return $assignedUser;
    }

    private function  matchStatus($status, $badge = false)
    {
        if($badge)
        {
            switch ($status) 
            {
                 case 'REQUEST':
                     return 'badge-primary';
                     break;
                 case 'PROCESSING':
                     return 'badge-warning';
                     break;
                 case 'PAYMENT':
                     return 'badge-success';
                     break;
                 case 'SHIPPING':
                     return 'badge-info';
                     break;
                 default:
                     return 'badge-secondary';
             }
        }else
        {
            switch ($status) 
            {
                case 'REQUEST':
                    return '<i class="fa fa-circle"></i> <span class="badge badge-primary">REQUEST</span>';
                    break;
                case 'PROCESSING':
                    return '<i class="fa fa-spinner fa-spin"></i> <span class="badge badge-warning">PROCESSING</span>';
                    break;
                case 'PAYMENT':
                    return '<i class="fa fa-check"></i> <span class="badge badge-success">PAYMENT</span>';
                    break;
                case 'SHIPPING':
                    return '<i class="fa fa-truck"></i> <span class="badge badge-info">SHIPPING</span>';
                    break;
                default:
                    return '<i class="fa fa-circle"></i> <span class="badge badge-secondary">'.$status.'</span>';
            }
        }


    }

    private function sentInbox($to,$message,$directUrl, $itemRequest = null)
    {
        if($itemRequest)
        {
            broadcast(new ChatMessageSent(
                "",
                $message,
                now(),
                $itemRequest,
                Auth::user()->id
            ))->toOthers();
        }

        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );
        return;
    }
    /**
     * Handle AJAX request to fetch ProductSuppliers based on supplier_category_id and supplier_type_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductSupplier(Request $request)
    {
        $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'supplier_type_id' => 'required|exists:supplier_types,id',
        ]);
    
       $suppliers = ProductSupplier::byCompany(Auth::user()->company_id)
        ->where('supplier_type_id', $request->supplier_type_id)
        ->whereHas('supplierCategories', function ($query) use ($request) {
            $query->where('supplier_category_id', $request->supplier_category_id);
        })
        ->get();
    
        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }
}
