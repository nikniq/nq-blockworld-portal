@extends('layouts.app')
@section('title', 'Admin')

@section('content')
<div class="admin">
    <h1>Admin</h1>
    @include('admin._tabs')

    <div class="stats">
        @foreach ($stats as $label => $value)<div class="stat"><b>{{ $value }}</b><span>{{ $label }}</span></div>@endforeach
    </div>

    <div class="row">
        <div>
            <h2>Newest players</h2>
            <table>
                <tr><th>Handle</th><th>Name</th><th>Joined</th></tr>
                @foreach ($recentUsers as $u)
                    <tr><td><a href="{{ route('profile.show', $u) }}">{{ $u->handle }}</a></td><td>{{ $u->name }}</td><td>{{ $u->created_at->diffForHumans() }}</td></tr>
                @endforeach
            </table>
        </div>
        <div>
            <h2>Newest worlds</h2>
            <table>
                <tr><th>Title</th><th>By</th><th>Shared</th></tr>
                @foreach ($recentGames as $g)
                    <tr><td><a href="{{ route('games.show', $g) }}">{{ $g->title }}</a></td><td>{{ $g->user->handle }}</td><td>{{ $g->created_at->diffForHumans() }}</td></tr>
                @endforeach
            </table>
        </div>
    </div>

    <h2>Server</h2>
    <table style="max-width: 36rem">
        @foreach ($env as $k => $v)<tr><th>{{ $k }}</th><td>{{ $v }}</td></tr>@endforeach
    </table>

    <h2>Recent errors</h2>
    @if (empty($logErrors))
        <p class="muted">No errors in the log.</p>
    @else
        <div class="log">@foreach ($logErrors as $line){{ $line }}
@endforeach</div>
    @endif
</div>
@endsection
