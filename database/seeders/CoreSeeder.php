<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Service;
use App\Models\State;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoreSeeder extends Seeder
{
    public const IMAGE_ALT = [
        'technician-servicing-condenser.jpg' => 'HVAC technician checking refrigerant gauges on a residential outdoor air conditioning unit',
        'high-efficiency-gas-furnace.jpg' => 'High-efficiency gas furnace with PVC venting and ductwork in a basement',
        'technician-servicing-outdoor-unit.jpg' => 'Technician servicing the outdoor unit of a heat pump system',
        'technicians-condenser-installation.jpg' => 'Two HVAC technicians beside newly installed outdoor condenser units',
        'technician-reviewing-options-with-customer.jpg' => 'HVAC technician reviewing service options with a homeowner on a tablet',
        'technician-repairing-ac-condenser.jpg' => 'Technician repairing wiring inside an air conditioning condenser',
        'smart-thermostat.jpg' => 'Smart thermostat mounted on a living room wall',
        'technician-servicing-wall-mounted-unit.jpg' => 'Technician adjusting a wall-mounted heating unit',
        'technician-adjusting-heating-controls.jpg' => 'Technician adjusting the controls of a home heating system',
        'technician-outdoor-heat-pump.jpg' => 'Smiling HVAC technician standing next to outdoor heat pump units',
        'technician-rooftop-equipment.jpg' => 'Technician performing maintenance on rooftop HVAC equipment',
    ];

    public function run(): void
    {
        $this->admin();
        $media = $this->media();
        $this->categories();
        $this->states();
        $this->services($media);
        $this->pages($media);
    }

    private function admin(): void
    {
        $email = config('site.seed_admin.email');
        if (! $email || User::query()->where('email', $email)->exists()) {
            return;
        }

        $password = config('site.seed_admin.password') ?: Str::password(20);
        User::query()->create([
            'name' => config('site.seed_admin.name') ?: 'Site Administrator',
            'email' => $email,
            'password' => $password,
            'role' => Role::SuperAdmin,
            'email_verified_at' => now(),
        ]);

        if (! config('site.seed_admin.password')) {
            $this->command?->warn("Super admin {$email} created with a generated password. Reset it with: php artisan app:reset-password {$email}");
        }
    }

    /** @return array<string, Media> filename => media */
    private function media(): array
    {
        $service = app(MediaService::class);
        $out = [];
        foreach (self::IMAGE_ALT as $file => $alt) {
            $existing = Media::query()->where('original_name', $file)->first();
            $out[$file] = $existing ?? $service->importLocalFile(database_path('seeders/assets/'.$file), ['alt' => $alt]);
        }

        return $out;
    }

    private function categories(): void
    {
        $categories = [
            'cooling' => ['Air Conditioning & Cooling', 'Troubleshooting, repair decisions, and efficiency tips for central air, heat pumps in cooling mode, and ductless systems.'],
            'heating' => ['Heating', 'Guides to furnaces, heat pumps, and other heating systems — from warning signs to replacement choices.'],
            'maintenance' => ['Maintenance', 'Seasonal checklists and the small habits that keep heating and cooling systems running well.'],
            'buying-guides' => ['Buying Guides', 'How to compare systems, quotes, efficiency ratings, and installation options.'],
            'indoor-air-quality' => ['Indoor Air Quality', 'Filtration, humidity, ventilation, and cleaner indoor air.'],
        ];
        $i = 0;
        foreach ($categories as $slug => [$name, $description]) {
            Category::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'sort_order' => $i++]);
        }
    }

    private function states(): void
    {
        foreach (require __DIR__.'/data/states.php' as $key => $value) {
            if ($key !== 'list') {
                continue;
            }
            foreach ($value as $code => $name) {
                State::query()->firstOrCreate(['code' => $code], [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'heading' => "HVAC services in {$name}",
                    'is_published' => false,
                    'coverage_verified' => false,
                ]);
            }
        }
    }

    private function services(array $media): void
    {
        $data = require __DIR__.'/data/services.php';

        foreach ($data as $i => $row) {
            Service::query()->updateOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'nav_label' => $row['nav_label'],
                'group' => $row['group'],
                'icon' => $row['icon'],
                'short_description' => $row['short'],
                'hero_heading' => $row['hero_heading'],
                'hero_text' => $row['hero_text'],
                'overview' => $row['overview'],
                'problems' => $row['problems'],
                'options' => $row['options'],
                'signs' => $row['signs'],
                'details' => $row['details'],
                'faqs' => $row['faqs'],
                'hero_media_id' => $media[$row['image']]->id ?? null,
                'is_featured' => $row['featured'],
                'is_published' => true,
                'sort_order' => $i,
                'seo_title' => 'Residential '.$row['title'].' | HVAC Services',
            ]);
        }

        foreach ($data as $row) {
            $service = Service::query()->where('slug', $row['slug'])->first();
            $service->relatedServices()->sync(Service::query()->whereIn('slug', $row['related'])->pluck('id'));
        }
    }

    private function pages(array $media): void
    {
        foreach (require __DIR__.'/data/pages.php' as $row) {
            Page::query()->updateOrCreate(['system_key' => $row['system_key']], [
                'slug' => $row['slug'],
                'title' => $row['title'],
                'template' => $row['template'],
                'heading' => $row['heading'] ?? null,
                'intro' => $row['intro'] ?? null,
                'body' => $row['body'] ?? null,
                'sections' => $row['sections'] ?? null,
                'cta_heading' => $row['cta_heading'] ?? null,
                'cta_text' => $row['cta_text'] ?? null,
                'hero_media_id' => isset($row['image']) ? $media[$row['image']]->id : null,
                'seo_title' => $row['seo_title'] ?? null,
                'seo_description' => $row['seo_description'] ?? null,
                'robots_index' => $row['robots_index'] ?? true,
                'show_in_footer' => $row['show_in_footer'] ?? false,
                'is_published' => true,
            ]);
        }
    }
}
