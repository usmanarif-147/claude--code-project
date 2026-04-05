<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
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
        .header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #7c3aed;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 4px;
        }
        .header .subtitle {
            font-size: 9px;
            color: #888888;
        }
        .diagram-section {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }
        .diagram-title {
            font-size: 14px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 4px;
        }
        .diagram-type {
            display: inline-block;
            font-size: 9px;
            color: #7c3aed;
            background: #f3f0ff;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .diagram-description {
            font-size: 10px;
            color: #555555;
            margin-bottom: 10px;
        }
        .code-block {
            background: #f5f5f5;
            border: 1px solid #dddddd;
            border-radius: 4px;
            padding: 12px;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 8px;
            line-height: 1.6;
            color: #333333;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .code-label {
            font-size: 8px;
            color: #888888;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .divider {
            border-top: 1px solid #eeeeee;
            margin: 16px 0;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #aaaaaa;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Generated on {{ $generatedAt }}</div>
    </div>

    @foreach ($diagrams as $diagram)
        <div class="diagram-section">
            <div class="diagram-title">{{ $diagram->title }}</div>
            <div class="diagram-type">{{ str_replace('-', ' ', ucfirst($diagram->type)) }}</div>

            @if ($diagram->description)
                <div class="diagram-description">{{ $diagram->description }}</div>
            @endif

            @if ($diagram->mermaid_syntax)
                <div class="code-label">Mermaid Syntax</div>
                <div class="code-block">{{ $diagram->mermaid_syntax }}</div>
            @else
                <div class="diagram-description" style="color: #aaaaaa; font-style: italic;">No Mermaid syntax defined.</div>
            @endif
        </div>

        @if (!$loop->last)
            <div class="divider"></div>
        @endif
    @endforeach

    <div class="footer">
        Design Board Export &mdash; {{ $generatedAt }}
    </div>
</body>
</html>
