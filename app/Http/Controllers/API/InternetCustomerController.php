<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ImportSkamJob;
use App\Http\Controllers\API\BaseController;

class InternetCustomerController extends BaseController
{
    /**
     * Import SKAM data from CSV file
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
                'response_url' => 'required|url',
                'callback_token' => 'required|string',
            ], [
                'file.required' => 'File CSV wajib diupload',
                'file.mimes' => 'File harus berformat CSV',
                'file.max' => 'Ukuran file maksimal 10MB',
                'response_url.required' => 'Response URL wajib diisi',
                'response_url.url' => 'Response URL harus berformat URL yang valid',
                'callback_token.required' => 'Callback token wajib diisi',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            // Get company from authenticated user
            $user = auth()->user();
            $companyId = $user->company_id;

            if (!$companyId) {
                return $this->sendError('Company not found for authenticated user', [], 400);
            }

            // Generate unique batch ID
            $batchId = (string) Str::uuid();

            // Read and validate CSV file
            $file = $request->file('file');
            $csvData = $this->readCSV($file->getRealPath());

            if (empty($csvData)) {
                return $this->sendError('File CSV kosong atau tidak valid', [], 400);
            }

            // Validate CSV has header
            if (count($csvData) < 2) {
                return $this->sendError('File CSV harus memiliki minimal 1 baris data selain header', [], 400);
            }

            // Dispatch job to queue
            ImportSkamJob::dispatch(
                $csvData,
                $user->id,
                $companyId,
                $batchId,
                $request->response_url,
                $request->callback_token
            );

            // Return immediate response
            return $this->sendResponse([
                'batch_id' => $batchId,
                'company_id' => $companyId,
                'total_rows' => count($csvData) - 1, // Exclude header
            ], 'Import queued successfully. Results will be sent to your response URL.');

        } catch (\Exception $e) {
            return $this->sendError('Server Error: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Read CSV file and return array of data
     * 
     * @param string $filePath
     * @return array
     */
    private function readCSV($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $header = null;
        $data = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        // Add header back to beginning for job processing
        if ($header) {
            array_unshift($data, $header);
        }

        return $data;
    }
}
