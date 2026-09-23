<x-layouts.public :title="__('Faculty & Staff')"
    description="{{ __('Meet the dedicated educators and staff who make our school a thriving community of learning.') }}"
>
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ __('Our People') }}</p>
            <h1>{{ __('Faculty & Staff') }}</h1>
            <p class="public-lead">{{ __('The dedicated educators and support team who bring our mission of academic excellence to life every day.') }}</p>
        </div>
    </section>

    <section class="public-section">
        <div class="public-section-head">
            <h2>{{ __('Teaching Faculty') }}</h2>
        </div>

        <div class="faculty-grid">
            @forelse ($faculty as $member)
                <article class="faculty-card">
                    <div class="faculty-avatar">
                        {{ str($member->full_name)->initials() }}
                    </div>
                    <h3 class="faculty-name">{{ $member->full_name }}</h3>
                    <p class="faculty-role">{{ $member->roles->pluck('name')->join(', ') }}</p>
                    @if ($member->email)
                        <a class="faculty-email" href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                    @endif
                </article>
            @empty
                <p class="public-empty">{{ __('Faculty profiles are being added. Check back soon.') }}</p>
            @endforelse
        </div>
    </section>
</x-layouts.public>
