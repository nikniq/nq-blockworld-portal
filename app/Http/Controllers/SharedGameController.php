<?php

namespace App\Http\Controllers;

use App\Models\SharedGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SharedGameController extends Controller
{
    public function index(Request $request): View
    {
        $mode = $request->query('mode');
        $sort = $request->query('sort', 'new');
        $query = SharedGame::with('user')
            ->when($mode && isset(SharedGame::MODES[$mode]), fn ($q) => $q->where('mode', $mode));
        match ($sort) {
            'popular' => $query->orderByDesc('downloads'),
            'score' => $query->orderByDesc('score'),
            default => $query->latest(),
        };

        return view('games.index', [
            'games' => $query->paginate(12)->withQueryString(),
            'mode' => $mode,
            'sort' => $sort,
        ]);
    }

    public function show(SharedGame $game): View
    {
        return view('games.show', ['game' => $game->load('user')]);
    }

    public function create(): View
    {
        return view('games.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $save = $request->file('save');
        $data['save_path'] = $save->storeAs('games', uniqid('world-', true).'.sav', 'local');
        $data['save_size'] = $save->getSize();
        if ($request->hasFile('screenshot')) {
            $data['screenshot_path'] = $request->file('screenshot')->store('screenshots', 'public');
        }

        $game = $request->user()->games()->create($data);

        return redirect()->route('games.show', $game)->with('status', 'Your world is shared.');
    }

    public function edit(SharedGame $game): View
    {
        Gate::authorize('update', $game);

        return view('games.edit', ['game' => $game]);
    }

    public function update(Request $request, SharedGame $game): RedirectResponse
    {
        Gate::authorize('update', $game);
        $data = $this->validated($request, false);
        if ($request->hasFile('save')) {
            Storage::disk('local')->delete($game->save_path);
            $data['save_path'] = $request->file('save')->storeAs('games', uniqid('world-', true).'.sav', 'local');
            $data['save_size'] = $request->file('save')->getSize();
        }
        if ($request->hasFile('screenshot')) {
            if ($game->screenshot_path) {
                Storage::disk('public')->delete($game->screenshot_path);
            }
            $data['screenshot_path'] = $request->file('screenshot')->store('screenshots', 'public');
        }
        $game->update($data);

        return redirect()->route('games.show', $game)->with('status', 'World updated.');
    }

    public function download(SharedGame $game): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($game->save_path), 404, 'Save file is missing.');
        $game->increment('downloads');

        return Storage::disk('local')->download($game->save_path, 'world.sav');
    }

    public function destroy(SharedGame $game): RedirectResponse
    {
        Gate::authorize('delete', $game);
        Storage::disk('local')->delete($game->save_path);
        if ($game->screenshot_path) {
            Storage::disk('public')->delete($game->screenshot_path);
        }
        $game->delete();

        return redirect()->route('games.index')->with('status', 'World removed.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $saveRequired = true): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
            'mode' => ['required', 'in:'.implode(',', array_keys(SharedGame::MODES))],
            'world_size' => ['required', 'in:'.implode(',', array_keys(SharedGame::SIZES))],
            'score' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'nights' => ['nullable', 'integer', 'min:0', 'max:100000'],
            // world.sav is a plain binary; validate on extension and size (64 MB).
            'save' => [$saveRequired ? 'required' : 'nullable', 'file', 'max:65536', 'extensions:sav'],
            'screenshot' => ['nullable', 'image', 'max:4096'],
        ]);
        $data['score'] ??= 0;
        $data['nights'] ??= 0;
        unset($data['save'], $data['screenshot']);

        return $data;
    }
}
