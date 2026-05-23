<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Social Post</title>
    @include('social-posts.templates._styles')
</head>
<body>
@foreach($pages as $i => $page)
    <div class="page page--{{ $page['role'] }} @if(!$loop->last) page-break @endif">
        {!! $page['content'] !!}
    </div>
@endforeach
</body>
</html>
