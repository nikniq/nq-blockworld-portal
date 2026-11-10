<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Release;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReleaseController extends Controller
{
    public function index(): View
    {
        $releases = Release::orderByDesc('released_at')->orderBy('platform')->get()
            ->each(fn (Release $r) => $r->present = Storage::disk('local')->exists($r->file_path));

        return view('admin.releases', compact('releases'));
    }

    public function update(Request $request, Release $release): RedirectResponse
    {
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);
        $release->update(['notes' => $data['notes'] ?? null]);

        return back()->with('status', 'Notes saved for '.$release->file_name.'.');
    }

    public function destroy(Release $release): RedirectResponse
    {
        Storage::disk('local')->delete($release->file_path);
        $release->delete();

        return back()->with('status', 'Removed '.$release->file_name.'.');
    }
}
