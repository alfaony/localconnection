<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

use App\Models\Customer;
class UniqueCustomer implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($status, $email = null)
    {
        $this->email = $email;
        $this->status = $status;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($value, $attribute = null)
    {
        if($this->status == "update")
        {
            if($this->email == $attribute)
            {
                return true;
            }else
            {
                $customer = Customer::byCompany(Auth::user()->company_id)->where('email',$attribute)->first();
                if($customer)
                {
                    return false;
                }else
                {
                    return true;
                }
            }  
        }elseif ($this->status == "store") 
        {
            // dd($value,$attribute);
            $customer = Customer::byCompany(Auth::user()->company_id)->where('email',$attribute)->first();
            if($customer)
            {
                return false;
            }else
            {
                return true;
            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Email sudah terdaftar.';
    }
}
