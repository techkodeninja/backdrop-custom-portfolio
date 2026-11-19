# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Fixed
- The plugin now works with Backdrop framework 1.x and 2.0 themes. When the active theme already provides a framework, the plugin shares it instead of loading its bundled copy (1.x), which previously caused a fatal error (`Cannot redeclare function Backdrop\Attr\attr()`) with 2.0 themes. The bundled framework is now only loaded when no theme framework is present. The plugin's functions are loaded directly instead of through Composer's "files" autoload, and `app/framework.php` is removed (its bootstrap moved into the main plugin file).
- Regenerated the translation template (`public/lang/backdrop-custom-portfolio.pot`): 118 strings, up from 73, now including all 1.1.0 strings and the current version (1.1.0). `Report-Msgid-Bugs-To` now points at the GitHub issues instead of wordpress.org, and a new `scripts/fix-pot-header.mjs` keeps it that way on every regeneration (node-wp-i18n always writes the wordpress.org address).

## [1.1.0] - 2026-11-17

### Added
- Extra post types. Add more portfolio-style post types (for example Photography or Themes) from **Portfolio → Settings → Extra Post Types**, or in code with the `bcp/post_types` filter. They share the portfolio categories and tags.
- A Menu setting for each extra post type: under the Portfolio menu (the default), its own top-level menu, or hidden from the menu.
- Every portfolio post type is listed in the "At a Glance" dashboard widget, including ones with no published items yet.
- Excerpt and page attributes (menu order) support on portfolio post types.
- Labels for the "published", "updated", "scheduled" and "reverted to draft" notices, plus archives and attributes.
- A FAQ in `readme.txt`, `Requires CP`, a `LICENSE` file, and `uninstall.php`, which removes the plugin's options when it is deleted.
- Merging a `release/x.y.z` pull request into `develop` now tags and publishes the release and attaches the plugin ZIP.

### Changed
- Requires `backdrop-dev/framework` `^1.0.1` (the 1.x line that Backdrop themes such as Prismatic use) instead of `^2.0`. Two copies of the framework from different lines can't share one request, so the plugin must use the same line as the theme. The separate `backdrop-dev/contracts` package is no longer needed because 1.x ships its contracts.
- The framework boots on `after_setup_theme`, after the theme is set up, and reuses a Backdrop theme's application when there is one.
- A missing `vendor/` folder now shows an admin notice instead of a fatal error.
- Translations load from `public/lang` (`Domain Path` corrected).
- The rewrite base is sanitized as a slug and can't use reserved slugs (such as `page` and `category`).
- Rewrite rules are flushed once on the next request after the settings change, instead of while rendering the settings page.
- The admin stylesheet handle is now `bcp-admin` and only loads on portfolio list screens.
- The thumbnail column now shows the right image for each row and sits after the title.
- `composer.lock` is no longer committed; the framework is resolved from `composer.json`.
- Assets are built with Vite instead of Laravel Mix: `resources/scss/screen.scss` compiles to `public/assets/css/screen.css`, `scripts/export.mjs` builds the release folder, and the npm script `watch` is now `dev`. The admin stylesheet is enqueued directly and versioned with the file's modified time.
- The release workflow uses current actions and the built-in `GITHUB_TOKEN`.

### Removed
- `laravel-mix` and `sass-loader` dev dependencies, `webpack.mix.js` and `public/mix-manifest.json`.
- The `backdrop-dev/mix` Composer dependency and its service provider.

### Filters
- `bcp/post_types` (new), `bcp/portfolio/args` and `bcp/portfolio/labels` now receive the post type name as a second argument.

## [1.0.3]

Current release prior to the Vite migration.
