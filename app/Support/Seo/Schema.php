<?php

namespace App\Support\Seo;

use App\Models\Post;
use App\Models\Service;
use App\Support\Phone;
use App\Support\RichText;
use App\Support\Settings;

/**
 * JSON-LD builders. Only facts the business has actually provided are emitted:
 * no ratings, reviews, street addresses or opening hours are generated.
 */
class Schema
{
    public static function organization(): array
    {
        $site = app(Settings::class);
        $sameAs = collect($site->get('social_links', []))->pluck('url')->filter()->values()->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => $site->name(),
            'url' => url('/'),
            'logo' => $site->asset('logo_light_background'),
            'description' => $site->get('footer_about'),
            'areaServed' => ['@type' => 'Country', 'name' => 'United States'],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+1-'.implode('-', str_split(Phone::digits($site->get('phone')), 3)),
                'contactType' => 'customer service',
                'areaServed' => 'US',
                'availableLanguage' => 'English',
            ],
            'sameAs' => $sameAs ?: null,
        ]);
    }

    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => app(Settings::class)->name(),
            'url' => url('/'),
            'publisher' => ['@id' => url('/').'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('blog.index').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn ($crumb, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->all(),
        ];
    }

    public static function service(Service $service, ?string $areaName = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->title,
            'serviceType' => $service->title,
            'description' => $service->short_description,
            'url' => $service->publicUrl(),
            'provider' => ['@id' => url('/').'#organization'],
            'areaServed' => $areaName ? ['@type' => 'Place', 'name' => $areaName] : ['@type' => 'Country', 'name' => 'United States'],
            'image' => $service->heroMedia?->url(),
        ]);
    }

    public static function article(Post $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->seo_description ?: $post->excerpt,
            'image' => $post->featuredMedia?->url(),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => ($post->content_updated_at ?? $post->updated_at)?->toIso8601String(),
            'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
            'mainEntityOfPage' => $post->canonical(),
            'articleSection' => $post->category?->name,
            'keywords' => collect([$post->primary_keyword, $post->secondary_keywords])->filter()->implode(', ') ?: null,
            'wordCount' => str_word_count(strip_tags((string) $post->body)),
        ]);
    }

    /** @param array<int, array{question: string, answer: string}> $faqs */
    public static function faq(array $faqs): ?array
    {
        if (count($faqs) === 0) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => RichText::excerpt($faq['answer'], 1000)],
            ])->all(),
        ];
    }
}
