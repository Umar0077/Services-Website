<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global middleware (runs before routing):
 *  - strips trailing slashes and ".html" with a 301, so each page has one URL;
 *  - applies admin-managed redirects (including automatic ones created when a slug changes).
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true) || $request->is('admin*', 'livewire*')) {
            return $next($request);
        }

        $path = '/'.ltrim($request->getPathInfo(), '/');
        $query = $request->getQueryString() ? '?'.$request->getQueryString() : '';

        // Legacy static-site URLs: /about.html → /about, /index.html → /, /landing/x.html → /x
        if (str_ends_with(strtolower($path), '.html') || preg_match('#^/landing(/|$)#i', $path)) {
            $legacy = preg_replace(['#^/landing#i', '#\.html$#i'], '', $path);
            $legacy = '/'.trim(strtolower($legacy), '/');

            return redirect(($legacy === '/index' ? '/' : $legacy).$query, 301);
        }

        $normalized = Redirect::normalize($path);
        $map = Redirect::map();
        if (isset($map[$normalized])) {
            [$to, $status, $id] = $map[$normalized];
            Redirect::query()->whereKey($id)->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);

            return redirect($to.(str_contains($to, '?') ? '' : $query), $status);
        }

        if ($path !== '/' && str_ends_with($path, '/')) {
            return redirect(rtrim($path, '/').$query, 301);
        }

        return $next($request);
    }
}
