@php($settings = app(\App\Services\SettingsService::class))

@if ($settings->announcementEnabled())
    @php($announcementLink = trim($settings->get('announcement_link')))

    <div class="bg-charcoal text-white">
        <div class="shell flex flex-wrap items-center justify-center gap-x-4 gap-y-1 py-2.5 text-center">
            <p class="text-[0.6875rem] uppercase tracking-[0.18em] text-white/85">
                {{ $settings->announcementText() }}
            </p>

            @if ($announcementLink !== '')
                <a
                    href="{{ url($announcementLink) }}"
                    class="inline-flex items-center gap-1.5 border-b border-white/35 pb-px text-[0.6875rem] uppercase tracking-[0.18em] text-white transition-colors duration-200 hover:border-white"
                >
                    Shop now
                    <span aria-hidden="true">&rarr;</span>
                </a>
            @endif
        </div>
    </div>
@endif
