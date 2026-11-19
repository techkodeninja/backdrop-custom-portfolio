# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [2.0.0] - Unreleased

### Added
- A Post Types manager under the new top-level **Portfolio** menu: list, add, edit and delete post types from **Portfolio → Post Types**, instead of the extra-types table on the settings page. Every post type is a row in one registry (`bcp_post_types`); Portfolio is the seeded first row, not special code.
- A rewrite slug field per post type. Leave it empty and the type lives under the portfolio base (`/{base}/{slug}/`, or just `/{base}/` for the type whose slug is the base); enter a plain slug such as `themes` to put its archive at the top level instead.
- A menu icon field per post type (a Dashicon class, used when the type has its own menu).
- Category and tag slugs are now settings on **Portfolio → Settings → Permalinks**, alongside the portfolio base.

### Changed
- Post types moved from the `bcp_settings` option (a fixed main type plus up to 10 extra types) into the `bcp_post_types` registry. The settings option now holds only the shared settings: title, description, rewrite base, and the category/tag slugs.
- The 10-extra-types cap is gone: a manager with an arbitrary cap is wrong.
- New post types default to living under the portfolio base (`/{base}/{slug}/`). Post types created in 1.x keep the root-level URL they had (`/{slug}/`), stored as a rewrite override — edit a type to move it under the base.
- The portfolio base is now the URL prefix for the categories and tags and the default prefix for new post types. Post types already in the registry keep their own rewrite slug when the base changes; edit a type to move it.
- Updating from 1.x migrates once, on first load: the main type and the extra types become registry rows with the same post type names and rewrite slugs, the shared settings carry over, and the rewrite rules are flushed. The old `portfolio_type` and `extra_types` settings are removed.
- The `bcp/post_types` filter still works the same way: it filters the registry output. Entries may also carry `menu` and `icon` keys now.

### Fixed
- The plugin now works with Backdrop framework 1.x and 2.0 themes. When the active theme already provides a framework, the plugin shares it instead of loading its bundled copy (1.x), which previously caused a fatal error (`Cannot redeclare function Backdrop\Attr\attr()`) with 2.0 themes. The bundled framework is now only loaded when no theme framework is present. The plugin's functions are loaded directly instead of through Composer's "files" autoload, and `app/framework.php` is removed (its bootstrap moved into the main plugin file).
- Regenerated the translation template (`public/lang/backdrop-custom-portfolio.pot`): 118 strings, up from 73, now including all 1.1.0 strings and the current version (1.1.0). `Report-Msgid-Bugs-To` now points at the GitHub issues instead of wordpress.org, and a new `scripts/fix-pot-header.mjs` keeps it that way on every regeneration (node-wp-i18n always writes the wordpress.org address).
- Portfolio categories and tags now live under the portfolio base (`/{base}/portfolio-category/{term}/` and `/{base}/portfolio-tag/{term}/`) as the rewrite base docblock always promised, instead of sitting at the top level. After updating, re-save the portfolio settings or permalinks once so the rewrite rules pick the new URLs up.
- Two extra post types whose slugs shorten to the same post type name no longer silently drop the second one: saving the settings now reports which slug collided with which.
- Saving more than 10 extra post types now says so: only the first 10 are kept, and the settings page reports that the rest were dropped instead of silently discarding them.
- The rewrite-base reserved check now inspects every segment, so a base like `work/page` is rejected instead of slipping a reserved slug in past the first-segment check.
- `bcp_get_portfolio_rewrite_slug()` is now a deprecated alias for `bcp_get_portfolio_rewrite_base()` — one function and one filter for the value instead of two of each. The `bcp_get_project_rewrite_slug` filter still works.
- The extra post types table no longer uses an inline `<style>` block: its rules moved into the admin stylesheet (`resources/scss/screen.scss` and the built `public/assets/css/screen.css`), which is now also enqueued on the settings page.
- readme.txt now states `Tested up to: 6.2`.
- Portfolio post types use the `dashicons-portfolio` menu icon instead of `dashicons-category`.
- Provider docblocks corrected (`1..0.0` / `5.0.0` versions, "Sidebar Provider" / "Attr provider class" copy-paste).
- The missing-dependencies admin notice now loads the text domain before translating, so it can no longer show untranslated.

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
