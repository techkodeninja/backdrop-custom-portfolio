# Backdrop Custom Portfolio

A portfolio management plugin for ClassicPress, built for creative people such as artists, photographers, and web designers to showcase their work. It is built on the [Backdrop](https://github.com/backdrop-dev) framework.

## Requirements

- PHP 7.4 or later
- ClassicPress 1.0 or later
- [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) for development

Works with Backdrop framework 1.x and 2.0 themes: the plugin shares the theme's framework when there is one and uses its bundled copy otherwise.

## Installation

1. In your admin panel, go to **Plugins → Add New** and click **Upload Plugin**.
2. Choose the plugin's ZIP file and click **Install Now**.
3. Click **Activate**.

## Multiple post types

Go to **Portfolio → Settings → Extra Post Types** and add a slug (used in the URL), a singular name and a plural name. Each extra type is listed under the Portfolio menu by default (a Menu setting also lets it have its own top-level menu or be hidden) and gets its own archive at `/slug/`, and shares the portfolio categories and tags. Clear a row's slug and save to remove it. Posts are never deleted when a type is removed.

From code, add or change types with the `bcp/post_types` filter:

```php
add_filter( 'bcp/post_types', function( $types ) {
	$types['bcp-photography'] = [
		'singular' => 'Photo Set',
		'plural'   => 'Photography',
		'rewrite'  => 'photography',
	];

	return $types;
} );
```

## Development

```bash
composer install
npm install
```

| Command | What it does |
| --- | --- |
| `npm run dev` | Rebuilds the stylesheet on every change (`vite build --watch`). |
| `npm run prod` | Builds `resources/scss/screen.scss` to `public/assets/css/screen.css` with Vite. |
| `npm run i18n` | Adds the text domain and regenerates `public/lang/backdrop-custom-portfolio.pot`. |
| `npm run export` | Copies the production files into `backdrop-custom-portfolio/`. |
| `npm run build` | Runs `prod`, `i18n`, and `export` in order. |

Install Composer dependencies with `composer install --no-dev` before running `npm run export` so `vendor/` is production-ready.

## Releasing

1. Branch from `develop` as `release/x.y.z` (for example `release/1.1.0`).
2. In that branch, set the version in `backdrop-custom-portfolio.php` (`Version:`), `readme.txt` (`Stable tag:`) and `package.json`, and turn the `[Unreleased]` section of `CHANGELOG.md` into `## [x.y.z] - date`.
3. Open a pull request into `develop` and merge it.

Merging runs `.github/workflows/release.yml`, which checks the three versions and the changelog section match the branch name, tags the merge commit `x.y.z` (no "v"), publishes the release with that changelog section as its notes, then builds the plugin and attaches `backdrop-custom-portfolio.x.y.z.zip`. Publishing a release by hand also works: the ZIP is built and attached.

`composer.lock` is not committed; the framework is resolved from `composer.json` (`^1.0.1`).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later. Copyright Benjamin Lu.
