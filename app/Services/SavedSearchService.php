<?php

namespace App\Services;

use App\Models\JobSearch\SavedSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SavedSearchService
{
    public function list(int $userId, ?string $search, ?string $status): LengthAwarePaginator
    {
        $query = SavedSearch::query()
            ->forUser($userId)
            ->orderByDesc('created_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate(10);
    }

    public function create(int $userId, array $data): SavedSearch
    {
        $data = $this->normalizeArrayFields($data);
        $data['user_id'] = $userId;

        return SavedSearch::create($data);
    }

    public function update(SavedSearch $savedSearch, array $data): SavedSearch
    {
        $data = $this->normalizeArrayFields($data);
        $savedSearch->update($data);

        return $savedSearch;
    }

    public function delete(SavedSearch $savedSearch): void
    {
        $savedSearch->delete();
    }

    public function toggleActive(SavedSearch $savedSearch): SavedSearch
    {
        $savedSearch->update([
            'is_active' => ! $savedSearch->is_active,
        ]);

        return $savedSearch;
    }

    public function getActiveForUser(int $userId): Collection
    {
        return SavedSearch::query()
            ->forUser($userId)
            ->active()
            ->get();
    }

    private function normalizeArrayFields(array $data): array
    {
        $arrayFields = ['preferred_titles', 'preferred_tech', 'enabled_platforms'];

        foreach ($arrayFields as $field) {
            if (isset($data[$field]) && is_array($data[$field]) && empty($data[$field])) {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
