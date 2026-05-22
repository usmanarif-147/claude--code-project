<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $header['name'] ?? 'Resume' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 28px 32px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #1f2937;
        }

        /* ===== HEADER ===== */
        .header {
            border-bottom: 2px solid #d1d5db;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header h1 {
            font-size: 26px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: 0.5px;
        }
        .header .tagline {
            font-size: 10px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 3px;
            margin-bottom: 8px;
        }
        .header .contact {
            font-size: 9px;
            color: #4b5563;
        }
        .header .contact span {
            margin-right: 14px;
        }

        /* ===== TWO-COLUMN BODY (table-based for DomPDF reliability) ===== */
        table.body {
            width: 100%;
            border-collapse: collapse;
        }
        table.body > tbody > tr > td {
            vertical-align: top;
        }
        td.left {
            width: 60%;
            padding-right: 18px;
        }
        td.right {
            width: 40%;
            padding-left: 18px;
            border-left: 1px solid #e5e7eb;
        }

        /* ===== SECTION HEADING ===== */
        .section {
            margin-bottom: 14px;
        }
        .section h2 {
            font-size: 11px;
            font-weight: 700;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1.5px solid #1d4ed8;
            padding-bottom: 3px;
            margin-bottom: 7px;
        }

        /* ===== PROFILE ===== */
        .profile p {
            font-size: 10px;
            color: #374151;
            line-height: 1.55;
        }

        /* ===== WORK EXPERIENCE ===== */
        .job {
            margin-bottom: 9px;
        }
        .job .row {
            margin-bottom: 1px;
        }
        .job .company {
            font-size: 10.5px;
            font-weight: 700;
            color: #111827;
        }
        .job .dates {
            font-size: 9px;
            color: #6b7280;
            font-style: italic;
            float: right;
        }
        .job .role {
            font-size: 9.5px;
            color: #1d4ed8;
            font-weight: 600;
            margin-bottom: 3px;
        }
        ul.bullets {
            list-style: none;
            padding-left: 8px;
        }
        ul.bullets li {
            font-size: 9.5px;
            color: #374151;
            margin-bottom: 2px;
            padding-left: 8px;
            text-indent: -8px;
        }
        ul.bullets li::before {
            content: "• ";
            color: #1d4ed8;
            font-weight: 700;
        }

        /* ===== PROJECTS ===== */
        .project {
            margin-bottom: 9px;
        }
        .project .title {
            font-size: 10.5px;
            font-weight: 700;
            color: #111827;
        }
        .project .subtitle {
            font-size: 9px;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 2px;
        }
        .project .tech {
            font-size: 9px;
            color: #374151;
            margin-top: 2px;
        }
        .project .tech strong {
            font-weight: 700;
        }
        .project .tech .stack {
            font-style: italic;
        }

        /* ===== SKILLS ===== */
        .skill-group {
            margin-bottom: 7px;
        }
        .skill-group .category {
            font-size: 9.5px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 3px;
        }
        .skill-group .tags {
            line-height: 1.7;
        }
        .skill-group .tag {
            display: inline-block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 8.5px;
            padding: 1px 6px;
            margin: 0 2px 2px 0;
            border-radius: 3px;
        }

        /* ===== STRENGTHS ===== */
        table.strengths {
            width: 100%;
            border-collapse: collapse;
        }
        table.strengths td {
            font-size: 9.5px;
            color: #374151;
            padding: 1px 4px 1px 0;
            width: 50%;
        }

        /* ===== ACHIEVEMENTS ===== */
        .achievement-list {
            list-style: none;
            padding: 0;
        }
        .achievement-list li {
            font-size: 9.5px;
            color: #374151;
            margin-bottom: 4px;
            padding-left: 9px;
            text-indent: -9px;
        }
        .achievement-list li::before {
            content: "• ";
            color: #1d4ed8;
            font-weight: 700;
        }

        /* ===== EDUCATION ===== */
        .education-entry {
            margin-bottom: 7px;
        }
        .education-entry .degree {
            font-size: 10px;
            font-weight: 700;
            color: #111827;
        }
        .education-entry .institution {
            font-size: 9.5px;
            color: #374151;
        }
        .education-entry .dates {
            font-size: 9px;
            color: #1d4ed8;
            font-weight: 600;
            margin-top: 1px;
        }
    </style>
