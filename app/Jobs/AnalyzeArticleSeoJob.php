<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyzeArticleSeoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $articleId)
    {
    }

    /**
     * Panggil API AI SEO eksternal secara asinkron agar tidak memblokir
     * request penyimpanan artikel oleh kontributor.
     */
    public function handle(): void
    {
        $article = Article::find($this->articleId);

        if (!$article) {
            return;
        }

        try {
            $seoApiUrl = 'https://public-seo-ai.example.com/analyze'; // Replace with actual public SEO AI endpoint
            $response = Http::timeout(15)->post($seoApiUrl, [
                'title' => $article->title,
                'body' => is_array($article->body_web) ? implode(' ', $article->body_web) : $article->body_web,
            ]);

            if ($response->successful()) {
                $analysis = $response->json();
                $article->seo()->updateOrCreate([], [
                    'seo_title' => $analysis['seo_title'] ?? $article->title,
                    'seo_description' => $analysis['seo_description'] ?? null,
                    'seo_keywords' => $analysis['seo_keywords'] ?? null,
                    'og_title' => $analysis['og_title'] ?? null,
                    'og_description' => $analysis['og_description'] ?? null,
                    'og_image' => $analysis['og_image'] ?? null,
                    'canonical_url' => $analysis['canonical_url'] ?? null,
                    'robots_index' => $analysis['robots_index'] ?? true,
                    'robots_follow' => $analysis['robots_follow'] ?? true,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Gagal menganalisis SEO artikel #' . $this->articleId . ': ' . $e->getMessage());
        }
    }
}
