=== Backdrop Custom Portfolio ===

Contributors: benlumia007
Tags: portfolio
Requires CP: 1.0
Tested up to: 6.2
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A complete portfolio solution for creative people.

== Description ==

Backdrop Custom Portfolio is a portfolio management plugin for creative people such as artists, photographers, and Web designers to showcase their work.

You can add more portfolio-style post types, such as Photography or Web Design, from Portfolio -> Settings. They share the portfolio categories and tags.

== Frequently Asked Questions ==

= How do I add another post type, such as Photography? =

Go to Portfolio -> Settings -> Extra Post Types. Enter a slug (used in the URL), a singular name and a plural name, choose where it appears in the admin menu, and save. The extra types share the portfolio categories and tags.

= What happens to my content if I remove a post type? =

Clear its slug and save. The posts are not deleted, but they are not shown anywhere until the post type is added again with the same slug.

= Can I add post types from code? =

Yes. Use the `bcp/post_types` filter. Each entry has `singular`, `plural` and `rewrite` keys, and an optional `menu` key of `portfolio`, `own` or `hidden`. The `bcp/portfolio/args` and `bcp/portfolio/labels` filters receive the post type name as a second argument.

= Does this work with other themes? =

It is built on the Backdrop framework, so it shares the framework with Backdrop themes such as Prismatic. It does not need a Backdrop theme.

== Installation ==

1. In your admin panel, go to Plugins -> Add New and click the Upload Plugin button.
2. Choose the plugin's ZIP file, then click Install Now.
3. Click Activate to use your new plugin right away.

== Development ==

The stylesheet is compiled with Vite from `resources/scss/screen.scss`. Source and build instructions are at https://github.com/techkodeninja/backdrop-custom-portfolio.

== Changelog ==

= 1.1.0 =
* Added extra post types, configured from Portfolio -> Settings or with the `bcp/post_types` filter, each with a menu setting (under Portfolio, its own menu, or hidden).
* Added all portfolio post types to the At a Glance dashboard widget.
* Added excerpt and menu order support, and more admin notice labels.
* Added uninstall cleanup and a clearer notice when dependencies are missing.
* Now requires the Backdrop framework 1.x so it can share a request with Backdrop themes such as Prismatic.
* Replaced Laravel Mix with Vite for building assets, and removed the backdrop-dev/mix dependency.

= 1.0.3 =
* Previous release.