</head>
<body>

{{-- =================== HEADER =================== --}}
@php
    $hasHeader = !empty($header['name'] ?? '') || !empty($header['tagline'] ?? '') || !empty($header['email'] ?? '') || !empty($header['phone'] ?? '');
@endphp
@if ($hasHeader)
    <div class="header">
        @if (!empty($header['name'] ?? ''))
            <h1>{{ strtoupper($header['name']) }}</h1>
        @endif
        @if (!empty($header['tagline'] ?? ''))
            <div class="tagline">{{ $header['tagline'] }}</div>
        @endif
        <div class="contact">
            @if (!empty($header['phone'] ?? ''))<span>&#9990; {{ $header['phone'] }}</span>@endif
            @if (!empty($header['email'] ?? ''))<span>&#9993; {{ $header['email'] }}</span>@endif
            @if (!empty($header['location'] ?? ''))<span>&#9737; {{ $header['location'] }}</span>@endif
            @if (!empty($header['github'] ?? ''))<span>&#8962; {{ $header['github'] }}</span>@endif
        </div>
    </div>
@endif

{{-- =================== BODY (two columns) =================== --}}
<table class="body">
    <tr>
        {{-- ============ LEFT COLUMN ============ --}}
        <td class="left">

            {{-- PROFILE --}}
            @if (!empty($profile))
                <div class="section profile">
                    <h2>Profile</h2>
                    <p>{{ $profile }}</p>
                </div>
            @endif

            {{-- WORK EXPERIENCE --}}
            @if (!empty($experiences))
                <div class="section">
                    <h2>Work Experience</h2>
                    @foreach ($experiences as $job)
                        <div class="job">
                            <div class="row">
                                <span class="dates">
                                    {{ $job['start'] ?? '' }}{{ ($job['start'] ?? '') || ($job['end'] ?? '') ? ' – ' : '' }}{{ ($job['is_current'] ?? false) ? 'Present' : ($job['end'] ?? '') }}
                                </span>
                                <span class="company">{{ $job['company'] ?? '' }}</span>
                                <div style="clear: both;"></div>
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
                </div>
            @endif

            {{-- KEY PROJECTS --}}
            @if (!empty($projects))
                <div class="section">
                    <h2>Key Projects</h2>
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
                </div>
            @endif

        </td>

        {{-- ============ RIGHT COLUMN ============ --}}
        <td class="right">

            {{-- SKILLS --}}
            @if (!empty($skillGroups))
                <div class="section">
                    <h2>Skills</h2>
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
                </div>
            @endif

            {{-- STRENGTHS --}}
            @if (!empty($strengths))
                <div class="section">
                    <h2>Strengths</h2>
                    <table class="strengths">
                        @php $rows = array_chunk($strengths, 2); @endphp
                        @foreach ($rows as $pair)
                            <tr>
                                <td>&#9733; {{ $pair[0] ?? '' }}</td>
                                <td>@if (isset($pair[1])) &#9733; {{ $pair[1] }} @endif</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

            {{-- KEY ACHIEVEMENTS --}}
            @if (!empty($achievements))
                <div class="section">
                    <h2>Key Achievements</h2>
                    <ul class="achievement-list">
                        @foreach ($achievements as $a)
                            <li>{{ $a }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- EDUCATION --}}
            @if (!empty($educations))
                <div class="section">
                    <h2>Education</h2>
                    @foreach ($educations as $e)
                        <div class="education-entry">
                            <div class="degree">{{ $e['degree'] ?? '' }}</div>
                            <div class="institution">{{ $e['institution'] ?? '' }}</div>
                            <div class="dates">{{ $e['start'] ?? '' }}{{ ($e['start'] ?? '') || ($e['end'] ?? '') ? ' – ' : '' }}{{ $e['end'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

        </td>
    </tr>
</table>

</body>
</html>
