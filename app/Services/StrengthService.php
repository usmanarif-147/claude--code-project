<?php

namespace App\Services;

use App\Models\Strength;

class StrengthService
{
    public function create(array $data): Strength
    {
        return Strength::create($data);
    }

    public function update(Strength $strength, array $data): Strength
    {
        $strength->update($data);

        return $strength;
    }

    public function delete(Strength $strength): void
    {
        $strength->delete();
    }
}
