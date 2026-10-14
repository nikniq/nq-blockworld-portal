@extends('layouts.app')
@section('title', 'Download')

@section('content')
<h1>Download Blockworld @if($latestVersion)<span class="muted">v{{ $latestVersion }}</span>@endif</h1>
<p class="lead">One file per platform. Unzip it and run. Nothing else to install; the game generates its own textures and sounds.</p>

@if ($byPlatform->isEmpty())
    <p class="muted">No builds have been published yet. Check back soon.</p>
@endif

<div class="grid platforms">
    @foreach (\App\Models\Release::PLATFORMS as $key => $label)
        @php($releases = $byPlatform->get($key, collect()))
        <article class="platform">
            <h2>{{ $label }}</h2>
            @forelse ($releases as $release)
                <div class="release">
                    <a href="{{ route('downloads.file', $release) }}" class="btn">Download v{{ $release->version }} ({{ $release->arch }})</a>
                    <p class="small muted">{{ $release->file_name }} · {{ $release->humanSize() }} · {{ number_format($release->downloads) }} downloads</p>
                    @if ($release->sha256)<p class="small mono">SHA-256 {{ $release->sha256 }}</p>@endif
                    @if ($release->notes)<p class="small">{{ $release->notes }}</p>@endif
                </div>
            @empty
                <p class="muted">Not available yet. Build it from source with <code>./cx.sh</code> on a {{ $label }} machine.</p>
            @endforelse
        </article>
    @endforeach
</div>

<section class="install">
    <h2>Installing</h2>
    <h3>macOS</h3>
    <p>Unzip and drag Blockworld.app to Applications. The build is unsigned, so on first launch right click the app and choose Open, or run:</p>
    <pre>xattr -dr com.apple.quarantine /Applications/Blockworld.app</pre>
    <h3>Linux</h3>
    <p>Extract the archive and run <code>./install.sh</code> to add Blockworld to your application menu, or launch the <code>blockworld</code> binary directly.</p>
    <h3>Windows</h3>
    <p>Unzip and double-click blockworld.exe. Windows SmartScreen may ask you to confirm the first run.</p>
    <h3>Where saves live</h3>
    <p>Your world is saved to <code>world.sav</code> in the Blockworld folder of your user data directory (on macOS, <code>~/Library/Application Support/blockworld/</code>). Drop a downloaded shared world there and press C on the main menu to continue it.</p>
</section>
@endsection
