# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- Extra post types. Add more portfolio-style post types (for example Photography) from **Portfolio → Settings**, or in code with the `bcp/post_types` filter. They share the portfolio categories and tags.
- Excerpt and page attributes (menu order) support on portfolio post types.
- Labels for the "published", "updated", "scheduled" and "reverted to draft" notices, plus archives and attributes.
- `uninstall.php`, which removes the plugin's options when it is deleted.
- `LICENSE` file.

### Changed
- Requires `backdrop-dev/framework` `^1.0.1` (the 1.x line that Backdrop themes such as Prismatic use) instead of `^2.0`. Two copies of the framework from different lines can't share one request: the 2.x copy loaded first and broke the theme. The separate `backdrop-dev/contracts` package is no longer needed because 1.x ships its contracts.
- A missing `vendor/` folder now shows an admin notice instead of a fatal error.
- Translations load from `public/lang` (`Domain Path` corrected).
- The rewrite base is sanitized as a slug and can't use reserved slugs (such as `page` and `category`).
- Rewrite rules are flushed once on the next request after the settings change, instead of while rendering the settings page.
- The admin stylesheet handle is now `bcp-admin` and only loads on portfolio list screens.
- The thumbnail column now shows the right image for each row and sits after the title.
- The release workflow uses current actions, the built-in `GITHUB_TOKEN`, and no SSH key.
- Replaced Laravel Mix with Vite for compiling `resources/scss/screen.scss` to `public/assets/css/screen.css`.
- Replaced `webpack.mix.export.js` with `scripts/export.mjs` for building the release folder.
- The admin stylesheet is now enqueued directly and versioned with the file's modified time.
- npm scripts: `watch` is now `dev` (`vite build --watch`), and `prod` runs `vite build`.

### Removed
- `laravel-mix` and `sass-loader` dev dependencies, `webpack.mix.js` and `public/mix-manifest.json`.
- The `backdrop-dev/mix` Composer dependency and its service provider.

### Filters
- `bcp/post_types` (new), `bcp/portfolio/args` and `bcp/portfolio/labels` now receive the post type name as a second argument.

## [1.0.3]

Current release prior to the Vite migration.
