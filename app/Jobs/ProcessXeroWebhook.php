<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Invoice;
use App\Models\ApiLog;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InvoiceProduct;
use App\Models\SettingCompany;
use Xero;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use Carbon\Carbon;

use App\Services\XeroBos;

class ProcessXeroWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoiceXeroId;
    protected $xeroBos;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoiceXeroId)
    {
        $this->invoiceXeroId = $invoiceXeroId;
        $this->xeroBos = new XeroBos();
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try 
        {
            return $this->updateInvoiceFromXero($this->invoiceXeroId);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th->getMessage());
        }   
    }

    protected function updateInvoiceFromXero($invoiceId)
    {
        $form = array();
        try {
            $user = User::whereHas('role', function ($query) {
                $query->where('name', RoleSchema::ROOT);
            })->first();
            $invoice = Invoice::where('invoice_xero_id', $invoiceId)->first();
            $this->xeroBos->setCompanyPublic($invoice->userCreate->company_id);
            $xeroInvoice = $this->xeroBos->get('Invoices/'.$invoiceId);

            // Add If
            if($xeroInvoice['body']['Invoices'] && $invoice)
            {                
                // Ambil detail invoice dari Xero menggunakan SDK atau API Xero
                $xeroInvoice = $xeroInvoice['body']['Invoices'][0];
                $findContact = $this->findOrCreateContact($invoice, $xeroInvoice['Contact']['Name'], $xeroInvoice['Contact']['EmailAddress']);

                $product = array();
                $discount = 0;
                $serviceFee = 0;
                $otherCharges = 0;
                $totalProductPrice = 0;
                $taxVariable = 0;

                if(isset($xeroInvoice['LineItems']))
                {
                    foreach ($xeroInvoice['LineItems'] as $key => $value) 
                    {
                        if(isset($value['TaxType']))
                        {
                            $taxVariable = $this->findTaxRate($value['TaxType']);
                        }
                        if($value['Description'] == ParamSchema::ADDTIONALCHARGES)
                        {
                            $otherCharges = $value['UnitAmount'];
                        }

                        if($value['Description'] == ParamSchema::SERVICEFEE)
                        {
                            $serviceFee = $value['UnitAmount'];
                        }

                        if($value['Description'] == ParamSchema::DISCOUNT)
                        {
                            $discount = $value['UnitAmount'];
                        }

                        if(isset($value['ItemCode']) && isset($value['UnitAmount']))
                        {
                            $product[] = [
                                'code' => $value['ItemCode'],
                                'product' => $this->findOrCreateProduct($invoice, $value['ItemCode'], $value['UnitAmount'], $value),
                                'description' => $value['Description'],
                                'qty' => $value['Quantity'],
                                'price' => $value['UnitAmount'],
                                'lineAmount' => $value['LineAmount'],
                            ];

                            $totalProductPrice += $value['LineAmount'];
                        }
                        elseif ($value['Description'] != ParamSchema::ADDTIONALCHARGES && $value['Description'] != ParamSchema::SERVICEFEE && $value['Description'] != ParamSchema::DISCOUNT) 
                        {
                            $product[] = [
                                'product' => $this->findOrCreateProduct($invoice, null, $value['UnitAmount'], $value ,true),
                                'description' => $value['Description'],
                                'qty' => $value['Quantity'],
                                'price' => $value['UnitAmount'],
                                'lineAmount' => $value['LineAmount'],
                            ];

                            $totalProductPrice += $value['LineAmount'];
                        }
                    }
                }

                $totalAll = ($totalProductPrice + $otherCharges) + $discount;
                // $serviceFeePercentage = $totalAll > 0 ? round(($serviceFee / $totalAll) * 100) : 0;
                $serviceFeePercentage = $serviceFee ?  ($serviceFee * 100) / $totalAll : 0;
                // update invoice tax
                $invoice->tax = $taxVariable;
                $invoice->save();

                $form = 
                [
                    'Reference' => $xeroInvoice['Reference'],
                    'number_result' => $xeroInvoice['InvoiceNumber'],
                    'start_date' => Carbon::parse($xeroInvoice['DateString'])->format('Y-m-d'),
                    'end_date' => Carbon::parse($xeroInvoice['DueDateString'])->format('Y-m-d'),
                    'contact' => $findContact,
                    'status' => $xeroInvoice['Status'],
                    'product_item' => $xeroInvoice['LineItems'],
                    'discount' => $discount,
                    'other_charges' => $otherCharges,
                    'service_fee' => $serviceFee,
                    'service_fee_percentage' => $serviceFeePercentage,
                    'invoiceDetail'=> $product,
                    'sub_total' => $xeroInvoice['SubTotal'],
                    'total_tax' => $xeroInvoice['TotalTax'],
                    'total' => $xeroInvoice['Total'],
                ];

                if (!empty($xeroInvoice['Payments']) && isset($xeroInvoice['Payments'][0]['Date'])) {
                    $rawDate = $xeroInvoice['Payments'][0]['Date'];
                
                    // Check if the date is in `/Date(...)` format
                    if (preg_match('/\/Date\((\d+)(?:[+-]\d+)?\)\//', $rawDate, $matches)) {
                        $timestamp = $matches[1] / 1000; // Convert milliseconds to seconds
                        $form['payment_date'] = Carbon::createFromTimestamp($timestamp);
                    } else {
                        // If the date is in ISO 8601 format or any other valid format
                        $form['payment_date'] = Carbon::parse($rawDate);
                    }
                } else 
                {
                    // Handle cases where no payments or date is available
                    $form['payment_date'] = null; // or throw an exception if necessary
                }
                
                
                return $this->updateInvoice($invoice, $form);
            }
            else {
                Log::info("Invoice with ID {$invoiceId} not found in local database.");
                ApiLog::create([
                    'user_id' => $user->id,
                    'endpoint' => '/webhook/xero',
                    'method' => 'POST',
                    'request_payload' => json_encode($form),
                    'response_payload' => json_encode(['error' => "Invoice with ID {$invoiceId} not found in local database."]),
                    'status_code' => 500,
                ]);
            }
        } catch (\Exception $e) {
            // dd($e);
            ApiLog::create([
                'user_id' => $user->id,
                'endpoint' => '/webhook/xero',
                'method' => 'POST',
                'request_payload' => json_encode($form),
                'response_payload' => json_encode($e->getMessage()),
                'status_code' => 500,
            ]);
            
            Log::error("Failed to update invoice from Xero: " . $e->getMessage());
        }
    }

    protected function findOrCreateContact($invoice, $name, $email)
    {
        $findCustoner = Customer::byCompany($invoice->userCreate->company_id)->where('name', $name)->where('email', $email)->first();
        if(!$findCustoner)
        {
            $findCustoner = new Customer();
            $findCustoner->name = $name;
            $findCustoner->email = $email;
            $findCustoner->user_created_id = $invoice->userCreate->id;
            $findCustoner->user_updated_id = $invoice->userCreate->id;
            $findCustoner->save();
        }

        return $findCustoner->id;
    }

    protected function findOrCreateProduct($invoice, $code, $amount, $productXero = null, $productDescription = false)
    {
        if(!$productDescription)
        {
            $product = Product::withTrashed()->byCompany($invoice->userCreate->company_id)->where('xero_code', $code)->first();
            if(!$product)
            {
                $maxNumber = Product::byCompany($invoice->userCreate->company_id)->max('number');
                $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
    
                $product = new Product();
                $product->number = $nextNumber;
                $product->name = isset($productXero['Item']['Name']) ? $productXero['Item']['Name'] : $productXero['Description'];
                $product->xero_code = $code;
                $product->price_sell = $amount;
                $product->price_buy = 0;
                $product->method_count = "Item Xero";
                $product->user_created_id = $invoice->userCreate->id;
                $product->user_updated_id = $invoice->userCreate->id;
                $product->save();
            }
        }else
        {
            $product = Product::withTrashed()->byCompany($invoice->userCreate->company_id)->where('name',$productXero['Description'])->first();
            if(!$product)
            {
                $maxNumber = Product::withTrashed()->byCompany($invoice->userCreate->company_id)->max('number');
                $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
    
                $product = new Product();
                $product->number = $nextNumber;
                $product->name = isset($productXero['Item']['Name']) ? $productXero['Item']['Name'] : $productXero['Description'];
                $product->xero_code = $code;
                $product->price_sell = $amount;
                $product->price_buy = 0;
                $product->method_count = "Product From Xero";
                $product->user_created_id = $invoice->userCreate->id;
                $product->user_updated_id = $invoice->userCreate->id;
                $product->save();
            }

        }

        return $product->id;
    }

    protected function updateInvoice($invoice, $form)
    {
        DB::beginTransaction();
        try 
        {
            activity()->disableLogging();
            $invoice->reference = $form['Reference'];
            $invoice->number_result = $form['number_result'];
            $invoice->start_date = $form['start_date'] ?? Carbon::now();
            $invoice->end_date = $form['end_date'] ?? Carbon::now();
            $invoice->customer_id = $form['contact'] ;
            $invoice->service_fee = $form['service_fee_percentage'];
            $invoice->discount = $form['discount'] != 0 ? -($form['discount']) : 0;
            $invoice->charges = $form['other_charges'];
            $invoice->status = $form['status'];
            $invoice->save();

            $invoice->invoiceProducts()->delete();


            if(isset($form['invoiceDetail']))
            {
                $i = 0;
                foreach ($form['invoiceDetail'] as $key => $value) 
                {
                    $invoiceProduct = new InvoiceProduct();
                    $invoiceProduct->sort = $i + 1;
                    $invoiceProduct->description = $value['description'];
                    $invoiceProduct->product_id = $value['product'];
                    $invoiceProduct->qty = $value['qty'];
                    $invoiceProduct->price_sell = $value['price'];
                    $invoiceProduct->sub_total = $value['lineAmount'];
                    
                    $invoice->invoiceProducts()->save($invoiceProduct);
                }
            }

            $this->grandTotal($invoice);
            
            if ($invoice->bast) 
            {
                // Gabungkan file BAST dengan invoice dari Xero
                if ($invoice->bast->file_merge_path) 
                {
                    $mergedFilePath = $this->mergePdf($invoice, $invoice->bast->file_merge_path);
                    
                    // Simpan path hasil gabungan ke database
                    $invoice->file_merge_path = $mergedFilePath;
                    $invoice->save();
                }
            }

            activity()->enableLogging();
            activity()
                ->performedOn($invoice)
                ->withProperties([
                    'start_date' => $invoice->start_date,
                    'end_date' => $invoice->end_date,
                    'total' => $invoice->total,
                    'tax' => $invoice->tax,
                    'service_fee' => $invoice->service_fee,
                    'discount' => $invoice->discount,
                    'charges' => $invoice->charges,
                    'status' => $invoice->status
                ])
                ->log('Invoice updated via Webhook');
            DB::commit();

            return true;
        } catch (\Throwable $th) {
            $user = User::whereHas('role', function ($query) {
                $query->where('name', RoleSchema::ROOT);
            })->first();

            ApiLog::create([
                'user_id' => $user->id,
                'endpoint' => '/webhook/xero',
                'method' => 'POST',
                'request_payload' => json_encode($form),
                'response_payload' => json_encode(['status' => 'success']),
                'status_code' => 200,
            ]);

            DB::rollback();
            Log::error($th);

            return false;
        }
    }

    protected function grandTotal($invoice)
    {
        $service_fee = $invoice->service_fee ?? 0;
        $tax = $invoice->tax ?? 0;

        $total =  $invoice->invoiceProducts() ? $invoice->invoiceProducts()->sum('sub_total') : 0;
        $charges = $invoice->charges ?? 0;
        $discount = $invoice->discount ?? 0;

        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? round(($totalAll * $service_fee) / ParamSchema::PERCENTAGE) : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? round(($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE) : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        $invoice->total = $grandTotal;
        $invoice->save();
    }

    protected function deleteInvoice($invoice, $xeroInvoice)
    {
        try {
            if ($invoice) 
            {
                $invoice->invoiceProducts()->delete(); // Hapus produk terkait
                $invoice->delete(); // Hapus invoice

                Log::info("Invoice with ID {$invoiceId} deleted successfully.");
            } else {
                Log::info("Invoice with ID {$invoiceId} not found in local database for deletion.");
            }

        } catch (\Exception $e) {
            Log::error("Failed to delete invoice from Xero: " . $e->getMessage());
        }
    }

    protected function findTaxRate($TaxType)
    {
        // Coba temukan TaxRate yang ada dengan tarif pajak yang diminta
        $taxRatesResponse = $this->xeroBos->get('taxRates');
        $taxRates = $taxRatesResponse['body']['TaxRates'];

        // Periksa apakah ada TaxRate dengan EffectiveRate yang sesuai
         foreach ($taxRates as $taxRate) 
         {
            if($taxRate['TaxType'] == $TaxType)
            {
                // return $taxRate['TaxComponents'][0]['Rate'];
                foreach ($taxRate['TaxComponents'] as $key => $value) 
                {
                    return $value['Rate'];
                }
            }
        } 

    }

    public function mergePdf($invoice, $bastFilePath)
    {
        // Path relatif untuk file gabungan
        $outputPath = "public/invoices/merged_invoice_{$invoice->number_result}_".date('YmdHis').'_'.Str::random(5).".pdf";
        
        // Hapus file gabungan sebelumnya jika ada
        if ($invoice->file_merge_path && Storage::exists($invoice->file_merge_path)) {
            Storage::delete($invoice->file_merge_path);
        }

        // Unduh PDF dari Xero dan simpan sementara
        $tempInvoicePdfPath = sys_get_temp_dir() . "/invoice_temp_{$invoice->id}.pdf";
        $xeroInvoicePdf = $this->getInvoice($invoice);
        file_put_contents($tempInvoicePdfPath, $xeroInvoicePdf);

        // Gunakan FPDI untuk menggabungkan file
        $pdf = new \setasign\Fpdi\Fpdi();

        // Tambahkan halaman dari file invoice (PDF dari Xero) terlebih dahulu
        $pageCount = $pdf->setSourceFile($tempInvoicePdfPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }

        // Tambahkan halaman dari file BAST
        $pageCount = $pdf->setSourceFile(Storage::path($bastFilePath));
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }

        // Simpan hasil gabungan ke storage public
        if (!Storage::exists(dirname($outputPath))) {
            Storage::makeDirectory(dirname($outputPath)); // Buat direktori jika belum ada
        }
        $mergedAbsolutePath = Storage::path($outputPath);
        $pdf->Output($mergedAbsolutePath, 'F'); // Simpan file gabungan

        // Hapus file sementara setelah selesai
        if (file_exists($tempInvoicePdfPath)) {
            unlink($tempInvoicePdfPath);
        }

        return $outputPath; // Kembalikan path relatif untuk disimpan di database
    }

    protected function getInvoice($invoice)
    {
        $this->xeroBos->setCompanyPublic($invoice->userCreate->company_id);
        $pdfInvoice = $this->xeroBos->get("invoices/{$invoice->invoice_xero_id}", null, true, 'application/pdf');
        // Nama file yang akan digunakan saat mendownload
        $fileName = "invoice_{$invoice->invoice_xero_id}.pdf";

        // Mengirimkan file PDF sebagai respons untuk di-download
        return response($pdfInvoice['body'])
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
