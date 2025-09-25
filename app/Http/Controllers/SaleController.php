<?php

namespace App\Http\Controllers;

use App\Models\ProductStore;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index()
    {
        // Ambil draft sales untuk ditampilkan di tab
        $drafts = Sale::where('status', 'draft')
            ->where('user_id', Auth::id())
            ->with('items.productStore')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('store_selling.sale.index', compact('drafts'));
    }

    public function searchProduct(Request $request)
    {
        $barcode = $request->get('barcode');
        
        $product = ProductStore::byCompany(Auth::user()->company_id)
            ->where('barcode', $barcode)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    public function processPayment(Request $request)
    {
        DB::beginTransaction();

        try {
            $saleData = $request->validate([
                'items' => 'required|array',
                'items.*.product_store_id' => 'required|exists:product_stores,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,debit_credit,qris',
                'customer_email' => 'nullable|email',
                'payment_details' => 'nullable|array',
                'tax_value' => 'required|numeric|min:0|max:100'
            ]);

            // Calculate totals
            $totalAmount = 0;
            foreach ($saleData['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $totalAmount * ($saleData['tax_value'] / 100);
            $finalAmount = $totalAmount + $taxAmount;

            // Create sale
            $sale = Sale::create([
                'transaction_code' => 'TRX-' . Str::upper(Str::random(8)),
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'final_amount' => $finalAmount,
                'payment_method' => $saleData['payment_method'],
                'payment_details' => $saleData['payment_details'] ?? [],
                'status' => 'completed',
                'user_id' => auth()->id(),
                'customer_email' => $saleData['customer_email'] ?? null,
                'tax_value' => $saleData['tax_value'],
            ]);

            // Create sale items
            foreach ($saleData['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_store_id' => $item['product_store_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                // Update stock jika perlu (optional)
                // $product = ProductStore::find($item['product_store_id']);
                // $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => $sale->load('items.productStore'),
                'message' => 'Transaksi berhasil diproses'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveDraft(Request $request)
    {
        DB::beginTransaction();

        try {
            $saleData = $request->validate([
                'items' => 'required|array',
                'items.*.product_store_id' => 'required|exists:product_stores,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'payment_method' => 'nullable|in:cash,debit_credit,qris',
                'customer_email' => 'nullable|email',
                'payment_details' => 'nullable|array',
                'tax_value' => 'required|numeric|min:0|max:100'
            ]);

            // Calculate totals
            $totalAmount = 0;
            foreach ($saleData['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $totalAmount * ($saleData['tax_value'] / 100);
            $finalAmount = $totalAmount + $taxAmount;

            // Create draft sale
            $sale = Sale::create([
                'transaction_code' => 'DRAFT-' . Str::upper(Str::random(8)),
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'final_amount' => $finalAmount,
                'payment_method' => $saleData['payment_method'] ?? 'cash',
                'payment_details' => $saleData['payment_details'] ?? [],
                'status' => 'draft',
                'user_id' => auth()->id(),
                'customer_email' => $saleData['customer_email'] ?? null,
                'tax_value' => $saleData['tax_value'],
            ]);

            // Create sale items
            foreach ($saleData['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_store_id' => $item['product_store_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'draft' => $sale->load('items.productStore'),
                'message' => 'Draft berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function loadDraft($id)
    {
        $draft = Sale::with('items.productStore')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'draft' => $draft
        ]);
    }

    public function deleteDraft($id)
    {
        $draft = Sale::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'draft')
            ->firstOrFail();

        $draft->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft berhasil dihapus'
        ]);
    }

    public function printReceipt($saleId)
    {
        $sale = Sale::with(['items.productStore', 'user'])
            ->where('id', $saleId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'sale' => $sale
        ]);
    }

    public function getDrafts()
    {
        $drafts = Sale::where('status', 'draft')
            ->where('user_id', Auth::id())
            ->with('items.productStore')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'drafts' => $drafts
        ]);
    }
}