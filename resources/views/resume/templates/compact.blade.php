<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $user->name }} — Resume</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            line-height: 1.3;
            color: #333333;
            background: #ffffff;
            padding: 18px 24px;
        }
        /* Header - compact */
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 2px;
        }
        .header .tagline {
            font-size: 9px;
            color: #4f46e5;
        }
        .contact-right {
            text-align: right;
            font-size: 8px;
            color: #666666;
            line-height: 1.5;
        }
        /* Sections */
        .section {
            margin-bottom: 8px;
        }
        .section-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }
        /* Two-column skills layout */
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col td {
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }
        .two-col td:last-child {
            padding-right: 0;
            padding-left: 8px;
        }
        /* Skills compact */
        .skill-line {
            margin-bottom: 2px;
        }
        .skill-label {
            font-size: 8px;
            font-weight: 600;
            color: #374151;
            display: inline;
        }
        .skill-level {
            font-size: 7.5px;
            color: #9ca3af;
            display: inline;
        }
        /* Tech compact */
        .tech-cat {
            font-size: 8px;
            font-weight: 700;
            color: #374151;
            display: inline;
        }
        .tech-names {
            font-size: 8px;
            color: #6b7280;
            display: inline;
        }
        .tech-line {
            margin-bottom: 2px;
        }
        /* Experience compact */
        .exp-item {
            margin-bottom: 6px;
        }
        .exp-row {
            width: 100%;
            border-collapse: collapse;
        }
        .exp-row td {
            vertical-align: top;
        }
        .exp-role {
            font-size: 9px;
            font-weight: 700;
            color: #1f2937;
            display: inline;
        }
        .exp-at {
            font-size: 8.5px;
            color: #6b7280;
            display: inline;
        }
        .exp-company {
            font-size: 8.5px;
            color: #4f46e5;
            font-weight: 600;
            display: inline;
        }
        .exp-date {
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
            white-space: nowrap;
        }
        .responsibilities {
            margin-top: 2px;
            padding-left: 12px;
        }
        .responsibilities li {
            font-size: 8px;
            color: #555555;
            margin-bottom: 1px;
        }
        /* Education compact */
        .edu-line {
            font-size: 8.5px;
            margin-bottom: 3px;
        }
        .edu-degree {
            font-weight: 700;
            color: #1f2937;
        }
        .edu-detail {
            color: #6b7280;
        }
        /* Projects compact */
        .project-line {
            margin-bottom: 4px;
        }
        .project-name {
            font-size: 8.5px;
            font-weight: 700;
            color: #1f2937;
            display: inline;
        }
        .project-desc {
            font-size: 8px;
            color: #555555;
        }
        .project-stack {
            font-size: 7.5px;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <h1>{{ $user->name }}</h1>
                    @if($profile && $profile->tagline)
                        <div class="tagline">{{ $profile->tagline }}</div>
                    @endif
                </td>
                <td class="contact-right">
                    {{ $user->email }}<br>
                    @if($profile && $profile->phone)
                        {{ $profile->phone }}<br>
                    @endif
                    @if($profile && $profile->location)
                        {{ $profile->location }}<br>
                    @endif
                    @if($profile && $profile->linkedin_url)
                        {{ $profile->linkedin_url }}<br>
                    @endif
                    @if($profile && $profile->github_url)
                        {{ $profile->github_url }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Bio - short --}}
    @if($profile && $profile->bio)
        <div class="section">
            <div class="section-title">Summary</div>
            <div style="font-size: 8.5px; color: #555555;">{{ \Illuminate\Support\Str::limit($profile->bio, 300) }}</div>
        </div>
    @endif

    {{-- Skills & Technologies - two column --}}
    @if($skills->count() || $technologies->count())
        <div class="section">
            <table class="two-col">
                <tr>
                    <td>
                        @if($skills->count())
                            <div class="section-title">Skills</div>
                            @foreach($skills as $skill)
                                <div class="skill-line">
                                    <span class="skill-label">{{ $skill->title }}</span>
                                    <span class="skill-level">({{ $skill->proficiency }}%)</span>
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if($technologies->count())
                            <div class="section-title">Technologies</div>
                            @foreach($technologies as $category => $techs)
                                <div class="tech-line">
                                    <span class="tech-cat">{{ $category }}:</span>
                                    <span class="tech-names">{{ $techs->pluck('name')->join(', ') }}</span>
                                </div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- Work Experience --}}
    @if($workExperience->count())
        <div class="section">
            <div class="section-title">Experience</div>
            @foreach($workExperience as $exp)
                <div class="exp-item">
                    <table class="exp-row" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                <span class="exp-role">{{ $exp->role }}</span>
                                <span class="exp-at"> at </span>
                                <span class="exp-company">{{ $exp->company }}</span>
                            </td>
                            <td class="exp-date">
                                {{ $exp->start_date->format('M Y') }} —
                                {{ $exp->is_current ? 'Present' : $exp->end_date?->format('M Y') }}
                            </td>
                        </tr>
                    </table>
                    @if($exp->responsibilities->count())
                        <ul class="responsibilities">
                            @foreach($exp->responsibilities as $resp)
                                <li>{{ $resp->description }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Education --}}
    @if($education->count())
        <div class="section">
            <div class="section-title">Education</div>
            @foreach($education as $edu)
                <div class="edu-line">
                    <span class="edu-degree">{{ $edu->degree ?? $edu->role }}</span>
                    @if($edu->field_of_study)
                        <span class="edu-detail"> in {{ $edu->field_of_study }}</span>
                    @endif
                    <span class="edu-detail">
                        — {{ $edu->company }}
                        ({{ $edu->start_date->format('Y') }}–{{ $edu->is_current ? 'Present' : $edu->end_date?->format('Y') }})
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Projects --}}
    @if($projects->count())
        <div class="section">
            <div class="section-title">Projects</div>
            @foreach($projects as $project)
                <div class="project-line">
                    <span class="project-name">{{ $project->title }}</span>
                    @if($project->short_description)
                        <div class="project-desc">{{ \Illuminate\Support\Str::limit($project->short_description, 120) }}</div>
                    @endif
                    @if($project->tech_stack && count($project->tech_stack))
                        <div class="project-stack">{{ implode(' / ', $project->tech_stack) }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
