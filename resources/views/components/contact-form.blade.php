{{-- Lead form. Server-validated (StoreLeadRequest), CSRF-protected, rate-limited, honeypot + timing spam traps. --}}
@props(['selectedService' => null, 'compact' => false, 'heading' => 'Request HVAC help'])
@php
    $services = \App\Models\Service::published()->ordered()->get(['id', 'title', 'slug']);
    $states = \App\Models\State::query()->orderBy('name')->get(['id', 'name']);
    $selectedId = old('service_id', $services->firstWhere('slug', $selectedService)?->id);
    $err = fn ($f) => $errors->has($f) ? 'true' : 'false';
@endphp
<form method="POST" action="{{ route('contact.store') }}" novalidate {{ $attributes->merge(['class' => 'card p-6 md:p-8']) }} aria-labelledby="lead-form-title">
    @csrf
    <input type="hidden" name="form_token" value="{{ \App\Http\Requests\StoreLeadRequest::token() }}">
    {{-- Honeypot: hidden from people and assistive tech; bots fill it. --}}
    <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
        <label for="company_website">Leave this field empty</label>
        <input type="text" id="company_website" name="company_website" tabindex="-1" autocomplete="off">
    </div>

    <h2 id="lead-form-title" class="text-[1.375rem] font-bold tracking-[-0.01em] text-ink">{{ $heading }}</h2>
    <p class="mt-1.5 text-[0.9375rem]">Tell us about your home system. Fields marked <span aria-hidden="true">*</span><span class="sr-only">with an asterisk</span> are required.</p>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-[#FECDCA] bg-[#FEF3F2] p-4 text-[0.9375rem] text-[#912018]" role="alert" tabindex="-1" x-data x-init="$el.focus()">
            <p class="font-semibold">Please check the highlighted fields.</p>
            @if ($errors->has('form_token') || $errors->has('company_website'))
                <p class="mt-1">{{ $errors->first('form_token') ?: $errors->first('company_website') }}</p>
            @endif
        </div>
    @endif

    <div class="mt-6 grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="service_id" class="field-label">What do you need help with?</label>
            <select id="service_id" name="service_id" class="input" aria-invalid="{{ $err('service_id') }}">
                <option value="">Not sure / something else</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected((string) $selectedId === (string) $service->id)>{{ $service->title }}</option>
                @endforeach
            </select>
            @error('service_id')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="name" class="field-label">Full name <span class="text-orange-ink" aria-hidden="true">*</span></label>
            <input id="name" name="name" type="text" autocomplete="name" required maxlength="100" value="{{ old('name') }}" class="input"
                   aria-invalid="{{ $err('name') }}" @error('name') aria-describedby="name-error" @enderror>
            @error('name')<p id="name-error" class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="phone" class="field-label">Phone <span class="text-orange-ink" aria-hidden="true">*</span></label>
            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" required maxlength="20" value="{{ old('phone') }}" class="input" placeholder="(555) 555-0123"
                   aria-invalid="{{ $err('phone') }}" @error('phone') aria-describedby="phone-error" @enderror>
            @error('phone')<p id="phone-error" class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="email" class="field-label">Email <span class="font-normal text-muted">(optional)</span></label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="150" value="{{ old('email') }}" class="input"
                   aria-invalid="{{ $err('email') }}" @error('email') aria-describedby="email-error" @enderror>
            @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="zip" class="field-label">ZIP code <span class="text-orange-ink" aria-hidden="true">*</span></label>
            <input id="zip" name="zip" type="text" inputmode="numeric" autocomplete="postal-code" required maxlength="5" pattern="\d{5}" value="{{ old('zip') }}" class="input"
                   aria-invalid="{{ $err('zip') }}" @error('zip') aria-describedby="zip-error" @enderror>
            @error('zip')<p id="zip-error" class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="state_id" class="field-label">State</label>
            <select id="state_id" name="state_id" class="input" autocomplete="address-level1" aria-invalid="{{ $err('state_id') }}">
                <option value="">Select a state</option>
                @foreach ($states as $state)
                    <option value="{{ $state->id }}" @selected((string) old('state_id') === (string) $state->id)>{{ $state->name }}</option>
                @endforeach
            </select>
        </div>
        @unless ($compact)
            <div class="sm:col-span-2">
                <label for="city_name" class="field-label">City <span class="font-normal text-muted">(optional)</span></label>
                <input id="city_name" name="city_name" type="text" autocomplete="address-level2" maxlength="100" value="{{ old('city_name') }}" class="input">
            </div>
        @endunless
        <div class="sm:col-span-2">
            <label for="message" class="field-label">Describe the problem <span class="font-normal text-muted">(optional)</span></label>
            <textarea id="message" name="message" rows="{{ $compact ? 3 : 4 }}" maxlength="2000" class="input min-h-28 resize-y" placeholder="For example: the AC runs but blows warm air, and there is ice on the copper line.">{{ old('message') }}</textarea>
            @error('message')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-start gap-3 text-[0.875rem] leading-relaxed">
                <input type="checkbox" name="consent" value="1" required @checked(old('consent')) class="mt-1 size-5 shrink-0 rounded border-[#9CA3AF] accent-teal-900"
                       aria-invalid="{{ $err('consent') }}" @error('consent') aria-describedby="consent-error" @enderror>
                <span>
                    {{ $site->get('form_privacy_notice') }}
                    <a href="{{ \App\Models\Page::urlFor('privacy', '/privacy-policy') }}" class="text-link">Privacy Policy</a> ·
                    <a href="{{ route('disclaimer') }}" class="text-link">Disclaimer</a>
                </span>
            </label>
            @error('consent')<p id="consent-error" class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-page px-4 py-3 text-[0.8125rem] leading-relaxed text-body">
        HVAC Services is an independent referral service. Providers are independent; HVAC Services does not perform the work.
        <a href="{{ route('disclaimer') }}" class="font-medium text-teal-900 underline underline-offset-2">Read the full disclaimer</a> before submitting.
    </div>

    <button type="submit" class="btn btn-primary btn-lg mt-6 w-full">Send my request <x-glyph name="arrow-right" /></button>
</form>
