=== Creative Commons ===

Contributors: ahmadbilaldev, cctimidrobot, BjornW, robmyers, tatti
Donate link: https://us.netdonor.net/page/6650/donate/1?ea.tracking.id=top-of-page-banner
Tags: Creative Commons, CC, license, copyright, copyleft, attribution, attribute, ownership, all rights reserved, some rights reserved, footer, widget
Requires at least: 3.1
Tested up to: 5.2
Stable tag: v2019.7.1
Requires PHP: 5.6.20
License: GPLv2 or later
License URI: https://gnu.org/licenses/gpl-2.0.html

Official Creative Commons plugin for licensing your content. With Creative
Commons licenses, keep your copyright AND share your creativity.


== Description ==

The CreativeCommons WordPress plugin gives authors the ability to license
content with a Creative Commons license
([Choose a License](https://creativecommons.org/choose/)). With this plugin
you can:

* License your blog (single WordPress install)
* Display license for the site, posts and pages
* Prevent license changes per site (all pages on a site must use the same
  license)
* License your WordPress Network (WordPress Multisite install)
* License some of your sites differently in your WordPress Network
* Prevent license changes in your WordPress Network (all pages on all sites
  need to use the same license)
* License all your content with the same license (license per author)
* License some posts, pages, or images differently from your default license
  (per content license)
* License posts and pages by simply including CC Gutenberg blocks for each
  license required (Gutenberg License Blocks)
* Display license information with "One Click Attribution" for images

The default license used by the plugin is the Creative Commons
[Attribution-ShareAlike
(CC BY-SA)](http://creativecommons.org/licenses/by-sa/4.0/) license. This can
be easily changed including attribution, depending on the permissions by a user
with the role: superadmin, site admin or author.

By default, the plugin will display a license in the footer of your theme. The
license plugin is also a widget, and can be dragged to any widget area like the
side-bar and all other available areas (this will suppress display of the
license in the theme footer and display it only in the widget area).


== Installation ==

1. Visit ‘Plugins > Add New’ and search for the plugin to install it. **OR**
   Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. From the Widgets menu, drag the License widget to the widget area in which
   you would like the license to appear.  Otherwise, the license will appear in
   the Wordpress footer area by default.


== Frequently Asked Questions ==


= Where can I find more information about the plugin? =

See the GitHub project: [creativecommons/wp-plugin-creativecommons](https://github.com/creativecommons/wp-plugin-creativecommons)


== Screenshots ==

1. In wp-admin, you can access the Creative Commons page inside the Settings. This page has all the license settings. By pressing "Change License", you can change the license.
2. If you are creating a page or a post with the Gutenberg editor, you can include license to any content or the page/post itself by CC Gutenberg blocks. They are bundled in a seperate category.
3. Selecting a block adds the respective license block. You can also change backgrounf and text colors of the block from the color pallete on the right.
4. Gutenberg Block included in a post.
5. Default license as a widget.


== Changelog ==


= 2019.7.1 =

* Initial release.
* The plugin is an updated and revamped version of the WPLicense plugin by
  Creative Commons: https://github.com/tarmot/wp-cc-plugin
* The pugin has been made compatible to the latest version of WordPress (5.2.2)
  while the former one was stable only up to 3.8.1.
* The revamp brings bug fixes and security fixes.
* Gutenberg blocks for Creative Commons have been added which can be used in
  posts and pages.
