<?php

namespace App\Observers;

use App\Models\News;
use App\Services\DiscordWebhookService;

class NewsObserver
{
    public function created(News $news): void
    {
        // Se a notícia for criada já com status publicado (publicado <= agora)
        if ($news->published_at && $news->published_at <= now()) {
            app(DiscordWebhookService::class)->sendNewsPublished($news);
        }
    }

    public function updated(News $news): void
    {
        // Se a data de publicação mudou para uma data válida e no passado/presente
        if ($news->isDirty('published_at') && $news->published_at && $news->published_at <= now()) {
            app(DiscordWebhookService::class)->sendNewsPublished($news);
        }
    }
}
