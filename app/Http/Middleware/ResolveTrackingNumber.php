<?php

namespace App\Http\Middleware;

use App\Support\Phone;
use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the phone number to show: an approved tracking number (from ?pn=code or a matching legacy ?phone= value),
 * remembered in a cookie so attribution survives navigation. Also remembers first-touch UTM/gclid data for leads.
 */
class ResolveTrackingNumber
{
    public const ATTRIBUTION_COOKIE = 'hvac_attr';

    public function handle(Request $request, Closure $next): Response
    {
        $code = Phone::codeFromRequest($request);
        $request->attributes->set('tracking_code', $code);

        $response = $next($request);

        $days = (int) app(Settings::class)->get('tracking_cookie_days', 30);
        if ($code && $request->cookie(Phone::COOKIE) !== $code) {
            $response->headers->setCookie(cookie(Phone::COOKIE, $code, $days * 1440, secure: $request->isSecure(), httpOnly: true, sameSite: 'lax'));
        }

        $attribution = array_filter($request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid']));
        if ($attribution && ! $request->cookie(self::ATTRIBUTION_COOKIE)) {
            $attribution = array_map(fn ($v) => mb_substr((string) $v, 0, 190), $attribution);
            $attribution['landing_page'] = mb_substr($request->fullUrl(), 0, 250);
            $response->headers->setCookie(cookie(self::ATTRIBUTION_COOKIE, json_encode($attribution), $days * 1440, secure: $request->isSecure(), httpOnly: true, sameSite: 'lax'));
        }

        return $response;
    }
}
