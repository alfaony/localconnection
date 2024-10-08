<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\ApiLog;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InvoiceProduct;
use Xero;
use App\Schemas\ParamSchema;

use Carbon\Carbon;

class XeroWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Ambil payload mentah dan Webhook Signing Key dari environment
        $payload = $request->getContent();
        $xeroSigningKey = config('xero.webhookKey');
        $calculatedSignature = base64_encode(hash_hmac('sha256', $payload, $xeroSigningKey, true));
        $xeroSignature = $request->header('X-Xero-Signature');
        $user = User::where('name','root')->first();

        // Verifikasi signature
        if ($calculatedSignature !== $xeroSignature) 
        {
            Log::warning('Invalid Xero webhook signature');

            // Log kesalahan
            ApiLog::create([
                'user_id' => $user->id,
                'endpoint' => '/webhook/xero',
                'method' => 'POST',
                'request_payload' => json_encode($request->all()),
                'response_payload' => json_encode(['error' => 'Invalid signature']),
                'status_code' => 401,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Proses event webhook dari Xero
        $events = json_decode($payload, true)['events'];

        foreach ($events as $event) 
        {
            if ($event['eventType'] === 'UPDATE' && $event['eventCategory'] === 'INVOICE') {
                $invoiceId = $event['resourceId'];
                $this->updateInvoiceFromXero($invoiceId);
            }
        }
        // Log sukses
        ApiLog::create([
            'user_id' => $user->id,
            'endpoint' => '/webhook/xero',
            'method' => 'POST',
            'request_payload' => json_encode($request->all()),
            'response_payload' => json_encode(['status' => 'success']),
            'status_code' => 200,
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    protected function updateInvoiceFromXeroStatus($invoiceId)
    {
        try {
            // Ambil detail invoice dari Xero menggunakan SDK atau API Xero
            $xeroInvoice = Xero::invoices()->find($invoiceId);

            // Cari invoice di database berdasarkan `xero_invoice_id`
            $invoice = Invoice::where('invoice_xero_id', $invoiceId)->first();

            if ($invoice) {
                // Update data invoice di database dengan data dari Xero
                $invoice->status = $xeroInvoice['Status'];
                $invoice->save();
            } else {
                Log::info("Invoice with ID {$invoiceId} not found in local database.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to update invoice from Xero: " . $e->getMessage());
        }
    }

    protected function updateInvoiceFromXero($invoiceId)
    {
        $invoice = Invoice::where('invoice_xero_id', $invoiceId)->firstOrFail();
        try {
            $form = array();
            // Ambil detail invoice dari Xero menggunakan SDK atau API Xero
            $xeroInvoice = Xero::invoices()->find($invoiceId);

            $findContact = $this->findOrCreateContact($invoice, $xeroInvoice['Contact']['Name'], $xeroInvoice['Contact']['EmailAddress']);

            $product = array();
            $discount = 0;
            $serviceFee = 0;
            $otherCharges = 0;
            $totalProductPrice = 0;

            if(isset($xeroInvoice['LineItems']))
            {
                foreach ($xeroInvoice['LineItems'] as $key => $value) 
                {
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
                            'product' => $this->findOrCreateProduct($invoice, $value['ItemCode'], $value['UnitAmount']),
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
            $serviceFeePercentage = $totalAll > 0 ? round(($serviceFee / $totalAll) * 100) : 0;

            $form = 
            [
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

            
            return $this->updateInvoice($invoice, $form);
        } catch (\Exception $e) {
            // ApiLog::create([
            //     'user_id' => $invoice->userCreate->id,
            //     'endpoint' => '/webhook/xero',
            //     'method' => 'POST',
            //     'request_payload' => json_encode($form),
            //     'response_payload' => json_encode($e->getMessage()),
            //     'status_code' => 500,
            // ]);
            
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

    protected function findOrCreateProduct($invoice, $code, $amount)
    {
        $product = Product::byCompany($invoice->userCreate->company_id)->where('xero_code', $code)->first();
        if(!$product)
        {
            $maxNumber = Product::byCompany($invoice->userCreate->company_id)->max('number');
            $nextNumber = $maxNumber ? $maxNumber + 1 : 1;

            $product = new Product();
            $product->number = $nextNumber;
            $product->xero_code = $code;
            $product->price_sale = $amount;
            $product->user_created_id = $invoice->userCreate->id;
            $product->user_updated_id = $invoice->userCreate->id;
            $product->save();
        }

        return $product->id;
    }

    protected function updateInvoice($invoice, $form)
    {
        DB::beginTransaction();
        try 
        {
            activity()->disableLogging();

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

            // ApiLog::create([
            //     'user_id' => $invoice->userCreate->id,
            //     'endpoint' => '/webhook/xero',
            //     'method' => 'POST',
            //     'request_payload' => json_encode($form),
            //     'response_payload' => json_encode(['status' => 'success']),
            //     'status_code' => 200,
            // ]);

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
}
