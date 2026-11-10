@extends('layouts.app')
@section('title', 'Admin · Releases')

@section('content')
<div class="admin">
    <h1>Releases</h1>
    @include('admin._tabs')
    <p class="muted small">Builds are published with <code>./cx.sh 40</code> from your machine; here you can annotate or withdraw them.</p>
    <table>
        <tr><th>Version</th><th>Platform</th><th>Arch</th><th>Size</th><th>Downloads</th><th>Released</th><th>File</th><th>Notes</th><th></th></tr>
        @forelse ($releases as $r)
            <tr>
                <td>v{{ $r->version }}</td>
                <td>{{ $r->platformLabel() }}</td>
                <td>{{ $r->arch }}</td>
                <td>{{ $r->humanSize() }}</td>
                <td>{{ $r->downloads }}</td>
                <td class="small">{{ $r->released_at?->toDateString() }}</td>
                <td>@if ($r->present)<span class="tag tag-ok">on disk</span>@else<span class="tag tag-warn">file missing</span>@endif<br><span class="small mono">{{ $r->file_name }}</span></td>
                <td>
                    <form method="post" action="{{ route('admin.releases.update', $r) }}">@csrf @method('PUT')
                        <textarea name="notes" class="narrow-notes" maxlength="2000" placeholder="install notes shown on the download page">{{ $r->notes }}</textarea>
                        <button class="btn btn-small btn-ghost">Save notes</button>
                    </form>
                </td>
                <td class="actions">
                    <form method="post" action="{{ route('admin.releases.destroy', $r) }}" onsubmit="return confirm('Withdraw {{ $r->file_name }}? The archive is deleted from the server.')">@csrf @method('DELETE')<button class="btn btn-small btn-danger">Withdraw</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="muted">No releases published.</td></tr>
        @endforelse
    </table>
</div>
@endsection
