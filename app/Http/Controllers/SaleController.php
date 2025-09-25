<?php

namespace App\Http\Controllers;

use App\Models\ProductStore;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index()
    {
        return view('store-selling.sale.index');
    }

    public function searchProduct(Request $request)
    {
        $barcode = $request->get('barcode');
        
        $product = ProductStore::where('barcode', $barcode)
            ->orWhere('code', $barcode)
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
                'payment_details' => 'nullable|array'
            ]);

            // Calculate totals
            $totalAmount = 0;
            foreach ($saleData['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $totalAmount * 0.1; // 10% tax
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
                'sale' => $sale,
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
        // Similar to processPayment but with status 'draft'
        // Implementation for draft saving
    }

    public function printReceipt($saleId)
    {
        $sale = Sale::with(['items.productStore', 'user'])->findOrFail($saleId);
        
        // Return receipt data for printing
        return response()->json([
            'success' => true,
            'sale' => $sale
        ]);
    }
}
