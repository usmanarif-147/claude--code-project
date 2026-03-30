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
            font-size: 10px;
            line-height: 1.5;
            color: #333333;
            background: #ffffff;
            padding: 30px 40px;
        }
        /* Header */
        .header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #333333;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 4px;
        }
        .header .tagline {
            font-size: 11px;
            color: #555555;
            margin-bottom: 8px;
        }
        .contact-info {
            font-size: 9px;
            color: #666666;
        }
        .contact-info span {
            margin: 0 6px;
        }
        .contact-info .sep {
            color: #cccccc;
        }
        /* Sections */
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #111111;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid #cccccc;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        /* Experience */
        .exp-item {
            margin-bottom: 10px;
        }
        .exp-top-row {
            width: 100%;
        }
        .exp-top-row td {
            vertical-align: top;
        }
        .exp-role {
            font-size: 11px;
            font-weight: 700;
            color: #222222;
        }
        .exp-company {
            font-size: 10px;
            color: #444444;
        }
        .exp-date {
            font-size: 9px;
            color: #888888;
            text-align: right;
            white-space: nowrap;
        }
        .exp-desc {
            font-size: 9px;
            color: #555555;
            margin-top: 3px;
        }
        .responsibilities {
            margin-top: 3px;
            padding-left: 16px;
        }
        .responsibilities li {
            font-size: 9px;
            color: #555555;
            margin-bottom: 1px;
        }
        /* Education */
        .edu-degree {
            font-size: 11px;
            font-weight: 700;
            color: #222222;
        }
        .edu-field {
            font-size: 9px;
            color: #555555;
        }
        .edu-school {
            font-size: 9px;
            color: #777777;
        }
        /* Skills */
        .skills-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .skills-grid td {
            vertical-align: top;
            width: 50%;
            padding-right: 12px;
            padding-bottom: 4px;
        }
        .skill-category {
            font-size: 9.5px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 2px;
        }
        .skill-list {
            font-size: 9px;
            color: #555555;
        }
        /* Technologies */
        .tech-category-name {
            font-size: 9.5px;
            font-weight: 700;
            color: #333333;
        }
        .tech-items {
            font-size: 9px;
            color: #555555;
        }
        .tech-row {
            margin-bottom: 4px;
        }
        /* Projects */
        .project-item {
            margin-bottom: 8px;
        }
        .project-title {
            font-size: 10px;
            font-weight: 700;
            color: #222222;
        }
        .project-desc {
            font-size: 9px;
            color: #555555;
        }
        .project-tech {
            font-size: 8.5px;
            color: #888888;
            font-style: italic;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $user->name }}</h1>
        @if($profile && $profile->tagline)
            <div class="tagline">{{ $profile->tagline }}</div>
        @endif
        <div class="contact-info">
            <span>{{ $user->email }}</span>
            @if($profile && $profile->phone)
                <span class="sep">|</span>
                <span>{{ $profile->phone }}</span>
            @endif
            @if($profile && $profile->location)
                <span class="sep">|</span>
                <span>{{ $profile->location }}</span>
            @endif
            @if($profile && $profile->linkedin_url)
                <span class="sep">|</span>
                <span>{{ $profile->linkedin_url }}</span>
            @endif
            @if($profile && $profile->github_url)
                <span class="sep">|</span>
                <span>{{ $profile->github_url }}</span>
            @endif
        </div>
    </div>

    {{-- Bio --}}
    @if($profile && $profile->bio)
        <div class="section">
            <div class="section-title">Summary</div>
            <div class="exp-desc">{{ $profile->bio }}</div>
        </div>
    @endif

    {{-- Work Experience --}}
    @if($workExperience->count())
        <div class="section">
            <div class="section-title">Professional Experience</div>
            @foreach($workExperience as $exp)
                <div class="exp-item">
                    <table class="exp-top-row" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                <div class="exp-role">{{ $exp->role }}</div>
                                <div class="exp-company">{{ $exp->company }}</div>
                            </td>
                            <td class="exp-date">
                                {{ $exp->start_date->format('M Y') }} —
                                {{ $exp->is_current ? 'Present' : $exp->end_date?->format('M Y') }}
                            </td>
                        </tr>
                    </table>
                    @if($exp->description)
                        <div class="exp-desc">{{ $exp->description }}</div>
                    @endif
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
                <div class="exp-item">
                    <table class="exp-top-row" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                <div class="edu-degree">{{ $edu->degree ?? $edu->role }}</div>
                                @if($edu->field_of_study)
                                    <div class="edu-field">{{ $edu->field_of_study }}</div>
                                @endif
                                <div class="edu-school">{{ $edu->company }}</div>
                            </td>
                            <td class="exp-date">
                                {{ $edu->start_date->format('M Y') }} —
                                {{ $edu->is_current ? 'Present' : $edu->end_date?->format('M Y') }}
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Skills --}}
    @if($skills->count())
        <div class="section">
            <div class="section-title">Skills</div>
            @php $grouped = $skills->groupBy('category'); @endphp
            @foreach($grouped as $category => $categorySkills)
                <div class="tech-row">
                    <span class="tech-category-name">{{ $category }}:</span>
                    <span class="tech-items">{{ $categorySkills->pluck('title')->join(', ') }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Technologies --}}
    @if($technologies->count())
        <div class="section">
            <div class="section-title">Technologies</div>
            @foreach($technologies as $category => $techs)
                <div class="tech-row">
                    <span class="tech-category-name">{{ $category }}:</span>
                    <span class="tech-items">{{ $techs->pluck('name')->join(', ') }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Projects --}}
    @if($projects->count())
        <div class="section">
            <div class="section-title">Projects</div>
            @foreach($projects as $project)
                <div class="project-item">
                    <div class="project-title">{{ $project->title }}</div>
                    @if($project->short_description)
                        <div class="project-desc">{{ $project->short_description }}</div>
                    @endif
                    @if($project->tech_stack && count($project->tech_stack))
                        <div class="project-tech">{{ implode(', ', $project->tech_stack) }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
