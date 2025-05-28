<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Delivery;
use App\Models\ItemRequest;
use App\Models\SupplierCategory;
use App\Jobs\ProcessItemRequestCreated;

use App\Helpers\Access;
use App\Services\WorkflowService;

class ItemRequestController extends Controller
{
    public function index()
    {
        $requests = ItemRequest::byCompany(auth()->user()->company_id)->latest()->paginate(10);
        return view('item_request.index', compact('requests'));
    }

    public function create()
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('item_request.createOrEdit', compact('categories'));
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

        $userCandidate = $this->findCandidate(Auth::user()->company_id);

        if ($request->hasFile('picture')) 
        {
            $validated['picture'] = $request->file('picture')->store('item_pictures', 'public');
        }

        $validated['assigned_pic_id'] = $userCandidate->id;
        $validated['user_id'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'REQUESTED';

        $item = ItemRequest::create($validated);

        dispatch(new ProcessItemRequestCreated($item->id));
        return redirect()->route('item-request.show',$item->id)->with('success', 'Request submitted.');
    }

    public function edit(ItemRequest $itemRequest)
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('item_request.createOrEdit', compact('itemRequest', 'categories'));
    }

    public function show(ItemRequest $itemRequest)
    {
       

        $processingSteps = 
        [
            [
                'title' => 'Pengajuan Diterima',
                'description' => 'Permintaan telah dikirim ke divisi pengadaan.',
                'completed' => true,
                'date' => '2025-05-15 08:00',
                'icon' => 'fa-envelope',
                'attachment' => null
            ],
            [
                'title' => 'Bon Pesanan Diunggah',
                'description' => 'Bon pesanan telah diunggah.',
                'completed' => true,
                'date' => '2025-05-16 10:30',
                'icon' => 'fa-file-alt',
                'attachment' => 'https://example.com/bon.pdf'
            ],
            [
                'title' => 'Menunggu Pembayaran',
                'description' => 'Menunggu proses pembayaran dari bagian keuangan.',
                'completed' => false,
                'date' => null,
                'icon' => 'fa-credit-card',
                'attachment' => null
            ]
        ];

        $potentialVendors = [
            (object)[
                'id' => 101,
                'name' => 'CV. Teknologi Utama',
                'rating' => 4.7
            ],
            (object)[
                'id' => 102,
                'name' => 'PT. Solusi Perkakas',
                'rating' => 4.3
            ]
        ];


        return view('item_request.show', compact('itemRequest', 'processingSteps', 'potentialVendors'));
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

        if ($request->hasFile('picture')) 
        {
            if ($itemRequest->picture) {
                Storage::delete($itemRequest->picture);
            }
            $validated['picture'] = $request->file('picture')->store('item_pictures', 'public');
        }

        // dispatch(new ProcessItemRequestCreated($itemRequest->id));
        $itemRequest->update($validated);

        return redirect()->route('item-request.index')->with('success', 'Request updated.');
    }

    public function destroy(ItemRequest $itemRequest)
    {
        $itemRequest->delete();
        return redirect()->route('item-request.index')->with('success', 'Request deleted.');
    }

    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = ItemRequest::query();
        $query->with('category')->byCompany(Auth::user()->company_id)->orderBy('updated_at', 'desc');
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

        // if(Access::can('downloadPdf','quotes'))
        // {
            $pdf = [
                'name' => 'show',
                'route' => 'item-request.show',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        // }

        // if(Access::can('edit','quotes'))
        // {
            $edit = [
                'name' => 'Edit',
                'route' => 'item-request.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        // }

        // if(Access::can('destroy','quotes'))
        // {
            $destroy = [
                'name' => 'Delete',
                'route' => 'item-request.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        // }


        $response =  datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();

        foreach ($data->data as $index => $item) 
        {
            $item->estimated_price = 'Rp. '.number_format($item->estimated_price, 0,',','.'); // Format angka dengan 2 desimal
            $item->status = $item->status_badge;
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

        if ($request->hasFile('airwillbill_photo')) {
            $airwillbillPath = $request->file('airwillbill_photo')->store('airwillbill', 'public');
        } else {
            $airwillbillPath = null;
        }
        $deliveryPhotoPath = $request->hasFile('delivery_photo') ? $request->file('delivery_photo')->store('delivery_photo', 'public') : null;

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
        }
        else
        {
            $itemRequest->delivery->update([
                'delivery_photo' => $deliveryPhotoPath,
                'delivered_at' => now(),
            ]);

            $delivery = $itemRequest->delivery;

            ItemRequest::where('id', $id)->update(['status' => 'DELIVERED']);
        }

        return response()->json(['success' => true, 'delivery' => $delivery]);
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
}
