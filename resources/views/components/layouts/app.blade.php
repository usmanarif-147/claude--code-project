<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Usman Arif — Full-Stack Developer Portfolio">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Usman Arif — Full-Stack Developer</title>

    {{-- Google Fonts: Inter + Fira Code --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="landing bg-dark-950 text-gray-400 font-sans antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
