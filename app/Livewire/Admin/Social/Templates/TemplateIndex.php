<?php

namespace App\Livewire\Admin\Social\Templates;

use App\Services\SocialPdfRenderService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class TemplateIndex extends Component
{
    public function render(SocialPdfRenderService $renderer)
    {
        $registry = $renderer->availableTemplates();
        $placeholder = $this->placeholderPostData();

        $templates = [];
        foreach ($registry as $slug => $meta) {
            try {
                $previewHtml = $renderer->renderPageHtml($placeholder, 0, $slug);
            } catch (\Throwable $e) {
                $previewHtml = $this->fallbackPreviewHtml($e->getMessage());
            }

            $templates[] = [
                'slug' => $slug,
                'label' => $meta['label'] ?? ucfirst($slug),
                'previewHtml' => $previewHtml,
            ];
        }

        return view('livewire.admin.social.templates.index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Placeholder data used to render each template's gallery thumbnail.
     *
     * @return array<string, mixed>
     */
    private function placeholderPostData(): array
    {
        return [
            'title' => 'Sample Carousel Post',
            'cover_page' => "Laravel Request Lifecycle\n\nA simple overview for backend developers.",
            'content_pages' => [
                "1. Request enters via public/index.php\n2. Composer autoloads classes\n3. The HTTP Kernel boots middleware",
                "4. Route matching\n5. Controller execution\n6. Response generation",
            ],
            'final_page' => "Thanks for reading!\n\nFollow for more Laravel tips.",
        ];
    }

    private function fallbackPreviewHtml(string $message): string
    {
        $safe = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<html><body style="font-family:sans-serif;color:#888;padding:20px;">'
            ."Preview unavailable: {$safe}"
            .'</body></html>';
    }
}
