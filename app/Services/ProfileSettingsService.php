<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileSettingsService
{
    public function updateProfile(int $userId, array $data): Profile
    {
        return Profile::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    public function updateProfileImage(Profile $profile, UploadedFile $image): string
    {
        if ($profile->profile_image) {
            Storage::disk('public')->delete($profile->profile_image);
        }

        $path = $image->store('profile-images', 'public');
        $profile->update(['profile_image' => $path]);

        return $path;
    }

    public function removeProfileImage(Profile $profile): void
    {
        if ($profile->profile_image) {
            Storage::disk('public')->delete($profile->profile_image);
            $profile->update(['profile_image' => null]);
        }
    }

    public function updateUserName(User $user, string $name): void
    {
        $user->update(['name' => $name]);
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
    }
}
