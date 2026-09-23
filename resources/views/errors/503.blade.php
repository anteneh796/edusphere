<x-layouts.public title="Under maintenance" description="We are carrying out scheduled maintenance. Please check back shortly.">
    <section class="public-error">
        <div class="public-error-inner">
            <p class="public-error-code">503</p>
            <div class="public-error-icon"><x-icon name="tools" /></div>
            <h1>Under maintenance</h1>
            <p>We are carrying out scheduled maintenance right now. Please check back shortly — we appreciate your patience.</p>
            <div class="public-error-actions">
                <a href="{{ route('public.home') }}" class="btn btn-primary">Back to homepage</a>
            </div>
        </div>
    </section>
</x-layouts.public>