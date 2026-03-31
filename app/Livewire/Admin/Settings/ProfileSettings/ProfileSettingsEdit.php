<?php

namespace App\Livewire\Admin\Settings\ProfileSettings;

use App\Models\Profile;
use App\Services\ProfileSettingsService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ProfileSettingsEdit extends Component
{
    use WithFileUploads;

    public ?int $profileId = null;

    public string $name = '';

    public string $tagline = '';

    public string $bio = '';

    public $profile_image;

    public ?string $existing_image = null;

    public string $secondary_email = '';

    public string $phone = '';

    public string $location = '';

    public string $linkedin_url = '';

    public string $github_url = '';

    public string $fiverr_url = '';

    public string $youtube_url = '';

    public string $availability_status = '';

    public string $timezone = 'UTC';

    public string $language = 'en';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name ?? '';

        $profile = $user->profile;

        if ($profile) {
            $this->profileId = $profile->id;
            $this->tagline = $profile->tagline ?? '';
            $this->bio = $profile->bio ?? '';
            $this->existing_image = $profile->profile_image;
            $this->secondary_email = $profile->secondary_email ?? '';
            $this->phone = $profile->phone ?? '';
            $this->location = $profile->location ?? '';
            $this->linkedin_url = $profile->linkedin_url ?? '';
            $this->github_url = $profile->github_url ?? '';
            $this->fiverr_url = $profile->fiverr_url ?? '';
            $this->youtube_url = $profile->youtube_url ?? '';
            $this->availability_status = $profile->availability_status ?? '';
            $this->timezone = $profile->timezone ?? 'UTC';
            $this->language = $profile->language ?? 'en';
        }
    }

    public function save(ProfileSettingsService $service): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'secondary_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'fiverr_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
            'availability_status' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();

        $service->updateUserName($user, $validated['name']);

        $profile = $service->updateProfile(
            $user->id,
            collect($validated)->except(['name', 'profile_image'])->toArray()
        );

        if ($this->profile_image) {
            $newPath = $service->updateProfileImage($profile, $this->profile_image);
            $this->existing_image = $newPath;
            $this->profile_image = null;
        }

        $this->profileId = $profile->id;

        session()->flash('success', 'Profile settings saved successfully.');
        $this->redirect(route('admin.settings.profile'), navigate: true);
    }

    public function removeImage(ProfileSettingsService $service): void
    {
        if ($this->profileId) {
            $profile = Profile::find($this->profileId);
            if ($profile) {
                $service->removeProfileImage($profile);
            }
        }

        $this->existing_image = null;
        $this->profile_image = null;
    }

    public function changePassword(ProfileSettingsService $service): void
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');

            return;
        }

        $service->changePassword($user, $this->new_password);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        session()->flash('success', 'Password changed successfully.');
    }

    public function render()
    {
        return view('livewire.admin.settings.profile-settings.edit');
    }
}
