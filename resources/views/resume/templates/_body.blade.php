@php
    $interactive = $interactive ?? false;
    $hasHeader = !empty($header['name'] ?? '') || !empty($header['tagline'] ?? '') || !empty($header['email'] ?? '') || !empty($header['phone'] ?? '');

    // Convert a YYYY-MM month-picker value (e.g. "2022-02") into a display string
    // like "2022-feb". If the value isn't in Y-m form, return it unchanged so
    // legacy or partial values don't break the render.
    $formatDate = function ($v) {
        $v = trim((string) $v);
        if ($v === '') {
            return '';
        }
        try {
            return strtolower(\Carbon\Carbon::createFromFormat('Y-m', $v)->format('Y-M'));
        } catch (\Throwable) {
            return $v;
        }
    };
@endphp

<div class="resume-paper">

    {{-- ============ HEADER ============ --}}
    <div class="resume-header">
        @if ($interactive)
            <button type="button" wire:click="openSection('header')" class="rb-add-btn rb-add-btn-lg" title="{{ $hasHeader ? 'Edit Header' : 'Add Header' }}">
                {{ $hasHeader ? '✎' : '+' }}
            </button>
        @endif

        @if ($hasHeader)
            @if (!empty($header['name'] ?? ''))
                <h1>{{ strtoupper($header['name']) }}</h1>
            @endif
            @if (!empty($header['tagline'] ?? ''))
                <div class="tagline">{{ $header['tagline'] }}</div>
            @endif
            <div class="contact">
                @if (!empty($header['phone'] ?? ''))<span><span class="ico">&#9990;</span> {{ $header['phone'] }}</span>@endif
                @if (!empty($header['email'] ?? ''))<span><span class="ico">&#9993;</span> {{ $header['email'] }}</span>@endif
                @if (!empty($header['location'] ?? ''))<span><span class="ico">&#9737;</span> {{ $header['location'] }}</span>@endif
                @if (!empty($header['github'] ?? ''))<span><span class="ico">&#8962;</span> {{ $header['github'] }}</span>@endif
            </div>
        @elseif ($interactive)
            <div class="empty-hint">Header — click + to add your name, tagline, phone, email, location, and GitHub URL.</div>
        @endif
    </div>

    {{-- ============ TWO-COLUMN BODY ============ --}}
    <table class="resume-body">
        <tr>
            {{-- ===== LEFT COLUMN ===== --}}
            <td class="col-left">

                {{-- PROFILE --}}
                <div class="section profile">
                    @include('resume.templates._section_title', ['title' => 'Profile', 'section' => 'profile', 'hasData' => $profile !== '', 'interactive' => $interactive])
                    @if ($profile !== '')
                        <p>{{ $profile }}</p>
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add a short profile summary paragraph.</div>
                    @endif
                </div>

                {{-- WORK EXPERIENCE --}}
                <div class="section">
                    @include('resume.templates._section_title', ['title' => 'Work Experience', 'section' => 'work', 'hasData' => count($experiences) > 0, 'interactive' => $interactive])
                    @if (count($experiences) > 0)
                        @foreach ($experiences as $job)
                            <div class="job">
                                <div class="job-head">
                                    <span class="company">{{ $job['company'] ?? '' }}</span>
                                    <span class="dates">
                                        {{ $formatDate($job['start'] ?? '') }}{{ ($job['start'] ?? '') || ($job['end'] ?? '') ? ' – ' : '' }}{{ ($job['is_current'] ?? false) ? 'Present' : $formatDate($job['end'] ?? '') }}
                                    </span>
                                </div>
                                @if (!empty($job['role'] ?? ''))
                                    <div class="role">{{ $job['role'] }}</div>
                                @endif
                                @if (!empty($job['bullets']))
                                    <ul class="bullets">
                                        @foreach ($job['bullets'] as $b)
                                            @if (trim((string) $b) !== '')
                                                <li>{{ $b }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add companies, roles, dates, and bullet points.</div>
                    @endif
                </div>

                {{-- KEY PROJECTS --}}
                <div class="section">
                    @include('resume.templates._section_title', ['title' => 'Key Projects', 'section' => 'projects', 'hasData' => count($projects) > 0, 'interactive' => $interactive])
                    @if (count($projects) > 0)
                        @foreach ($projects as $p)
                            <div class="project">
                                <div class="title">{{ $p['title'] ?? '' }}</div>
                                @if (!empty($p['subtitle'] ?? ''))
                                    <div class="subtitle">{{ $p['subtitle'] }}</div>
                                @endif
                                @if (!empty($p['bullets']))
                                    <ul class="bullets">
                                        @foreach ($p['bullets'] as $b)
                                            @if (trim((string) $b) !== '')
                                                <li>{{ $b }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                                @if (!empty($p['tech'] ?? ''))
                                    <div class="tech"><strong>Tech:</strong> <span class="stack">{{ $p['tech'] }}</span></div>
                                @endif
                            </div>
                        @endforeach
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add project name, subtitle, bullets, and tech stack.</div>
                    @endif
                </div>

            </td>

            {{-- ===== RIGHT COLUMN ===== --}}
            <td class="col-right">

                {{-- SKILLS --}}
                <div class="section">
                    @include('resume.templates._section_title', ['title' => 'Skills', 'section' => 'skills', 'hasData' => count($skillGroups) > 0, 'interactive' => $interactive])
                    @if (count($skillGroups) > 0)
                        @foreach ($skillGroups as $group)
                            <div class="skill-group">
                                @if (!empty($group['category'] ?? ''))
                                    <div class="category">{{ $group['category'] }}</div>
                                @endif
                                <div class="tags">
                                    @foreach (($group['tags'] ?? []) as $tag)
                                        @if (trim((string) $tag) !== '')
                                            <span class="tag">{{ $tag }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add skill categories and tags.</div>
                    @endif
                </div>

                {{-- STRENGTHS --}}
                <div class="section">
                    @include('resume.templates._section_title', ['title' => 'Strengths', 'section' => 'strengths', 'hasData' => count($strengths) > 0, 'interactive' => $interactive])
                    @if (count($strengths) > 0)
                        <table class="strengths">
                            @php $rows = array_chunk($strengths, 2); @endphp
                            @foreach ($rows as $pair)
                                <tr>
                                    <td><span class="ico">&#9733;</span> {{ $pair[0] ?? '' }}</td>
                                    <td>@if (isset($pair[1]))<span class="ico">&#9733;</span> {{ $pair[1] }}@endif</td>
                                </tr>
                            @endforeach
                        </table>
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add a list of strengths.</div>
                    @endif
                </div>

                {{-- KEY ACHIEVEMENTS --}}
                <div class="section">
                    @include('resume.templates._section_title', ['title' => 'Key Achievements', 'section' => 'achievements', 'hasData' => count($achievements) > 0, 'interactive' => $interactive])
                    @if (count($achievements) > 0)
                        <ul class="achievement-list">
                            @foreach ($achievements as $a)
                                <li>{{ $a }}</li>
                            @endforeach
                        </ul>
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add achievement bullets.</div>
                    @endif
                </div>

                {{-- EDUCATION --}}
                <div class="section">
                    @include('resume.templates._section_title', ['title' => 'Education', 'section' => 'education', 'hasData' => count($educations) > 0, 'interactive' => $interactive])
                    @if (count($educations) > 0)
                        @foreach ($educations as $e)
                            <div class="education-entry">
                                <div class="degree">{{ $e['degree'] ?? '' }}</div>
                                <div class="institution">{{ $e['institution'] ?? '' }}</div>
                                <div class="dates">{{ $formatDate($e['start'] ?? '') }}{{ ($e['start'] ?? '') || ($e['end'] ?? '') ? ' – ' : '' }}{{ $formatDate($e['end'] ?? '') }}</div>
                            </div>
                        @endforeach
                    @elseif ($interactive)
                        <div class="empty-hint">Click + to add degree, institution, and years.</div>
                    @endif
                </div>

            </td>
        </tr>
    </table>

</div>
