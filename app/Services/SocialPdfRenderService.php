<?php

namespace App\Services;

use App\Models\Social\ScheduledPost;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use RuntimeException;

class SocialPdfRenderService
{
    private const PDF_DISK = 'public';

    private const PDF_DIRECTORY = 'social-posts';

    /**
     * Square paper roughly matching LinkedIn's recommended 1:1 carousel aspect.
     * DomPDF uses points (1pt = 1/72in); 1200pt ≈ 16.67in square — adequate for
     * sharp rendering at LinkedIn's 1200×1200 display size.
     */
    private const PAPER_SIZE = [0, 0, 1200, 1200];

    /**
     * Returns the registered template registry from config.
     *
     * @return array<string, array{label: string, fields: array}>
     */
    public function availableTemplates(): array
    {
        return config('social-templates', []);
    }

    /**
     * Render the SINGLE focused page as a standalone HTML document.
     * Used by the live preview pane in the post form.
     */
    public function renderPageHtml(array $postData, int $pageIndex, string $templateSlug): string
    {
        $templateSlug = $this->resolveTemplateSlug($templateSlug);
        $pages = $this->assemblePages($postData);

        if ($pageIndex < 0 || $pageIndex >= count($pages)) {
            throw new InvalidArgumentException(
                "Page index {$pageIndex} is out of range (0–".(count($pages) - 1).').'
            );
        }

        // Single-page mode: render just the focused page, no page-breaks.
        $singlePage = [$pages[$pageIndex]];

        return View::make("social-posts.templates.{$templateSlug}", [
            'pages' => $singlePage,
        ])->render();
    }

    /**
     * Render the full multi-page PDF for a saved ScheduledPost and cache it
     * under storage/app/public/social-posts/{id}.pdf. Returns the
     * storage-relative path.
     */
    public function renderPdfFromPost(ScheduledPost $post): string
    {
        if (! $post->exists) {
            throw new InvalidArgumentException('Cannot render PDF for an unsaved post.');
        }

        $templateSlug = $this->resolveTemplateSlug($post->template_slug);
        $pages = $this->assemblePages($post);

        $pdf = Pdf::loadView("social-posts.templates.{$templateSlug}", [
            'pages' => $pages,
        ])
            ->setPaper(self::PAPER_SIZE)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $this->ensureStorageDirectoryExists();

        $relativePath = self::PDF_DIRECTORY."/{$post->id}.pdf";
        $written = Storage::disk(self::PDF_DISK)->put($relativePath, $pdf->output());

        if ($written === false) {
            throw new RuntimeException(
                "Failed to write rendered PDF to disk at '{$relativePath}'. ".
                'Check directory permissions on storage/app/public/'.self::PDF_DIRECTORY.'.'
            );
        }

        // Persist cache path on the model so subsequent edits can invalidate it.
        $post->forceFill(['rendered_pdf_path' => $relativePath])->save();

        return $relativePath;
    }

    /**
     * Delete the cached PDF for a post if it exists.
     */
    public function clearCachedPdf(ScheduledPost $post): void
    {
        if (! $post->rendered_pdf_path) {
            return;
        }

        if (Storage::disk(self::PDF_DISK)->exists($post->rendered_pdf_path)) {
            Storage::disk(self::PDF_DISK)->delete($post->rendered_pdf_path);
        }

        $post->forceFill(['rendered_pdf_path' => null])->save();
    }

    /**
     * Build the ordered carousel page array [cover, ...content, final].
     * Accepts either a raw array (from form state) or a ScheduledPost model.
     *
     * Each returned page is shape: ['role' => 'cover|content|final', 'content' => string].
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function assemblePages(array|ScheduledPost $source): array
    {
        $data = $source instanceof ScheduledPost
            ? [
                'cover_page' => $source->cover_page ?? '',
                'content_pages' => is_array($source->content_pages) ? $source->content_pages : [],
                'final_page' => $source->final_page ?? '',
            ]
            : [
                'cover_page' => $source['cover_page'] ?? '',
                'content_pages' => is_array($source['content_pages'] ?? null) ? $source['content_pages'] : [],
                'final_page' => $source['final_page'] ?? '',
            ];

        $pages = [];

        $pages[] = [
            'role' => 'cover',
            'content' => $this->formatBody($data['cover_page']),
        ];

        foreach ($data['content_pages'] as $contentPage) {
            $formatted = $this->formatBody($contentPage);
            if ($formatted === '') {
                continue;
            }

            $pages[] = [
                'role' => 'content',
                'content' => $formatted,
            ];
        }

        $pages[] = [
            'role' => 'final',
            'content' => $this->formatBody($data['final_page']),
        ];

        // If literally every slot was empty, surface a single placeholder so
        // we never produce a zero-page PDF.
        if ($this->allPagesEmpty($pages)) {
            return [[
                'role' => 'cover',
                'content' => '<p style="color:#94a3b8;font-style:italic;">No content yet — start writing in the form to see your carousel here.</p>',
            ]];
        }

        return $pages;
    }

    private function resolveTemplateSlug(?string $slug): string
    {
        $slug = $slug ?: 'default';
        $registry = $this->availableTemplates();

        if (! array_key_exists($slug, $registry)) {
            return 'default';
        }

        return $slug;
    }

    /**
     * Escape user-supplied text and preserve line breaks as paragraphs/<br>.
     */
    private function formatBody(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }

        $escaped = htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Convert blank lines to paragraph breaks, single newlines to <br>.
        $paragraphs = preg_split("/(\r?\n){2,}/", $escaped) ?: [];

        $html = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = nl2br($paragraph, false);
            $html .= '<p>'.$paragraph.'</p>';
        }

        return $html;
    }

    private function allPagesEmpty(array $pages): bool
    {
        foreach ($pages as $page) {
            if (trim($page['content']) !== '') {
                return false;
            }
        }

        return true;
    }

    private function ensureStorageDirectoryExists(): void
    {
        $disk = Storage::disk(self::PDF_DISK);
        if (! $disk->exists(self::PDF_DIRECTORY)) {
            $disk->makeDirectory(self::PDF_DIRECTORY);
        }
    }
}
