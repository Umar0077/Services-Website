<?php

namespace Database\Seeders;

use App\Enums\LeadStatus;
use App\Enums\Role;
use App\Models\Lead;
use App\Models\Service;
use App\Models\State;
use App\Models\TrackingNumber;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * LOCAL DEVELOPMENT ONLY. Never run in production: creates sample users and clearly fake leads.
 * Demo user passwords come from DEMO_PASSWORD in .env.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $password = config('site.seed_admin.demo_password');
        if ($password) {
            foreach ([Role::Admin, Role::Editor, Role::Author] as $role) {
                User::query()->firstOrCreate(['email' => $role->value.'@hvac.test'], [
                    'name' => 'Demo '.$role->getLabel(),
                    'password' => $password,
                    'role' => $role,
                    'email_verified_at' => now(),
                ]);
            }
        }

        // Example of an approved tracking number. 555-01xx numbers are reserved for fiction.
        TrackingNumber::query()->firstOrCreate(['code' => 'demo'], [
            'label' => 'Demo campaign (local only)',
            'number' => '2025550142',
            'source' => 'Example',
        ]);

        if (Lead::query()->exists()) {
            return;
        }

        $services = Service::query()->pluck('id', 'title');
        $texas = State::query()->where('code', 'TX')->value('id');
        $samples = [
            ['Demo Lead One', 'AC Repair', 'Houston', '77002', LeadStatus::New, 'AC running but blowing warm air since yesterday.'],
            ['Demo Lead Two', 'Furnace Repair', 'Dallas', '75201', LeadStatus::Contacted, 'Furnace clicks but does not light.'],
            ['Demo Lead Three', 'HVAC Maintenance', 'San Antonio', '78205', LeadStatus::Qualified, 'Looking for a spring tune-up.'],
        ];
        foreach ($samples as $i => [$name, $service, $city, $zip, $status, $message]) {
            $title = $services->keys()->first(fn ($t) => str_contains($t, explode(' ', $service)[0]));
            Lead::query()->create([
                'name' => $name.' (sample)',
                'phone' => '20255501'.str_pad((string) (50 + $i), 2, '0'),
                'email' => 'sample'.($i + 1).'@example.com',
                'zip' => $zip,
                'service_id' => $services[$title] ?? null,
                'service_label' => $title,
                'state_id' => $texas,
                'city_name' => $city,
                'property_type' => 'residential',
                'message' => $message,
                'status' => $status,
                'consent' => true,
                'utm_source' => $i === 0 ? 'google' : null,
                'utm_medium' => $i === 0 ? 'cpc' : null,
                'created_at' => now()->subHours(($i + 1) * 7),
            ]);
        }
    }
}
