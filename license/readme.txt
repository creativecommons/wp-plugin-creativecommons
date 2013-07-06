=== License ===
Contributors: mitchoyoshitaka, bmellor
Author: mitcho (Michael Yoshitaka Erlewine), Brett Mellor
Author URI: http://ecs.mit.edu/
Tags: creative commons, CC, license, copyright, copyleft, attribution, ownership, all rights reserved, some rights reserved, footer, widget
Requires at least: 3.1
Tested up to: 3.3
Stable tag: 0.5

The license plugin gives authors the ability to mark their content with a Creative Commons license and to display the license along with the content.  

== Description ==

The License plugin gives authors the ability to mark their content with a [Creative Commons](http://creativecommons.org/) license and to display the license along with the content.  With Creative Commons licenses, you keep your copyright but share your creativity.  By default, the plugin will display a license in the footer of your theme.  The license plugin is also a widget, and can be dragged to any widget area.  This will supress display of the license in the theme footer and display it instead in the widget area.

The site default license is the Creative Commons [Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)](http://creativecommons.org/licenses/by-nc-sa/2.0/) license.  Authors can set their own default license using the license settings provided under Personal Options on the edit profile page.  The license can also be set for each individual post using the setting provided in the Publish box while creating a new post.  

Authors can choose how they would like their attribution to be displayed on the site.  The author can attribute the work to their display name or their nickname as defined in their profile options.  Alternatively, the author can attribute the work to the site as defined in the General Settings of the site.  

This plugin is a component of the [MIT Educational Collaboration Space](http://ecs.mit.edu) project.

== Installation ==

1. Upload the `license` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. From the Widgets menu, drag the License widget to the widget area in which you would like the license to appear.  Otherwise, the license will appear in the Wordpress footer area by default.

== Frequently Asked Questions ==

= Your question here! =

Our answer here!

== Screenshots ==

1. License settings appear under Personal Options when user is editing their profile
2. A dialog is provided by Creative Commons for choosing a license type
3. License setting is available in the Publish Box when creating new content
4. A license is displayed in the footer of a page including a Creative Commons license mark, title of the content, and to whom the license is atributed.

== Changelog ==

= 0.5 =
* Fixed a XSS security vulnerability. props duck_.
* Now requires the PHP JSON extension, which is normally a standard part of PHP builds.

= 0.4 =
* removed hard coded styling from license_print_license_html

= 0.3 =
* Initial public release.