<x-layouts.public :title="__('Apply Now')"
    :description="__('Start your child\'s application to join our school community today.')"
>
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ __('Enrolment') }}</p>
            <h1>{{ __('Apply Now') }}</h1>
            <p class="public-lead">{{ __('Ready to start your child\'s journey? Submit an inquiry and our admissions team will guide you through every step.') }}</p>
        </div>
    </section>

    <section class="public-section">
        <div class="apply-layout">
            <div class="apply-form-card">
                <h2>{{ __('Start your application') }}</h2>
                <p class="apply-subtitle">{{ __('Fields marked with * are required.') }}</p>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any() && !session('success'))
                    <div class="alert alert-danger">{{ __('Please review the highlighted fields below and try again.') }}</div>
                @endif

                <form method="POST" action="{{ route('public.inquiry.store') }}" class="public-form">
                    @csrf
                    <input type="hidden" name="type" value="admissions">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name" class="form-label">{{ __('Parent / Guardian full name *') }}</label>
                            <input id="full_name" name="full_name" type="text" class="form-input" value="{{ old('full_name') }}" placeholder="{{ __('e.g. Daniel Tesfaye') }}" required>
                            @error('full_name')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">{{ __('Email *') }}</label>
                            <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" placeholder="you@example.com" required>
                            @error('email')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">{{ __('Phone') }}</label>
                            <input id="phone" name="phone" type="tel" class="form-input" value="{{ old('phone') }}" placeholder="+251 9xx xxx xxx">
                            @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="grade_level" class="form-label">{{ __('Grade applying for') }}</label>
                            <input id="grade_level" name="grade_level" type="text" class="form-input" value="{{ old('grade_level') }}" placeholder="{{ __('e.g. Grade 5, KG 2') }}">
                            @error('grade_level')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group form-group-full">
                            <label for="student_name" class="form-label">{{ __('Student full name') }}</label>
                            <input id="student_name" name="student_name" type="text" class="form-input" value="{{ old('student_name') }}" placeholder="{{ __('e.g. Liya Daniel') }}">
                            @error('student_name')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group form-group-full">
                            <label for="message" class="form-label">{{ __('Anything else you would like us to know?') }}</label>
                            <textarea id="message" name="message" rows="5" class="form-input">{{ old('message') }}</textarea>
                            @error('message')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">{{ __('Submit application inquiry') }}</button>
                </form>
            </div>

            <div class="apply-info">
                <div class="apply-info-card">
                    <h3>{{ __('What happens next?') }}</h3>
                    <ol class="apply-steps">
                        <li>{{ __('Our admissions team reviews your inquiry within one business day.') }}</li>
                        <li>{{ __('We schedule a friendly campus visit and assessment.') }}</li>
                        <li>{{ __('You receive an offer letter and complete enrolment.') }}</li>
                        <li>{{ __('Your child is welcomed into the class!') }}</li>
                    </ol>
                </div>
                <div class="apply-info-card">
                    <h3>{{ __('Need help?') }}</h3>
                    <p>{{ __('Contact our admissions team directly:') }}</p>
                    <ul class="apply-contact">
                        <li><x-icon name="phone" /> {{ \App\Domains\Settings\Models\Setting::value('school_phone', '+251 11 000 0000') }}</li>
                        <li><x-icon name="mail" /> {{ \App\Domains\Settings\Models\Setting::value('school_email', 'info@edusphere.com') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>