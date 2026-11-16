# Backdrop Custom Portfolio

A portfolio management plugin for WordPress, built for creative people such as artists, photographers, and web designers to showcase their work. It is built on the [Backdrop](https://github.com/backdrop-dev) framework.

## Requirements

- PHP 7.4 or later
- WordPress
- [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) for development

## Installation

1. In your admin panel, go to **Plugins → Add New** and click **Upload Plugin**.
2. Choose the plugin's ZIP file and click **Install Now**.
3. Click **Activate**.

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

Publishing a GitHub release runs `.github/workflows/release.yml`, which builds the plugin and attaches the ZIP to the release.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later. Copyright Benjamin Lu.
