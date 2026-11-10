@extends('layouts.app')
@section('title', 'Admin · Shared worlds')

@section('content')
<div class="admin">
    <h1>Shared worlds</h1>
    @include('admin._tabs')
    <form class="search" method="get"><input name="q" value="{{ $q }}" placeholder="title or owner handle"><button class="btn btn-small">Search</button></form>
    <table>
        <tr><th>Title</th><th>By</th><th>Mode</th><th>Size</th><th>Score</th><th>Downloads</th><th>Save</th><th>Shared</th><th></th></tr>
        @forelse ($games as $g)
            <tr>
                <td><a href="{{ route('games.show', $g) }}">{{ $g->title }}</a></td>
                <td><a href="{{ route('profile.show', $g->user) }}">{{ $g->user->handle }}</a></td>
                <td>{{ $g->modeLabel() }}</td>
                <td>{{ $g->sizeLabel() }}</td>
                <td>{{ number_format($g->score) }}</td>
                <td>{{ $g->downloads }}</td>
                <td>{{ $g->humanSize() }}</td>
                <td class="small">{{ $g->created_at->toDateString() }}</td>
                <td class="actions">
                    <a class="btn btn-small btn-ghost" href="{{ route('games.edit', $g) }}">Edit</a>
                    <form method="post" action="{{ route('admin.games.destroy', $g) }}" onsubmit="return confirm('Remove “{{ $g->title }}”?')">@csrf @method('DELETE')<button class="btn btn-small btn-danger">Remove</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="muted">No worlds match.</td></tr>
        @endforelse
    </table>
    {{ $games->links() }}
</div>
@endsection
