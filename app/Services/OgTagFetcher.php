<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class OgTagFetcher
{
    private const USER_AGENT = 'UsmanPortfolioBot/1.0 (+https://usmaniqbal.dev)';

    /**
     * Fetch a URL and extract OG meta tags (with sensible fallbacks).
     *
     * Always returns the documented array shape; never throws.
     */
    public function fetch(string $url): array
    {
        $default = [
            'ok' => false,
            'title' => null,
            'description' => null,
            'image' => null,
            'site_name' => null,
            'error' => null,
        ];

        try {
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                return array_merge($default, ['error' => 'Invalid URL']);
            }

            $response = Http::timeout(10)
                ->withUserAgent(self::USER_AGENT)
                ->get($url);

            if (! $response->successful()) {
                return array_merge($default, [
                    'error' => 'HTTP '.$response->status(),
                ]);
            }

            $html = $response->body();

            $title = $this->extractMeta($html, 'og:title')
                ?? $this->extractTitleTag($html);

            $description = $this->extractMeta($html, 'og:description')
                ?? $this->extractMetaName($html, 'description');

            $image = $this->extractMeta($html, 'og:image');
            if ($image) {
                $image = $this->absolutize($image, $url);
            }

            $siteName = $this->extractMeta($html, 'og:site_name')
                ?? parse_url($url, PHP_URL_HOST);

            return [
                'ok' => true,
                'title' => $this->decode($title),
                'description' => $this->decode($description),
                'image' => $image,
                'site_name' => $this->decode($siteName),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return array_merge($default, ['error' => $e->getMessage()]);
        }
    }

    private function extractMeta(string $html, string $property): ?string
    {
        // Matches <meta property="og:title" content="..."> in any attribute order, quotes, case.
        $prop = preg_quote($property, '/');
        $patterns = [
            '/<meta[^>]+property=["\']'.$prop.'["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i',
            '/<meta[^>]+content=["\']([^"\']*)["\'][^>]*property=["\']'.$prop.'["\'][^>]*>/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m) && $m[1] !== '') {
                return $m[1];
            }
        }

        return null;
    }

    private function extractMetaName(string $html, string $name): ?string
    {
        $n = preg_quote($name, '/');
        $patterns = [
            '/<meta[^>]+name=["\']'.$n.'["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i',
            '/<meta[^>]+content=["\']([^"\']*)["\'][^>]*name=["\']'.$n.'["\'][^>]*>/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m) && $m[1] !== '') {
                return $m[1];
            }
        }

        return null;
    }

    private function extractTitleTag(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $val = trim($m[1]);

            return $val !== '' ? $val : null;
        }

        return null;
    }

    private function absolutize(string $image, string $base): string
    {
        if (preg_match('/^https?:\/\//i', $image)) {
            return $image;
        }

        $parts = parse_url($base);
        if (! isset($parts['scheme'], $parts['host'])) {
            return $image;
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (str_starts_with($image, '//')) {
            return $parts['scheme'].':'.$image;
        }

        if (str_starts_with($image, '/')) {
            return $origin.$image;
        }

        return $origin.'/'.ltrim($image, '/');
    }

    private function decode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
