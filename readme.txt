=== Backdrop Custom Portfolio ===

Contributors: benlumia007
Tags: portfolio
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A complete portfolio solution for creative people.

== Description ==

Backdrop Custom Portfolio is a portfolio management plugin for creative people such as artists, photographers, and Web designers to showcase their work.

== Installation ==

1. In your admin panel, go to Plugins -> Add New and click the Upload Plugin button.
2. Choose the plugin's ZIP file, then click Install Now.
3. Click Activate to use your new plugin right away.

== Development ==

The stylesheet is compiled with Vite from `resources/scss/screen.scss`. Source and build instructions are at https://github.com/techkodeninja/backdrop-custom-portfolio.

== Changelog ==

= Unreleased =
* Replaced Laravel Mix with Vite for building assets.
* Removed the backdrop-dev/mix dependency; the admin stylesheet is now enqueued directly.
