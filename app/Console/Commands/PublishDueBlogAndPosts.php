<?php

namespace App\Console\Commands;

use App\Services\BlogAndPostPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishDueBlogAndPosts extends Command
{
    /** @var string */
    protected $signature = 'blog-and-post:publish-due';

    /** @var string */
    protected $description = 'Publish all blog-and-post posts that are due (scheduled_at <= now)';

    public function handle(BlogAndPostPublisher $publisher): int
    {
        $summary = $publisher->publishDue();

        $message = sprintf(
            'blog-and-post:publish-due — attempted: %d, posted: %d, failed: %d',
            $summary['attempted'],
            $summary['posted'],
            $summary['failed'],
        );

        $this->info($message);
        Log::info('blog-and-post:publish-due completed', ['summary' => $summary]);

        return self::SUCCESS;
    }
}
