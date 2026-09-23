<x-layouts.public title="Access denied" description="You do not have permission to access this area.">
    <section class="public-error">
        <div class="public-error-inner">
            <p class="public-error-code">403</p>
            <div class="public-error-icon"><x-icon name="shield" /></div>
            <h1>Access denied</h1>
            <p>You do not have permission to access this area. If you believe this is a mistake, please contact the school office.</p>
            <div class="public-error-actions">
                <a href="{{ route('public.home') }}" class="btn btn-primary">Back to homepage</a>
                <a href="{{ route('auth.login') }}" class="btn btn-outline">Portal login</a>
            </div>
        </div>
    </section>
</x-layouts.public>