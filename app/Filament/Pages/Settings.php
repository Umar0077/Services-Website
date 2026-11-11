<?php

namespace App\Filament\Pages;

use App\Models\DisclaimerVersion;
use App\Models\Setting;
use App\Support\Disclaimer;
use App\Support\Settings as SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use UnitEnum;

/** Site-wide settings, including the centrally managed, versioned disclaimer. Admins only. */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Site settings';

    /** Image settings that are uploaded files (stored path on the public disk). */
    private const UPLOADS = ['logo_light_background', 'logo_dark_background', 'favicon', 'default_og_image'];

    private const KEYS = [
        'site_name', 'tagline', 'phone', 'email', 'hours_text', 'footer_about', 'social_links',
        'header_cta_label', 'hero_heading', 'hero_text', 'home_hero_media_id', 'why_items', 'how_steps', 'home_faqs',
        'final_cta_heading', 'final_cta_text', 'default_seo_title', 'default_seo_description', 'title_separator',
        'analytics_enabled', 'gtm_id', 'ga4_id', 'google_ads_id', 'marketcall_enabled', 'marketcall_program_id',
        'tracking_cookie_days', 'lead_notification_email', 'form_privacy_notice',
    ];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Gate::allows('manage-settings');
    }

    public function mount(): void
    {
        $site = app(SiteSettings::class);
        $stored = Setting::query()->pluck('value', 'key');

        $values = collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => $site->get($k)])->all();
        foreach (self::UPLOADS as $key) {
            $values[$key] = $stored[$key] ?? null; // only real uploads; empty = bundled default
        }
        $values['disclaimer'] = Disclaimer::text();

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        $upload = fn (string $key, string $label, string $hint) => FileUpload::make($key)->label($label)->image()
            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(4096)
            ->disk('public')->directory('branding')->helperText($hint.' Leave empty to use the bundled file.');

        $pairs = fn (string $key, string $label, string $a = 'title', string $b = 'text') => Repeater::make($key)->label($label)
            ->schema([TextInput::make($a)->required()->maxLength(200), Textarea::make($b)->required()->rows(2)->maxLength(1000)])
            ->itemLabel(fn (array $state) => $state[$a] ?? null)->collapsible()->collapsed()->reorderable();

        return $schema->statePath('data')->components([
            Tabs::make('settings')->persistTabInQueryString()->tabs([
                Tab::make('General')->icon(Heroicon::OutlinedBuildingOffice)->schema([
                    Grid::make(2)->schema([
                        TextInput::make('site_name')->required()->maxLength(80),
                        TextInput::make('tagline')->maxLength(120),
                        TextInput::make('phone')->label('Default phone number')->required()->tel()->rule('regex:/^\D*1?\D*(\d\D*){10}$/')
                            ->helperText('Shown everywhere unless an approved tracking number applies.'),
                        TextInput::make('email')->label('Public email')->email()->maxLength(150)->helperText('Optional. Hidden when empty.'),
                        TextInput::make('hours_text')->label('Call center hours')->maxLength(120)
                            ->helperText('Leave empty until hours are confirmed. Do not promise 24/7 availability.'),
                        TextInput::make('header_cta_label')->label('Header button label')->maxLength(20),
                    ]),
                    Textarea::make('footer_about')->label('Footer description')->rows(2)->maxLength(400),
                    Repeater::make('social_links')->schema([
                        Select::make('network')->options(['Facebook' => 'Facebook', 'Instagram' => 'Instagram', 'YouTube' => 'YouTube', 'LinkedIn' => 'LinkedIn', 'X' => 'X', 'Nextdoor' => 'Nextdoor'])->required(),
                        TextInput::make('url')->url()->required()->startsWith(['https://'])->maxLength(250),
                    ])->columns(2)->defaultItems(0)->addActionLabel('Add social profile'),
                ]),
                Tab::make('Branding')->icon(Heroicon::OutlinedSwatch)->schema([
                    Grid::make(2)->schema([
                        $upload('logo_light_background', 'Logo — light backgrounds', 'Transparent PNG, used in the header.'),
                        $upload('logo_dark_background', 'Logo — dark backgrounds', 'White version, used in the footer.'),
                        $upload('favicon', 'Favicon', 'Square PNG, at least 48×48.'),
                        $upload('default_og_image', 'Default social share image', '1200×630 recommended.'),
                    ]),
                ]),
                Tab::make('Homepage')->icon(Heroicon::OutlinedHome)->schema([
                    TextInput::make('hero_heading')->required()->maxLength(60)->helperText('Two short sentences display on two lines, e.g. "Your comfort. Our priority."'),
                    Textarea::make('hero_text')->required()->rows(3)->maxLength(400),
                    Select::make('home_hero_media_id')->label('Hero image')->options(fn () => \App\Models\Media::query()->latest()->limit(300)->pluck('filename', 'id'))->searchable(),
                    $pairs('why_items', 'Why homeowners use us (4 recommended)'),
                    $pairs('how_steps', 'How it works (3 steps)'),
                    $pairs('home_faqs', 'Homepage FAQs', 'question', 'answer'),
                    Grid::make(2)->schema([
                        TextInput::make('final_cta_heading')->label('Default CTA heading')->maxLength(120),
                        Textarea::make('final_cta_text')->label('Default CTA text')->rows(2)->maxLength(300),
                    ]),
                ]),
                Tab::make('SEO defaults')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    TextInput::make('default_seo_title')->label('Homepage / default title')->required()->maxLength(70),
                    Textarea::make('default_seo_description')->label('Default meta description')->required()->rows(2)->maxLength(320),
                    TextInput::make('title_separator')->maxLength(5)->helperText('Between page title and site name, e.g. " | ".'),
                    Text::make(new HtmlString('Sitemaps, <code>robots.txt</code> and <code>llms.txt</code> update automatically when content changes: <a href="/sitemap.xml" target="_blank" class="underline">sitemap.xml</a> · <a href="/llms.txt" target="_blank" class="underline">llms.txt</a> · <a href="/robots.txt" target="_blank" class="underline">robots.txt</a>')),
                ]),
                Tab::make('Analytics & call tracking')->icon(Heroicon::OutlinedChartBar)->schema([
                    Callout::make('Tracking scripts')->description('Google Tag Manager, GA4, Google Ads and MarketCall scripts load only when enabled. Keep them off on staging.')->info(),
                    Grid::make(2)->schema([
                        Toggle::make('analytics_enabled')->label('Load Google tags')->inline(false),
                        Toggle::make('marketcall_enabled')->label('Load MarketCall scripts')->inline(false),
                        TextInput::make('gtm_id')->label('Google Tag Manager ID')->regex('/^GTM-[A-Z0-9]{4,12}$/')->placeholder('GTM-XXXXXXX'),
                        TextInput::make('ga4_id')->label('GA4 measurement ID')->regex('/^G-[A-Z0-9]{6,14}$/')->placeholder('G-XXXXXXXXXX'),
                        TextInput::make('google_ads_id')->label('Google Ads ID')->regex('/^AW-\d{6,14}$/')->placeholder('AW-XXXXXXXXXX'),
                        TextInput::make('marketcall_program_id')->label('MarketCall program ID')->regex('/^\d{1,10}$/'),
                        TextInput::make('tracking_cookie_days')->label('Attribution window (days)')->numeric()->minValue(1)->maxValue(90),
                    ]),
                    Text::make(new HtmlString('Campaign phone numbers are managed under <a href="'.\App\Filament\Resources\TrackingNumbers\TrackingNumberResource::getUrl().'" class="underline">Tracking numbers</a>. Only approved numbers can appear on the site.')),
                ]),
                Tab::make('Leads & forms')->icon(Heroicon::OutlinedInboxArrowDown)->schema([
                    TextInput::make('lead_notification_email')->label('Send new-lead alerts to')->email()->maxLength(150)
                        ->helperText('Leave empty to disable email alerts. Leads are always saved in the admin.'),
                    Textarea::make('form_privacy_notice')->label('Consent text next to the form checkbox')->required()->rows(3)->maxLength(600),
                ]),
                Tab::make('Disclaimer')->icon(Heroicon::OutlinedScale)->schema([
                    Callout::make('Client-approved legal text')
                        ->description('This exact text appears in the footer, on /disclaimer, next to every availability mention, and in llms.txt. Change it only when a new version has been approved. Every change is saved in the history below.')
                        ->warning(),
                    Textarea::make('disclaimer')->label('Disclaimer text')->required()->rows(10)->maxLength(4000)
                        ->helperText('Separate paragraphs with a blank line. A first line ending in ":" is shown as a label.'),
                    Grid::make(2)->schema([
                        TextInput::make('disclaimer_note')->label('Reason for change')->maxLength(200)
                            ->required(fn (Get $get) => trim(str_replace("\r\n", "\n", (string) $get('disclaimer'))) !== Disclaimer::text()),
                        Toggle::make('disclaimer_confirm')->label('I confirm this wording has been approved')->inline(false)
                            ->accepted(fn (Get $get) => trim(str_replace("\r\n", "\n", (string) $get('disclaimer'))) !== Disclaimer::text()),
                    ]),
                    Section::make('Version history')->collapsible()->schema([
                        Text::make(fn () => new HtmlString($this->historyHtml())),
                    ]),
                ]),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([$this->getFormContentComponent()]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([Actions::make([Action::make('save')->label('Save settings')->submit('save')->keyBindings(['mod+s'])])->sticky()]);
    }

    public function save(): void
    {
        abort_unless(Gate::allows('manage-settings'), 403);

        $data = $this->form->getState();
        $site = app(SiteSettings::class);

        if (trim(str_replace("\r\n", "\n", (string) $data['disclaimer'])) !== Disclaimer::text()) {
            Disclaimer::update($data['disclaimer'], auth()->user(), $data['disclaimer_note'] ?? null);
        }

        $values = collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => $data[$k] ?? null])->all();
        $values['phone'] = \App\Support\Phone::digits($values['phone']);
        foreach (self::UPLOADS as $key) {
            $values[$key] = $data[$key] ?? null;
        }
        // Store only values that differ from defaults so config changes still apply.
        foreach ($values as $key => $value) {
            if ($value === config("site.{$key}") || $value === null || $value === '') {
                Setting::query()->where('key', $key)->delete();
                unset($values[$key]);
            }
        }
        $site->set($values);
        \App\Models\ActivityLog::record('settings_updated', null, 'Site settings saved', ['fields' => array_keys($values)]);

        $this->data['disclaimer_note'] = null;
        $this->data['disclaimer_confirm'] = false;

        Notification::make()->title('Settings saved')->success()->send();
    }

    private function historyHtml(): string
    {
        $versions = DisclaimerVersion::query()->with('user')->latest('created_at')->limit(20)->get();
        if ($versions->isEmpty()) {
            return '<p>No changes yet. The current text is the original approved version.</p>';
        }

        return '<table style="width:100%;font-size:13px;border-collapse:collapse">'
            .'<thead><tr style="text-align:left"><th style="padding:6px">When</th><th style="padding:6px">By</th><th style="padding:6px">Note</th><th style="padding:6px">Text</th></tr></thead><tbody>'
            .$versions->map(fn ($v) => '<tr style="border-top:1px solid rgba(127,127,127,.2);vertical-align:top">'
                .'<td style="padding:6px;white-space:nowrap">'.e($v->created_at->format('M j, Y g:ia')).'</td>'
                .'<td style="padding:6px">'.e($v->user?->name ?? '—').'</td>'
                .'<td style="padding:6px">'.e($v->note ?? '').'</td>'
                .'<td style="padding:6px"><details><summary>View</summary><div style="white-space:pre-wrap;margin-top:6px">'.e($v->text).'</div></details></td></tr>')
                ->implode('')
            .'</tbody></table>';
    }
}
