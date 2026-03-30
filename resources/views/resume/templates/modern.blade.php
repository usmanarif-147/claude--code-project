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
            line-height: 1.4;
            color: #2d2d2d;
            background: #ffffff;
        }
        .container {
            width: 100%;
            padding: 0;
        }
        /* Header */
        .header {
            background-color: #1a1a2e;
            color: #ffffff;
            padding: 24px 30px;
            margin-bottom: 0;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .header .tagline {
            font-size: 11px;
            color: #a5b4fc;
            margin-bottom: 10px;
        }
        .contact-row {
            font-size: 9px;
            color: #d1d5db;
        }
        .contact-row span {
            margin-right: 14px;
        }
        /* Two-column layout */
        .content {
            width: 100%;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sidebar {
            width: 32%;
            vertical-align: top;
            padding: 20px 16px 20px 30px;
            background-color: #f8f9fa;
            border-right: 1px solid #e5e7eb;
        }
        .main {
            width: 68%;
            vertical-align: top;
            padding: 20px 30px 20px 20px;
        }
        /* Section titles */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 18px;
        }
        /* Skills */
        .skill-item {
            margin-bottom: 8px;
        }
        .skill-name {
            font-size: 9px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 2px;
        }
        .skill-bar-bg {
            width: 100%;
            height: 5px;
            background-color: #e5e7eb;
            border-radius: 3px;
        }
        .skill-bar-fill {
            height: 5px;
            background-color: #6366f1;
            border-radius: 3px;
        }
        /* Technologies */
        .tech-category {
            font-size: 9px;
            font-weight: 700;
            color: #4b5563;
            margin-bottom: 3px;
            margin-top: 8px;
        }
        .tech-list {
            font-size: 8.5px;
            color: #6b7280;
            line-height: 1.5;
        }
        /* Experience */
        .exp-item {
            margin-bottom: 14px;
        }
        .exp-header {
            margin-bottom: 4px;
        }
        .exp-role {
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
        }
        .exp-company {
            font-size: 10px;
            color: #6366f1;
            font-weight: 600;
        }
        .exp-date {
            font-size: 9px;
            color: #9ca3af;
        }
        .exp-desc {
            font-size: 9px;
            color: #4b5563;
            margin-top: 3px;
        }
        .responsibilities {
            margin-top: 4px;
            padding-left: 14px;
        }
        .responsibilities li {
            font-size: 9px;
            color: #4b5563;
            margin-bottom: 2px;
        }
        /* Education */
        .edu-degree {
            font-size: 10px;
            font-weight: 700;
            color: #1f2937;
        }
        .edu-field {
            font-size: 9px;
            color: #6366f1;
        }
        .edu-school {
            font-size: 9px;
            color: #6b7280;
        }
        /* Projects */
        .project-item {
            margin-bottom: 10px;
            padding: 8px 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .project-title {
            font-size: 10px;
            font-weight: 700;
            color: #1f2937;
        }
        .project-desc {
            font-size: 9px;
            color: #4b5563;
            margin-top: 2px;
        }
        .project-tech {
            font-size: 8px;
            color: #6366f1;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>{{ $user->name }}</h1>
            @if($profile && $profile->tagline)
                <div class="tagline">{{ $profile->tagline }}</div>
            @endif
            <div class="contact-row">
                <span>{{ $user->email }}</span>
                @if($profile && $profile->phone)
                    <span>{{ $profile->phone }}</span>
                @endif
                @if($profile && $profile->location)
                    <span>{{ $profile->location }}</span>
                @endif
                @if($profile && $profile->linkedin_url)
                    <span>{{ $profile->linkedin_url }}</span>
                @endif
                @if($profile && $profile->github_url)
                    <span>{{ $profile->github_url }}</span>
                @endif
            </div>
        </div>

        {{-- Two-column body --}}
        <table class="content-table">
            <tr>
                {{-- Sidebar --}}
                <td class="sidebar">
                    {{-- Skills --}}
                    @if($skills->count())
                        <div class="section">
                            <div class="section-title">Skills</div>
                            @foreach($skills as $skill)
                                <div class="skill-item">
                                    <div class="skill-name">{{ $skill->title }}</div>
                                    <div class="skill-bar-bg">
                                        <div class="skill-bar-fill" style="width: {{ $skill->proficiency }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Technologies --}}
                    @if($technologies->count())
                        <div class="section">
                            <div class="section-title">Technologies</div>
                            @foreach($technologies as $category => $techs)
                                <div class="tech-category">{{ $category }}</div>
                                <div class="tech-list">{{ $techs->pluck('name')->join(', ') }}</div>
                            @endforeach
                        </div>
                    @endif
                </td>

                {{-- Main content --}}
                <td class="main">
                    {{-- Bio --}}
                    @if($profile && $profile->bio)
                        <div class="section">
                            <div class="section-title">About</div>
                            <div class="exp-desc">{{ $profile->bio }}</div>
                        </div>
                    @endif

                    {{-- Work Experience --}}
                    @if($workExperience->count())
                        <div class="section">
                            <div class="section-title">Experience</div>
                            @foreach($workExperience as $exp)
                                <div class="exp-item">
                                    <div class="exp-header">
                                        <div class="exp-role">{{ $exp->role }}</div>
                                        <div class="exp-company">{{ $exp->company }}</div>
                                        <div class="exp-date">
                                            {{ $exp->start_date->format('M Y') }} —
                                            {{ $exp->is_current ? 'Present' : $exp->end_date?->format('M Y') }}
                                        </div>
                                    </div>
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
                                    <div class="edu-degree">{{ $edu->degree ?? $edu->role }}</div>
                                    @if($edu->field_of_study)
                                        <div class="edu-field">{{ $edu->field_of_study }}</div>
                                    @endif
                                    <div class="edu-school">{{ $edu->company }}</div>
                                    <div class="exp-date">
                                        {{ $edu->start_date->format('M Y') }} —
                                        {{ $edu->is_current ? 'Present' : $edu->end_date?->format('M Y') }}
                                    </div>
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
                                        <div class="project-tech">{{ implode(' · ', $project->tech_stack) }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
