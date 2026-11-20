<?php

namespace Tests\Feature;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Models\TrackingNumber;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Tests\SeededTestCase;

class LeadFormTest extends SeededTestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Homeowner',
            'phone' => '(202) 555-0177',
            'email' => 'test@example.com',
            'zip' => '77002',
            'service_id' => Service::where('slug', 'ac-repair')->value('id'),
            'message' => 'AC blows warm air.',
            'consent' => '1',
            'form_token' => Crypt::encryptString((string) (time() - 30)),
        ], $overrides);
    }

    public function test_valid_request_is_saved_and_notifies(): void
    {
        Notification::fake();
        config(['site.lead_notification_email' => 'leads@example.com']);

        $this->post('/contact', $this->payload())->assertRedirect('/contact/thank-you');
        $this->followingRedirects()->get('/contact/thank-you');

        $lead = Lead::latest('id')->first();
        $this->assertSame('2025550177', $lead->phone);
        $this->assertSame('Air Conditioning Repair', $lead->service_label);
        $this->assertSame('residential', $lead->property_type);
        Notification::assertSentOnDemand(NewLeadNotification::class);
    }

    public function test_thank_you_page_requires_a_real_submission(): void
    {
        $this->get('/contact/thank-you')->assertRedirect('/contact');
    }

    public function test_validation_errors_are_returned_and_nothing_saved(): void
    {
        $before = Lead::count();
        $this->post('/contact', $this->payload(['zip' => '12', 'phone' => '123', 'consent' => null]))
            ->assertSessionHasErrors(['zip', 'phone', 'consent']);
        $this->assertSame($before, Lead::count());
    }

    public function test_spam_traps_reject_bots(): void
    {
        $before = Lead::count();
        $this->post('/contact', $this->payload(['company_website' => 'spam.example']))->assertSessionHasErrors('company_website');
        $this->post('/contact', $this->payload(['form_token' => StoreLeadRequest::token()]))->assertSessionHasErrors('form_token'); // too fast
        $this->post('/contact', $this->payload(['form_token' => 'tampered']))->assertSessionHasErrors('form_token');
        $this->assertSame($before, Lead::count());
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/contact', $this->payload())->assertRedirect();
        }
        // The 4th is blocked: the visitor is sent back to the form with a toast instead of a bare 429 page.
        $before = Lead::count();
        $this->from('/contact')->post('/contact', $this->payload())
            ->assertRedirect('/contact')
            ->assertSessionHas('toast', fn ($t) => $t['title'] === 'Too many requests');
        $this->assertSame($before, Lead::count());
    }

    public function test_csrf_protection_is_active(): void
    {
        // Laravel skips CSRF checks while running tests, so assert the middleware is on the web group instead.
        $this->assertContains(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class, app('router')->getMiddlewareGroups()['web']);
    }

    public function test_only_approved_tracking_numbers_can_replace_the_phone(): void
    {
        TrackingNumber::create(['label' => 'Test', 'code' => 'spring', 'number' => '2025550199']);

        $this->get('/call-hvac?pn=spring')->assertSee('(202) 555-0199')->assertCookie('hvac_pn');
        $this->get('/call-hvac?phone=2025550199')->assertSee('(202) 555-0199');
        $this->get('/call-hvac?phone=9005551234')->assertDontSee('(900) 555-1234')->assertSee('(888) 217-5445');
        $this->get('/call-hvac?pn=unknown')->assertSee('(888) 217-5445');
    }

    public function test_lead_is_saved_even_when_email_notification_throws(): void
    {
        config(['site.lead_notification_email' => 'leads@example.com']);
        Notification::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP connection refused'));

        $before = Lead::count();
        $this->post('/contact', $this->payload())->assertRedirect('/contact/thank-you');
        $this->assertSame($before + 1, Lead::count());
    }
}
