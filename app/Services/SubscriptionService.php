<?php

namespace App\Services;

use App\Models\MasterAccount;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Find available master account for a software
     */
    public function findAvailableMasterAccount($softwareId, $companyId)
    {
        return MasterAccount::where('software_id', $softwareId)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereRaw('used_slots < max_slots')
            ->orderBy('used_slots', 'asc')
            ->first();
    }

    /**
     * Check if slots are available for a software
     */
    public function checkSlotsAvailability($softwareId, $companyId)
    {
        $masterAccount = $this->findAvailableMasterAccount($softwareId, $companyId);
        return $masterAccount !== null;
    }

    /**
     * Create subscription with slot reservation
     */
    public function createSubscription($data)
    {
        DB::beginTransaction();
        
        try {
            // Find available master account
            $masterAccount = $this->findAvailableMasterAccount(
                $data['software_id'], 
                $data['company_id']
            );

            if (!$masterAccount) {
                throw new \Exception('No available slots for this software');
            }

            // Reserve slot
            $masterAccount->reserveSlot();

            // Create subscription
            $subscription = CustomerSubscription::create([
                'company_id' => $data['company_id'],
                'user_id' => $data['user_id'],
                'master_account_id' => $masterAccount->id,
                'package_id' => $data['package_id'],
                'harga_bayar' => $data['harga_bayar'],
                'status' => 'active',
                'payment_status' => 'unpaid',
            ]);

            DB::commit();
            
            Log::info('Subscription created', [
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'master_account_id' => $masterAccount->id,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create subscription', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Activate subscription after payment
     */
    public function activateSubscription($subscription, $packageDuration, $paymentData = [])
    {
        DB::beginTransaction();
        
        try {
            // Calculate dates
            $tanggalMulai = Carbon::now();
            $tanggalExpired = Carbon::now()->addDays($packageDuration);

            // Update subscription
            $subscription->update([
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_expired' => $tanggalExpired,
                'status' => 'active',
                'payment_status' => 'paid',
            ]);

            // Create payment record if payment data provided
            if (!empty($paymentData)) {
                SubscriptionPayment::create(array_merge([
                    'company_id' => $subscription->company_id,
                    'subscription_id' => $subscription->id,
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                ], $paymentData));
            }

            DB::commit();
            
            Log::info('Subscription activated', [
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_expired' => $tanggalExpired,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to activate subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Suspend subscription and release slot
     */
    public function suspendSubscription($subscription, $reason = 'Payment expired')
    {
        DB::beginTransaction();
        
        try {
            // Update subscription status
            $subscription->update([
                'status' => 'suspended',
            ]);

            // Release slot from master account
            if ($subscription->masterAccount) {
                $subscription->masterAccount->releaseSlot();
            }

            DB::commit();
            
            Log::info('Subscription suspended', [
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to suspend subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Expire subscription and release slot
     */
    public function expireSubscription($subscription)
    {
        DB::beginTransaction();
        
        try {
            // Update subscription status
            $subscription->update([
                'status' => 'expired',
            ]);

            // Release slot from master account
            if ($subscription->masterAccount) {
                $subscription->masterAccount->releaseSlot();
            }

            DB::commit();
            
            Log::info('Subscription expired', [
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to expire subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get subscriptions expiring soon
     */
    public function getExpiringSoon($days = 7, $companyId = null)
    {
        $query = CustomerSubscription::expiringSoon($days);
        
        if ($companyId) {
            $query->byCompany($companyId);
        }

        return $query->with(['user', 'masterAccount.software', 'package'])->get();
    }

    /**
     * Get expired subscriptions that need to be processed
     */
    public function getExpiredSubscriptions($companyId = null)
    {
        $query = CustomerSubscription::where('status', 'active')
            ->where('tanggal_expired', '<', Carbon::now());
        
        if ($companyId) {
            $query->byCompany($companyId);
        }

        return $query->with(['user', 'masterAccount'])->get();
    }

    /**
     * Extend subscription (renewal)
     */
    public function extendSubscription($subscription, $additionalDays)
    {
        DB::beginTransaction();
        
        try {
            $newExpiredDate = Carbon::parse($subscription->tanggal_expired)->addDays($additionalDays);

            $subscription->update([
                'tanggal_expired' => $newExpiredDate,
                'status' => 'active',
            ]);

            DB::commit();
            
            Log::info('Subscription extended', [
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'additional_days' => $additionalDays,
                'new_expired_date' => $newExpiredDate,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to extend subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}