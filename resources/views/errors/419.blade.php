<x-layouts.public title="Session expired" description="Your session has expired. Please refresh the page.">
    <section class="public-error">
        <div class="public-error-inner">
            <p class="public-error-code">419</p>
            <div class="public-error-icon"><x-icon name="refresh" /></div>
            <h1>Session expired</h1>
            <p>Your session has expired. Please refresh the page and try again.</p>
            <div class="public-error-actions">
                <a href="{{ url()->previous() ?? route('public.home') }}" class="btn btn-primary">Go back</a>
                <a href="{{ route('public.home') }}" class="btn btn-outline">Homepage</a>
            </div>
        </div>
    </section>
</x-layouts.public>