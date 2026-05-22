<div class="space-y-6">
    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Resume Builder</h1>
            <p class="text-sm text-gray-400 mt-1">
                Click <span class="text-primary-light">+</span> in any section to add details. Data is in-memory only and resets on page refresh.
            </p>
        </div>
    </div>

    {{-- Paper preview --}}
    <div class="bg-white text-slate-900 rounded-xl shadow-2xl p-10 max-w-5xl mx-auto font-sans">

        {{-- HEADER SECTION --}}
        @php $hasHeader = !empty($header['name'] ?? '') || !empty($header['tagline'] ?? '') || !empty($header['email'] ?? '') || !empty($header['phone'] ?? ''); @endphp
        <div class="relative pb-5 border-b-2 border-slate-200 mb-6 group">
            <button wire:click="openSection('header')"
                class="absolute top-0 right-0 flex items-center justify-center w-9 h-9 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-md transition-all cursor-pointer"
                title="{{ $hasHeader ? 'Edit Header' : 'Add Header' }}">
                @if ($hasHeader)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                @endif
            </button>

            @if ($hasHeader)
                <h2 class="text-4xl font-extrabold text-blue-700 tracking-tight">{{ strtoupper($header['name'] ?? '') }}</h2>
                @if (!empty($header['tagline'] ?? ''))
                    <p class="text-sm font-semibold text-slate-700 mt-1 uppercase tracking-widest">{{ $header['tagline'] }}</p>
                @endif
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-600 mt-3">
                    @if (!empty($header['phone'] ?? ''))<span>✆ {{ $header['phone'] }}</span>@endif
                    @if (!empty($header['email'] ?? ''))<span>✉ {{ $header['email'] }}</span>@endif
                    @if (!empty($header['location'] ?? ''))<span>📍 {{ $header['location'] }}</span>@endif
                    @if (!empty($header['github'] ?? ''))<span>⌂ {{ $header['github'] }}</span>@endif
                </div>
            @else
                <div class="py-3 text-slate-400 text-sm italic">Header — click + to add your name, tagline, phone, email, location, and GitHub URL.</div>
            @endif
        </div>

        {{-- Two-column body --}}
        <div class="grid grid-cols-12 gap-8">

            {{-- LEFT COLUMN --}}
            <div class="col-span-12 md:col-span-7 space-y-6">

                {{-- PROFILE --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'PROFILE', 'section' => 'profile', 'hasData' => $profile !== ''])
                    @if ($profile !== '')
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $profile }}</p>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add a short profile summary paragraph.</div>
                    @endif
                </section>

                {{-- WORK EXPERIENCE --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'WORK EXPERIENCE', 'section' => 'work', 'hasData' => count($experiences) > 0])
                    @if (count($experiences) > 0)
                        <div class="space-y-4">
                            @foreach ($experiences as $job)
                                <div>
                                    <div class="flex items-baseline justify-between">
                                        <h4 class="text-sm font-bold text-slate-900">{{ $job['company'] ?? '' }}</h4>
                                        <span class="text-xs italic text-slate-500">{{ $job['start'] ?? '' }}{{ ($job['start'] ?? '') || ($job['end'] ?? '') ? ' – ' : '' }}{{ ($job['is_current'] ?? false) ? 'Present' : ($job['end'] ?? '') }}</span>
                                    </div>
                                    <p class="text-xs text-blue-700 font-medium mb-1">{{ $job['role'] ?? '' }}</p>
                                    @if (!empty($job['bullets']))
                                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                                            @foreach ($job['bullets'] as $b)
                                                @if (trim((string) $b) !== '')
                                                    <li>{{ $b }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add companies, roles, dates, and bullet points.</div>
                    @endif
                </section>

                {{-- KEY PROJECTS --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'KEY PROJECTS', 'section' => 'projects', 'hasData' => count($projects) > 0])
                    @if (count($projects) > 0)
                        <div class="space-y-4">
                            @foreach ($projects as $p)
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $p['title'] ?? '' }}</h4>
                                    @if (!empty($p['subtitle'] ?? ''))
                                        <p class="text-xs italic text-slate-500">{{ $p['subtitle'] }}</p>
                                    @endif
                                    @if (!empty($p['bullets']))
                                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1 mt-1">
                                            @foreach ($p['bullets'] as $b)
                                                @if (trim((string) $b) !== '')
                                                    <li>{{ $b }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if (!empty($p['tech'] ?? ''))
                                        <p class="text-xs text-slate-700 mt-1"><span class="font-bold">Tech:</span> <span class="italic">{{ $p['tech'] }}</span></p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add project name, subtitle, bullets, and tech stack.</div>
                    @endif
                </section>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-span-12 md:col-span-5 space-y-6">

                {{-- SKILLS --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'SKILLS', 'section' => 'skills', 'hasData' => count($skillGroups) > 0])
                    @if (count($skillGroups) > 0)
                        <div class="space-y-3">
                            @foreach ($skillGroups as $group)
                                <div>
                                    <h5 class="text-xs font-bold text-slate-800 mb-1.5">{{ $group['category'] ?? '' }}</h5>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach (($group['tags'] ?? []) as $tag)
                                            @if (trim((string) $tag) !== '')
                                                <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 border border-blue-200 rounded">{{ $tag }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add skill categories and tags.</div>
                    @endif
                </section>

                {{-- STRENGTHS --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'STRENGTHS', 'section' => 'strengths', 'hasData' => count($strengths) > 0])
                    @if (count($strengths) > 0)
                        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-700">
                            @foreach ($strengths as $s)
                                <div>★ {{ $s }}</div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add a list of strengths.</div>
                    @endif
                </section>

                {{-- KEY ACHIEVEMENTS --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'KEY ACHIEVEMENTS', 'section' => 'achievements', 'hasData' => count($achievements) > 0])
                    @if (count($achievements) > 0)
                        <ul class="list-disc pl-5 text-xs text-slate-700 space-y-1">
                            @foreach ($achievements as $a)
                                <li>{{ $a }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add achievement bullets.</div>
                    @endif
                </section>

                {{-- EDUCATION --}}
                <section class="relative">
                    @include('livewire.admin.portfolio.resume-builder.partials.section-heading', ['title' => 'EDUCATION', 'section' => 'education', 'hasData' => count($educations) > 0])
                    @if (count($educations) > 0)
                        <div class="space-y-3">
                            @foreach ($educations as $e)
                                <div>
                                    <h5 class="text-sm font-bold text-slate-900">{{ $e['degree'] ?? '' }}</h5>
                                    <p class="text-xs text-slate-700">{{ $e['institution'] ?? '' }}</p>
                                    <p class="text-xs text-blue-700 font-medium">{{ $e['start'] ?? '' }}{{ ($e['start'] ?? '') || ($e['end'] ?? '') ? ' – ' : '' }}{{ $e['end'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-400 text-sm italic">Click + to add degree, institution, and years.</div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    {{-- ============================ MODALS ============================ --}}
    @if ($openModal !== null)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click.self="closeSection">
            <div class="bg-dark-800 border border-dark-700 rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700 sticky top-0 bg-dark-800 z-10">
                    <h3 class="text-lg font-mono font-bold text-white uppercase tracking-wider">
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
                    <button wire:click="closeSection" class="text-gray-400 hover:text-white cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    {{-- ===== HEADER MODAL ===== --}}
                    @if ($openModal === 'header')
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">Full Name</label>
                                <input type="text" wire:model="form.name" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">Tagline</label>
                                <input type="text" wire:model="form.tagline" placeholder="e.g. Software Engineer | Laravel & Full-Stack" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">Phone</label>
                                <input type="text" wire:model="form.phone" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                                <input type="email" wire:model="form.email" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">Location</label>
                                <input type="text" wire:model="form.location" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">GitHub URL</label>
                                <input type="text" wire:model="form.github" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    @endif

                    {{-- ===== PROFILE MODAL ===== --}}
                    @if ($openModal === 'profile')
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Summary</label>
                            <textarea wire:model="form.summary" rows="6" placeholder="Software Engineer with X+ years of experience…" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    @endif

                    {{-- ===== WORK EXPERIENCE MODAL ===== --}}
                    @if ($openModal === 'work')
                        @foreach ($form['jobs'] ?? [] as $jobIndex => $job)
                            <div class="border border-dark-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-300">Job #{{ $jobIndex + 1 }}</h4>
                                    @if (count($form['jobs']) > 1)
                                        <button type="button" wire:click="removeRow('jobs', {{ $jobIndex }})" class="text-xs text-red-400 hover:text-red-300 cursor-pointer">Remove</button>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="text" placeholder="Company" wire:model="form.jobs.{{ $jobIndex }}.company" class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                    <input type="text" placeholder="Role" wire:model="form.jobs.{{ $jobIndex }}.role" class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                    <input type="text" placeholder="Start (e.g. 2022)" wire:model="form.jobs.{{ $jobIndex }}.start" class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                    <input type="text" placeholder="End (e.g. Aug 2025)" wire:model="form.jobs.{{ $jobIndex }}.end" class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm" @if ($job['is_current'] ?? false) disabled @endif>
                                </div>
                                <label class="flex items-center gap-2 text-sm text-gray-300">
                                    <input type="checkbox" wire:model="form.jobs.{{ $jobIndex }}.is_current" class="rounded border-dark-600 bg-dark-700 text-primary focus:ring-primary">
                                    Currently working here
                                </label>
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1">Bullets</label>
                                    @foreach ($job['bullets'] ?? [] as $bIndex => $b)
                                        <div class="flex items-center gap-2 mb-2">
                                            <input type="text" wire:model="form.jobs.{{ $jobIndex }}.bullets.{{ $bIndex }}" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Bullet point">
                                            <button type="button" wire:click="removeBulletFromJob({{ $jobIndex }}, {{ $bIndex }})" class="text-gray-400 hover:text-red-400 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addBulletToJob({{ $jobIndex }})" class="text-xs text-primary-light hover:text-primary cursor-pointer">+ add bullet</button>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('jobs')" class="text-sm text-primary-light hover:text-primary cursor-pointer">+ add another job</button>
                    @endif

                    {{-- ===== PROJECTS MODAL ===== --}}
                    @if ($openModal === 'projects')
                        @foreach ($form['projects'] ?? [] as $pIndex => $p)
                            <div class="border border-dark-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-300">Project #{{ $pIndex + 1 }}</h4>
                                    @if (count($form['projects']) > 1)
                                        <button type="button" wire:click="removeRow('projects', {{ $pIndex }})" class="text-xs text-red-400 hover:text-red-300 cursor-pointer">Remove</button>
                                    @endif
                                </div>
                                <input type="text" placeholder="Project Title" wire:model="form.projects.{{ $pIndex }}.title" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                <input type="text" placeholder="Subtitle (company / URL)" wire:model="form.projects.{{ $pIndex }}.subtitle" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1">Bullets</label>
                                    @foreach ($p['bullets'] ?? [] as $bIndex => $b)
                                        <div class="flex items-center gap-2 mb-2">
                                            <input type="text" wire:model="form.projects.{{ $pIndex }}.bullets.{{ $bIndex }}" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Bullet point">
                                            <button type="button" wire:click="removeBulletFromProject({{ $pIndex }}, {{ $bIndex }})" class="text-gray-400 hover:text-red-400 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addBulletToProject({{ $pIndex }})" class="text-xs text-primary-light hover:text-primary cursor-pointer">+ add bullet</button>
                                </div>
                                <input type="text" placeholder="Tech stack (comma separated)" wire:model="form.projects.{{ $pIndex }}.tech" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('projects')" class="text-sm text-primary-light hover:text-primary cursor-pointer">+ add another project</button>
                    @endif

                    {{-- ===== SKILLS MODAL ===== --}}
                    @if ($openModal === 'skills')
                        @foreach ($form['groups'] ?? [] as $gIndex => $group)
                            <div class="border border-dark-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-300">Group #{{ $gIndex + 1 }}</h4>
                                    @if (count($form['groups']) > 1)
                                        <button type="button" wire:click="removeRow('groups', {{ $gIndex }})" class="text-xs text-red-400 hover:text-red-300 cursor-pointer">Remove</button>
                                    @endif
                                </div>
                                <input type="text" placeholder="Category (e.g. Backend & Frontend)" wire:model="form.groups.{{ $gIndex }}.category" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1">Tags</label>
                                    @foreach ($group['tags'] ?? [] as $tIndex => $t)
                                        <div class="flex items-center gap-2 mb-2">
                                            <input type="text" wire:model="form.groups.{{ $gIndex }}.tags.{{ $tIndex }}" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Tag (e.g. Laravel)">
                                            <button type="button" wire:click="removeTagFromGroup({{ $gIndex }}, {{ $tIndex }})" class="text-gray-400 hover:text-red-400 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addTagToGroup({{ $gIndex }})" class="text-xs text-primary-light hover:text-primary cursor-pointer">+ add tag</button>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('groups')" class="text-sm text-primary-light hover:text-primary cursor-pointer">+ add another category</button>
                    @endif

                    {{-- ===== STRENGTHS MODAL ===== --}}
                    @if ($openModal === 'strengths')
                        @foreach ($form['items'] ?? [] as $iIndex => $item)
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="form.items.{{ $iIndex }}" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Strength (e.g. API Design)">
                                @if (count($form['items']) > 1)
                                    <button type="button" wire:click="removeRow('items', {{ $iIndex }})" class="text-gray-400 hover:text-red-400 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('items')" class="text-sm text-primary-light hover:text-primary cursor-pointer">+ add strength</button>
                    @endif

                    {{-- ===== ACHIEVEMENTS MODAL ===== --}}
                    @if ($openModal === 'achievements')
                        @foreach ($form['items'] ?? [] as $iIndex => $item)
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="form.items.{{ $iIndex }}" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm" placeholder="Achievement bullet">
                                @if (count($form['items']) > 1)
                                    <button type="button" wire:click="removeRow('items', {{ $iIndex }})" class="text-gray-400 hover:text-red-400 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('items')" class="text-sm text-primary-light hover:text-primary cursor-pointer">+ add achievement</button>
                    @endif

                    {{-- ===== EDUCATION MODAL ===== --}}
                    @if ($openModal === 'education')
                        @foreach ($form['entries'] ?? [] as $eIndex => $entry)
                            <div class="border border-dark-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-300">Entry #{{ $eIndex + 1 }}</h4>
                                    @if (count($form['entries']) > 1)
                                        <button type="button" wire:click="removeRow('entries', {{ $eIndex }})" class="text-xs text-red-400 hover:text-red-300 cursor-pointer">Remove</button>
                                    @endif
                                </div>
                                <input type="text" placeholder="Degree (e.g. B.S. Software Engineering)" wire:model="form.entries.{{ $eIndex }}.degree" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                <input type="text" placeholder="Institution" wire:model="form.entries.{{ $eIndex }}.institution" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="text" placeholder="Start year" wire:model="form.entries.{{ $eIndex }}.start" class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                    <input type="text" placeholder="End year" wire:model="form.entries.{{ $eIndex }}.end" class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 text-sm">
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addRow('entries')" class="text-sm text-primary-light hover:text-primary cursor-pointer">+ add another</button>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-700 sticky bottom-0 bg-dark-800">
                    <button wire:click="closeSection" class="px-4 py-2 text-sm text-gray-300 hover:text-white cursor-pointer">Cancel</button>
                    <button wire:click="save" class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2 transition-colors text-sm cursor-pointer">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
