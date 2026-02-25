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

    protected string $question;
    protected mixed  $filters;
    protected int|string $userId;

    // ─── Job Config ───────────────────────────────────────────
    public int   $tries   = 5;
    public int   $timeout = 180;
    public array $backoff = [10, 30, 60, 120];

    public function __construct(string $question, mixed $filters, int|string $userId)
    {
        $this->question = $question;
        $this->filters  = $filters;
        $this->userId   = $userId;
    }

    // ─────────────────────────────────────────────────────────
    // HANDLE
    // ─────────────────────────────────────────────────────────
    public function handle(ServiceOpenAi $openAiService): void
    {
        $cacheKey = $this->buildCacheKey();

        // FIX #5 — Idempotency: skip OpenAI call jika hasil sudah ada di cache
        // Ini mencegah duplicate event dan pemborosan token saat job di-retry
        if (cache()->has($cacheKey)) {
            Log::info('ProcessOpenAiQuery: cache hit, skipping OpenAI call', [
                'userId'   => $this->userId,
                'cacheKey' => $cacheKey,
            ]);
            return;
        }

        $raw = $openAiService->askOpenAi($this->question);

        [$analysis, $trustScore, $executionScore] = $this->parseResponse($raw);

        // FIX #3 — Cache key unik per user + question, bukan hanya per user
        // Mencegah collision jika user kirim 2 pertanyaan berbeda dalam 10 menit
        cache()->put($cacheKey, [
            'analysis'        => $analysis,
            'trust_score'     => $trustScore,
            'execution_score' => $executionScore, // bisa null jika AI tidak generate
        ], now()->addMinutes(10));

        Log::info('ProcessOpenAiQuery: success', [
            'userId'          => $this->userId,
            'trust_score'     => $trustScore,
            'execution_score' => $executionScore,
            'cacheKey'        => $cacheKey,
            // Tidak log 'answer' mentah — terlalu panjang dan ada data sensitif
        ]);

        // FIX #3 — Kirim cacheKey ke frontend via event
        // agar frontend tahu harus fetch response yang mana
        event(new AskBosResponseReady(
            userId:         $this->userId,
            analysis:       $analysis,
            trustScore:     $trustScore,
            executionScore: $executionScore,
            cacheKey:       $cacheKey,      // ← tambahan parameter
        ));
    }

    // ─────────────────────────────────────────────────────────
    // PARSE RESPONSE
    // Multi-layer parser — robust terhadap berbagai format output AI
    //
    // Layer 0 — html_entity_decode : ubah &quot; → " , &amp; → &
    // Layer 1 — json_decode pure   : response sudah JSON murni
    // Layer 2 — strip code fence   : hapus ```json ... ``` lalu decode
    // Layer 3 — extract {...} block: AI nulis teks sebelum/sesudah JSON
    // Layer 4 — regex scrape skor  : AI nulis narasi, scrape angka skor
    // Layer 5 — complete fallback  : pakai teks mentah, skor 0
    // ─────────────────────────────────────────────────────────
    private function parseResponse(string $raw): array
    {
        // Layer 0: decode HTML entities
        // FIX #4 — Hapus stripslashes() karena merusak backslash valid
        //          misal: "C:\\Users" → "C:\Users" (rusak)
        $text = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);

        // Layer 1: pure JSON
        if ($result = $this->decodeAndExtract(json_decode($text, true))) {
            return $result;
        }

        // Layer 2: strip markdown code fence ```json ... ```
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $stripped = preg_replace('/\s*```\s*$/m', '', $stripped ?? '');
        $stripped = trim($stripped ?? '');

        if ($result = $this->decodeAndExtract(json_decode($stripped, true))) {
            return $result;
        }

        // Layer 3: cari blok { ... } di dalam teks
        if (preg_match('/\{[\s\S]+\}/u', $stripped, $match)) {
            if ($result = $this->decodeAndExtract(json_decode($match[0], true))) {
                return $result;
            }
        }

        // Layer 4: regex scrape angka skor dari teks narasi
        $trustScore     = $this->scrapeTrustScore($text);
        $executionScore = $this->scrapeExecutionScore($text);

        if ($trustScore > 0 || $executionScore !== null) {
            Log::warning('ProcessOpenAiQuery: used regex fallback (Layer 4)', [
                'userId'          => $this->userId,
                'trust_score'     => $trustScore,
                'execution_score' => $executionScore,
                'preview'         => substr($text, 0, 200),
            ]);
            return [$text, $trustScore, $executionScore];
        }

        // Layer 5: complete fallback
        Log::error('ProcessOpenAiQuery: all parse layers failed (Layer 5)', [
            'userId'  => $this->userId,
            'preview' => substr($text, 0, 300),
        ]);

        return [$text ?: 'Tidak ada jawaban.', 0, null];
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * Validasi dan ekstrak field dari array hasil json_decode.
     * Return null jika data tidak valid.
     */
    private function decodeAndExtract(mixed $data): ?array
    {
        if (!is_array($data) || !isset($data['Analysis'])) {
            return null;
        }

        return [
            (string) $data['Analysis'],
            (int)    ($data['trust_score'] ?? 0),

            // FIX #2 — Bedakan null (field tidak ada) vs 0 (field ada tapi nol)
            // Null artinya prompt tidak meminta execution_score
            // 0 artinya AI generate tapi nilainya memang 0
            isset($data['execution_score'])
                ? (int) $data['execution_score']
                : null,
        ];
    }

    /**
     * Scrape trust score dari teks narasi.
     * Cocok: "trust_score": 70 | Skor trust: 70/100 | Trust Score — 70
     */
    private function scrapeTrustScore(string $text): int
    {
        $patterns = [
            '/trust_score[":\s]+([0-9]+)/i',
            '/[Ss]kor\s+trust[^0-9]*([0-9]+)/u',
            '/[Tt]rust\s+[Ss]core[^0-9]*([0-9]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return (int) $m[1];
            }
        }

        return 0;
    }

    /**
     * Scrape execution score dari teks narasi.
     * Return null jika tidak ditemukan (bukan 0).
     */
    private function scrapeExecutionScore(string $text): ?int
    {
        $patterns = [
            '/execution_score[":\s]+([0-9]+)/i',
            '/[Ss]kor\s+eksekusi[^0-9]*([0-9]+)/u',
            '/[Ee]xecution\s+[Ss]core[^0-9]*([0-9]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return (int) $m[1];
            }
        }

        return null; // Tidak ditemukan → null, bukan 0
    }

    /**
     * Build cache key unik per user + pertanyaan.
     * FIX #3 — Mencegah collision antar pertanyaan berbeda dari user yang sama.
     */
    private function buildCacheKey(): string
    {
        return sprintf(
            'ai_response_%s_%s',
            $this->userId,
            md5($this->question)
        );
    }

    // ─────────────────────────────────────────────────────────
    // FAILED HANDLER
    // ─────────────────────────────────────────────────────────

    /**
     * Dipanggil Laravel setelah semua retry habis dan job tetap gagal.
     * Kirim event error agar frontend tidak stuck loading.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessOpenAiQuery: job permanently failed', [
            'userId'    => $this->userId,
            'error'     => $exception->getMessage(),
            'question'  => substr($this->question, 0, 200),
        ]);

        // Notify frontend bahwa proses gagal total
        event(new AskBosResponseReady(
            userId:         $this->userId,
            analysis:       'Maaf, sistem sedang tidak dapat memproses permintaan. Silakan coba lagi.',
            trustScore:     0,
            executionScore: null,
            cacheKey:       null,
            isError:        true,   // flag baru untuk frontend handle error state
        ));
    }
}