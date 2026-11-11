{{-- Renders the controlled CMS section blocks (text / features / steps / image_text). No arbitrary HTML builder. --}}
@props(['sections' => []])
@php($mediaIds = collect($sections)->pluck('data.media_id')->filter()->all())
@php($media = $mediaIds ? \App\Models\Media::query()->whereIn('id', $mediaIds)->get()->keyBy('id') : collect())
@foreach ($sections ?? [] as $i => $section)
    @php($d = $section['data'] ?? [])
    @php($alt = $i % 2 === 1)
    <section class="section {{ $alt ? 'bg-white' : '' }}">
        <div class="container-site">
            @switch($section['type'] ?? '')
                @case('text')
                    <div class="grid gap-8 lg:grid-cols-[1fr_1.35fr] lg:gap-16">
                        <x-section-heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['heading'] ?? ''" class="reveal" />
                        <div class="prose-article reveal">{!! \App\Support\RichText::render($d['body'] ?? '') !!}</div>
                    </div>
                    @break

                @case('features')
                    <x-section-heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['heading'] ?? ''" :intro="$d['intro'] ?? null" class="reveal" />
                    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($d['items'] ?? [] as $item)
                            <div class="card reveal p-6">
                                <span class="icon-chip"><x-glyph :name="['check-circle','compass','shield-check','sliders'][$loop->index % 4]" /></span>
                                <h3 class="mt-5 text-lg font-bold text-ink">{{ $item['title'] ?? '' }}</h3>
                                <p class="mt-2 text-[0.9375rem] leading-relaxed">{{ $item['text'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('steps')
                    <x-section-heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['heading'] ?? ''" class="reveal" />
                    <ol class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($d['items'] ?? [] as $item)
                            <li class="reveal relative rounded-[var(--radius-card)] border border-line bg-white p-6">
                                <span class="display text-5xl text-orange">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="mt-3 text-lg font-bold text-ink">{{ $item['title'] ?? '' }}</h3>
                                <p class="mt-2 text-[0.9375rem] leading-relaxed">{{ $item['text'] ?? '' }}</p>
                            </li>
                        @endforeach
                    </ol>
                    @break

                @case('image_text')
                    <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                        <div class="reveal {{ ($d['image_side'] ?? 'left') === 'right' ? 'lg:order-2' : '' }}">
                            <x-picture :media="$media[$d['media_id'] ?? 0] ?? null" sizes="(min-width: 1024px) 600px, 100vw" class="aspect-[4/3] w-full rounded-[var(--radius-card)] object-cover" />
                        </div>
                        <div class="reveal">
                            <x-section-heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['heading'] ?? ''" />
                            <div class="prose-article mt-6">{!! \App\Support\RichText::render($d['body'] ?? '') !!}</div>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </section>
@endforeach
