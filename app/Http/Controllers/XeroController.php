<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\XeroService;

class XeroController extends Controller
{
    protected $xeroService;

    public function __construct(XeroService $xeroService)
    {
        $this->xeroService = $xeroService;
    }

    public function connect()
    {
        return $this->xeroService->connect();
    }

    public function disconnect()
    {
        $this->xeroService->disconnect();
        return true;
    }

    public function callback(Request $request)
    {
        $this->xeroService->handleCallback($request);
        return redirect()->route('xero.invoices')->with('success', 'Connected to Xero.');
    }

    // public function createInvoice(Request $request)
    // {
    //     $invoice = $this->xeroService->createInvoice($request->all());

    //     if (!$invoice) {
    //         return redirect()->back()->with('error', 'Unable to create invoice');
    //     }

    //     return redirect()->route('xero.invoices')->with('success', 'Invoice created successfully.');
    // }

    // public function listInvoices()
    // {
    //     $invoices = $this->xeroService->getInvoices();
    //     return view('xero.invoices', compact('invoices'));
    // }

}
