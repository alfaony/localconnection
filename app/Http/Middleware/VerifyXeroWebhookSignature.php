<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SettingCompany;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;

class VerifyXeroWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the payload and Xero signature from the request
        $payload = $request->getContent();
        $xeroSignature = $request->header('X-Xero-Signature');

        // if (!$xeroSignature) {
        //     return $this->respondWithError('Header x-xero-signature not found', $payload, $xeroSignature, 401);
        // }

        // // Decode the payload to get tenantId
        // $payloadData = json_decode($payload, true);
        // $tenantId = $payloadData['events'][0]['tenantId'] ?? null;

        // if (!$tenantId) {
        //     return $this->respondWithError('Tenant ID not found', $payload, $xeroSignature, 401);
        // }

        // // Find the company using the tenantId from XeroToken
        // $company = DB::table('xero_tokens')->where('tenant_id', $tenantId)->first();
        // if (!$company) {
        //     return $this->respondWithError('Company not found', $payload, $xeroSignature, 401);
        // }

        // // Get the webhook key for the company
        // $webhookKey = SettingCompany::byCompany($company->company_id)
        //     ->where('field_title', 'webhook_key')
        //     ->value('field_value');

        // if (!$webhookKey) {
        //     return $this->respondWithError('Webhook key not found', $payload, $xeroSignature, 401);
        // }
        $webhookKey = 'W6kviXZ/CpPjxASPcOYahCcenEc8Vix5R62PFTngMnJgD7j52Ca3cixUByEFcBnmxgLYLD1Xc1R1UC9pTzi58w==';


        // Calculate the signature and validate it
        $calculatedSignature = base64_encode(hash_hmac('sha256', $payload, $webhookKey, true));

        // If signatures do not match, return a 401 Unauthorized response
        if (!hash_equals($calculatedSignature, $xeroSignature)) {
            return $this->respondWithError('Invalid signature', $payload, $xeroSignature, 401);
        }

        WebhookLog::create([
            'source' => 'Xero',
            'signature' => $xeroSignature,
            'headers' => json_encode($request->headers->all()),
            'payload' => $payload,
            'is_valid' => true,
            'status' => 'processed',
        ]);

        // Allow the request to proceed if the signature is valid
        return response()->json(['status' => 'success'], 200);
        return $next($request);
    }

    /**
     * Handle error response and log webhook.
     *
     * @param  string  $message
     * @param  string  $payload
     * @param  string|null  $xeroSignature
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message, $payload, $xeroSignature = null, $statusCode = 400)
    {
        Log::warning($message);

        // Log webhook with error status
        WebhookLog::create([
            'source' => 'Xero',
            'signature' => $xeroSignature,
            'headers' => json_encode(request()->headers->all()),
            'payload' => $payload,
            'is_valid' => false,
            'status' => $message,
        ]);

        return response()->json(['error' => $message], $statusCode);
    }
}
