# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Changed
- Replaced Laravel Mix with Vite for compiling `resources/scss/screen.scss` to `public/assets/css/screen.css`.
- Replaced `webpack.mix.export.js` with `scripts/export.mjs` for building the release folder.
- The admin stylesheet is now enqueued directly and versioned with the file's modified time.
- npm scripts: `watch` is now `dev` (`vite build --watch`), and `prod` runs `vite build`.

### Removed
- `laravel-mix` and `sass-loader` dev dependencies, `webpack.mix.js` and `public/mix-manifest.json`.
- The `backdrop-dev/mix` Composer dependency and its service provider.

## [1.0.3]

Current release prior to the Vite migration.
