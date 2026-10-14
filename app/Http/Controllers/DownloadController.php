<?php

namespace App\Http\Controllers;

use App\Models\Release;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function index(): View
    {
        $releases = Release::orderByDesc('released_at')->orderBy('platform')->get();

        return view('downloads.index', [
            'latestVersion' => $releases->first()?->version,
            'byPlatform' => $releases->groupBy('platform'),
        ]);
    }

    public function download(Release $release): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($release->file_path), 404, 'Release file is missing.');
        $release->increment('downloads');

        return Storage::disk('local')->download($release->file_path, $release->file_name);
    }
}
