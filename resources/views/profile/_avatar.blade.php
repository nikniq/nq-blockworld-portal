@if ($user->avatar_path)
    <img src="{{ Storage::url($user->avatar_path) }}" alt="" class="avatar {{ $size ?? '' }}">
@else
    <span class="avatar avatar-blank {{ $size ?? '' }}">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
@endif
