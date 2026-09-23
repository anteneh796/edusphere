<x-layouts.public title="Something went wrong" description="An unexpected error occurred. Please try again shortly.">
    <section class="public-error">
        <div class="public-error-inner">
            <p class="public-error-code">500</p>
            <div class="public-error-icon"><x-icon name="alert-triangle" /></div>
            <h1>Something went wrong</h1>
            <p>An unexpected error occurred while processing your request. Our team has been notified — please try again shortly.</p>
            <div class="public-error-actions">
                <a href="{{ route('public.home') }}" class="btn btn-primary">Back to homepage</a>
                <a href="{{ route('public.contact') }}" class="btn btn-outline">Contact us</a>
            </div>
        </div>
    </section>
</x-layouts.public>