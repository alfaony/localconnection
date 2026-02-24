<?php

namespace App\Jobs;

use App\Events\AskBosResponseReady;
use App\Services\ServiceOpenAi;
use Illuminate\Bus\Queueable;
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
        $this->filters  = $filters;
        $this->userId   = $userId;
    }

    public function handle(ServiceOpenAi $openAiService)
    {
        $answer = $openAiService->askOpenAi($this->question);

        $cleanJsonString = stripslashes($answer);
        $data = json_decode($cleanJsonString, true);

        $analysis       = $data['Analysis']       ?? 'Not found Analysis';
        if($analysis === "Not found Analysis"){
            Log::error("Not found Analysis", [
                'answer' => $answer,
                'data' => $data
            ]);
        }
        $trustScore     = (int) ($data['trust_score']     ?? 0);
        $executionScore = (int) ($data['execution_score'] ?? 0);
        // Simpan ke cache sebagai fallback jika broadcast gagal
        cache()->put("ai_response_{$this->userId}", [
            'analysis'       => $analysis,
            'trust_score'    => $trustScore,
            'execution_score'=> $executionScore,
        ], now()->addMinutes(10));

        // Broadcast langsung ke user (primary — realtime)
        event(new AskBosResponseReady($this->userId, $analysis, $trustScore, $executionScore));
    }
}