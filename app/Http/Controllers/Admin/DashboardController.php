<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Release;
use App\Models\SharedGame;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $log = storage_path('logs/laravel.log');
        $errors = [];
        if (File::exists($log)) {
            // Last few error lines, newest first, without reading the whole file.
            $tail = array_slice(File::lines($log)->all(), -400);
            foreach (array_reverse($tail) as $line) {
                if (preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY): /', $line)) {
                    $errors[] = mb_strimwidth($line, 0, 220, '…');
                    if (count($errors) >= 8) {
                        break;
                    }
                }
            }
        }

        return view('admin.index', [
            'stats' => [
                'Players' => User::count(),
                'Joined this week' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'Admins' => User::where('is_admin', true)->count(),
                'Via Google' => User::whereNotNull('google_id')->count(),
                'Shared worlds' => SharedGame::count(),
                'Shared this week' => SharedGame::where('created_at', '>=', now()->subDays(7))->count(),
                'World downloads' => SharedGame::sum('downloads'),
                'Game downloads' => Release::sum('downloads'),
                'Upload storage' => humanSize((int) SharedGame::sum('save_size')),
            ],
            'recentUsers' => User::latest()->take(5)->get(),
            'recentGames' => SharedGame::with('user')->latest()->take(5)->get(),
            'env' => [
                'Environment' => config('app.env'),
                'Debug' => config('app.debug') ? 'ON (turn off in production)' : 'off',
                'Google sign-in' => config('services.google.client_id') ? 'on' : 'off',
                'PHP' => PHP_VERSION,
                'Laravel' => app()->version(),
                'Database' => config('database.default'),
                'Log size' => File::exists($log) ? humanSize(File::size($log)) : 'no log',
            ],
            'logErrors' => $errors,
        ]);
    }
}
