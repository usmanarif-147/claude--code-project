<?php

namespace App\Console\Commands;

use App\Services\SocialPublishingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishDueSocialPosts extends Command
{
    /** @var string */
    protected $signature = 'social:publish-due';

    /** @var string */
    protected $description = 'Publish all social posts that are due (scheduled_at <= now)';

    public function handle(SocialPublishingService $publisher): int
    {
        $summary = $publisher->publishDue();

        $message = sprintf(
            'social:publish-due — attempted: %d, posted: %d, failed: %d',
            $summary['attempted'],
            $summary['posted'],
            $summary['failed'],
        );

        $this->info($message);
        Log::info($message, $summary);

        return self::SUCCESS;
    }
}
