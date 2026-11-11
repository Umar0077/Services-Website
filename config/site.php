<?php

/*
| Default site settings. Every key here can be overridden in Admin → Settings (stored in the `settings` table).
| Values shown here are the verified facts carried over from the legacy site; nothing here is invented.
*/

return [
    'site_name' => 'HVAC Services',
    'tagline' => 'Heating and cooling help, connected.',
    'logo_light_background' => '/images/brand/logo-hvac-services.png',       // navy + orange, for light backgrounds
    'logo_dark_background' => '/images/brand/logo-hvac-services-white.png', // white + orange, for dark backgrounds
    'favicon' => '/images/brand/favicon.png',

    'phone' => '8882175445',
    'email' => null,
    'hours_text' => null, // leave empty until the business confirms call-center hours

    'default_seo_title' => 'HVAC Services | Home Heating & Air Conditioning Help',
    'default_seo_description' => 'HVAC Services helps U.S. homeowners connect with independent local providers for home AC repair, furnace and heating service, heat pumps, installation, and maintenance.',
    'default_og_image' => '/images/hvac/hero-technician-condenser.jpg',
    'title_separator' => ' | ',

    'social_links' => [], // [{network, url}]

    'header_cta_label' => 'Call',
    'hero_heading' => 'Your comfort. Our priority.',
    'hero_text' => 'Tell us what your home heating or cooling system needs. HVAC Services is an independent referral service that helps homeowners get connected with available local HVAC providers — for repairs, replacements, new installations, and routine maintenance.',
    'final_cta_heading' => 'Talk to someone about your HVAC system',
    'final_cta_text' => 'Call to describe the problem, or send a request online. We will help connect you with an available independent provider near you.',

    'why_items' => [
        ['title' => 'One place to start', 'text' => 'Repairs, replacements, new installations, maintenance, and indoor air quality — describe the problem once instead of calling around.'],
        ['title' => 'Built for homeowners', 'text' => 'Single-family homes, townhomes, condos, and manufactured homes — central air, furnaces, heat pumps, and ductless systems.'],
        ['title' => 'Free to use', 'text' => 'There is no charge to use HVAC Services. Any quote, contract, and payment is between you and the provider you choose to hire.'],
        ['title' => 'You stay in control', 'text' => 'Providers assess the system and explain options before work begins. You decide whether to go ahead.'],
    ],
    'how_steps' => [
        ['title' => 'Choose your service', 'text' => 'Pick the type of help you need, from a no-cooling repair to a full system replacement.'],
        ['title' => 'Call or send a request', 'text' => 'Share your ZIP code, the system you have, and what is happening. It takes a couple of minutes.'],
        ['title' => 'Get connected', 'text' => 'We work to connect you with an independent provider who serves your area and is available to help.'],
    ],
    'home_faqs' => [
        ['question' => 'Does HVAC Services do the repair work itself?', 'answer' => 'No. HVAC Services is a referral service. We help connect you with independent HVAC contractors and service providers. The provider you hire performs the work and is responsible for it.'],
        ['question' => 'Is there a fee to use HVAC Services?', 'answer' => 'No. Using the service to request a connection is free. Any diagnostic fee, repair price, or installation quote comes from the provider and is agreed between you and them before work starts.'],
        ['question' => 'How quickly can someone come out?', 'answer' => 'It depends on your location, the time of year, and which providers are available. Same-day and 24/7 emergency services are subject to provider participation, location, technician availability, and demand. When you call, ask about current availability in your area.'],
        ['question' => 'How do I know a contractor is licensed and insured?', 'answer' => 'Ask the provider for their state contractor or mechanical license number and proof of liability insurance before work begins. Most states let you look up a license online. Technicians who handle refrigerant must hold EPA Section 608 certification.'],
        ['question' => 'What kinds of home systems can I get help with?', 'answer' => 'Central air conditioners, gas and electric furnaces, heat pumps, ductless mini-splits, ductwork, thermostats, and indoor air quality equipment such as filters, humidifiers, and dehumidifiers.'],
    ],

    'footer_about' => 'HVAC Services is an independent referral service that helps U.S. homeowners connect with local heating, ventilation, and air conditioning service providers.',

    // Client-approved disclaimer (Part 22.3). Exact text: do not reword. Editable only by admins in
    // Admin → Settings → Disclaimer, which keeps a version history. Paragraphs are separated by a blank line.
    'disclaimer' => "Disclaimer:\n\nHVAC Services is an independent referral service to assist homeowners in connecting with local service providers. All contractors/providers are independent and HVAC Services does not endorse, represent, or assume liability for any work performed. It is the responsibility of the homeowner to verify that the hired contractor furnishes the necessary license and insurance required for the work being performed. All persons depicted in a photo or video are actors or models and not contractors listed on HVAC Services.\n\nSame-day and 24/7 emergency services are subject to provider participation, location, technician availability, and demand. Availability may vary by market, dispatch volume, and appointment capacity.",
    'form_privacy_notice' => 'By submitting this form, you agree that HVAC Services may share the information you provide with independent HVAC service providers in your area so they can contact you about your request.',

    // Analytics & call tracking (IDs from the legacy site).
    'gtm_id' => 'GTM-5THTR8PS',
    'ga4_id' => 'G-CZ86LBQJFP',
    'google_ads_id' => 'AW-18542742635',
    'analytics_enabled' => env('ANALYTICS_ENABLED', false), // off locally; enable in production settings
    'marketcall_enabled' => env('ANALYTICS_ENABLED', false),
    'marketcall_program_id' => '0',
    'tracking_cookie_days' => 30,

    'lead_notification_email' => env('LEAD_NOTIFICATION_EMAIL'),

    // Generate responsive WebP sizes on upload (switched off in the test suite for speed).
    'media_variants' => (bool) env('MEDIA_VARIANTS', true),

    // Local demo login: pre-fills the admin sign-in form and lists demo accounts.
    // Ignored in production no matter what (see App\Filament\Pages\Auth\Login::demoEnabled()).
    'demo_login' => (bool) env('ADMIN_DEMO_LOGIN', false),

    // Used only by database seeders.
    'seed_admin' => [
        'name' => env('ADMIN_NAME'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
        'demo_password' => env('DEMO_PASSWORD'),
    ],
];
