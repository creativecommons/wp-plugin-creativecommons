# Creative Commons WordPress Plugin

Official Creative Commons plugin for licensing your content. With Creative
Commons licenses, keep your copyright AND share your creativity.

Download the latest version from this project's [releases][releases].

[releases]: https://github.com/creativecommons/creativecommons-wordpress-plugin/releases "Releases · creativecommons/creativecommons-wordpress-plugin"


## Description

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


### Possible future features

Here's a list of some features we'd love to add to this plugin or support in a
separate (child) plugin. If you'd like these or other features to be
implemented you may consider supporting this plugin with code contributions,
testing or funding Creative Commons for further development.

* Allow more kinds of media files to be licensed
* Extract license information from more kinds of media files
* Allow to search (using external search engines) for specific Creative Commons
  licensed media files


## Code of Conduct

[`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md):
> The Creative Commons team is committed to fostering a welcoming community.
> This project and all other Creative Commons open source projects are governed
> by our [Code of Conduct][code_of_conduct]. Please report unacceptable
> behavior to [conduct@creativecommons.org](mailto:conduct@creativecommons.org)
> per our [reporting guidelines][reporting_guide].

[code_of_conduct]:https://creativecommons.github.io/community/code-of-conduct/
[reporting_guide]:https://creativecommons.github.io/community/code-of-conduct/enforcement/


## Installation

1. Upload the "creativecommons-wordpress-plugin" folder to the
   "/wp-content/plugins/" directory
2. Activate the plugin through the "Plugins" menu in WordPress
3. From the Widgets menu, drag the License widget to the widget area in which
   you would like the license to appear.  Otherwise, the license for each page
   or post will appear in the Wordpress footer area by default.


## Usage

The default license used by the plugin is the Creative Commons
[Attribution-ShareAlike
(CC BY-SA)](http://creativecommons.org/licenses/by-sa/4.0/) license. This can
be easily changed including attribution, depending on the permissions by a user
with the role: superadmin, site admin or author.

By default, the plugin will display a license in the footer of your theme. The
license plugin is also a widget, and can be dragged to any widget area like the
side-bar and all other available areas (this will suppress display of the
license in the theme footer and display it only in the widget area).

### Choose A Site License

![Site license chooser](assets/screenshot-1.jpg)

To choose a site-wide license, go to the "Creative Commons" option in the
"Settings" item on the left hand menu in the WordPress admin site.

Be careful when changing these options, they may affect existing published
work.


### Choose An Author License

![Author license chooser](assets/screenshot-3.jpg)

When changing an individual author's license is enabled, the option to do so is
included on the Users Profile page for that author.

You can change the license ("Select a default license") or the attribution
("Set attribution to").


### Choose A Post Or Page License

![Post edit page with license chooser](assets/screenshot-2.jpg)

If changing the license on individual posts and pages is enabled, the option to
do so is included in the editor on the right hand side of the page under the
heading "Licensed:".

You can change the license or the attribution.

Remember to save the post or page after you change its license.


### Choose An Image License

![Media item license metadata editor](assets/screenshot-4.jpg)

In the "Attachment Details" page for an image in the Media Library, there are
fields to edit license and attribution information underneath the usual Title,
Caption and other fields for the image.

Images that support Exif data may include licensing metadata. Where possible
the plugin extracts that metadata and uses it to pre-populate the license
information fields.


### Display An Image License

If you place a `[license]` shortcode around an image that has licensing
metadata, that will display the license block and the One Click Attribution
button:

![Image with license block](assets/screenshot-6.jpg)


## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md).


## Development

If you're interested in the code have a look at the master branch for the
releases. Development will be done in the development branch.

Occasionally other branches may be available to test new features or play with
new ideas, but they may be deleted anytime so don't rely on those branches.


## History

This plugin is loosely based on an existing, but seemingly abandoned WordPress
plugin named 'License' (a component of the [MIT Educational Collaboration
Space][collabspace] project) by mitcho (Michael Yoshitaka Erlewine) and
Brett Mellor. We're also inspired by Creative Commons' original
[wordpress-cc-plugin][oldplugin] written by former Creative Commons CTO Nathan
Yergler.

[collabspace]:http://cispace.mit.edu/
[oldplugin]:https://github.com/cc-archive/wordpress-cc-plugin


### Credits

* Michael Yoshitaka Erlewine (License v0.5)
* Brett Mellor (License v0.5)
* Bjorn Wijers
* Matt Lee
* Rob Myers
* Tarmo Toikkanen


## License

* [`license.txt`](license.txt) ([GPLv2 or later][gplv2] License)

[gplv2]: https://opensource.org/licenses/GPL-2.0 "GNU General Public License version 2 | Open Source Initiative"
