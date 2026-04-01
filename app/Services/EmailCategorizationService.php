<?php

namespace App\Services;

use App\Models\Email\Email;
use App\Models\Email\EmailCategory;
use App\Models\Email\EmailCategoryCorrection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailCategorizationService
{
    /**
     * Keyword sets for rule-based categorization.
     */
    protected array $categoryKeywords = [
        'job-response' => [
            'subjects' => ['interview', 'application', 'position', 'hiring', 'job offer', 'recruitment', 'candidacy', 'resume', 'opportunity', 'we reviewed', 'shortlisted'],
            'senders' => ['recruit', 'talent', 'hiring', 'hr@', 'careers@', 'jobs@', 'noreply@linkedin', 'noreply@indeed', 'noreply@glassdoor'],
        ],
        'freelance' => [
            'subjects' => ['project', 'freelance', 'contract', 'proposal', 'quote', 'gig', 'collaboration', 'budget', 'milestone', 'deliverable'],
            'senders' => ['upwork', 'fiverr', 'toptal', 'freelancer', 'guru.com'],
        ],
        'important' => [
            'subjects' => ['urgent', 'important', 'action required', 'immediate', 'critical', 'deadline', 'reminder', 'payment', 'invoice', 'account'],
            'senders' => ['bank', 'paypal', 'stripe', 'gov', 'tax', 'security'],
        ],
        'newsletter' => [
            'subjects' => ['newsletter', 'digest', 'weekly', 'monthly', 'roundup', 'update', 'bulletin', 'subscribe', 'unsubscribe'],
            'senders' => ['newsletter@', 'digest@', 'updates@', 'news@', 'noreply@', 'mailer@', 'substack', 'mailchimp'],
        ],
        'spam-noise' => [
            'subjects' => ['win', 'free', 'prize', 'congratulations', 'limited time', 'act now', 'click here', 'offer', 'discount', 'promotion', 'sale'],
            'senders' => ['promo@', 'marketing@', 'deals@', 'offer@', 'sales@', 'info@'],
        ],
    ];

    public function getCategories(): Collection
    {
        return EmailCategory::orderBy('sort_order')->get();
    }

    public function getCategoryById(int $id): EmailCategory
    {
        return EmailCategory::findOrFail($id);
    }

    public function createCategory(array $data): EmailCategory
    {
        $data['slug'] = Str::slug($data['name']);

        if (! isset($data['sort_order'])) {
            $data['sort_order'] = (EmailCategory::max('sort_order') ?? 0) + 1;
        }

        return EmailCategory::create($data);
    }

    public function updateCategory(int $id, array $data): EmailCategory
    {
        $category = EmailCategory::findOrFail($id);
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);

        return $category;
    }

    public function deleteCategory(int $id): void
    {
        $category = EmailCategory::findOrFail($id);
        $category->delete();
    }

    public function reorderCategories(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                EmailCategory::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });
    }

    public function categorizeEmail(Email $email): ?EmailCategory
    {
        $categories = EmailCategory::orderBy('sort_order')->get();
        $subject = strtolower($email->subject ?? '');
        $snippet = strtolower($email->snippet ?? '');
        $fromEmail = strtolower($email->from_email ?? '');
        $fromName = strtolower($email->from_name ?? '');
        $searchText = $subject.' '.$snippet.' '.$fromEmail.' '.$fromName;

        $bestMatch = null;
        $bestScore = 0;

        foreach ($categories as $category) {
            $keywords = $this->categoryKeywords[$category->slug] ?? null;
            if (! $keywords) {
                continue;
            }

            $score = 0;

            foreach ($keywords['subjects'] as $keyword) {
                if (str_contains($searchText, strtolower($keyword))) {
                    $score++;
                }
            }

            foreach ($keywords['senders'] as $keyword) {
                if (str_contains($fromEmail, strtolower($keyword)) || str_contains($fromName, strtolower($keyword))) {
                    $score += 2;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $category;
            }
        }

        if ($bestMatch && $bestScore >= 1) {
            $email->category_id = $bestMatch->id;
            $email->save();

            return $bestMatch;
        }

        return null;
    }

    public function categorizeUncategorized(): int
    {
        $count = 0;

        Email::whereNull('category_id')
            ->chunkById(100, function ($emails) use (&$count) {
                foreach ($emails as $email) {
                    $result = $this->categorizeEmail($email);
                    if ($result) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function reassignCategory(Email $email, int $newCategoryId): void
    {
        $fromCategoryId = $email->category_id;

        DB::transaction(function () use ($email, $newCategoryId, $fromCategoryId) {
            $email->category_id = $newCategoryId;
            $email->save();

            EmailCategoryCorrection::create([
                'email_id' => $email->id,
                'from_category_id' => $fromCategoryId,
                'to_category_id' => $newCategoryId,
                'corrected_at' => now(),
            ]);
        });
    }

    public function getCategoryStats(): array
    {
        $stats = Email::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();

        return $stats;
    }

    public function getCorrections(int $perPage = 15): LengthAwarePaginator
    {
        return EmailCategoryCorrection::with(['email', 'fromCategory', 'toCategory'])
            ->orderByDesc('corrected_at')
            ->paginate($perPage);
    }

    public function getAccuracyRate(): float
    {
        $totalCategorized = Email::whereNotNull('category_id')->count();

        if ($totalCategorized === 0) {
            return 100.0;
        }

        $uniqueCorrectedEmails = EmailCategoryCorrection::distinct('email_id')->count('email_id');

        return round((($totalCategorized - $uniqueCorrectedEmails) / $totalCategorized) * 100, 1);
    }
}
