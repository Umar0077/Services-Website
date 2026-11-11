<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Enums\PostStatus;
use App\Models\City;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Service;
use App\Models\State;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $posts = Post::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $stats = [
            Stat::make('Blog posts', array_sum($posts->all()))
                ->description(($posts[PostStatus::Published->value] ?? 0).' published · '.($posts[PostStatus::Draft->value] ?? 0).' drafts · '.($posts[PostStatus::Scheduled->value] ?? 0).' scheduled')
                ->icon('heroicon-o-newspaper'),
            Stat::make('Service pages', Service::query()->where('is_published', true)->count())
                ->description(Service::query()->count().' total')->icon('heroicon-o-wrench-screwdriver'),
            Stat::make('State pages live', State::query()->where('is_published', true)->count())
                ->description(State::query()->where('is_published', true)->where('coverage_verified', true)->count().' verified & indexable')
                ->icon('heroicon-o-map'),
            Stat::make('City pages live', City::query()->where('is_published', true)->count())
                ->description(City::query()->where('is_published', true)->where('service_verified', true)->count().' verified')
                ->icon('heroicon-o-map-pin'),
        ];

        if (auth()->user()->can('viewAny', Lead::class)) {
            $stats[] = Stat::make('New form leads', Lead::query()->where('status', LeadStatus::New)->count())
                ->description(Lead::query()->where('created_at', '>=', now()->subDays(7))->count().' in the last 7 days')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('warning');
        }

        return $stats;
    }
}
