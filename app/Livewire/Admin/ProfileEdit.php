<?php

namespace App\Livewire\Admin;

use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ProfileEdit extends Component
{
    use WithFileUploads;

    public ?int $profileId = null;

    public string $tagline = '';

    public string $bio = '';

    public $profile_image;

    public ?string $existing_image = null;

    public string $secondary_email = '';

    public string $phone = '';

    public string $location = '';

    public string $linkedin_url = '';

    public string $github_url = '';

    public string $availability_status = '';

    public function mount(): void
    {
        $profile = auth()->user()->profile;

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
            $this->availability_status = $profile->availability_status ?? '';
        }
    }

    public function removeImage(): void
    {
        if ($this->existing_image) {
            Storage::disk('public')->delete($this->existing_image);
            $profile = Profile::find($this->profileId);
            if ($profile) {
                $profile->update(['profile_image' => null]);
            }
            $this->existing_image = null;
        }
        $this->profile_image = null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'tagline' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'secondary_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'availability_status' => 'nullable|string|max:100',
        ]);

        $data = collect($validated)->except('profile_image')->toArray();

        if ($this->profile_image) {
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            $data['profile_image'] = $this->profile_image->store('profile-images', 'public');
            $this->existing_image = $data['profile_image'];
            $this->profile_image = null;
        }

        $profile = Profile::updateOrCreate(
            ['user_id' => auth()->id()],
            $data
        );

        $this->profileId = $profile->id;

        session()->flash('success', 'Profile updated successfully.');
        $this->redirect(route('admin.profile.edit'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.profile-edit');
    }
}
