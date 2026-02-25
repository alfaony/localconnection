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
        $answer = $openAiService->askOpenAi($this->question);

        [$analysis, $trustScore, $executionScore] = $this->parseResponse($answer);

        cache()->put("ai_response_{$this->userId}", [
            'analysis'        => $analysis,
            'trust_score'     => $trustScore,
            'execution_score' => $executionScore,
        ], now()->addMinutes(10));

        \Log::info("ProcessOpenAiQuery: success", [
            'answer'   => $answer,
            'analysis' => $analysis,
            'trust_score' => $trustScore,
            'execution_score' => $executionScore,
            'question' => $this->question,
        ]);

        event(new AskBosResponseReady($this->userId, $analysis, $trustScore, $executionScore));
    }

    /**
     * Robust multi-layer parser untuk response AI.
     *
     * Layer 0  — html_entity_decode  : ubah &quot;→" &amp;→& &#039;→'
     * Layer 1  — json_decode          : kalau response sudah JSON murni
     * Layer 2  — strip code fence     : hapus ```json ... ``` lalu decode
     * Layer 3  — extract {...} block  : model nulis teks sebelum/sesudah JSON
     * Layer 4  — regex scrape skor   : model nulis narasi, cari angka skor
     * Layer 5  — complete fallback   : pakai teks mentah, skor 0
     */
    private function parseResponse(string $raw): array
    {
        // Layer 0: decode HTML entities (api mengembalikan &quot; dll)
        $text = html_entity_decode(stripslashes($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
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

        // Layer 2: strip markdown code fence ```json ... ```
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $stripped = preg_replace('/\s*```\s*$/', '', $stripped);
        $stripped = trim($stripped);
        $data = json_decode($stripped, true);
        if (is_array($data) && isset($data['Analysis'])) {
            return [
                $data['Analysis'],
                (int) ($data['trust_score']     ?? 0),
                (int) ($data['execution_score'] ?? 0),
            ];
        }

        // Layer 3: cari blok { ... } di dalam teks
        if (preg_match('/\{[\s\S]+\}/u', $stripped, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data) && isset($data['Analysis'])) {
                return [
                    $data['Analysis'],
                    (int) ($data['trust_score']     ?? 0),
                    (int) ($data['execution_score'] ?? 0),
                ];
            }
        }

        // Layer 4: regex scrape angka skor dari teks narasi
        $trustScore     = 0;
        $executionScore = 0;

        // Cocok: "trust_score": 70 atau Skor trust: 70/100
        if (preg_match('/trust_score[":\s]+([0-9]+)/i', $text, $m)) {
            $trustScore = (int) $m[1];
        } elseif (preg_match('/[Ss]kor\s+trust[^0-9]*([0-9]+)/u', $text, $m)) {
            $trustScore = (int) $m[1];
        }

        // Cocok: "execution_score": 74 atau Skor eksekusi: 74/100
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

        // Layer 5: complete fallback
        Log::error('ProcessOpenAiQuery: all parse layers failed', ['preview' => substr($text, 0, 300)]);
        return [$text ?: 'Tidak ada jawaban.', 0, 0];
    }
}