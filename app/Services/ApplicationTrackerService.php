<?php

namespace App\Services;

use App\Models\JobSearch\JobApplication;

class ApplicationTrackerService
{
    public function getApplicationsGroupedByStatus(?string $search = null): array
    {
        $query = JobApplication::query()->orderBy('sort_order');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $applications = $query->get();

        $grouped = [];
        foreach (array_keys(JobApplication::ALL_STATUSES) as $status) {
            $grouped[$status] = $applications->where('status', $status)->values();
        }

        return $grouped;
    }

    public function getStatusCounts(): array
    {
        $counts = JobApplication::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $result = [];
        foreach (array_keys(JobApplication::ALL_STATUSES) as $status) {
            $result[$status] = $counts[$status] ?? 0;
        }

        return $result;
    }

    public function createApplication(array $data): JobApplication
    {
        if (($data['status'] ?? 'saved') === JobApplication::STATUS_APPLIED && empty($data['applied_date'])) {
            $data['applied_date'] = now()->toDateString();
        }

        return JobApplication::create($data);
    }

    public function updateApplication(JobApplication $application, array $data): JobApplication
    {
        if (
            $application->status === JobApplication::STATUS_SAVED
            && ($data['status'] ?? $application->status) === JobApplication::STATUS_APPLIED
            && empty($data['applied_date'])
            && ! $application->applied_date
        ) {
            $data['applied_date'] = now()->toDateString();
        }

        $application->update($data);

        return $application;
    }

    public function deleteApplication(JobApplication $application): void
    {
        $application->delete();
    }

    public function updateStatus(JobApplication $application, string $newStatus, int $newSortOrder): void
    {
        $data = [
            'status' => $newStatus,
            'sort_order' => $newSortOrder,
        ];

        if (
            $application->status === JobApplication::STATUS_SAVED
            && $newStatus === JobApplication::STATUS_APPLIED
            && ! $application->applied_date
        ) {
            $data['applied_date'] = now()->toDateString();
        }

        $application->update($data);

        // Reorder remaining cards in the target column
        JobApplication::query()
            ->byStatus($newStatus)
            ->where('id', '!=', $application->id)
            ->where('sort_order', '>=', $newSortOrder)
            ->increment('sort_order');
    }

    public function reorderColumn(string $status, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            JobApplication::query()
                ->where('id', $id)
                ->where('status', $status)
                ->update(['sort_order' => $index]);
        }
    }
}
