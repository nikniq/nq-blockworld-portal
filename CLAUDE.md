# CLAUDE.md

Laravel 13 portal for the Blockworld game (sibling repo `../nq-blockworld`). No Node build: views are plain
Blade under `resources/views`, styling is `public/css/app.css`.

- Run: `php artisan serve`; tests: `php artisan test`; lint: `vendor/bin/pint`.
- Releases are imported from the game repo's `dist/` by `ReleaseSeeder` (`php artisan db:seed`). Release
  archives and uploaded saves live on the private `local` disk; avatars and screenshots on `public`
  (needs `php artisan storage:link`).
- Models: `User` (routed by `handle`), `Release`, `SharedGame` (routed by `slug`, owner checks in
  `SharedGamePolicy`). Platform and mode option lists are constants on `User` and `SharedGame`.

## Git push
Auto-commit and push to GitHub (`origin main`) after every completed change, without waiting to be told.
Run `php artisan test` and `vendor/bin/pint --test` first; only push if both pass.
