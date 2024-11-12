<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SettingCompany;
use App\Models\XeroToken;

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

        // Decode the payload to get tenantId
        $payloadData = json_decode($payload, true);
        $tenantId = $payloadData['tenantId'] ?? null;

        if (!$tenantId) {
            Log::warning('Xero webhook received with missing tenant ID');
            return response()->json(['error' => 'Tenant ID not found'], 400);
        }

        // Find the company using the tenantId from XeroToken
        $company = XeroToken::where('tenant_id', $tenantId)->first();
        if (!$company) {
            Log::warning('Xero webhook received with unknown tenant ID');
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Get the webhook key for the company
        $webhookKey = SettingCompany::byCompany($company->company_id)
                        ->where('field_title', 'webhook_key')
                        ->value('field_value');

        if (!$webhookKey) {
            Log::warning('Xero webhook received without a configured webhook key');
            return response()->json(['error' => 'Webhook key not found'], 400);
        }

        // Calculate the signature and validate it
        $calculatedSignature = base64_encode(hash_hmac('sha256', $payload, $webhookKey, true));

        // If signatures do not match, return a 401 Unauthorized response
        if ($calculatedSignature !== $xeroSignature) {
            Log::warning('Invalid Xero webhook signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Allow the request to proceed if the signature is valid
        return $next($request);
    }
}
