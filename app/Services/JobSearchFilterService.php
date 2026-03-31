<?php

namespace App\Services;

use App\Models\JobSearchFilter;
use App\Models\User;

class JobSearchFilterService
{
    public function getOrCreateForUser(User $user): JobSearchFilter
    {
        return JobSearchFilter::firstOrCreate(
            ['user_id' => $user->id],
            ['salary_currency' => 'USD']
        );
    }

    public function update(JobSearchFilter $filter, array $data): JobSearchFilter
    {
        $filter->update($data);

        return $filter;
    }
}
