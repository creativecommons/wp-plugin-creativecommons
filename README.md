<div align="center">  
<img src="https://mirrors.creativecommons.org/presskit/icons/cc.xlarge.png" height="150">

<h2 align="center">CC WordPress Plugin</h2>
<p align="center">Official Creative Commons plugin for licensing your content on your WordPress website. With Creative Commons licenses, keep your copyright and share your creativity.
</p>

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com) [![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) [![CalVer Format: YYYY.0M.Micro](https://img.shields.io/badge/calver-YYYY.0M.MICRO-22bfda.svg)](https://calver.org/)

</div>

<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-2-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->


## Description

The plugin is an attribution tool. It has multiple features that allow users to
attribute their content by including Creative Commons license ([Choose a
License](https://creativecommons.org/choose/)) on their WordPress website. This
includes default, post, page and media attribution.


## Installation

Download the latest version from this project's [releases][releases]. You can
install the plugin to your WP website using any of these methods:

[releases]: https://github.com/creativecommons/creativecommons-wordpress-plugin/releases "Releases · creativecommons/creativecommons-wordpress-plugin"

1. In your plugin Dashboard on WordPress, Click **Add New** and upload the
   plugin `.zip` file. When installed, activate the plugin.
2. Extract the `.zip` file and paste the extracted folder to the
   "/wp-content/plugins/" directory. Go to your plugin Dashboard and activate
   the plugin.


## Features


### Setting a Default Site License

After activating the plugin, head to **Settings > Creative Commons** to set up
the default license.

![Plugin Settings](https://cl.ly/01ae314c5c57/img)

Selecting a license is simple. Select one from the given CC licenses, by
default [**CC BY-SA**](http://creativecommons.org/licenses/by-sa/4.0/) license
is used.

![Select License](https://cl.ly/bfd84b912c78/img)

There are multiple options available for the license. You
can add:

- Additional attribution text for a custom note.
- Title and Title URL. If not mentioned it defaults to "the content".
- Author and Author URL. If not mentioned it defaults to "on this site".
- Display options.

![License Options](https://cl.ly/b4520d6ab6b1/img)


### Widget

There are two options to display the default license, as a widget or in the
footer. We recommend using the widget for better theme compatibility.

![Widget](https://cl.ly/2dacc1739955/img)

After selecting the widget go to **Appearance > Widgets** and drag the CC
License Widget to the required area. The widget will then display the default
license on all pages of the site.

![Widget Front-end](https://cl.ly/b9b584688f46/img)


### Gutenberg Blocks

The plugin adds specific Gutenberg blocks for each Creative Commons license. If
you are using the default Gutenberg editor, you will find these blocks under a
separate category.

![Blocks Category](https://cl.ly/4934cdc59cd4/img)

These blocks can be used to license any page/post/image or other media.

![Blocks Back-end](https://cl.ly/b454a77259ce/img)

Following is an image attributed using CC gutenberg block.

![Attributed Image](https://cl.ly/bde9d591b534/img)

At a glance, with WP CC Plugin you can:

- License your site with a default license.
- You can display the default license in the footer or as a widget in widget areas.
- Display license for the site, posts and pages
  license)
- License your WordPress Network (WordPress Multisite install)
- License some of your sites differently in your WordPress Network
- License some posts, pages, or images differently from your default license
  (per content license)
- License posts and pages by simply including CC Gutenberg blocks for each
  license required (Gutenberg License Blocks)


## Contributing

Contributions will be very appreciated. See
[`CONTRIBUTING.md`](CONTRIBUTING.md).


## Release Schedule

We will release a new version every month that there are substantial changes.
See [milestones][milestones] for how GitHub issues are assigned for release.

[milestones]: https://github.com/creativecommons/wp-plugin-creativecommons/milestones


## History

This plugin is loosely based on an existing, but seemingly abandoned WordPress
plugin named 'License' (a component of the [MIT Educational Collaboration
Space][collabspace] project) by mitcho (Michael Yoshitaka Erlewine) and Brett
Mellor. We're also inspired by Creative Commons' original
[wordpress-cc-plugin][oldplugin] written by former Creative Commons CTO Nathan
Yergler.

[collabspace]:http://cispace.mit.edu/
[oldplugin]:https://github.com/cc-archive/wordpress-cc-plugin


### Credits

- Michael Yoshitaka Erlewine (License v0.5)
- Brett Mellor (License v0.5)
- Bjorn Wijers
- Matt Lee
- Rob Myers
- Tarmo Toikkanen


## License

* [`license.txt`](license.txt) ([GPLv2 or later][gplv2] License)

[gplv2]: https://opensource.org/licenses/GPL-2.0 "GNU General Public License version 2 | Open Source Initiative"

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://github.com/kusinkay"><img src="https://avatars.githubusercontent.com/u/1234511?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Juane Puig</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=kusinkay" title="Code">💻</a></td>
    <td align="center"><a href="http://rhea.art/"><img src="https://avatars.githubusercontent.com/u/21746?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Rhea Myers</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=rheaplex" title="Code">💻</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!
