=== License ===
Contributors: BjornW, mitchoyoshitaka, bmellor
Author: Bjorn Wijers <burobjorn@burobjorn.nl> ,  mitcho (Michael Yoshitaka Erlewine), Brett Mellor
Author URI: http://burobjorn.nl, http://ecs.mit.edu/
Tags: creative commons, CC, license, copyright, copyleft, attribution, ownership, all rights reserved, some rights reserved, footer, widget
Requires at least: 3.1
Tested up to: 3.6.1
Stable tag: 0.7

The license plugin gives authors the ability to mark their content with a Creative Commons license and to display the license along with the content.  

== Description ==

The License plugin gives authors the ability to mark their content with a [Creative Commons](http://creativecommons.org/) license and to display the license along with the content.
With Creative Commons licenses, you keep your copyright but share your creativity. 

By default, the plugin will display a license in the footer of your theme. The license plugin is also a widget, and can be dragged to any widget area.
This will suppress display of the license in the theme footer and display it instead in the widget area.

The default license used by the plugin is the Creative Commons [Attribution-ShareAlike (CC BY-SA)](http://creativecommons.org/licenses/by-sa/3.0/) license.
This can be easily changed including attribution, depending on the permissions by a user with the role: superadmin, site admin or author. 

This plugin is based on the License plugin (a component of the [MIT Educational Collaboration Space](http://ecs.mit.edu) project) by mitcho (Michael Yoshitaka Erlewine) & Brett Mellor. 
It will work perfectly fine with WordPress Network (aka Multisite). 

== Installation ==

1. Upload the `license` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. From the Widgets menu, drag the License widget to the widget area in which you would like the license to appear.  Otherwise, the license will appear in the Wordpress footer area by default.

== Frequently Asked Questions ==

= Your question here! =

Our answer here!

== Screenshots ==

TODO


== Changelog ==

= 0.7 = 
Forked License plugin
* Rewrote most of the code to support Multisite and add extra features. 

= 0.5 =
* Fixed a XSS security vulnerability. props duck_.
* Now requires the PHP JSON extension, which is normally a standard part of PHP builds.

= 0.4 =
* removed hard coded styling from license_print_license_html

= 0.3 =
* Initial public release.
