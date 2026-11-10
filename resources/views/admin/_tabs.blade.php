<nav class="tabs">
    <a href="{{ route('admin.index') }}" @class(['active' => request()->routeIs('admin.index')])>Overview</a>
    <a href="{{ route('admin.users') }}" @class(['active' => request()->routeIs('admin.users*')])>Players</a>
    <a href="{{ route('admin.games') }}" @class(['active' => request()->routeIs('admin.games*')])>Shared worlds</a>
    <a href="{{ route('admin.releases') }}" @class(['active' => request()->routeIs('admin.releases*')])>Releases</a>
</nav>
