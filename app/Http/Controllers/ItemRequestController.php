<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemRequest;
use App\Models\SupplierCategory;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Access;

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
            'description' => 'nulQUlable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty'=> "required|numeric|min:1"
        ]);


        $validated['user_id'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'REQUESTED';

        ItemRequest::create($validated);

        return redirect()->route('item-request.index')->with('success', 'Request submitted.');
    }

    public function edit(ItemRequest $itemRequest)
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('item_request.createOrEdit', compact('itemRequest', 'categories'));
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

        if(Access::can('downloadPdf','quotes'))
        {
            $pdf = [
                'name' => 'Pdf',
                'route' => 'quote.download.pdf',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','quotes'))
        {
            $edit = [
                'name' => 'Edit',
                'route' => 'quote.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','quotes'))
        {
            $destroy = [
                'name' => 'Delete',
                'route' => 'quote.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }


        $response =  datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();

        foreach ($data->data as $index => $item) 
        {
            $item->estimated_price = 'Rp. '.number_format($item->estimated_price, 0,',','.'); // Format angka dengan 2 desimal
            $item->status = '<span class="badge '.$this->matchStatus($item->status, true).'">'.$this->matchStatus($item->status).'</span>';
        }
        
        return response()->json($data);
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
