<?php

namespace App\Jobs;

use App\Events\AskBosResponseReady;
use App\Services\ServiceOpenAi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOpenAiQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $question;
    protected $filters;
    protected $userId;


    public $tries   = 5;          // maksimal 5x percobaan
    public $timeout = 180;        // timeout job 3 menit
    public $backoff = [10, 30, 60, 120]; // jeda antar retry (detik)

    public function __construct($question, $filters, $userId)
    {
        $this->question = $question;
        $this->filters  = $filters;
        $this->userId   = $userId;
    }

    public function handle(ServiceOpenAi $openAiService)
    {
        // System prompt: paksa model output JSON terstruktur
        $systemPrompt = <<<'SYSPROMPT'
            You are an expert business decision analyst. You MUST respond ONLY with a valid JSON object — no markdown, no code fences, no extra text before or after.
            The JSON must have exactly these three keys:
            {
            "Analysis": "<full analysis text here>",
            "trust_score": <integer 0-100>,
            "execution_score": <integer 0-100>
            }
            Do not include any other keys. Do not wrap in ```json or any other block.
        SYSPROMPT;

        $answer = $openAiService->askOpenAi($this->question, $systemPrompt);

        [$analysis, $trustScore, $executionScore] = $this->parseResponse($answer);

        // Simpan ke cache sebagai fallback jika broadcast gagal
        cache()->put("ai_response_{$this->userId}", [
            'analysis'        => $analysis,
            'trust_score'     => $trustScore,
            'execution_score' => $executionScore,
        ], now()->addMinutes(10));

        // Broadcast langsung ke user (primary — realtime)
        event(new AskBosResponseReady($this->userId, $analysis, $trustScore, $executionScore));
    }

    /**
     * Multi-layer response parser:
     * Layer 1 — clean JSON decode
     * Layer 2 — extract JSON block dari dalam teks (model menambah preamble/code fence)
     * Layer 3 — regex scrape trust_score & execution_score dari teks narasi
     * Layer 4 — fallback total: teks mentah sebagai analysis, skor 0
     */
    private function parseResponse(string $raw): array
    {
        $text = trim(stripslashes($raw));

        // Hapus code fence jika ada: ```json ... ```
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        // Layer 1: pure JSON
        $data = json_decode($text, true);
        if (is_array($data) && isset($data['Analysis'])) {
            return [
                $data['Analysis'],
                (int) ($data['trust_score']     ?? 0),
                (int) ($data['execution_score'] ?? 0),
            ];
        }

        // Layer 2: extract first {...} JSON block dari dalam teks
        if (preg_match('/\{[\s\S]+\}/u', $text, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data) && isset($data['Analysis'])) {
                return [
                    $data['Analysis'],
                    (int) ($data['trust_score']     ?? 0),
                    (int) ($data['execution_score'] ?? 0),
                ];
            }
        }

        // Layer 3: regex scrape skor dari teks narasi
        $trustScore     = 0;
        $executionScore = 0;

        if (preg_match('/trust_score[":\s]+([0-9]+)/i', $text, $m)) {
            $trustScore = (int) $m[1];
        } elseif (preg_match('/[Ss]kor\s+trust[^0-9]*([0-9]+)/u', $text, $m)) {
            $trustScore = (int) $m[1];
        }

        if (preg_match('/execution_score[":\s]+([0-9]+)/i', $text, $m)) {
            $executionScore = (int) $m[1];
        } elseif (preg_match('/[Ss]kor\s+eksekusi[^0-9]*([0-9]+)/u', $text, $m)) {
            $executionScore = (int) $m[1];
        }

        if ($trustScore > 0 || $executionScore > 0) {
            Log::warning('ProcessOpenAiQuery: used regex fallback', [
                'trust_score'     => $trustScore,
                'execution_score' => $executionScore,
            ]);
            return [$text, $trustScore, $executionScore];
        }

        // Layer 4: complete fallback
        Log::error('ProcessOpenAiQuery: all parse layers failed', ['preview' => substr($text, 0, 300)]);
        return [$text ?: 'Tidak ada jawaban.', 0, 0];
    }
}