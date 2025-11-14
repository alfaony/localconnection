<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use App\Models\InternetCustomer;

class CustomerCodeInput extends Component
{
    public $customer_code = '';
    public $error_message = '';
    public $is_loading = false;

    protected $rules = [
        'customer_code' => 'required|min:3|max:50',
    ];

    protected $messages = [
        'customer_code.required' => 'Kode pelanggan harus diisi',
        'customer_code.min' => 'Kode pelanggan minimal 3 karakter',
        'customer_code.max' => 'Kode pelanggan maksimal 50 karakter',
    ];

    public function mount()
    {
        // Reset session error if any
        session()->forget('customer_error');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->error_message = '';
    }

    public function checkCustomer()
    {
        $this->validate();
        
        $this->is_loading = true;
        $this->error_message = '';

        try {
            // Normalize code: trim and uppercase
            $code = strtoupper(trim($this->customer_code));
            
            // Find customer by code
            $customer = InternetCustomer::where('code', $code)->first();

            if (!$customer) {
                $this->is_loading = false;
                $this->error_message = 'Kode pelanggan tidak ditemukan. Silakan periksa kembali atau hubungi admin.';
                $this->addError('customer_code', 'Kode tidak valid');
                return;
            }

            // Redirect to customer detail page
            $this->is_loading = false;
            return redirect()->route('internet-customer.customer.show', ['code' => $code]);

        } catch (\Exception $e) {
            $this->is_loading = false;
            $this->error_message = 'Terjadi kesalahan. Silakan coba lagi atau hubungi admin.';
            logger()->error('Customer code check error: ' . $e->getMessage());
        }
    }

    public function clearInput()
    {
        $this->reset(['customer_code', 'error_message']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.internet-customer.customer-code-input')
        ->extends('layouts.app_customer');
    }
}