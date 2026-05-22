<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $header['name'] ?? 'Resume' }}</title>
    <style>
        @page { margin: 18px 24px; }
        body { margin: 0; padding: 0; }
        .resume-paper {
            box-shadow: none !important;
            width: auto !important;
            height: auto !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
    </style>
    @include('resume.templates._styles')
    @include('resume.templates._format_overrides')
</head>
<body>
    @include('resume.templates._body', ['interactive' => false])
</body>
</html>
