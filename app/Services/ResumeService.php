<?php

namespace App\Services;

use App\Models\Experience\Experience;
use App\Models\Profile;
use App\Models\Project\Project;
use App\Models\ResumeDownload;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResumeService
{
    public function getResumeData(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        return [
            'user' => $user,
            'profile' => $profile,
            'skills' => Skill::query()->active()->ordered()->get(),
            'technologies' => Technology::groupedByCategory(),
            'workExperience' => Experience::query()->active()->ordered()->work()->with('responsibilities')->get(),
            'education' => Experience::query()->active()->ordered()->education()->get(),
            'projects' => Project::query()->active()->ordered()->get(),
        ];
    }

    public function getAvailableTemplates(): array
    {
        return [
            'modern' => 'Modern — Clean and minimal design',
            'classic' => 'Classic — Traditional resume format',
            'compact' => 'Compact — Dense single-page layout',
        ];
    }

    public function generateHtml(string $template = 'modern', ?User $user = null): string
    {
        $this->validateTemplate($template);
        $data = $this->getResumeData($user);

        return view("resume.templates.{$template}", $data)->render();
    }

    public function generatePdf(string $template = 'modern', ?User $user = null): \Barryvdh\DomPDF\PDF
    {
        $this->validateTemplate($template);
        $data = $this->getResumeData($user);

        return Pdf::loadView("resume.templates.{$template}", $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);
    }

    private function validateTemplate(string $template): void
    {
        if (! array_key_exists($template, $this->getAvailableTemplates())) {
            throw new \InvalidArgumentException("Invalid resume template: {$template}");
        }
    }

    public function download(string $template, ?Request $request = null, ?User $user = null): Response
    {
        $pdf = $this->generatePdf($template, $user);

        if ($request) {
            ResumeDownload::create([
                'ip_address' => $request->ip(),
                'referrer' => $request->headers->get('referer') ? substr($request->headers->get('referer'), 0, 500) : null,
                'template_used' => $template,
                'downloaded_at' => now(),
            ]);
        }

        $user = $user ?? Auth::user();
        $filename = str_replace(' ', '_', $user->name).'_Resume.pdf';

        return $pdf->download($filename);
    }
}
