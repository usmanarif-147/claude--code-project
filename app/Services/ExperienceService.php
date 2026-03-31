<?php

namespace App\Services;

use App\Models\Experience\Experience;
use App\Models\Experience\ExperienceResponsibility;
use Illuminate\Support\Facades\DB;

class ExperienceService
{
    public function create(array $data, array $responsibilities = []): Experience
    {
        return DB::transaction(function () use ($data, $responsibilities) {
            $experience = Experience::create($data);
            $this->syncResponsibilities($experience, $responsibilities);

            return $experience;
        });
    }

    public function update(Experience $experience, array $data, array $responsibilities = []): Experience
    {
        return DB::transaction(function () use ($experience, $data, $responsibilities) {
            $experience->update($data);
            $this->syncResponsibilities($experience, $responsibilities);

            return $experience;
        });
    }

    public function delete(Experience $experience): void
    {
        $experience->delete();
    }

    private function syncResponsibilities(Experience $experience, array $responsibilities): void
    {
        $keepIds = [];

        foreach ($responsibilities as $resp) {
            if (! empty($resp['id'] ?? null)) {
                $responsibility = ExperienceResponsibility::find($resp['id']);
                if ($responsibility) {
                    $responsibility->update([
                        'description' => $resp['description'],
                        'sort_order' => $resp['sort_order'],
                    ]);
                    $keepIds[] = $responsibility->id;
                }
            } else {
                $new = $experience->responsibilities()->create([
                    'description' => $resp['description'],
                    'sort_order' => $resp['sort_order'],
                ]);
                $keepIds[] = $new->id;
            }
        }

        $experience->responsibilities()->whereNotIn('id', $keepIds)->delete();
    }
}
