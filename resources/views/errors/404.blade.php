<x-layouts.public title="Page not found" description="The page you are looking for does not exist or has been moved.">
    <section class="public-error">
        <div class="public-error-inner">
            <p class="public-error-code">404</p>
            <div class="public-error-icon"><x-icon name="compass" /></div>
            <h1>Page not found</h1>
            <p>The page you are looking for does not exist or has been moved. Let's get you back on track.</p>
            <div class="public-error-actions">
                <a href="{{ route('public.home') }}" class="btn btn-primary">Back to homepage</a>
                <a href="{{ route('public.contact') }}" class="btn btn-outline">Contact us</a>
            </div>
        </div>
    </section>
</x-layouts.public>