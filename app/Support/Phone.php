<?php

namespace App\Support;

use App\Models\TrackingNumber;
use Illuminate\Http\Request;

/**
 * Phone number display + safe dynamic number insertion.
 *
 * The legacy site let any ?phone= value replace the number on the page. Here a URL can only select a number
 * from the admin-approved tracking list (?pn=<code>), or a legacy ?phone=/?mc_phone= value that exactly matches
 * an approved number. Anything else is ignored and the default number is shown.
 */
class Phone
{
    public const COOKIE = 'hvac_pn';

    public const LEGACY_PARAMS = ['phone', 'tel', 'p', 'mc_phone', 'call'];

    public static function digits(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return strlen($digits) === 11 && str_starts_with($digits, '1') ? substr($digits, 1) : $digits;
    }

    public static function format(?string $value): string
    {
        $d = self::digits($value);

        return strlen($d) === 10 ? sprintf('(%s) %s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6)) : (string) $value;
    }

    public static function tel(?string $value): string
    {
        $d = self::digits($value);

        return 'tel:'.(strlen($d) === 10 ? '+1'.$d : $d);
    }

    /** Returns the approved tracking code requested by this request, if any. */
    public static function codeFromRequest(Request $request): ?string
    {
        $map = TrackingNumber::activeMap();

        $code = strtolower((string) $request->query('pn', ''));
        if ($code !== '' && isset($map[$code])) {
            return $code;
        }

        foreach (self::LEGACY_PARAMS as $param) {
            $digits = self::digits((string) $request->query($param, ''));
            if ($digits !== '' && ($found = array_search($digits, $map, true)) !== false) {
                return (string) $found;
            }
        }

        $cookie = strtolower((string) $request->cookie(self::COOKIE, ''));

        return $cookie !== '' && isset($map[$cookie]) ? $cookie : null;
    }

    /** The number to display for the current request (digits). */
    public static function current(): string
    {
        $request = request();
        $code = $request->attributes->get('tracking_code');
        $map = TrackingNumber::activeMap();

        return $code && isset($map[$code]) ? $map[$code] : self::digits(app(Settings::class)->get('phone'));
    }
}
