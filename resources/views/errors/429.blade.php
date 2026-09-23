<x-layouts.public title="Too many requests" description="You have made too many requests in a short time.">
    <section class="public-error">
        <div class="public-error-inner">
            <p class="public-error-code">429</p>
            <div class="public-error-icon"><x-icon name="clock" /></div>
            <h1>Too many requests</h1>
            <p>You have made too many requests in a short time. Please wait a moment and try again.</p>
            <div class="public-error-actions">
                <a href="{{ route('public.home') }}" class="btn btn-primary">Back to homepage</a>
            </div>
        </div>
    </section>
</x-layouts.public>