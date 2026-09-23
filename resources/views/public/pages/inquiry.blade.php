<x-layouts.public :title="__('Inquire')"
    description="{{ __('Questions about admissions, enrollment, or visiting our school? Send us an inquiry and our team will get back to you.') }}"
>
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ __('Admissions & General') }}</p>
            <h1>{{ __('Send an Inquiry') }}</h1>
            <p class="public-lead">{{ __('Curious about our programs, admissions, or visiting day? Tell us a little about yourself and our team will follow up shortly.') }}</p>
        </div>
    </section>

    <section class="public-section">
        <div class="inquiry-layout">
            <div class="inquiry-info">
                <h2>{{ __('We\'re here to help') }}</h2>
                <p>{{ __('Our admissions team responds to every inquiry — usually within one business day. You can also reach us directly using the contact details below.') }}</p>

                <ul class="inquiry-contact">
                    <li>
                        <x-icon name="phone" class="icon-sm" />
                        <span>{{ \App\Domains\Settings\Models\Setting::value('school_phone', '+251 11 000 0000') }}</span>
                    </li>
                    <li>
                        <x-icon name="mail" class="icon-sm" />
                        <span>{{ \App\Domains\Settings\Models\Setting::value('school_email', 'info@edusphere.com') }}</span>
                    </li>
                    <li>
                        <x-icon name="map-pin" class="icon-sm" />
                        <span>{{ \App\Domains\Settings\Models\Setting::value('school_address', 'Addis Ababa, Ethiopia') }}</span>
                    </li>
                </ul>
            </div>

            <div class="inquiry-card">
                @if (session('success'))
                    <x-alert type="success" dismissible>
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if ($errors->any() && ! session('success'))
                    <x-alert type="danger">
                        {{ __('Please review the highlighted fields below and try again.') }}
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('public.inquiry.store') }}" class="form-stack">
                    @csrf

                    <x-select
                        name="type"
                        :label="__('Inquiry type')"
                        :required="true"
                        :options="[
                            'admissions' => __('Admissions'),
                            'general' => __('General question'),
                            'visit' => __('Book a school visit'),
                        ]"
                        :value="old('type', 'admissions')"
                    />

                    <div class="form-row">
                        <x-input name="full_name" :label="__('Full name')" :required="true" :value="old('full_name')" :placeholder="__('e.g. Sara Alemu')" />
                        <x-input name="email" type="email" :label="__('Email')" :required="true" :value="old('email')" placeholder="you@example.com" />
                    </div>

                    <div class="form-row">
                        <x-input name="phone" :label="__('Phone (optional)')" :value="old('phone')" placeholder="+251 9xx xxx xxx" />
                        <x-input name="grade_level" :label="__('Grade level (optional)')" :value="old('grade_level')" :placeholder="__('e.g. Grade 7, KG 2')" />
                    </div>

                    <x-input name="student_name" :label="__('Student name (optional)')" :value="old('student_name')" :placeholder="__('e.g. Liya Bekele')" />

                    <x-textarea name="message" :label="__('Message (optional)')" :rows="5" :value="old('message')" :placeholder="__('Tell us more about your question...')" />

                    <button type="submit" class="btn btn-primary btn-block">
                        <x-icon name="send" class="icon-sm" />
                        {{ __('Submit inquiry') }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>