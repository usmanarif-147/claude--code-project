<div>
    @include('resume.templates._styles')
    @include('resume.templates._format_overrides')

    {{-- ============ PAGE CHROME ============ --}}
    <div class="rb-page-header">
        <div>
            <h1 class="rb-page-title">Resume Builder</h1>
            <p class="rb-page-subtitle">
                Click <span class="accent">+</span> in any section to add details. Data is in-memory only and resets on page refresh.
            </p>
        </div>
        <div class="rb-header-actions">
            <button type="button" wire:click="loadSampleData" class="rb-secondary-btn" title="Populate all sections with sample data for testing">
                Load Sample Data
            </button>
            <button type="button" wire:click="downloadPdf" class="rb-download-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download PDF
            </button>
        </div>
    </div>

    {{-- ============ FORMATTING TOOLBAR (Phase 2C) ============ --}}
    @php $opts = $this->formattingOptions(); @endphp
    <div class="rb-toolbar">
        <div class="rb-toolbar-head">
            <span class="rb-toolbar-scope">Applies to: <strong>Entire Resume</strong></span>
            <button type="button" wire:click="resetFormatting" class="rb-reset-btn">Reset to defaults</button>
        </div>

        <div class="rb-toolbar-row">
            <label class="rb-toolbar-label">Font</label>
            <select wire:model.live="fontFamily" class="rb-select">
                @foreach ($opts['fontFamilies'] as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <label class="rb-toolbar-label">Size</label>
            <select wire:model.live="fontSize" class="rb-select">
                @foreach ($opts['fontSizes'] as $sz)
                    <option value="{{ $sz }}">{{ $sz }}</option>
                @endforeach
            </select>

            <button type="button" wire:click="$toggle('bold')" class="rb-bold-toggle {{ $bold ? 'rb-bold-toggle-active' : '' }}" title="Toggle Bold">
                <strong>B</strong>
            </button>

            <label class="rb-toolbar-label">Color</label>
            <div class="rb-color-row">
                @foreach ($opts['textColors'] as $key => $hex)
                    <button type="button" wire:click="$set('textColor', '{{ $key }}')"
                        class="rb-color-swatch {{ $textColor === $key ? 'rb-color-swatch-active' : '' }}"
                        style="background: {{ $hex }};"
                        title="{{ ucfirst($key) }}"></button>
                @endforeach
            </div>
        </div>

        <div class="rb-toolbar-row">
            <label class="rb-toolbar-label">Align</label>
            <div class="rb-align-row">
                @foreach ($opts['alignments'] as $a)
                    <button type="button" wire:click="$set('textAlign', '{{ $a }}')"
                        class="rb-align-btn {{ $textAlign === $a ? 'rb-align-btn-active' : '' }}"
                        title="Align {{ $a }}">
                        @switch($a)
                            @case('left') ⇤ @break
                            @case('center') ≡ @break
                            @case('right') ⇥ @break
                            @case('justify') ⇔ @break
                        @endswitch
                    </button>
                @endforeach
            </div>

            <label class="rb-toolbar-label">Line</label>
            <select wire:model.live="lineSpacing" class="rb-select">
                @foreach ($opts['lineSpacings'] as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <label class="rb-toolbar-label">Section</label>
            <select wire:model.live="sectionSpacing" class="rb-select">
                @foreach ($opts['sectionSpacings'] as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ============ LIVE PREVIEW (same partial used by PDF) ============ --}}
    @include('resume.templates._body', ['interactive' => true])

    {{-- ============ MODALS ============ --}}
    @if ($openModal !== null)
        @php
            // Count summary shown in the modal title (e.g. "Work Experience — 2 of 3 jobs").
            $countSummary = match ($openModal) {
                'work' => count($form['jobs'] ?? []) . ' of ' . $itemLimits['jobs'] . ' jobs',
                'projects' => count($form['projects'] ?? []) . ' of ' . $itemLimits['projects'] . ' projects',
                'skills' => count($form['groups'] ?? []) . ' of ' . $itemLimits['skill_groups'] . ' groups',
                'strengths' => count($form['items'] ?? []) . ' of ' . $itemLimits['strengths'] . ' items',
                'achievements' => count($form['items'] ?? []) . ' of ' . $itemLimits['achievements'] . ' items',
                'education' => count($form['entries'] ?? []) . ' of ' . $itemLimits['educations'] . ' entries',
                default => null,
            };
            $formInvalid = ! $this->isFormValid();
        @endphp

        <div class="rb-modal-overlay">
            <div class="rb-modal-panel">

                <div class="rb-modal-header">
                    <h3 class="rb-modal-title">
                        @switch($openModal)
                            @case('header') Header Details @break
                            @case('profile') Profile Summary @break
                            @case('work') Work Experience @break
                            @case('projects') Key Projects @break
                            @case('skills') Skills @break
                            @case('strengths') Strengths @break
                            @case('achievements') Key Achievements @break
                            @case('education') Education @break
                        @endswitch
                        @if ($countSummary)
                            <span style="color:#9ca3af; font-weight:400; font-size:12px; margin-left:8px; letter-spacing:1px;">— {{ $countSummary }}</span>
                        @endif
                    </h3>
                    <button type="button" wire:click="closeSection" class="rb-modal-close" title="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="rb-modal-body">

                    {{-- ===== HEADER MODAL ===== --}}
                    @if ($openModal === 'header')
                        <div class="rb-grid-2">
                            <div style="grid-column: span 2;">
                                <label class="rb-field-label">Full Name</label>
                                <input type="text" wire:model.live.debounce.250ms="form.name" maxlength="{{ $fieldLimits['name'][1] }}" class="rb-input">
                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['name'] ?? '', 'limit' => $fieldLimits['name']])
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="rb-field-label">Tagline</label>
                                <input type="text" wire:model.live.debounce.250ms="form.tagline" maxlength="{{ $fieldLimits['tagline'][1] }}" placeholder="e.g. Software Engineer | Laravel & Full-Stack" class="rb-input">
                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['tagline'] ?? '', 'limit' => $fieldLimits['tagline']])
                            </div>
                            <div>
                                <label class="rb-field-label">Phone</label>
                                <input type="text" wire:model.live.debounce.250ms="form.phone" maxlength="{{ $fieldLimits['phone'][1] }}" class="rb-input">
                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['phone'] ?? '', 'limit' => $fieldLimits['phone']])
                            </div>
                            <div>
                                <label class="rb-field-label">Email</label>
                                <input type="email" wire:model.live.debounce.250ms="form.email" maxlength="{{ $fieldLimits['email'][1] }}" class="rb-input">
                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['email'] ?? '', 'limit' => $fieldLimits['email']])
                            </div>
                            <div>
                                <label class="rb-field-label">Location</label>
                                <input type="text" wire:model.live.debounce.250ms="form.location" maxlength="{{ $fieldLimits['location'][1] }}" class="rb-input">
                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['location'] ?? '', 'limit' => $fieldLimits['location']])
                            </div>
                            <div>
                                <label class="rb-field-label">GitHub URL</label>
                                <input type="text" wire:model.live.debounce.250ms="form.github" maxlength="{{ $fieldLimits['github'][1] }}" class="rb-input">
                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['github'] ?? '', 'limit' => $fieldLimits['github']])
                            </div>
                        </div>
                    @endif

                    {{-- ===== PROFILE MODAL ===== --}}
                    @if ($openModal === 'profile')
                        <div>
                            <label class="rb-field-label">Summary</label>
                            <textarea wire:model.live.debounce.250ms="form.summary" rows="6" maxlength="{{ $fieldLimits['profile'][1] }}" placeholder="Software Engineer with X+ years of experience…" class="rb-textarea"></textarea>
                            @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $form['summary'] ?? '', 'limit' => $fieldLimits['profile']])
                        </div>
                    @endif

                    {{-- ===== WORK EXPERIENCE MODAL ===== --}}
                    @if ($openModal === 'work')
                        @php $jobsAtCap = count($form['jobs'] ?? []) >= $itemLimits['jobs']; @endphp
                        @foreach ($form['jobs'] ?? [] as $jobIndex => $job)
                            @php $bulletsAtCap = count($job['bullets'] ?? []) >= $itemLimits['job_bullets']; @endphp
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Job #{{ $jobIndex + 1 }}</h4>
                                    @if (count($form['jobs']) > 1)
                                        <button type="button" wire:click="removeRow('jobs', {{ $jobIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <div class="rb-grid-2">
                                    <div>
                                        <input type="text" placeholder="Company" wire:model.live.debounce.250ms="form.jobs.{{ $jobIndex }}.company" maxlength="{{ $fieldLimits['job.company'][1] }}" class="rb-input rb-input-sm">
                                        @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $job['company'] ?? '', 'limit' => $fieldLimits['job.company']])
                                    </div>
                                    <div>
                                        <input type="text" placeholder="Role" wire:model.live.debounce.250ms="form.jobs.{{ $jobIndex }}.role" maxlength="{{ $fieldLimits['job.role'][1] }}" class="rb-input rb-input-sm">
                                        @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $job['role'] ?? '', 'limit' => $fieldLimits['job.role']])
                                    </div>
                                    <div>
                                        <label class="rb-field-label-sm">Start</label>
                                        <input type="month" wire:model.live.debounce.250ms="form.jobs.{{ $jobIndex }}.start" class="rb-input rb-input-sm">
                                    </div>
                                    <div>
                                        <label class="rb-field-label-sm">End</label>
                                        <input type="month" wire:model.live.debounce.250ms="form.jobs.{{ $jobIndex }}.end" class="rb-input rb-input-sm" @if ($job['is_current'] ?? false) disabled @endif>
                                    </div>
                                </div>
                                <label class="rb-checkbox-label">
                                    <input type="checkbox" wire:model.live.debounce.250ms="form.jobs.{{ $jobIndex }}.is_current">
                                    Currently working here
                                </label>
                                <div>
                                    <label class="rb-field-label-sm">Bullets ({{ count($job['bullets'] ?? []) }} of {{ $itemLimits['job_bullets'] }})</label>
                                    @foreach ($job['bullets'] ?? [] as $bIndex => $b)
                                        <div class="rb-inline-row">
                                            <div style="flex:1;">
                                                <input type="text" wire:model.live.debounce.250ms="form.jobs.{{ $jobIndex }}.bullets.{{ $bIndex }}" maxlength="{{ $fieldLimits['job.bullet'][1] }}" class="rb-input rb-input-sm" placeholder="Bullet point">
                                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $b ?? '', 'limit' => $fieldLimits['job.bullet']])
                                            </div>
                                            <button type="button" wire:click="removeBulletFromJob({{ $jobIndex }}, {{ $bIndex }})" class="rb-icon-btn-x" title="Remove">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addBulletToJob({{ $jobIndex }})" class="rb-btn-link-blue rb-btn-link-blue-sm" @if ($bulletsAtCap) disabled @endif>+ add bullet</button>
                                    @if ($bulletsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['job_bullets'] }} reached</span>@endif
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <button type="button" wire:click="addRow('jobs')" class="rb-btn-link-blue rb-btn-link-blue-md" @if ($jobsAtCap) disabled @endif>+ add another job</button>
                            @if ($jobsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['jobs'] }} reached</span>@endif
                        </div>
                    @endif

                    {{-- ===== PROJECTS MODAL ===== --}}
                    @if ($openModal === 'projects')
                        @php $projectsAtCap = count($form['projects'] ?? []) >= $itemLimits['projects']; @endphp
                        @foreach ($form['projects'] ?? [] as $pIndex => $p)
                            @php $pBulletsAtCap = count($p['bullets'] ?? []) >= $itemLimits['project_bullets']; @endphp
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Project #{{ $pIndex + 1 }}</h4>
                                    @if (count($form['projects']) > 1)
                                        <button type="button" wire:click="removeRow('projects', {{ $pIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <div>
                                    <input type="text" placeholder="Project Title" wire:model.live.debounce.250ms="form.projects.{{ $pIndex }}.title" maxlength="{{ $fieldLimits['project.title'][1] }}" class="rb-input rb-input-sm">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $p['title'] ?? '', 'limit' => $fieldLimits['project.title']])
                                </div>
                                <div>
                                    <input type="text" placeholder="Subtitle (company / URL)" wire:model.live.debounce.250ms="form.projects.{{ $pIndex }}.subtitle" maxlength="{{ $fieldLimits['project.subtitle'][1] }}" class="rb-input rb-input-sm">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $p['subtitle'] ?? '', 'limit' => $fieldLimits['project.subtitle']])
                                </div>
                                <div>
                                    <label class="rb-field-label-sm">Bullets ({{ count($p['bullets'] ?? []) }} of {{ $itemLimits['project_bullets'] }})</label>
                                    @foreach ($p['bullets'] ?? [] as $bIndex => $b)
                                        <div class="rb-inline-row">
                                            <div style="flex:1;">
                                                <input type="text" wire:model.live.debounce.250ms="form.projects.{{ $pIndex }}.bullets.{{ $bIndex }}" maxlength="{{ $fieldLimits['project.bullet'][1] }}" class="rb-input rb-input-sm" placeholder="Bullet point">
                                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $b ?? '', 'limit' => $fieldLimits['project.bullet']])
                                            </div>
                                            <button type="button" wire:click="removeBulletFromProject({{ $pIndex }}, {{ $bIndex }})" class="rb-icon-btn-x" title="Remove">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addBulletToProject({{ $pIndex }})" class="rb-btn-link-blue rb-btn-link-blue-sm" @if ($pBulletsAtCap) disabled @endif>+ add bullet</button>
                                    @if ($pBulletsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['project_bullets'] }} reached</span>@endif
                                </div>
                                <div>
                                    <input type="text" placeholder="Tech stack (comma separated)" wire:model.live.debounce.250ms="form.projects.{{ $pIndex }}.tech" maxlength="{{ $fieldLimits['project.tech'][1] }}" class="rb-input rb-input-sm">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $p['tech'] ?? '', 'limit' => $fieldLimits['project.tech']])
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <button type="button" wire:click="addRow('projects')" class="rb-btn-link-blue rb-btn-link-blue-md" @if ($projectsAtCap) disabled @endif>+ add another project</button>
                            @if ($projectsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['projects'] }} reached</span>@endif
                        </div>
                    @endif

                    {{-- ===== SKILLS MODAL ===== --}}
                    @if ($openModal === 'skills')
                        @php $groupsAtCap = count($form['groups'] ?? []) >= $itemLimits['skill_groups']; @endphp
                        @foreach ($form['groups'] ?? [] as $gIndex => $group)
                            @php $tagsAtCap = count($group['tags'] ?? []) >= $itemLimits['skill_tags']; @endphp
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Group #{{ $gIndex + 1 }}</h4>
                                    @if (count($form['groups']) > 1)
                                        <button type="button" wire:click="removeRow('groups', {{ $gIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <div>
                                    <input type="text" placeholder="Category (e.g. Backend & Frontend)" wire:model.live.debounce.250ms="form.groups.{{ $gIndex }}.category" maxlength="{{ $fieldLimits['skill.category'][1] }}" class="rb-input rb-input-sm">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $group['category'] ?? '', 'limit' => $fieldLimits['skill.category']])
                                </div>
                                <div>
                                    <label class="rb-field-label-sm">Tags ({{ count($group['tags'] ?? []) }} of {{ $itemLimits['skill_tags'] }})</label>
                                    @foreach ($group['tags'] ?? [] as $tIndex => $t)
                                        <div class="rb-inline-row">
                                            <div style="flex:1;">
                                                <input type="text" wire:model.live.debounce.250ms="form.groups.{{ $gIndex }}.tags.{{ $tIndex }}" maxlength="{{ $fieldLimits['skill.tag'][1] }}" class="rb-input rb-input-sm" placeholder="Tag (e.g. Laravel)">
                                                @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $t ?? '', 'limit' => $fieldLimits['skill.tag']])
                                            </div>
                                            <button type="button" wire:click="removeTagFromGroup({{ $gIndex }}, {{ $tIndex }})" class="rb-icon-btn-x" title="Remove">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addTagToGroup({{ $gIndex }})" class="rb-btn-link-blue rb-btn-link-blue-sm" @if ($tagsAtCap) disabled @endif>+ add tag</button>
                                    @if ($tagsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['skill_tags'] }} reached</span>@endif
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <button type="button" wire:click="addRow('groups')" class="rb-btn-link-blue rb-btn-link-blue-md" @if ($groupsAtCap) disabled @endif>+ add another category</button>
                            @if ($groupsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['skill_groups'] }} reached</span>@endif
                        </div>
                    @endif

                    {{-- ===== STRENGTHS MODAL ===== --}}
                    @if ($openModal === 'strengths')
                        @php $strengthsAtCap = count($form['items'] ?? []) >= $itemLimits['strengths']; @endphp
                        @foreach ($form['items'] ?? [] as $iIndex => $item)
                            <div class="rb-inline-row">
                                <div style="flex:1;">
                                    <input type="text" wire:model.live.debounce.250ms="form.items.{{ $iIndex }}" maxlength="{{ $fieldLimits['strength'][1] }}" class="rb-input rb-input-sm" placeholder="Strength (e.g. API Design)">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $item ?? '', 'limit' => $fieldLimits['strength']])
                                </div>
                                @if (count($form['items']) > 1)
                                    <button type="button" wire:click="removeRow('items', {{ $iIndex }})" class="rb-icon-btn-x" title="Remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        <div>
                            <button type="button" wire:click="addRow('items')" class="rb-btn-link-blue rb-btn-link-blue-md" @if ($strengthsAtCap) disabled @endif>+ add strength</button>
                            @if ($strengthsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['strengths'] }} reached</span>@endif
                        </div>
                    @endif

                    {{-- ===== ACHIEVEMENTS MODAL ===== --}}
                    @if ($openModal === 'achievements')
                        @php $achievementsAtCap = count($form['items'] ?? []) >= $itemLimits['achievements']; @endphp
                        @foreach ($form['items'] ?? [] as $iIndex => $item)
                            <div class="rb-inline-row">
                                <div style="flex:1;">
                                    <input type="text" wire:model.live.debounce.250ms="form.items.{{ $iIndex }}" maxlength="{{ $fieldLimits['achievement'][1] }}" class="rb-input rb-input-sm" placeholder="Achievement bullet">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $item ?? '', 'limit' => $fieldLimits['achievement']])
                                </div>
                                @if (count($form['items']) > 1)
                                    <button type="button" wire:click="removeRow('items', {{ $iIndex }})" class="rb-icon-btn-x" title="Remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        <div>
                            <button type="button" wire:click="addRow('items')" class="rb-btn-link-blue rb-btn-link-blue-md" @if ($achievementsAtCap) disabled @endif>+ add achievement</button>
                            @if ($achievementsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['achievements'] }} reached</span>@endif
                        </div>
                    @endif

                    {{-- ===== EDUCATION MODAL ===== --}}
                    @if ($openModal === 'education')
                        @php $educationsAtCap = count($form['entries'] ?? []) >= $itemLimits['educations']; @endphp
                        @foreach ($form['entries'] ?? [] as $eIndex => $entry)
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Entry #{{ $eIndex + 1 }}</h4>
                                    @if (count($form['entries']) > 1)
                                        <button type="button" wire:click="removeRow('entries', {{ $eIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <div>
                                    <input type="text" placeholder="Degree (e.g. B.S. Software Engineering)" wire:model.live.debounce.250ms="form.entries.{{ $eIndex }}.degree" maxlength="{{ $fieldLimits['education.degree'][1] }}" class="rb-input rb-input-sm">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $entry['degree'] ?? '', 'limit' => $fieldLimits['education.degree']])
                                </div>
                                <div>
                                    <input type="text" placeholder="Institution" wire:model.live.debounce.250ms="form.entries.{{ $eIndex }}.institution" maxlength="{{ $fieldLimits['education.institution'][1] }}" class="rb-input rb-input-sm">
                                    @include('livewire.admin.portfolio.resume-builder._counter', ['value' => $entry['institution'] ?? '', 'limit' => $fieldLimits['education.institution']])
                                </div>
                                <div class="rb-grid-2">
                                    <div>
                                        <label class="rb-field-label-sm">Start</label>
                                        <input type="month" wire:model.live.debounce.250ms="form.entries.{{ $eIndex }}.start" class="rb-input rb-input-sm">
                                    </div>
                                    <div>
                                        <label class="rb-field-label-sm">End</label>
                                        <input type="month" wire:model.live.debounce.250ms="form.entries.{{ $eIndex }}.end" class="rb-input rb-input-sm">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <button type="button" wire:click="addRow('entries')" class="rb-btn-link-blue rb-btn-link-blue-md" @if ($educationsAtCap) disabled @endif>+ add another</button>
                            @if ($educationsAtCap)<span class="rb-cap-hint">max {{ $itemLimits['educations'] }} reached</span>@endif
                        </div>
                    @endif

                </div>

                <div class="rb-modal-footer">
                    @if ($formInvalid)
                        <div class="rb-modal-warning">Some fields exceed the word limit. Trim them to save.</div>
                    @endif
                    <button type="button" wire:click="closeSection" class="rb-btn-secondary">Cancel</button>
                    <button type="button" wire:click="save" class="rb-btn-primary" @if ($formInvalid) disabled @endif>Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
