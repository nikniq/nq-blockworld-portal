<a href="{{ route('games.show', $game) }}" class="card game">
    @if ($game->screenshot_path)
        <img src="{{ Storage::url($game->screenshot_path) }}" alt="" class="shot">
    @else
        <div class="shot shot-blank mode-{{ $game->mode }}"></div>
    @endif
    <div class="card-body">
        <strong>{{ $game->title }}</strong>
        <div class="small muted">by {{ $game->user->name }} · {{ $game->modeLabel() }} · {{ $game->created_at->diffForHumans() }}</div>
        <div class="small">Score {{ number_format($game->score) }} · {{ $game->nights }} nights · {{ number_format($game->downloads) }} downloads</div>
    </div>
</a>
