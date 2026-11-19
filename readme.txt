=== Backdrop Custom Portfolio ===

Contributors: benlumia007
Tags: portfolio
Requires CP: 1.0
Tested up to: 6.2
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and manage custom post types for your portfolio.

== Description ==

Backdrop Custom Portfolio is a post type manager for creative people such as artists, photographers, and Web designers to showcase their work.

Every post type is a row in one registry, managed from Portfolio -> Post Types. Portfolio itself is the first row; add more the same way, such as Photography or Themes. They all share the portfolio categories and tags, and each type can live under the portfolio base (for example /portfolio/themes/) or at the top level (for example /themes/).

== Frequently Asked Questions ==

= How do I add another post type, such as Photography? =

Go to Portfolio -> Post Types -> Add New. Enter a slug (used in the URL), a singular name and a plural name, choose where it appears in the admin menu, and save. The new type shares the portfolio categories and tags. Its archive defaults to /portfolio/{slug}/; set the rewrite slug to a plain slug such as "photography" to put it at /photography/ instead.

= What happens to my content if I remove a post type? =

Delete it from Portfolio -> Post Types. The posts are not deleted, but they are not shown anywhere until the post type is added again with the same slug.

= What happens when I update from 1.x? =

The 1.x settings migrate once, on first load: the main type and the extra types become rows in the new registry with the same post type names and URLs, and the shared settings carry over. Post types created in 1.x keep the root-level URL they had; edit a type to move it under the portfolio base.

= Can I add post types from code? =

Yes. Use the `bcp/post_types` filter. Each entry has `singular`, `plural` and `rewrite` keys, and optional `menu` (one of `portfolio`, `own` or `hidden`) and `icon` keys. The `bcp/portfolio/args` and `bcp/portfolio/labels` filters receive the post type name as a second argument.

= Does this work with other themes? =

It is built on the Backdrop framework, so it shares the framework with Backdrop themes such as Prismatic. It does not need a Backdrop theme.

== Installation ==

1. In your admin panel, go to Plugins -> Add New and click the Upload Plugin button.
2. Choose the plugin's ZIP file, then click Install Now.
3. Click Activate to use your new plugin right away.

== Development ==

The stylesheet is compiled with Vite from `resources/scss/screen.scss`. Source and build instructions are at https://github.com/techkodeninja/backdrop-custom-portfolio.

== Changelog ==

= 2.0.0 =
* Post types are now managed from Portfolio -> Post Types (list, add, edit, delete) instead of the extra-types table on the settings page. Portfolio is the seeded first row of one registry.
* Each post type has a rewrite slug (defaults to /{base}/{slug}/, or a plain slug for a top-level archive) and a menu icon.
* The 10-extra-types cap is gone.
* Updating from 1.x migrates the settings once: same post type names and URLs, shared settings carried over.
* Includes the 1.1.0 fixes: works with Backdrop framework 1.x and 2.0 themes, categories and tags under the portfolio base, and the regenerated translation template.

= 1.1.0 =
* Added extra post types, configured from Portfolio -> Settings or with the `bcp/post_types` filter, each with a menu setting (under Portfolio, its own menu, or hidden).
* Added all portfolio post types to the At a Glance dashboard widget.
* Added excerpt and menu order support, and more admin notice labels.
* Added uninstall cleanup and a clearer notice when dependencies are missing.
* Now requires the Backdrop framework 1.x so it can share a request with Backdrop themes such as Prismatic.
* Replaced Laravel Mix with Vite for building assets, and removed the backdrop-dev/mix dependency.

= 1.0.3 =
* Previous release.
