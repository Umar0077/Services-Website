<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveTrackingNumber;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Models\State;
use App\Notifications\NewLeadNotification;
use App\Support\Seo\SeoData;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function show(Request $request)
    {
        $seo = SeoData::make(
            'Contact HVAC Services',
            'Call or send a request to get connected with an independent heating and air conditioning provider in your area.',
            route('contact'),
        )->breadcrumbs([['name' => 'Contact', 'url' => route('contact')]]);

        $selectedService = $request->query('service');

        return view('pages.contact', compact('seo', 'selectedService'));
    }

    public function store(StoreLeadRequest $request, Settings $site)
    {
        $data = $request->validated();
        $attribution = json_decode((string) $request->cookie(ResolveTrackingNumber::ATTRIBUTION_COOKIE), true) ?: [];

        $lead = Lead::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'zip' => $data['zip'],
            'service_id' => $data['service_id'] ?? null,
            'service_label' => isset($data['service_id']) ? Service::find($data['service_id'])?->title : null,
            'state_id' => $data['state_id'] ?? null,
            'city_name' => $data['city_name'] ?: null,
            'property_type' => 'residential',
            'message' => $data['message'] ?? null,
            'consent' => true,
            'landing_page' => mb_substr($attribution['landing_page'] ?? (string) $request->headers->get('referer'), 0, 250),
            'referrer' => mb_substr((string) $request->headers->get('referer'), 0, 250),
            'utm_source' => $attribution['utm_source'] ?? null,
            'utm_medium' => $attribution['utm_medium'] ?? null,
            'utm_campaign' => $attribution['utm_campaign'] ?? null,
            'utm_term' => $attribution['utm_term'] ?? null,
            'utm_content' => $attribution['utm_content'] ?? null,
            'gclid' => $attribution['gclid'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 250),
        ]);

        if ($email = $site->get('lead_notification_email')) {
            try {
                Notification::route('mail', $email)->notify(new NewLeadNotification($lead));
            } catch (\Throwable $e) {
                // The lead is already saved; a mail problem must not lose it or show the visitor an error.
                Log::error('Lead notification failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('contact.thanks')->with('lead_id', $lead->id);
    }

    public function thanks(Request $request)
    {
        // Only reachable straight after a saved submission.
        if (! $request->session()->has('lead_id')) {
            return redirect()->route('contact');
        }

        $seo = SeoData::make('Request received', null, route('contact.thanks'));
        $seo->robots = 'noindex,nofollow';

        return view('pages.thanks', compact('seo'));
    }
}
