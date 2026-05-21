<?php

namespace App\Http\Controllers;

use App\Models\ProductStore;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\EmailNotifHelper;
use App\Models\SettingCompany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExportSaleJob;

class SaleController extends Controller
{
    public function index()
    {
        $drafts = Sale::where('status', 'draft')
            ->where('user_id', Auth::id())
            ->with('items.productStore')
            ->orderBy('created_at', 'desc')
            ->get();

    $settingCompany = SettingCompany::byCompany(Auth::user()->company_id)->where('menu','store')->get()->pluck('field_value','field_title');

        return view('store_selling.sale.index', compact('drafts','settingCompany'));
    }

    public function searchProduct(Request $request)
    {
        $barcode = $request->get('barcode');
        
        $products = ProductStore::byCompany(Auth::user()->company_id)
            ->where('barcode', $barcode)
            ->with(['category', 'brand', 'inventory', 'primaryMedia'])
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ]);
        }

        // If only one product found, return it directly
        if ($products->count() === 1) {
            return response()->json([
                'success' => true,
                'product' => $products->first(),
                'multiple' => false
            ]);
        }

        // Multiple products found, return all for selection
        return response()->json([
            'success' => true,
            'products' => $products,
            'multiple' => true
        ]);
    }

    public function processPayment(Request $request)
    {
        DB::beginTransaction();

        try {
            $saleData = $request->validate([
                'items' => 'required|array',
                'items.*.product_store_id'  => 'required|exists:product_stores,id',
                'items.*.quantity'          => 'required|integer|min:1',
                'items.*.unit_price'        => 'required|numeric|min:0',
                'items.*.original_price'    => 'nullable|numeric|min:0',
                'items.*.discount_percent'  => 'nullable|numeric|min:0|max:100',
                'items.*.discount_type'     => 'nullable|in:percent,flat',
                'items.*.discount_amount'   => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:cash,debit_credit,qris',
                'customer_email' => 'nullable|email',
                'payment_details' => 'nullable|array',
                'tax_value' => 'required|numeric|min:0|max:100',
                'draft_id' => 'nullable|exists:sales,id'
            ]);

            // Calculate totals
            $totalAmount = 0;
            foreach ($saleData['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $totalAmount * ($saleData['tax_value'] / 100);
            $finalAmount = $totalAmount + $taxAmount;
            $cashDeduction = 0;

            // For cash and QRIS payment, round down to nearest 100 (ratusan)
            if ($saleData['payment_method'] === 'cash' || $saleData['payment_method'] === 'qris') {
                $roundedAmount = floor($finalAmount / 100) * 100;
                $cashDeduction = $finalAmount - $roundedAmount;
                $finalAmount = $roundedAmount;
            }

            // Jika ada draft_id, maka update draft tersebut
            if (!empty($saleData['draft_id'])) {
                $sale = Sale::where('id', $saleData['draft_id'])
                    ->byCompany(Auth::user()->company_id)
                    ->where('status', 'draft')
                    ->firstOrFail();

                // Update sale
                $sale->update([
                    'total_amount' => $totalAmount,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'cash_deduction' => $cashDeduction,
                    'final_amount' => $finalAmount,
                    'payment_method' => $saleData['payment_method'],
                    'payment_details' => $saleData['payment_details'] ?? [],
                    'status' => 'completed',
                    'customer_email' => $saleData['customer_email'] ?? null,
                    'tax_value' => $saleData['tax_value'],
                ]);

                // Hapus item lama dan buat yang baru
                $sale->items()->delete();
            } else {
                // Create new sale
                $sale = Sale::create([
                    'total_amount' => $totalAmount,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'cash_deduction' => $cashDeduction,
                    'final_amount' => $finalAmount,
                    'payment_method' => $saleData['payment_method'],
                    'payment_details' => $saleData['payment_details'] ?? [],
                    'status' => 'completed',
                    'user_id' => auth()->id(),
                    'customer_email' => $saleData['customer_email'] ?? null,
                    'tax_value' => $saleData['tax_value'],
                ]);
            }

            // Validasi stok sebelum transaksi diproses
            $stockErrors = [];
            $inventories  = [];
            foreach ($saleData['items'] as $item) {
                $inventory = Inventory::where('product_store_id', $item['product_store_id'])->first();
                $currentStock = $inventory ? $inventory->quantity : 0;

                if ($currentStock < $item['quantity']) {
                    $product = ProductStore::find($item['product_store_id']);
                    $stockErrors[] = sprintf(
                        '"%s" — stok tersedia: %d, diminta: %d',
                        $product->name ?? $item['product_store_id'],
                        $currentStock,
                        $item['quantity']
                    );
                }
                $inventories[$item['product_store_id']] = $inventory;
            }

            if (!empty($stockErrors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi untuk produk berikut:<br>' . implode('<br>', $stockErrors),
                ], 422);
            }

            // Create sale items
            foreach ($saleData['items'] as $item) {
                SaleItem::create([
                    'sale_id'          => $sale->id,
                    'product_store_id' => $item['product_store_id'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'original_price'   => $item['original_price'] ?? $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_type'    => $item['discount_type'] ?? 'percent',
                    'discount_amount'  => $item['discount_amount'] ?? 0,
                    'subtotal'         => $item['quantity'] * $item['unit_price'],
                ]);

                // Deduct stok
                $inventory = $inventories[$item['product_store_id']];
                if ($inventory) {
                    $inventory->deductStock(
                        $item['quantity'],
                        'Penjualan #' . ($sale->transaction_code ?? $sale->id),
                        'manual'
                    );
                }
            }
            \App\Helpers\XpHelper::award(Auth::user(), $sale, 'Transaksi Kasir');

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

    public function checkStock(Request $request)
    {
        $request->validate([
            'items'                      => 'required|array',
            'items.*.product_store_id'   => 'required|exists:product_stores,id',
            'items.*.quantity'           => 'required|integer|min:1',
        ]);

        $results  = [];
        $allOk    = true;

        foreach ($request->items as $item) {
            $product      = ProductStore::find($item['product_store_id']);
            $inventory    = Inventory::where('product_store_id', $item['product_store_id'])->first();
            $hasInventory = $inventory !== null;
            $stock        = $hasInventory ? $inventory->quantity : null;

            // Hanya gagal jika inventory ADA tapi stok kurang
            // Jika belum ada data inventory → tidak blokir di sini (frontend sudah handle)
            $ok = !$hasInventory || $stock >= $item['quantity'];

            if (!$ok) $allOk = false;

            $results[] = [
                'product_store_id' => $item['product_store_id'],
                'name'             => $product->name ?? '-',
                'requested'        => $item['quantity'],
                'stock'            => $stock,
                'unit'             => $inventory->unit ?? 'pcs',
                'has_inventory'    => $hasInventory,
                'ok'               => $ok,
            ];
        }

        return response()->json([
            'success' => true,
            'all_ok'  => $allOk,
            'results' => $results,
        ]);
    }

    public function saveDraft(Request $request)
    {
        DB::beginTransaction();

        try {
            $saleData = $request->validate([
                'items' => 'required|array',
                'items.*.product_store_id'  => 'required|exists:product_stores,id',
                'items.*.quantity'          => 'required|integer|min:1',
                'items.*.unit_price'        => 'required|numeric|min:0',
                'items.*.original_price'    => 'nullable|numeric|min:0',
                'items.*.discount_percent'  => 'nullable|numeric|min:0|max:100',
                'items.*.discount_type'     => 'nullable|in:percent,flat',
                'items.*.discount_amount'   => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|in:cash,debit_credit,qris',
                'customer_email' => 'nullable|email',
                'payment_details' => 'nullable|array',
                'tax_value' => 'required|numeric|min:0|max:100',
                'draft_id' => 'nullable|exists:sales,id'
            ]);

            // Calculate totals
            $totalAmount = 0;
            foreach ($saleData['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $totalAmount * ($saleData['tax_value'] / 100);
            $finalAmount = $totalAmount + $taxAmount;

            // Jika ada draft_id, maka update draft tersebut
            if (!empty($saleData['draft_id'])) {
                $sale = Sale::where('id', $saleData['draft_id'])
                    ->where('status', 'draft')
                    ->where('user_id', auth()->id())
                    ->firstOrFail();

                $sale->update([
                    'total_amount' => $totalAmount,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'cash_deduction' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => $saleData['payment_method'] ?? 'cash',
                    'payment_details' => $saleData['payment_details'] ?? [],
                    'customer_email' => $saleData['customer_email'] ?? null,
                    'tax_value' => $saleData['tax_value'],
                ]);

                // Hapus item lama
                $sale->items()->delete();
            } else {
                // Create draft sale
                $sale = Sale::create([
                    'total_amount' => $totalAmount,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'cash_deduction' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => $saleData['payment_method'] ?? 'cash',
                    'payment_details' => $saleData['payment_details'] ?? [],
                    'status' => 'draft',
                    'user_id' => auth()->id(),
                    'customer_email' => $saleData['customer_email'] ?? null,
                    'tax_value' => $saleData['tax_value'],
                ]);
            }

            // Create sale items
            foreach ($saleData['items'] as $item) {
                SaleItem::create([
                    'sale_id'          => $sale->id,
                    'product_store_id' => $item['product_store_id'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'original_price'   => $item['original_price'] ?? $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_type'    => $item['discount_type'] ?? 'percent',
                    'discount_amount'  => $item['discount_amount'] ?? 0,
                    'subtotal'         => $item['quantity'] * $item['unit_price'],
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

    public function printReceiptManagement($saleId)
    {
        $sale = Sale::byCompany(Auth::user()->company_id)
            ->with(['items.productStore', 'user'])
            ->findOrFail($saleId);

        $settingCompany = SettingCompany::byCompany(Auth::user()->company_id)
            ->where('menu', 'store')
            ->get()
            ->pluck('field_value', 'field_title');

        return response()->json([
            'success' => true,
            'sale' => $sale,
            'settings' => [
                'header_store_image' => !empty($settingCompany['header_store_image'])
                    ? s3_asset(true, 10, $settingCompany['header_store_image'])
                    : '',
                'store_name'         => $settingCompany['store_name'] ?? config('app.name'),
                'store_address'      => $settingCompany['store_address'] ?? '',
                'footer_store_message' => $settingCompany['footer_store_message'] ?? 'Terima kasih atas kunjungan Anda',
            ],
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
    // Add this method to your SaleController class

    public function sendReceiptByEmail(Request $request)
    {
        try {
            $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'customer_email' => 'required|email'
            ]);

            $sale = Sale::with(['items.productStore', 'user'])
                ->where('id', $request->sale_id)
                ->where('user_id', Auth::id())
                ->where('status', 'completed')
                ->firstOrFail();

            // Get SMTP configuration for the current company
            $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
            $fromEmail = $smtpConfig['username'] ?? '';
            $fromName = Auth::user()->name;

            if (!$smtpConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi email belum diatur. Silakan hubungi administrator.'
                ], 400);
            }

            
            // Prepare email data
            $headerImagePath = $smtpConfig['header_store_image'] ?? null;
            $headerImageUrl = null;

            if ($headerImagePath) {
                if (Str::startsWith($headerImagePath, ['http://', 'https://'])) {
                    $headerImageUrl = $headerImagePath;
                } else {
                    $headerImageUrl = s3_asset(true,10,$headerImagePath);

                    if ($headerImageUrl && !Str::startsWith($headerImageUrl, ['http://', 'https://'])) {
                        $headerImageUrl = URL::to($headerImageUrl);
                    }
                }
            }

            $emailData = [
                'user' => Auth::user(),
                'sale' => $sale,
                'transaction_code' => $sale->transaction_code,
                'total_amount' => $sale->total_amount,
                'tax_amount' => $sale->tax_amount,
                'final_amount' => $sale->final_amount,
                'payment_method' => $sale->payment_method,
                'payment_details' => $sale->payment_details,
                'items' => $sale->items,
                'created_at' => $sale->created_at->format('d/m/Y H:i:s'),
                'kasir_name' => Auth::user()->name,
                'company_name' => Auth::user()->company->name ?? 'Toko',
                'store_name' => $smtpConfig['store_name'],
                'header_store_image' => $headerImagePath,
                'header_store_image_url' => $headerImageUrl,
                'footer_store_message' => $smtpConfig['footer_store_message'],
                'store_address' => $smtpConfig['store_address'],
            ];
            
            // Email configuration array
            $smtpConfigArray = [
                'host' => $smtpConfig["host"],
                'port' => $smtpConfig["port"],
                'username' => $smtpConfig["username"],
                'password' => $smtpConfig["password"],
                'encryption' => $smtpConfig["encryption"] ?? 'tls',
                'name' => Auth::user()->company->name
            ];
            // Send email using EmailHelper
            $emailSent = EmailNotifHelper::sentEmail(
                            $fromEmail,
                            $fromName,  
                            [$request->customer_email], 
                            "Pelanggan", 
                            'Struk Pembelian - ' . $sale->transaction_code, // Subject
                            "email.receipt",
                            $emailData, 
                            $smtpConfigArray, 
                            Auth::user()->company_id, 
                        );

            if ($emailSent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Struk berhasil dikirim ke ' . $request->customer_email
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Periksa konfigurasi SMTP atau coba lagi nanti.'
                ], 500);
            }

        } catch (\Exception $e) {
            // dd($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $filters = $request->only([
                'search', 'start_date', 'end_date',
                'start_time', 'end_time', 'user_id', 'payment_method',
            ]);

            $companyIds = Auth::user()->accessibleCompanies
                ->pluck('id')
                ->push(Auth::user()->company_id)
                ->unique()
                ->values();

            ExportSaleJob::dispatch($filters, Auth::user(), $companyIds);

            return redirect()->back()->with('storeWithMessage', 'Export Sales sedang diproses. Anda akan menerima notifikasi inbox setelah selesai.');

        } catch (\Exception $e) {
            Log::error('Failed to dispatch sale export job', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Gagal memulai export Sales.');
        }
    }
}
