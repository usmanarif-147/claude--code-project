<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmailTemplateService
{
    public function getAll(?string $search, ?string $category, int $perPage = 10): LengthAwarePaginator
    {
        $query = EmailTemplate::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderByDesc('updated_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        return $query->paginate($perPage);
    }

    public function getFavorites(): Collection
    {
        return EmailTemplate::query()
            ->where('user_id', auth()->id())
            ->where('is_favorite', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getById(int $id): EmailTemplate
    {
        return EmailTemplate::where('user_id', auth()->id())->findOrFail($id);
    }

    public function create(array $data): EmailTemplate
    {
        $data['user_id'] = auth()->id();

        return EmailTemplate::create($data);
    }

    public function update(int $id, array $data): EmailTemplate
    {
        $template = $this->getById($id);
        $template->update($data);

        return $template;
    }

    public function delete(int $id): void
    {
        $template = $this->getById($id);
        $template->delete();
    }

    public function toggleFavorite(int $id): EmailTemplate
    {
        $template = $this->getById($id);
        $template->update(['is_favorite' => ! $template->is_favorite]);

        return $template;
    }

    public function markUsed(int $id): void
    {
        $template = $this->getById($id);
        $template->update(['last_used_at' => now()]);
    }

    public function getCategories(): array
    {
        return [
            'interview_follow_up' => 'Interview Follow-Up',
            'freelance_proposal' => 'Freelance Proposal',
            'thank_you' => 'Thank You',
            'cold_outreach' => 'Cold Outreach',
            'custom' => 'Custom',
        ];
    }
}
