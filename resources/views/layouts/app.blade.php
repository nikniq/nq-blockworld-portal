<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Blockworld') · {{ config('app.name') }}</title>
    <meta name="description" content="@yield('description', 'Blockworld: a Minecraft-style survival game with a rifle, dragons, dinosaurs and Australian wildlife. Free for macOS, Linux and Windows.')">
    <link rel="icon" href="{{ asset('icon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a href="{{ route('home') }}" class="brand"><img src="{{ asset('icon.png') }}" alt="" width="32" height="32"> Blockworld</a>
        <nav>
            <a href="{{ route('downloads.index') }}" @class(['active' => request()->routeIs('downloads.*')])>Download</a>
            <a href="{{ route('games.index') }}" @class(['active' => request()->routeIs('games.*')])>Shared worlds</a>
            <a href="{{ route('profile.index') }}" @class(['active' => request()->routeIs('profile.index')])>Players</a>
            @auth
                <a href="{{ route('games.create') }}" class="btn btn-small">Share a world</a>
                <a href="{{ route('profile.show', auth()->user()) }}" @class(['active' => request()->routeIs('profile.show', 'profile.edit')])>{{ auth()->user()->name }}</a>
                <form method="post" action="{{ route('logout') }}" class="inline">@csrf<button class="link">Log out</button></form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-small">Join</a>
            @endauth
        </nav>
    </div>
</header>

<main class="container">
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash flash-error">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <p>Blockworld is written in Go with raylib and generates every texture and sound at startup. Free to play on macOS, Linux and Windows.</p>
    </div>
</footer>
</body>
</html>
