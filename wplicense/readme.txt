=== WPLicense ===
Contributors: BjornW, mitchoyoshitaka, bmellor
Author: Bjorn Wijers <burobjorn@burobjorn.nl> ,  mitcho (Michael Yoshitaka Erlewine), Brett Mellor
Author URI: http://wiki.creativecommons.org/Wplicense
Tags: creative commons, CC, license, copyright, copyleft, attribution, ownership, all rights reserved, some rights reserved, footer, widget
Requires at least: 3.1
Tested up to: 3.8.1
Stable tag: trunk

Official Creative Commons plugin for Wordpress.
This plugin gives authors the ability to mark their content with Creative Commons licenses and to display the licenses along with the content.  

== Description ==

The WPLicense plugin gives authors the ability to mark their content with a [Creative Commons](http://creativecommons.org/) license and to display the license along with the content.
With Creative Commons licenses, you keep your copyright but share your creativity. 

By default, the plugin will display a license in the footer of your theme. The license plugin is also a widget, and can be dragged to any widget area.
This will suppress display of the license in the theme footer and display it instead in the widget area.

The default license used by the plugin is the Creative Commons [Attribution-ShareAlike (CC BY-SA)](http://creativecommons.org/licenses/by-sa/4.0/) license.
This can be easily changed including attribution, depending on the permissions by a user with the role: superadmin, site admin or author. 

WPLicense is based loosely on the License plugin (a component of the [MIT Educational Collaboration Space](http://ecs.mit.edu) project) by mitcho (Michael Yoshitaka Erlewine) & Brett Mellor. Bits from other plugins have also been used, and most of them of course are based on the original WpLicense, written by Creative Commons CTO Nathan Yergler.

WPLicense will work perfectly fine with WordPress Network (aka Multisite). 

== Installation ==

1. Upload the `wplicense` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. From the Widgets menu, drag the License widget to the widget area in which you would like the license to appear.  Otherwise, the license will appear in the Wordpress footer area by default.

== Frequently Asked Questions ==

= Your question here! =

Our answer here!

== Screenshots ==

TODO


== Changelog ==


= 2.0-beta = 
Second version of the WPLicense plugin. Added localization, tested plugin with CC licenses 4.0 
and fixed outstanding issues which can be found in Github repo: https://github.com/tarmot/wp-cc-plugin


= 2.0-alpha = 
First version of the WPLicense plugin. It is not recommended to use this version on production machines and it should be considered unstable. 
This release is for testers only so we can gather some more feedback.



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
