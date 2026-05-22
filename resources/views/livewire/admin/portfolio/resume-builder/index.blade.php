<div>
    @include('resume.templates._styles')

    {{-- ============ PAGE CHROME ============ --}}
    <div class="rb-page-header">
        <div>
            <h1 class="rb-page-title">Resume Builder</h1>
            <p class="rb-page-subtitle">
                Click <span class="accent">+</span> in any section to add details. Data is in-memory only and resets on page refresh.
            </p>
        </div>
        <button type="button" wire:click="downloadPdf" class="rb-download-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download PDF
        </button>
    </div>

    {{-- ============ LIVE PREVIEW (same partial used by PDF) ============ --}}
    @include('resume.templates._body', ['interactive' => true])

    {{-- ============ MODALS ============ --}}
    @if ($openModal !== null)
        <div class="rb-modal-overlay" wire:click.self="closeSection">
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
                                <input type="text" wire:model="form.name" class="rb-input">
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="rb-field-label">Tagline</label>
                                <input type="text" wire:model="form.tagline" placeholder="e.g. Software Engineer | Laravel & Full-Stack" class="rb-input">
                            </div>
                            <div>
                                <label class="rb-field-label">Phone</label>
                                <input type="text" wire:model="form.phone" class="rb-input">
                            </div>
                            <div>
                                <label class="rb-field-label">Email</label>
                                <input type="email" wire:model="form.email" class="rb-input">
                            </div>
                            <div>
                                <label class="rb-field-label">Location</label>
                                <input type="text" wire:model="form.location" class="rb-input">
                            </div>
                            <div>
                                <label class="rb-field-label">GitHub URL</label>
                                <input type="text" wire:model="form.github" class="rb-input">
                            </div>
                        </div>
                    @endif

                    {{-- ===== PROFILE MODAL ===== --}}
                    @if ($openModal === 'profile')
                        <div>
                            <label class="rb-field-label">Summary</label>
                            <textarea wire:model="form.summary" rows="6" placeholder="Software Engineer with X+ years of experience…" class="rb-textarea"></textarea>
                        </div>
                    @endif

                    {{-- ===== WORK EXPERIENCE MODAL ===== --}}
                    @if ($openModal === 'work')
                        @foreach ($form['jobs'] ?? [] as $jobIndex => $job)
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Job #{{ $jobIndex + 1 }}</h4>
                                    @if (count($form['jobs']) > 1)
                                        <button type="button" wire:click="removeRow('jobs', {{ $jobIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <div class="rb-grid-2">
                                    <input type="text" placeholder="Company" wire:model="form.jobs.{{ $jobIndex }}.company" class="rb-input rb-input-sm">
                                    <input type="text" placeholder="Role" wire:model="form.jobs.{{ $jobIndex }}.role" class="rb-input rb-input-sm">
                                    <input type="text" placeholder="Start (e.g. 2022)" wire:model="form.jobs.{{ $jobIndex }}.start" class="rb-input rb-input-sm">
                                    <input type="text" placeholder="End (e.g. Aug 2025)" wire:model="form.jobs.{{ $jobIndex }}.end" class="rb-input rb-input-sm" @if ($job['is_current'] ?? false) disabled @endif>
                                </div>
                                <label class="rb-checkbox-label">
                                    <input type="checkbox" wire:model="form.jobs.{{ $jobIndex }}.is_current">
                                    Currently working here
                                </label>
                                <div>
                                    <label class="rb-field-label-sm">Bullets</label>
                                    @foreach ($job['bullets'] ?? [] as $bIndex => $b)
                                        <div class="rb-inline-row">
                                            <input type="text" wire:model="form.jobs.{{ $jobIndex }}.bullets.{{ $bIndex }}" class="rb-input rb-input-sm" placeholder="Bullet point">
                                            <button type="button" wire:click="removeBulletFromJob({{ $jobIndex }}, {{ $bIndex }})" class="rb-icon-btn-x" title="Remove">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addBulletToJob({{ $jobIndex }})" class="rb-btn-link-blue rb-btn-link-blue-sm">+ add bullet</button>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('jobs')" class="rb-btn-link-blue rb-btn-link-blue-md">+ add another job</button>
                    @endif

                    {{-- ===== PROJECTS MODAL ===== --}}
                    @if ($openModal === 'projects')
                        @foreach ($form['projects'] ?? [] as $pIndex => $p)
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Project #{{ $pIndex + 1 }}</h4>
                                    @if (count($form['projects']) > 1)
                                        <button type="button" wire:click="removeRow('projects', {{ $pIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <input type="text" placeholder="Project Title" wire:model="form.projects.{{ $pIndex }}.title" class="rb-input rb-input-sm">
                                <input type="text" placeholder="Subtitle (company / URL)" wire:model="form.projects.{{ $pIndex }}.subtitle" class="rb-input rb-input-sm">
                                <div>
                                    <label class="rb-field-label-sm">Bullets</label>
                                    @foreach ($p['bullets'] ?? [] as $bIndex => $b)
                                        <div class="rb-inline-row">
                                            <input type="text" wire:model="form.projects.{{ $pIndex }}.bullets.{{ $bIndex }}" class="rb-input rb-input-sm" placeholder="Bullet point">
                                            <button type="button" wire:click="removeBulletFromProject({{ $pIndex }}, {{ $bIndex }})" class="rb-icon-btn-x" title="Remove">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addBulletToProject({{ $pIndex }})" class="rb-btn-link-blue rb-btn-link-blue-sm">+ add bullet</button>
                                </div>
                                <input type="text" placeholder="Tech stack (comma separated)" wire:model="form.projects.{{ $pIndex }}.tech" class="rb-input rb-input-sm">
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('projects')" class="rb-btn-link-blue rb-btn-link-blue-md">+ add another project</button>
                    @endif

                    {{-- ===== SKILLS MODAL ===== --}}
                    @if ($openModal === 'skills')
                        @foreach ($form['groups'] ?? [] as $gIndex => $group)
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Group #{{ $gIndex + 1 }}</h4>
                                    @if (count($form['groups']) > 1)
                                        <button type="button" wire:click="removeRow('groups', {{ $gIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <input type="text" placeholder="Category (e.g. Backend & Frontend)" wire:model="form.groups.{{ $gIndex }}.category" class="rb-input rb-input-sm">
                                <div>
                                    <label class="rb-field-label-sm">Tags</label>
                                    @foreach ($group['tags'] ?? [] as $tIndex => $t)
                                        <div class="rb-inline-row">
                                            <input type="text" wire:model="form.groups.{{ $gIndex }}.tags.{{ $tIndex }}" class="rb-input rb-input-sm" placeholder="Tag (e.g. Laravel)">
                                            <button type="button" wire:click="removeTagFromGroup({{ $gIndex }}, {{ $tIndex }})" class="rb-icon-btn-x" title="Remove">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addTagToGroup({{ $gIndex }})" class="rb-btn-link-blue rb-btn-link-blue-sm">+ add tag</button>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('groups')" class="rb-btn-link-blue rb-btn-link-blue-md">+ add another category</button>
                    @endif

                    {{-- ===== STRENGTHS MODAL ===== --}}
                    @if ($openModal === 'strengths')
                        @foreach ($form['items'] ?? [] as $iIndex => $item)
                            <div class="rb-inline-row">
                                <input type="text" wire:model="form.items.{{ $iIndex }}" class="rb-input rb-input-sm" placeholder="Strength (e.g. API Design)">
                                @if (count($form['items']) > 1)
                                    <button type="button" wire:click="removeRow('items', {{ $iIndex }})" class="rb-icon-btn-x" title="Remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('items')" class="rb-btn-link-blue rb-btn-link-blue-md">+ add strength</button>
                    @endif

                    {{-- ===== ACHIEVEMENTS MODAL ===== --}}
                    @if ($openModal === 'achievements')
                        @foreach ($form['items'] ?? [] as $iIndex => $item)
                            <div class="rb-inline-row">
                                <input type="text" wire:model="form.items.{{ $iIndex }}" class="rb-input rb-input-sm" placeholder="Achievement bullet">
                                @if (count($form['items']) > 1)
                                    <button type="button" wire:click="removeRow('items', {{ $iIndex }})" class="rb-icon-btn-x" title="Remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('items')" class="rb-btn-link-blue rb-btn-link-blue-md">+ add achievement</button>
                    @endif

                    {{-- ===== EDUCATION MODAL ===== --}}
                    @if ($openModal === 'education')
                        @foreach ($form['entries'] ?? [] as $eIndex => $entry)
                            <div class="rb-row-box">
                                <div class="rb-row-head">
                                    <h4>Entry #{{ $eIndex + 1 }}</h4>
                                    @if (count($form['entries']) > 1)
                                        <button type="button" wire:click="removeRow('entries', {{ $eIndex }})" class="rb-btn-link-red">Remove</button>
                                    @endif
                                </div>
                                <input type="text" placeholder="Degree (e.g. B.S. Software Engineering)" wire:model="form.entries.{{ $eIndex }}.degree" class="rb-input rb-input-sm">
                                <input type="text" placeholder="Institution" wire:model="form.entries.{{ $eIndex }}.institution" class="rb-input rb-input-sm">
                                <div class="rb-grid-2">
                                    <input type="text" placeholder="Start year" wire:model="form.entries.{{ $eIndex }}.start" class="rb-input rb-input-sm">
                                    <input type="text" placeholder="End year" wire:model="form.entries.{{ $eIndex }}.end" class="rb-input rb-input-sm">
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('entries')" class="rb-btn-link-blue rb-btn-link-blue-md">+ add another</button>
                    @endif

                </div>

                <div class="rb-modal-footer">
                    <button type="button" wire:click="closeSection" class="rb-btn-secondary">Cancel</button>
                    <button type="button" wire:click="save" class="rb-btn-primary">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
