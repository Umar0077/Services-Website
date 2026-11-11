<?php

namespace App\Filament\Widgets;

use App\Filament\Resources;
use App\Models;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-actions';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $actions = [
            ['label' => 'Write a blog post', 'icon' => 'heroicon-o-pencil-square', 'url' => Resources\Posts\PostResource::getUrl('create'), 'can' => $user->can('create', Models\Post::class)],
            ['label' => 'Add a city', 'icon' => 'heroicon-o-map-pin', 'url' => Resources\Cities\CityResource::getUrl('create'), 'can' => $user->can('create', Models\City::class)],
            ['label' => 'Edit a page', 'icon' => 'heroicon-o-document-text', 'url' => Resources\Pages\PageResource::getUrl(), 'can' => $user->can('viewAny', Models\Page::class)],
            ['label' => 'Upload media', 'icon' => 'heroicon-o-photo', 'url' => Resources\Media\MediaResource::getUrl(), 'can' => $user->can('create', Models\Media::class)],
            ['label' => 'Review leads', 'icon' => 'heroicon-o-inbox-arrow-down', 'url' => Resources\Leads\LeadResource::getUrl(), 'can' => $user->can('viewAny', Models\Lead::class)],
            ['label' => 'View website', 'icon' => 'heroicon-o-globe-alt', 'url' => url('/'), 'can' => true, 'external' => true],
        ];

        return ['actions' => array_filter($actions, fn ($a) => $a['can'])];
    }
}
