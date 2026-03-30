<?php

namespace App\Services;

use App\Models\Technology;

class TechnologyService
{
    public function create(array $data): Technology
    {
        return Technology::create($data);
    }

    public function update(Technology $technology, array $data): Technology
    {
        $technology->update($data);

        return $technology;
    }

    public function delete(Technology $technology): void
    {
        $technology->delete();
    }
}
