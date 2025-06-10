<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use App\Models\SupplierCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ImportProgress;
use App\Jobs\ProductSupplierImportJob;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;


class ProductSupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductSupplier::with('supplierCategories');

        // Jika ada keyword pencarian
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('owner_name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('store_name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('phone_number', 'LIKE', "%{$searchTerm}%")
                ->orWhere('location', 'LIKE', "%{$searchTerm}%")
                ->orWhere('sales_information', 'LIKE', "%{$searchTerm}%")
                ->orWhere('additional_information', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('supplierCategories', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        // Pagination & Order
        $suppliers = $query->byCompany(Auth::user()->company_id)->orderBy('created_at', 'desc')->paginate(10);

        return view('product_supplier.index', compact('suppliers'));
    }

    public function create()
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('product_supplier.createOrEdit', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'location' => 'required|string|max:255',
            // 'supplier_categories' => 'required|array|min:1'
        ]);

        DB::beginTransaction();
        try {
            // Simpan Product Supplier Baru
            $productSupplier = ProductSupplier::create([
                'owner_name' => $request->owner_name,
                'company_id' => Auth::user()->company_id,
                'store_name' => $request->store_name,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'sales_information' => $request->sales_information ?? null,
                'additional_information' => $request->additional_information ?? null
            ]);
            
            // Proses Supplier Categories (Buat baru jika belum ada)
            $categoryIds = [];
            if(isset($request->supplier_categories))
            {
                foreach ($request->supplier_categories as $categoryNameOrId) {
                    if (is_numeric($categoryNameOrId)) {
                        $categoryIds[] = $categoryNameOrId;
                    } else {
                        $newCategory = SupplierCategory::firstOrCreate(['name' => trim($categoryNameOrId),'company_id' => Auth::user()->company_id]);
                        $categoryIds[] = $newCategory->id;
                    }
                }
            }

            if ($request->has('store_photo') && $request->hasFile('store_photo')) {
                $productSupplier->store_photo = Storage::put('public/product-supplier-store-photos', $request->file('store_photo'));
                $productSupplier->save();
            }

            if ($request->has('ktp_photo') && $request->hasFile('ktp_photo')) {
                $productSupplier->ktp_photo = Storage::put('public/product-supplier-ktp-photos', $request->file('ktp_photo'));
                $productSupplier->save();
            }


            // Hubungkan kategori ke Product Supplier
            $productSupplier->supplierCategories()->sync($categoryIds);

            DB::commit();
            return redirect()->route('product-supplier.index')->with(['store' => true]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return back()->with('error', 'Failed to add supplier: ' . $e->getMessage());
        }
    }
    public function edit(ProductSupplier $productSupplier)
    {
        $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('product_supplier.createOrEdit', compact('productSupplier', 'categories'));
    }

    public function update(Request $request, ProductSupplier $productSupplier)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'location' => 'required|string|max:255',
            // 'supplier_categories' => 'required|array|min:1'
        ]);

        DB::beginTransaction();
        try {
            // Update Product Supplier
            $productSupplier->update([
                'owner_name' => $request->owner_name,
                'store_name' => $request->store_name,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
                'sales_information' => $request->sales_information ?? null,
                'additional_information' => $request->additional_information ?? null
            ]);

            // Proses Supplier Categories (Buat baru jika belum ada)
            $categoryIds = [];
            if(isset($request->supplier_categories))
            {
                foreach ($request->supplier_categories as $categoryNameOrId) {
                    if (is_numeric($categoryNameOrId)) {
                        $categoryIds[] = $categoryNameOrId;
                    } else {
                        $newCategory = SupplierCategory::firstOrCreate(['name' => trim($categoryNameOrId),'company_id' => Auth::user()->company_id]);
                        $categoryIds[] = $newCategory->id;
                    }
                }
            }

            // Perbarui kategori yang terhubung ke Product Supplier
            $productSupplier->supplierCategories()->sync($categoryIds);

            if ($request->has('store_photo') && $request->hasFile('store_photo')) {
                if ($productSupplier->store_photo) {
                    Storage::delete($productSupplier->store_photo);
                }

                $productSupplier->store_photo = Storage::put('public/product-supplier-store-photos', $request->file('store_photo'));
                $productSupplier->save();
            }

            if ($request->has('ktp_photo') && $request->hasFile('ktp_photo')) {
                if ($productSupplier->ktp_photo) {
                    Storage::delete($productSupplier->ktp_photo);
                }
                $productSupplier->ktp_photo = Storage::put('public/product-supplier-ktp-photos', $request->file('ktp_photo'));
                $productSupplier->save();
            }

            DB::commit();
            return redirect()->route('product-supplier.index')->with(['update' => true]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return back()->with('error', 'Failed to update supplier: ' . $e->getMessage());
        }
    }

    public function show(ProductSupplier $productSupplier)
    {
        $supplier = $productSupplier;
        // $categories = SupplierCategory::byCompany(Auth::user()->company_id)->get();
        return view('product_supplier.show', compact('supplier'));
    }
    public function destroy(ProductSupplier $productSupplier)
    {
        $productSupplier->delete();
        return redirect()->route('product-supplier.index')->with(['success' => 'Supplier deleted successfully.', 'delete' => true]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        // Generate batch ID
        $batchId = Str::uuid();

        // Parse CSV / Excel
        $data = Excel::toArray([], $request->file('file'))[0];

        // Ambil header
        $headers = array_map('trim', $data[0]);
        unset($data[0]);

        // Format data ke array
        $parsedData = [];
        foreach ($data as $row) {
            $parsedData[] = array_combine($headers, $row);
        }

        // Simpan progress awal
        ImportProgress::create([
            'batch_id' => $batchId,
            'total' => count($parsedData),
            'processed' => 0,
        ]);

        // Dispatch Job
        ProductSupplierImportJob::dispatch($parsedData, $batchId, Auth::user()->company_id);

        return response()->json(['batchId' => $batchId]);
    }
    public function importProgress($batchId)
    {
        $progress = ImportProgress::where('batch_id', $batchId)->firstOrFail();
        if ($progress->processed >= $progress->total) {
            ImportProgress::where('batch_id', $batchId)->delete();
        }

        return response()->json([
            'errors' => json_decode($progress->errors, true) ?? [],
            'processed' => $progress->processed,
            'total' => $progress->total,
            'progress' => ($progress->total > 0) ? ($progress->processed / $progress->total) * 100 : 0,
        ]);
    }
}
