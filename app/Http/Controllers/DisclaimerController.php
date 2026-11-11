<?php

namespace App\Http\Controllers;

use App\Support\Disclaimer;
use App\Support\Seo\SeoData;

/** /disclaimer always renders the central, admin-approved disclaimer — never a separate copy. */
class DisclaimerController extends Controller
{
    public function __invoke()
    {
        $seo = SeoData::make(
            'Disclaimer',
            'HVAC Services is an independent referral service that helps homeowners connect with local HVAC service providers. Read our full disclaimer.',
            route('disclaimer'),
        )->breadcrumbs([['name' => 'Disclaimer', 'url' => route('disclaimer')]]);

        return view('pages.disclaimer', ['seo' => $seo, 'paragraphs' => Disclaimer::paragraphs()]);
    }
}
