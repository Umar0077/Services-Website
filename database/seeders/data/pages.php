<?php

/*
| System pages. Legal pages expand the legacy site's modal text and MUST be reviewed by the business and its
| counsel before launch (see docs/05-LAUNCH-CHECKLIST.md). All content is editable in Admin → Pages.
*/

return [
    [
        'system_key' => 'about',
        'slug' => 'about',
        'title' => 'About HVAC Services',
        'template' => 'standard',
        'heading' => 'Helping people find heating and cooling help',
        'intro' => 'HVAC Services is an independent referral service that helps homeowners across the United States connect with independent heating, ventilation, and air conditioning providers.',
        'image' => 'technician-reviewing-options-with-customer.jpg',
        'seo_title' => 'About HVAC Services | How Our Referral Service Works',
        'seo_description' => 'Learn how HVAC Services helps homeowners connect with independent heating and air conditioning providers, and what to expect when you call.',
        'sections' => [
            ['type' => 'text', 'data' => [
                'eyebrow' => 'Who we are',
                'heading' => 'A simpler way to start an HVAC request',
                'body' => '<p>When a furnace stops in January or an air conditioner fails in July, most people do not have a heating and cooling company on speed dial. They search, call around, and wait for callbacks. HVAC Services was created to shorten that process.</p><p>You tell us what is happening with your system and where you are. We work to connect you with an independent HVAC provider who serves your area and has availability. The provider assesses your system, explains the options, and — if you choose to go ahead — does the work.</p>',
            ]],
            ['type' => 'features', 'data' => [
                'eyebrow' => 'Our mission',
                'heading' => 'What we focus on',
                'intro' => 'We keep our role narrow and clear: making it easier for you to reach the right kind of help.',
                'items' => [
                    ['title' => 'Clarity', 'text' => 'Plain explanations of HVAC problems and services, so you know what to ask for.'],
                    ['title' => 'Convenience', 'text' => 'One call or one form instead of searching and calling around.'],
                    ['title' => 'Transparency', 'text' => 'We are upfront that we are a referral service and that providers are independent.'],
                    ['title' => 'Respect for your choice', 'text' => 'You decide whether to hire a provider, after you hear their assessment and quote.'],
                ],
            ]],
            ['type' => 'steps', 'data' => [
                'eyebrow' => 'The process',
                'heading' => 'How the service connection works',
                'items' => [
                    ['title' => 'You reach out', 'text' => 'Call or submit a request with your ZIP code, the type of system you have, and what is going on.'],
                    ['title' => 'We look for availability', 'text' => 'Our routing considers your location, the service you need, and how urgent it is.'],
                    ['title' => 'A provider follows up', 'text' => 'An independent provider contacts you or is connected to your call to arrange a visit.'],
                    ['title' => 'You decide', 'text' => 'The provider diagnoses the system and gives you a quote. All agreements and payments are between you and the provider.'],
                ],
            ]],
            ['type' => 'text', 'data' => [
                'eyebrow' => 'Trust and transparency',
                'heading' => 'What we are — and what we are not',
                'body' => '<p>HVAC Services does not perform contracting work and does not employ technicians. Providers are independent, and HVAC Services does not endorse, represent, or assume liability for any work they perform; and pricing, scheduling, and warranties are set by the provider.</p><p>Before you authorize work, ask the provider for their state license number (where your state requires one) and proof of insurance. Technicians who handle refrigerant must hold EPA Section 608 certification. Many states offer online license lookups.</p><p>Same-day and 24/7 emergency services are subject to provider participation, location, technician availability, and demand. Availability may vary by market, dispatch volume, and appointment capacity.</p>',
            ]],
        ],
        'cta_heading' => 'Ready to talk about your system?',
        'cta_text' => 'Call or send a request and we will work to connect you with an available independent provider.',
    ],
    [
        'system_key' => 'call_landing',
        'slug' => 'call-hvac',
        'title' => 'Call for Heating & Air Conditioning Help',
        'template' => 'landing',
        'heading' => 'Heating or Cooling Problems? Connect With Local HVAC Service Providers',
        'intro' => 'Need help with your home\'s heating or cooling system? Call to get connected with participating local HVAC service providers in your area.',
        'image' => 'technician-servicing-condenser.jpg',
        'seo_title' => 'Call HVAC Services | Heating & AC Repair Connections',
        'seo_description' => 'Need help with your home\'s heating or cooling system? Call HVAC Services to get connected with participating local HVAC service providers in your area.',
        'robots_index' => false, // paid-traffic landing page
        'sections' => [
            ['type' => 'features', 'data' => [
                'eyebrow' => 'Residential HVAC assistance',
                'heading' => 'Services & Home Comfort Needs',
                'intro' => 'Call to get connected with participating independent service providers for common residential heating and cooling requests.',
                'items' => [
                    ['title' => 'Air Conditioning Repair', 'text' => 'Common home AC problems, reduced cooling, unusual noises, and system malfunctions.'],
                    ['title' => 'Heating & Furnace Repair', 'text' => 'Residential heating issues and furnace-related service needs.'],
                    ['title' => 'HVAC Installation & Replacement', 'text' => 'Homeowners exploring replacement or installation options.'],
                    ['title' => 'Heat Pumps & Mini-Splits', 'text' => 'Residential heat pump and ductless cooling and heating solutions.'],
                    ['title' => 'HVAC Maintenance', 'text' => 'Routine maintenance and seasonal system servicing.'],
                    ['title' => 'Local Service Connections', 'text' => 'Helping homeowners connect with participating service providers where available.'],
                ],
            ]],
        ],
        'cta_heading' => 'Need Help With Your Home\'s HVAC System?',
        'cta_text' => 'Call to explore available heating and cooling service options in your area.',
    ],
    [
        'system_key' => 'privacy',
        'slug' => 'privacy-policy',
        'title' => 'Privacy Policy',
        'template' => 'legal',
        'heading' => 'Privacy Policy',
        'intro' => 'How HVAC Services collects, uses, and protects information from visitors to this website and callers to our phone line.',
        'show_in_footer' => true,
        'body' => '<p><em>Last updated: October 2026</em></p><p>This Privacy Policy explains how HVAC Services ("we," "our," or "us") collects, uses, and shares information when you use this website or call our phone numbers.</p>'
            .'<h2>Information we collect</h2><p><strong>Information you provide.</strong> When you submit a request or call us, you may provide your name, phone number, email address, ZIP code, city and state, the type of property, the HVAC service you need, and a description of the problem.</p><p><strong>Information collected automatically.</strong> Like most websites, we collect technical information such as browser type, device type, IP address, pages visited, the referring website, and approximate location derived from your IP address. If you arrive from an advertisement, we may record campaign information such as UTM parameters or an advertising click identifier.</p><p><strong>Phone calls.</strong> Calls to our numbers may be routed through call-tracking and call-routing providers, which may record the calling number, call time, and call duration, and may record calls where permitted by law and with any required notice.</p>'
            .'<h2>How we use information</h2><ul><li>To respond to your request and connect you with independent HVAC service providers in your area</li><li>To operate, maintain, and improve this website</li><li>To measure the effectiveness of our advertising</li><li>To prevent spam, fraud, and abuse</li><li>To comply with legal obligations</li></ul>'
            .'<h2>How we share information</h2><p>To fulfill your request, we share the contact and request details you provide with independent HVAC contractors and with call-routing partners who operate in your area. These providers use your information to contact you about your request. We also use service providers who host this website, process form submissions, and provide analytics and advertising services.</p><p>We may disclose information when required by law or to protect the rights, property, or safety of our users, our business, or others.</p>'
            .'<h2>Cookies and advertising</h2><p>We use cookies and similar technologies to remember your preferences, attribute calls and requests to the advertising that brought you here, and analyze site traffic. We use Google Analytics and Google Ads. Google uses cookies to show ads based on your visits to this and other websites. You can opt out of personalized Google advertising at <a href="https://adssettings.google.com">adssettings.google.com</a>. See our Cookie Policy for details.</p>'
            .'<h2>Your privacy choices</h2><p>Depending on where you live, you may have the right to request access to, correction of, or deletion of your personal information, and to opt out of the sale or sharing of personal information for targeted advertising. California residents may exercise rights under the California Consumer Privacy Act (CCPA), as amended. To make a request, contact us using the details below. We will not discriminate against you for exercising these rights.</p>'
            .'<h2>Data retention</h2><p>We keep request information only as long as needed for the purposes described in this policy, including record-keeping and legal obligations.</p>'
            .'<h2>Security</h2><p>We use reasonable administrative, technical, and physical safeguards to protect information. No method of transmission or storage is completely secure.</p>'
            .'<h2>Children</h2><p>This website is not directed to children under 13, and we do not knowingly collect their personal information.</p>'
            .'<h2>Changes to this policy</h2><p>We may update this policy from time to time. The "last updated" date shows when it was last revised.</p>'
            .'<h2>Contact us</h2><p>For privacy questions or requests, contact us through our Contact page.</p>',
    ],
    [
        'system_key' => 'terms',
        'slug' => 'terms',
        'title' => 'Terms and Conditions',
        'template' => 'legal',
        'heading' => 'Terms and Conditions',
        'intro' => 'The terms that apply when you use this website or our telephone referral service.',
        'show_in_footer' => true,
        'body' => '<p><em>Last updated: October 2026</em></p><p>By accessing or using this website or our telephone referral service, you agree to these Terms and Conditions. If you do not agree, please do not use the website or service.</p>'
            .'<h2>Nature of the service</h2><p>This website is a free advertising and referral service for independent third-party HVAC contractors. HVAC Services does not provide HVAC contracting, installation, engineering, or repair services, and does not employ contractors, technicians, or apprentices.</p>'
            .'<h2>No warranty or guarantee</h2><p>We make no express or implied representations, warranties, or guarantees regarding the quality, pricing, timeliness, legality, or outcome of any work performed by independent contractors connected through this service. Any agreement for work is solely between you and the contractor you choose to hire.</p>'
            .'<h2>Your responsibilities</h2><p>You are responsible for verifying that any contractor you hire holds the licenses, permits, bonding, and insurance required for the work in your jurisdiction, and for reviewing quotes, contracts, and warranties before authorizing work.</p>'
            .'<h2>Availability</h2><p>Same-day and 24/7 emergency services are subject to provider participation, location, technician availability, and demand. Availability may vary by market, dispatch volume, and appointment capacity.</p>'
            .'<h2>Information on this website</h2><p>Articles and guides on this website are for general information only. They are not a substitute for an on-site assessment by a qualified professional. Never attempt repairs that involve gas, refrigerant, or high-voltage electrical components unless you are qualified to do so.</p>'
            .'<h2>Communications</h2><p>By submitting a request, you agree that HVAC Services and independent providers may contact you at the phone number or email address you provide about your request.</p>'
            .'<h2>Limitation of liability</h2><p>To the fullest extent permitted by law, HVAC Services is not liable for any damages arising from your use of this website or from services performed by independent contractors.</p>'
            .'<h2>Changes</h2><p>We may update these terms from time to time. Continued use of the website or service after changes means you accept the updated terms.</p>',
    ],
    [
        'system_key' => 'disclosures',
        'slug' => 'service-disclosures',
        'title' => 'Service Disclosures',
        'template' => 'legal',
        'heading' => 'Service and contractor disclosures',
        'intro' => 'How our referral service works, and what you should know about the independent contractors in our network.',
        'show_in_footer' => true,
        'body' => '<h2>How our service works</h2><p>HVAC Services is an independent referral and connection platform that helps homeowners identify and connect with independent heating, ventilation, and air conditioning contractors in their area.</p><h3>1. Initial contact</h3><p>When you call or submit a request, our routing technology evaluates provider availability based on your location, system type, and the urgency of your request.</p><h3>2. In-home assessment and quote</h3><p>An independent contractor assesses your system and provides an estimate for repairs or replacement options. You are under no obligation to proceed until you review and authorize the quote.</p><h3>3. Work authorization and payment</h3><p>All agreements, warranties, labor terms, and payments are handled directly between you and the independent provider you hire. HVAC Services does not perform contracting services or employ field technicians.</p>'
            .'<h2>Contractor licensing notice</h2><p>All HVAC, heating, refrigeration, and electrical work must comply with local, municipal, and state licensing regulations. HVAC Services does not verify, guarantee, or represent the licensing or insurance status of any provider. It is the responsibility of the homeowner to verify that the hired contractor holds the licenses and insurance required for the work, such as EPA Section 608 certification for refrigerant work and state mechanical or contractor licenses where required.</p><p>Before work begins, ask the provider for proof of licensure, liability insurance, and workers\' compensation coverage.</p>'
            .'<p>See our <a href="/disclaimer">full disclaimer</a>.</p>'
            .'<h2>Photos and media</h2><p>All persons depicted in photos or videos on this website are actors or models and are not contractors listed on HVAC Services.</p>',
    ],
    [
        'system_key' => 'cookies',
        'slug' => 'cookie-policy',
        'title' => 'Cookie Policy',
        'template' => 'legal',
        'heading' => 'Cookie Policy',
        'intro' => 'What cookies this website uses and how to control them.',
        'show_in_footer' => true,
        'body' => '<p><em>Last updated: October 2026</em></p><p>Cookies are small text files stored by your browser. This website uses the following categories of cookies.</p>'
            .'<h2>Essential cookies</h2><p>Required for the website to work, including security tokens that protect forms and a session cookie. These cannot be turned off.</p>'
            .'<h2>Attribution cookies</h2><p>When you arrive from an advertisement or a tracked link, we store the campaign source and the tracking phone number assigned to that campaign for up to 30 days, so calls and requests can be attributed correctly.</p>'
            .'<h2>Analytics and advertising cookies</h2><p>We use Google Tag Manager, Google Analytics, and Google Ads to understand how visitors use the site and to measure advertising. Call-tracking scripts from our routing partner may also set cookies. You can opt out of personalized Google ads at <a href="https://adssettings.google.com">adssettings.google.com</a> and install the Google Analytics opt-out browser add-on.</p>'
            .'<h2>Managing cookies</h2><p>Most browsers let you block or delete cookies in their settings. Blocking essential cookies may prevent forms from working.</p>',
    ],
];
