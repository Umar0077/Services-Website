{{-- Accessible accordion: real <button>s with aria-expanded/aria-controls; answers are in the HTML for search engines. --}}
@props(['items' => [], 'id' => 'faq'])
@if (count($items))
    <div x-data="{ open: 0 }" class="divide-y divide-line overflow-hidden rounded-[var(--radius-card)] border border-line bg-white" {{ $attributes }}>
        @foreach ($items as $i => $faq)
            <div>
                <h3>
                    <button type="button" id="{{ $id }}-q{{ $i }}" aria-controls="{{ $id }}-a{{ $i }}"
                            :aria-expanded="(open === {{ $i }}).toString()" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}"
                            @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="flex w-full items-start justify-between gap-6 px-5 py-5 text-left text-[1.0625rem] font-semibold text-ink transition-colors hover:bg-page md:px-7">
                        <span>{{ $faq['question'] }}</span>
                        <span class="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-line transition-all duration-300"
                              :class="open === {{ $i }} ? 'rotate-45 border-orange bg-orange text-teal-900' : 'text-teal-700'">
                            <x-glyph name="plus" class="size-4" />
                        </span>
                    </button>
                </h3>
                <div id="{{ $id }}-a{{ $i }}" role="region" aria-labelledby="{{ $id }}-q{{ $i }}"
                     x-show="open === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif>
                    <div class="px-5 pb-6 leading-relaxed md:px-7 md:pr-20">{{ $faq['answer'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
