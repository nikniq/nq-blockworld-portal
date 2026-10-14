<label>Title <input name="title" value="{{ old('title', $game->title ?? '') }}" required maxlength="80"></label>
<label>Description <textarea name="description" rows="6" maxlength="2000" placeholder="What is in this world? Where is the base, what has been built, what is still out there?">{{ old('description', $game->description ?? '') }}</textarea></label>
<div class="row">
    <label>Mode
        <select name="mode">@foreach (\App\Models\SharedGame::MODES as $k => $v)<option value="{{ $k }}" @selected(old('mode', $game->mode ?? 'survival') === $k)>{{ $v }}</option>@endforeach</select>
    </label>
    <label>World size
        <select name="world_size">@foreach (\App\Models\SharedGame::SIZES as $k => $v)<option value="{{ $k }}" @selected(old('world_size', $game->world_size ?? 'large') === $k)>{{ $v }}</option>@endforeach</select>
    </label>
</div>
<div class="row">
    <label>Score <input type="number" name="score" min="0" value="{{ old('score', $game->score ?? 0) }}"></label>
    <label>Nights survived <input type="number" name="nights" min="0" value="{{ old('nights', $game->nights ?? 0) }}"></label>
</div>
<label>Save file <input type="file" name="save" accept=".sav" @required(!isset($game))><small>Your world.sav from the Blockworld data folder (up to 64 MB).@isset($game) Leave empty to keep the current file.@endisset</small></label>
<label>Screenshot <input type="file" name="screenshot" accept="image/*"><small>Optional, up to 4 MB.</small></label>
