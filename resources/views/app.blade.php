<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d4ed8">

    <title inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
    <meta name="description" content="BlogVerse Pro — platform publikasi artikel tentang teknologi, pendidikan, gaya hidup, bisnis, dan opini." inertia="description">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased">
    @inertia
</body>
</html>
