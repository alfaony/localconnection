<?php

namespace App\Jobs;

use App\Services\ServiceOpenAi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOpenAiQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $question;
    protected $filters;
    protected $userId;

    public function __construct($question, $filters, $userId)
    {
        $this->question = $question;
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle(ServiceOpenAi $openAiService)
    {
        $prompt = $this->question;
        $answer = $openAiService->askOpenAi($prompt);

        $cleanJsonString = stripslashes($answer);

        $data = json_decode($cleanJsonString, true);
        
        // Simpan ke cache atau database jika diperlukan
        cache()->put("ai_response_{$this->userId}", [
            'analysis' => $data['Analysis']  ?? "Not found Analysis",
            'trust_score' => $data['trust_score'] ?? 0,
            'execution_score' => $data['execution_score'] ?? 0,
        ], now()->addMinutes(10));
    }
}