# CLAUDE.md

Laravel 13 portal for the Blockworld game (sibling repo `../nq-blockworld`). No Node build: views are plain
Blade under `resources/views`, styling is `public_html/css/app.css`.

- Run: `php artisan serve`; tests: `php artisan test`; lint: `vendor/bin/pint`.
- Releases are imported from the game repo's `dist/` by `ReleaseSeeder` (`php artisan db:seed`). Release
  archives and uploaded saves live on the private `local` disk; avatars and screenshots on `public`
  (needs `php artisan storage:link`).
- Models: `User` (routed by `handle`), `Release`, `SharedGame` (routed by `slug`, owner checks in
  `SharedGamePolicy`). Platform and mode option lists are constants on `User` and `SharedGame`.

## Git push
Auto-commit and push to GitHub (`origin main`) after every completed change, without waiting to be told.
Run `php artisan test` and `vendor/bin/pint --test` first; only push if both pass.

The web root is `public_html/` (not `public/`), set in `bootstrap/app.php` and `vite.config.js`.

## Deployment
Production is shared hosting with no Node (none needed). `deploy.sh` runs on the server (pull, composer if
the lockfile moved, migrate, storage link, optimize, verify; `--releases` imports uploaded builds). `cx.sh`
runs locally and drives the server over SSH from `vars.sh` (gitignored, see `vars.sh.example`); option 40
uploads game builds from the game repo's `dist/` and seeds them. After pushing, remind the user to run
`./cx.sh 20` (or `deploy.sh` on the server), since there is no SSH access from this environment.
