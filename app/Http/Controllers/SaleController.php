<?php

namespace App\Http\Controllers;

use App\Models\ProductStore;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\EmailNotifHelper;
use App\Models\SettingCompany; // Assuming you have this model for SMTP configuration
use Illuminate\Support\Facades\Storage;

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
                'tax_value' => 'required|numeric|min:0|max:100',
                'draft_id' => 'nullable|exists:sales,id' // Tambahkan validasi untuk draft_id
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
                    ->byCompany(Auth::user()->company_id)
                    ->where('status', 'draft')
                    ->firstOrFail();

                // Update sale
                $sale->update([
                    'total_amount' => $totalAmount,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
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
                    'final_amount' => $finalAmount,
                    'payment_method' => $saleData['payment_method'],
                    'payment_details' => $saleData['payment_details'] ?? [],
                    'status' => 'completed',
                    'user_id' => auth()->id(),
                    'customer_email' => $saleData['customer_email'] ?? null,
                    'tax_value' => $saleData['tax_value'],
                ]);
            }

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
                'tax_value' => 'required|numeric|min:0|max:100',
                'draft_id' => 'nullable|exists:sales,id' // Tambahkan validasi untuk draft_id
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
                $headerImageUrl = Str::startsWith($headerImagePath, ['http://', 'https://'])
                    ? $headerImagePath
                    : Storage::url($headerImagePath);
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
}
