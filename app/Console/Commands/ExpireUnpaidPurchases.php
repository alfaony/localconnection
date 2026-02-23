<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidPurchases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internet:expire-purchases {--count : Show count of purchases that would be expired} {--execute : Execute the expiration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unpaid purchases for Active/Reactivated internet customers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Searching for unpaid purchases from Active/Reactivated customers...');
        $this->newLine();

        // Get unpaid purchases from Active/Reactivated customers
        $unpaidPurchases = $this->getUnpaidPurchases();

        if ($unpaidPurchases->isEmpty()) {
            $this->info('✅ No unpaid purchases found.');
            return Command::SUCCESS;
        }

        $count = $unpaidPurchases->count();
        $customerCount = $unpaidPurchases->pluck('internet_customer_id')->unique()->count();

        // Display summary
        $this->info("📊 Found {$count} unpaid purchase(s) from {$customerCount} customer(s)");
        $this->newLine();

        // Show details in table
        $this->displayPurchasesTable($unpaidPurchases);

        // Handle --count option (dry run)
        if ($this->option('count')) {
            $this->newLine();
            $this->warn('ℹ️  This is a dry run. Use --execute to actually expire these purchases.');
            return Command::SUCCESS;
        }

        // Handle --execute option
        if ($this->option('execute')) {
            if (!$this->confirm('Are you sure you want to expire these purchases?', false)) {
                $this->info('❌ Operation cancelled.');
                return Command::SUCCESS;
            }

            $this->newLine();
            $this->info('⚙️  Expiring purchases...');
            
            $expired = $this->expirePurchases($unpaidPurchases);

            $this->newLine();
            $this->info("✅ Successfully expired {$expired} purchase(s)!");
            
            return Command::SUCCESS;
        }

        // No option provided
        $this->newLine();
        $this->warn('ℹ️  Please use --count to preview or --execute to expire these purchases.');
        
        return Command::SUCCESS;
    }

    /**
     * Get unpaid purchases from Active/Reactivated customers
     */
    protected function getUnpaidPurchases()
    {
        return InternetCustomerPurchase::whereHas('customer', function ($query) {
                $query->whereIn('status', [
                    ParamSchema::ACTIVE,
                    ParamSchema::REACTIVATED
                ]);
            })
            ->whereNull('confirmation_finance_at')
            ->whereNull('user_finance_id')
            ->where(function ($query) {
                $query->where('payment_method', '!=', ParamSchema::EXPIRED)
                      ->orWhereNull('payment_method');
            })
            ->with(['customer:id,code,name,status'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Display purchases in table format
     */
    protected function displayPurchasesTable($purchases)
    {
        $tableData = $purchases->map(function ($purchase) {
            return [
                'ID' => $purchase->id,
                'Customer Code' => $purchase->customer->code ?? '-',
                'Customer Name' => $purchase->customer->name ?? '-',
                'Status' => $purchase->customer->status ?? '-',
                'Amount' => 'Rp ' . number_format($purchase->amount_paid, 0, ',', '.'),
                'Period' => \Carbon\Carbon::parse($purchase->created_at)->format('M Y'),
                'Created' => \Carbon\Carbon::parse($purchase->created_at)->format('d M Y'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Customer Code', 'Customer Name', 'Status', 'Amount', 'Period', 'Created'],
            $tableData
        );
    }

    /**
     * Expire the purchases
     */
    protected function expirePurchases($purchases)
    {
        $expiredCount = 0;
        $bar = $this->output->createProgressBar($purchases->count());
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($purchases as $purchase) {
                $purchase->markAsExpired();
                
                Log::info('Purchase expired by command', [
                    'purchase_id' => $purchase->id,
                    'customer_id' => $purchase->internet_customer_id,
                    'customer_code' => $purchase->customer->code ?? '-',
                    'amount' => $purchase->amount_paid,
                ]);

                $expiredCount++;
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            
            $this->newLine();
            $this->error('❌ Error: ' . $e->getMessage());
            
            Log::error('Failed to expire purchases', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 0;
        }

        return $expiredCount;
    }
}
