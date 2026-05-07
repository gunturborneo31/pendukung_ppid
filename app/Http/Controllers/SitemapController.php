<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('daily'));

        Article::where('status', 'published')
            ->whereNotNull('published_at')
            ->each(function (Article $article) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('preview', $article->preview_token))
                        ->setLastModificationDate($article->updated_at)
                        ->setChangeFrequency('weekly')
                        ->setPriority(0.8)
                );
            });

        return $sitemap->toResponse(request());
    }
}
