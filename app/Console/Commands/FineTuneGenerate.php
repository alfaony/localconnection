<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\WorkOrder;
use App\Models\Company;
use App\Services\ServiceOpenAi;
use App\Schemas\RoleSchema;

class FineTuneGenerate extends Command
{
    protected $signature = 'finetune:generate';
    protected $description = 'Generate fine-tune dataset and send it to OpenAI Microservice';
    
    public function handle()
    {
        $tables = ['quotes','work_orders'];
        $service = new ServiceOpenAi();


        $responses = [];
        $companies = Company::where("name",'BOS 1')->get();

        foreach($companies as $company)
        {
            foreach ($tables as $table) 
            {
                $user = $company->user()
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                })
                ->first();

                $this->info("Processing table: $table");
    
                // 1. Generate JSONL file with chunking
                $filePath = $this->prepareFineTuneData($table, $company);
    
                // 2. Send JSONL file to Microservice
                $responsesData = $service->fineTuneOpenAi($filePath, $table, $user);
                
                $responses = $responsesData->original ?? null;
                if (isset($responses['fine_tune_id'])) {
                    // 3. Save Fine-Tune ID to Database
                    DB::table('fine_tuned_models')->updateOrInsert(
                        [
                            'company_id' => $company->id,
                            'table_name' => $table // ✅ Kondisi WHERE
                        ],
                        [
                            'filename' => $responses['fine_tune_id']['filename'],
                            'file_path' => $filePath,
                            'model_id' => $responses['fine_tune_id']['id'], // ✅ Data yang akan diperbarui
                            'updated_at' => now()
                        ]
                    );
    
                    Log::info("Fine-tuned model ID saved for $table: " . $responses['fine_tune_id']['id'] ?? NULL);
                }
            }
    
            $this->info('Fine-tuning process completed!');
        }
    }

    /**
     * Generate dataset JSONL for Fine-Tuning using chunking
     */
    private function prepareFineTuneData($table, $company)
    {
        // Pastikan slug tersedia
        if (!isset($company->slug)) {
            Log::error("Company slug not found for fine-tuning data.");
            return false;
        }

        // Simpan di folder public storage
        $filePath = storage_path("app/public/fine_tune/{$table}_{$company->slug}.jsonl");

        // Pastikan folder sudah ada
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Buka file untuk ditulis
        $file = fopen($filePath, 'w');

        // Use chunking to process data in batches
        if($table == "quotes")
        {
             Quote::with(['customer','workOrder','invoice','quoteProduct'])
            ->byCompany($company->id)
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($file, $table) {
                foreach ($rows as $row) {
                    // Convert data to array
                    $rowData = $row->toArray();

                    // Apply width limit to string fields
                    foreach ($rowData as $key => $value) 
                    {
                        if ($value instanceof \Carbon\Carbon) {
                            $rowData[$key] = $value->toDateTimeString(); // Ubah timestamp ke format string
                        } elseif (is_string($value)) {
                            $rowData[$key] = mb_substr($value, 0, 200); // Batasi panjang teks
                        } elseif (is_object($value) || is_resource($value)) {
                            unset($rowData[$key]); // Hapus field yang tidak kompatibel
                        }
                    }

                    // Generate JSONL entry
                    $entry = [
                        "messages" => [
                            ["role" => "system", "content" => "You are an AI trained to analyze $table data."],
                            ["role" => "user", "content" => "Analyze this data:\n" . json_encode($rowData, JSON_UNESCAPED_UNICODE)],
                            ["role" => "assistant", "content" => "Insight: [Generated insight based on the data]"]
                        ]
                    ];

                    // Write entry to file
                    fwrite($file, json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n");
                }
            });
        }

        if($table == "work_orders")
        {
             WorkOrder::with(['workOrderProduct','quote','project','bast'])
            ->byCompany($company->id)
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($file, $table) {
                foreach ($rows as $row) {
                    // Convert data to array
                    $rowData = $row->toArray();

                    // Apply width limit to string fields
                    foreach ($rowData as $key => $value) 
                    {
                        if ($value instanceof \Carbon\Carbon) {
                            $rowData[$key] = $value->toDateTimeString(); // Ubah timestamp ke format string
                        } elseif (is_string($value)) {
                            $rowData[$key] = mb_substr($value, 0, 200); // Batasi panjang teks
                        } elseif (is_object($value) || is_resource($value)) {
                            unset($rowData[$key]); // Hapus field yang tidak kompatibel
                        }
                    }

                    // Generate JSONL entry
                    $entry = [
                        "messages" => [
                            ["role" => "system", "content" => "You are an AI trained to analyze $table data."],
                            ["role" => "user", "content" => "Analyze this data:\n" . json_encode($rowData, JSON_UNESCAPED_UNICODE)],
                            ["role" => "assistant", "content" => "Insight: [Generated insight based on the data]"]
                        ]
                    ];

                    // Write entry to file
                    fwrite($file, json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n");
                }
            });
        }

        fclose($file); // Close file after writing

        Log::info("JSONL file created for $table at $filePath");

        return $filePath;
    }
}