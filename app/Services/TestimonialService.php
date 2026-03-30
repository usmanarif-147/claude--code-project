<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestimonialService
{
    public function create(array $data, ?UploadedFile $photo = null): Testimonial
    {
        if ($photo) {
            $data['client_photo'] = $photo->store('testimonials', 'public');
        }

        return Testimonial::create($data);
    }

    public function update(Testimonial $testimonial, array $data, ?UploadedFile $photo = null): Testimonial
    {
        if ($photo) {
            if ($testimonial->client_photo) {
                Storage::disk('public')->delete($testimonial->client_photo);
            }
            $data['client_photo'] = $photo->store('testimonials', 'public');
        } elseif (array_key_exists('client_photo', $data) && $data['client_photo'] === null && $testimonial->client_photo) {
            Storage::disk('public')->delete($testimonial->client_photo);
        }

        $testimonial->update($data);

        return $testimonial;
    }

    public function delete(Testimonial $testimonial): void
    {
        if ($testimonial->client_photo) {
            Storage::disk('public')->delete($testimonial->client_photo);
        }

        $testimonial->delete();
    }
}
