<x-layouts.app :title="__('Register student')">
    <x-breadcrumb :items="[
        ['label' => __('Students'), 'url' => route('students.index')],
        ['label' => __('Register student')],
    ]" />

    <x-page-header :title="__('Register student')" :description="__('Six-step student registration for the KG through Grade 8 SIS.')" />

    <div x-data="registrationWizard()" x-init="init()" style="max-width:1200px;">
        <div class="card p-3 mb-3">
            <div class="flex gap-1 flex-wrap" style="justify-content:space-between;">
                <template x-for="item in steps" :key="item.number">
                    <button type="button" class="btn btn-ghost" :class="{ 'text-primary': step === item.number }" @click="go(item.number)">
                        <span class="code-chip" x-text="item.number"></span> <span x-text="item.label"></span>
                    </button>
                </template>
            </div>
            <div style="height:6px;background:var(--color-surface-muted);border-radius:999px;overflow:hidden;margin-top:var(--space-2);">
                <div style="height:100%;background:var(--color-primary);transition:width .2s;" :style="{width: progress + '%'}"></div>
            </div>
        </div>

        <form method="POST" action="{{ route('students.store') }}" novalidate enctype="multipart/form-data" @submit="beforeSubmit">
            @csrf
            <x-card>
                @include('students._form', ['currentYearName' => $currentYearName ?? AppDomainsAcademicsModelsAcademicYear::current()->first()?->name])

                <section x-show="step === 4" x-cloak>
                    <h3 class="section-title">{{ __('Emergency contact') }}</h3>
                    <p class="text-sm text-muted mb-3">{{ __('Optional now, but keeping an emergency contact on the student record is recommended.') }}</p>
                    <div class="grid" style="grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-2);">
                        <x-input name="emergency_contact[name]" label="Full name" placeholder="e.g. Alem Berhanu" />
                        <x-input name="emergency_contact[relationship]" label="Relationship" placeholder="e.g. aunt" />
                        <x-input name="emergency_contact[phone]" label="Phone" placeholder="+251 9xx xxx xxx" />
                        <x-input name="emergency_contact[priority]" type="number" label="Priority" value="1" min="1" max="99" />
                        <x-input name="emergency_contact[notes]" label="Notes" placeholder="Optional instructions" />
                        <label class="form-label" style="display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="emergency_contact[authorized_pickup]" value="1" />
                            {{ __('Authorized to pick up the student') }}
                        </label>
                    </div>
                </section>

                <section x-show="step === 5" x-cloak>
                    <h3 class="section-title">{{ __('Review') }}</h3>
                    <div class="kv-list">
                        <div class="kv"><span>Student</span><strong x-text="value('first_name') + ' ' + value('last_name')"></strong></div>
                        <div class="kv"><span>Gender</span><strong x-text="value('gender')"></strong></div>
                        <div class="kv"><span>Date of birth</span><strong x-text="value('date_of_birth')"></strong></div>
                        <div class="kv"><span>Grade</span><strong x-text="selectedText('grade_level_id')"></strong></div>
                        <div class="kv"><span>Class</span><strong x-text="selectedText('class_room_id')"></strong></div>
                        <div class="kv"><span>Parent</span><strong x-text="value('guardian[first_name]') + ' ' + value('guardian[last_name]')"></strong></div>
                        <div class="kv"><span>Parent phone</span><strong x-text="value('guardian[phone]') || '—'"></strong></div>
                        <div class="kv"><span>Emergency contact</span><strong x-text="value('emergency_contact[name]') || 'Not provided'"></strong></div>
                    </div>
                </section>

                <section x-show="step === 6" x-cloak>
                    <div class="text-center" style="padding:var(--space-6) var(--space-3);">
                        <x-icon name="check-circle" class="icon-xl" />
                        <h2 class="h3 mt-2">{{ __('Ready to register') }}</h2>
                        <p class="text-sm text-muted" style="max-width:620px;margin:0 auto;">{{ __('Submitting will create the permanent student profile, current-year enrollment, parent relationship, optional emergency contact, and timeline entry. The student number is generated automatically.') }}</p>
                    </div>
                </section>

                <div class="card-footer flex" style="justify-content:space-between;align-items:center;">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <div class="flex gap-1">
                        <button type="button" class="btn btn-secondary" x-show="step > 1" x-cloak @click="step--; sync()">{{ __('Back') }}</button>
                        <button type="button" class="btn btn-primary" x-show="step < 6" @click="next()">{{ __('Continue') }}</button>
                        <button type="submit" class="btn btn-primary" x-show="step === 6" x-cloak>
                            <x-icon name="check" class="icon-sm" /> {{ __('Register student') }}
                        </button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        function registrationWizard() {
            return {
                step: 1,
                steps: [
                    {number:1,label:'Personal'},
                    {number:2,label:'Placement'},
                    {number:3,label:'Parent'},
                    {number:4,label:'Emergency'},
                    {number:5,label:'Review'},
                    {number:6,label:'Confirm'}
                ],
                get progress() { return Math.round((this.step / 6) * 100); },
                root: null,
                init() {
                    this.root = this.$el.querySelector('[data-student-form]');
                    this.sync();
                },
                sync() {
                    if (!this.root) return;
                    const children = Array.from(this.root.children);
                    const headings = children.map((el,i) => el.classList.contains('section-title') ? i : -1).filter(i => i >= 0);
                    headings.forEach((start, idx) => {
                        const end = headings[idx + 1] ?? children.length;
                        children.slice(start, end).forEach(el => el.style.display = ((idx + 1) === this.step) ? '' : 'none');
                    });
                },
                validCurrent() {
                    if (!this.root || this.step > 3) return true;
                    const children = Array.from(this.root.children);
                    const headings = children.map((el,i) => el.classList.contains('section-title') ? i : -1).filter(i => i >= 0);
                    const start = headings[this.step - 1];
                    const end = headings[this.step] ?? children.length;
                    const fields = children.slice(start, end).flatMap(el => Array.from(el.querySelectorAll('input,select,textarea')));
                    for (const field of fields) {
                        if (!field.checkValidity()) { field.reportValidity(); return false; }
                    }
                    return true;
                },
                next() {
                    if (!this.validCurrent()) return;
                    if (this.step < 6) { this.step++; this.sync(); window.scrollTo({top:0,behavior:'smooth'}); }
                },
                go(target) {
                    if (target > this.step && !this.validCurrent()) return;
                    this.step = target; this.sync();
                },
                value(name) {
                    const el = this.$el.querySelector('[name="' + name + '"]');
                    return el?.value || '';
                },
                selectedText(name) {
                    const el = this.$el.querySelector('[name="' + name + '"]');
                    return el?.selectedOptions?.[0]?.text || '—';
                },
                beforeSubmit() { this.step = 6; }
            }
        }
        window.registrationWizard = registrationWizard;
    </script>
</x-layouts.app>