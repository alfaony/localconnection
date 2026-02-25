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
     * Parse durasi_paket string into unit & value.
     * Supports: "90 hari", "3 bulan", "1 tahun", "2 years", "6 months", "30 days"
     * Returns: ['unit' => 'days'|'months', 'value' => int]
     */
    public static function parseDurasiPaket(?string $durasi): array
    {
        if (!$durasi) {
            return ['unit' => 'months', 'value' => 1]; // safe default
        }

        preg_match('/(\d+)/', $durasi, $matches);
        $value = !empty($matches[1]) ? (int) $matches[1] : 1;

        if (stripos($durasi, 'hari') !== false || stripos($durasi, 'day') !== false) {
            return ['unit' => 'days', 'value' => $value];
        }

        if (stripos($durasi, 'tahun') !== false || stripos($durasi, 'year') !== false) {
            return ['unit' => 'months', 'value' => $value * 12];
        }

        // Default: bulan / month
        return ['unit' => 'months', 'value' => $value];
    }

    /**
     * Calculate tanggal_expired from a start date and package durasi_paket string.
     */
    public static function calculateExpiredDate(Carbon $startDate, ?string $durasi): Carbon
    {
        $parsed = static::parseDurasiPaket($durasi);
        return $startDate->copy()->addDays($parsed['value']);
        // if ($parsed['unit'] === 'days') {
        // }
        // return $startDate->copy()->addMonths($parsed['value']);
    }


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

            // Create subscription (slot deadline = created_at + 24h, calculated dynamically)
            $subscription = CustomerSubscription::create([
                'company_id' => $data['company_id'],
                'software_id' => $data['software_id'],
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

    /**
     * Cancel a pending/stuck subscription and release slot.
     * Cancels all pending payments and frees the reserved master account slot.
     */
    public function cancelSubscription($subscription, string $reason = 'Cancelled by user')
    {
        DB::beginTransaction();

        try {
            // Cancel all pending payments related to this subscription
            SubscriptionPayment::where('subscription_id', $subscription->id)
                ->whereIn('status', ['pending', 'unpaid'])
                ->update([
                    'status'     => 'expired',
                    // 'notes'      => $reason,
                    'expired_at' => now(),
                ]);

            // Release slot on master account
            if ($subscription->masterAccount) {
                $subscription->masterAccount->releaseSlot();
            }

            // Mark subscription as expired (slot released, payment never completed)
            // Note: payment_status enum only allows 'unpaid'|'paid' — leave as unpaid
            $subscription->update([
                'status' => 'expired',
            ]);

            DB::commit();

            Log::info('Subscription cancelled and slot released', [
                'subscription_id' => $subscription->id,
                'order_number'    => $subscription->order_number,
                'reason'          => $reason,
            ]);

            return ['success' => true, 'subscription' => $subscription->fresh()];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Find a stale pending subscription for a user & software.
     * "Stale" = unpaid subscription older than given minutes.
     */
    public function findStalePendingSubscription($userId, $softwareId, int $staleMinutes = 30)
    {
        return CustomerSubscription::where('user_id', $userId)
            ->where('software_id', $softwareId)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->whereIn('status', ['active', 'pending'])
            ->where('created_at', '<=', now()->subMinutes($staleMinutes))
            ->latest()
            ->first();
    }

    /**
     * Find ANY pending subscription (even fresh ones) for a user & software.
     * Used to prevent duplicate active payment sessions.
     */
    public function findPendingSubscription($userId, $softwareId)
    {
        return CustomerSubscription::where('user_id', $userId)
            ->where('software_id', $softwareId)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->whereIn('status', ['active', 'pending'])
            ->with(['payments' => function ($q) {
                $q->whereIn('status', ['pending', 'unpaid'])->latest();
            }])
            ->latest()
            ->first();
    }

    /**
     * Auto-expire unpaid subscriptions whose slot reservation deadline has passed.
     * Releases the slot and marks subscription as expired.
     *
     * @param bool        $dryRun  If true, do not persist changes.
     * @param string|null $id      If provided, force expire this specific subscription (skip deadline).
     * @return array  List of expired subscription summaries.
     */
    public function autoExpireUnpaidSubscriptions(bool $dryRun = false, ?string $id = null): array
    {
        if ($id) {
            // Force-expire specific subscription by ID (for simulation/testing)
            $expired = CustomerSubscription::where('id', $id)
                ->where('payment_status', 'unpaid')
                ->whereIn('status', ['active', 'pending'])
                ->with(['user', 'software', 'masterAccount'])
                ->get();
        } else {
            $expired = CustomerSubscription::slotExpired()
                ->with(['user', 'software', 'masterAccount'])
                ->get();
        }

        $results = [];

        foreach ($expired as $sub) {
            $summary = [
                'subscription_id' => $sub->id,
                'order_number'    => $sub->order_number,
                'user'            => $sub->user->name ?? 'Unknown',
                'software'        => $sub->software->nama ?? 'N/A',
                'reserved_until'  => $sub->slot_deadline?->format('d M Y H:i'),
            ];

            if (!$dryRun) {
                $this->cancelSubscription($sub, $id ? 'Manual force-expire via artisan command' : 'Auto-expired: slot reservation deadline passed');
            }

            $results[] = $summary;
        }

        return $results;
    }
}