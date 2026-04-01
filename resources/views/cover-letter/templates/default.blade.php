<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cover Letter - {{ $coverLetter->job_title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #1a1a1a;
            background: #ffffff;
            padding: 60px 60px 40px 60px;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #7c3aed;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .header .contact-info {
            font-size: 9pt;
            color: #555555;
            line-height: 1.5;
        }

        .date {
            margin-bottom: 20px;
            font-size: 10pt;
            color: #555555;
        }

        .recipient {
            margin-bottom: 25px;
        }

        .recipient p {
            font-size: 10pt;
            color: #333333;
            line-height: 1.5;
        }

        .body {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 11pt;
            line-height: 1.7;
            color: #1a1a1a;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            font-size: 8pt;
            color: #999999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $user->name }}</h1>
        <div class="contact-info">
            @if($profile && $profile->location)
                {{ $profile->location }}<br>
            @endif
            @if($user->email)
                {{ $user->email }}
            @endif
            @if($profile && $profile->phone)
                &nbsp;&bull;&nbsp;{{ $profile->phone }}
            @endif
            @if($profile && $profile->linkedin_url)
                <br>{{ $profile->linkedin_url }}
            @endif
        </div>
    </div>

    <div class="date">
        {{ now()->format('F j, Y') }}
    </div>

    @if($coverLetter->company_name || $coverLetter->job_title)
        <div class="recipient">
            @if($coverLetter->company_name)
                <p><strong>{{ $coverLetter->company_name }}</strong></p>
            @endif
            <p>Re: {{ $coverLetter->job_title }}</p>
        </div>
    @endif

    <div class="body">{{ $coverLetter->content }}</div>

    <div class="footer">
        Generated on {{ $coverLetter->created_at->format('F j, Y') }}
    </div>
</body>
</html>
