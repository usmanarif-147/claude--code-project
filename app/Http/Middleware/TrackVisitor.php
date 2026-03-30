<?php

namespace App\Http\Middleware;

use App\Models\PortfolioVisitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('get') && ! $request->is('admin/*') && ! $request->ajax() && ! $request->is('livewire/*')) {
            $data = [
                'ip_address' => $request->ip(),
                'country' => null,
                'city' => null,
                'page_visited' => '/'.ltrim($request->path(), '/'),
                'referrer' => $request->headers->get('referer') ? substr($request->headers->get('referer'), 0, 500) : null,
                'user_agent' => $request->userAgent() ? substr($request->userAgent(), 0, 500) : null,
                'device_type' => self::parseDeviceType($request->userAgent()),
                'visited_at' => now(),
            ];

            dispatch(function () use ($data) {
                try {
                    PortfolioVisitor::create($data);
                } catch (\Throwable) {
                    // Silently fail — visitor tracking should never break the app
                }
            })->afterResponse();
        }

        return $response;
    }

    private static function parseDeviceType(?string $ua): string
    {
        if (! $ua) {
            return 'unknown';
        }
        if (preg_match('/Mobile|Android|iPhone/i', $ua)) {
            return 'mobile';
        }
        if (preg_match('/iPad|Tablet/i', $ua)) {
            return 'tablet';
        }

        return 'desktop';
    }
}
