<?php

namespace App\Support;

/**
 * Editorial checks shown to editors before publishing (Part 22.1 and 22.2).
 * They warn — they do not block — because a person should make the final call.
 */
class TargetingCheck
{
    /** Terms that signal commercial / B2B targeting, which this residential-only site must avoid. */
    public const OFF_TARGET = [
        'commercial', 'industrial', 'b2b', 'business owner', 'businesses', 'office building', 'offices',
        'corporate', 'facility manager', 'facilities', 'warehouse', 'retail space', 'restaurant', 'tenant',
        'rooftop unit', 'rtu', 'property manager', 'multi-location', 'chiller',
    ];

    /** Claims the referral service cannot make. */
    public const RISKY_CLAIMS = [
        'guaranteed', 'we guarantee', 'licensed and insured', 'certified technicians', 'nate-certified',
        'our technicians', 'our technician', 'we will arrive', 'arrive within', 'minutes away', 'always available',
        'best price', 'lowest price', 'flat-rate', 'free estimate', 'we employ', 'our team will repair', 'we install',
        'we repair',
    ];

    /** @return array<int, string> human-readable warnings */
    public static function run(?string ...$texts): array
    {
        $haystack = ' '.mb_strtolower(html_entity_decode(strip_tags(implode(' ', array_filter($texts))))).' ';
        $warnings = [];

        $offTarget = array_values(array_filter(self::OFF_TARGET, fn ($t) => preg_match('/\b'.preg_quote($t, '/').'\b/u', $haystack)));
        if ($offTarget) {
            $warnings[] = 'Possible commercial / B2B targeting: “'.implode('”, “', $offTarget).'”. This site targets homeowners only.';
        }

        // Negated wording ("cannot be guaranteed", "is not guaranteed", "no guarantee") is a correct qualifier, not a claim.
        $claimText = preg_replace('/\b(?:not|never|cannot|can\'t|no|without)\s+(?:be\s+|been\s+|always\s+)?(?:guaranteed|guarantee)\b/u', ' ', $haystack);
        $claims = array_values(array_filter(self::RISKY_CLAIMS, fn ($t) => str_contains($claimText, $t)));
        if ($claims) {
            $warnings[] = 'Possible unsupported claim: “'.implode('”, “', $claims).'”. HVAC Services is a referral service and cannot guarantee work, arrival times, or licensing.';
        }

        if (preg_match('/same[- ]day|24\/7|24 hours|after[- ]hours|emergency/u', $haystack)) {
            $warnings[] = 'Mentions same-day / 24/7 / emergency service. The page will show the availability qualification from the approved disclaimer — make sure the copy does not promise availability.';
        }

        return $warnings;
    }
}
