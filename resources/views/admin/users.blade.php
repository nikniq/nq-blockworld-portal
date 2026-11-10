@extends('layouts.app')
@section('title', 'Admin · Players')

@section('content')
<div class="admin">
    <h1>Players</h1>
    @include('admin._tabs')
    <form class="search" method="get"><input name="q" value="{{ $q }}" placeholder="handle, name or email"><button class="btn btn-small">Search</button></form>
    <table>
        <tr><th>Handle</th><th>Name</th><th>Email</th><th>Login</th><th>Worlds</th><th>Joined</th><th>Admin</th><th></th></tr>
        @forelse ($users as $u)
            <tr>
                <td><a href="{{ route('profile.show', $u) }}">{{ $u->handle }}</a></td>
                <td>{{ $u->name }}</td>
                <td class="small">{{ $u->email }}</td>
                <td><span class="tag">{{ $u->google_id ? 'Google' : 'password' }}</span></td>
                <td>{{ $u->games_count }}</td>
                <td class="small">{{ $u->created_at->toDateString() }}</td>
                <td>@if ($u->is_admin)<span class="tag tag-ok">admin</span>@endif</td>
                <td class="actions">
                    @unless ($u->is(auth()->user()))
                        <form method="post" action="{{ route('admin.users.admin', $u) }}">@csrf<button class="btn btn-small btn-ghost">{{ $u->is_admin ? 'Revoke admin' : 'Make admin' }}</button></form>
                        <form method="post" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete @{{ $u->handle }} and their {{ $u->games_count }} world(s)?')">@csrf @method('DELETE')<button class="btn btn-small btn-danger">Delete</button></form>
                    @else
                        <span class="muted small">you</span>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted">No players match.</td></tr>
        @endforelse
    </table>
    {{ $users->links() }}
</div>
@endsection
