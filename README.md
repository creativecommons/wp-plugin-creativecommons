<div align="center">
<img src="https://mirrors.creativecommons.org/presskit/icons/cc.xlarge.png" height="150">

<h2 align="center">CC WordPress Plugin</h2>
<p align="center">Official Creative Commons plugin for licensing your content on your WordPress website. With Creative Commons licenses, keep your copyright and share your creativity.
</p>

[X]![X]PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com) [X]![X]License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) [X]![X]CalVer Format: YYYY.0M.Micro](https://img.shields.io/badge/calver-YYYY.0M.MICRO-22bfda.svg)](https://calver.org/)

</div>

<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[X]![X]All Contributors](https://img.shields.io/badge/all_contributors-15-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->


## Description

The plugin is an attribution tool. It has multiple features that allow users to
attribute their content by including Creative Commons license ([X]Choose a
License](https://creativecommons.org/choose/)) on their WordPress website. This
includes default, post, page and media attribution.


## Code of conduct

[X]`CODE_OF_CONDUCT.md`][X]org-coc]:
> The Creative Commons team is committed to fostering a welcoming community.
> This project and all other Creative Commons open source projects are governed
> by our [X]Code of Conduct][X]code_of_conduct]. Please report unacceptable
> behavior to [X]conduct@creativecommons.org](mailto:conduct@creativecommons.org)
> per our [X]reporting guidelines][X]reporting_guide].

[X]org-coc]: https://github.com/creativecommons/.github/blob/main/CODE_OF_CONDUCT.md
[X]code_of_conduct]: https://opensource.creativecommons.org/community/code-of-conduct/
[X]reporting_guide]: https://opensource.creativecommons.org/community/code-of-conduct/enforcement/


## Installation

Download the latest version from this project's [X]releases][X]releases]. You can
install the plugin to your WP website using any of these methods:

[X]releases]: https://github.com/creativecommons/creativecommons-wordpress-plugin/releases "Releases · creativecommons/creativecommons-wordpress-plugin"

1. In your plugin Dashboard on WordPress, Click **Add New** and upload the
   plugin `.zip` file. When installed, activate the plugin.
2. Extract the `.zip` file and paste the extracted folder to the
   "/wp-content/plugins/" directory. Go to your plugin Dashboard and activate
   the plugin.


## Features


### Setting a Default Site License

After activating the plugin, head to **Settings > Creative Commons** to set up
the default license.

![X]Plugin Settings](https://cl.ly/01ae314c5c57/img)

Selecting a license is simple. Select one from the given CC licenses, by
default [X]**CC BY-SA**](http://creativecommons.org/licenses/by-sa/4.0/) license
is used.

![X]Select License](https://cl.ly/bfd84b912c78/img)

There are multiple options available for the license. You
can add:

- Additional attribution text for a custom note.
- Title and Title URL. If not mentioned it defaults to "the content".
- Author and Author URL. If not mentioned it defaults to "on this site".
- Display options.

![X]License Options](https://cl.ly/b4520d6ab6b1/img)


### Widget

There are two options to display the default license, as a widget or in the
footer. We recommend using the widget for better theme compatibility.

![X]Widget](https://cl.ly/2dacc1739955/img)

After selecting the widget go to **Appearance > Widgets** and drag the CC
License Widget to the required area. The widget will then display the default
license on all pages of the site.

![X]Widget Front-end](https://cl.ly/b9b584688f46/img)


### Gutenberg Blocks

The plugin adds specific Gutenberg blocks for each Creative Commons license. If
you are using the default Gutenberg editor, you will find these blocks under a
separate category.

![X]Blocks Category](https://cl.ly/4934cdc59cd4/img)

These blocks can be used to license any page/post/image or other media.

![X]Blocks Back-end](https://cl.ly/b454a77259ce/img)

Following is an image attributed using CC gutenberg block.

![X]Attributed Image](https://cl.ly/bde9d591b534/img)

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


## Contributing and development

**See [X]`development.md`](development.md).**


## Release Schedule

We will release a new version every month that there are substantial changes.
See [X]milestones][X]milestones] for how GitHub issues are assigned for release.

[X]milestones]: https://github.com/creativecommons/wp-plugin-creativecommons/milestones


## License

- [X]`license.txt`](license.txt) ([X]GPLv2 or later][X]gplv2] License)

[X]gplv2]: https://opensource.org/licenses/GPL-2.0 "GNU General Public License version 2 | Open Source Initiative"


## History

This plugin is loosely based on an existing, but seemingly abandoned WordPress
plugin named 'License' (a component of the [X]MIT Educational Collaboration
Space][X]collabspace] project) by mitcho (Michael Yoshitaka Erlewine) and Brett
Mellor. We're also inspired by Creative Commons' original
[X]wordpress-cc-plugin][X]oldplugin] written by former Creative Commons CTO Nathan
Yergler.

[X]collabspace]:http://cispace.mit.edu/
[X]oldplugin]:https://github.com/cc-archive/wordpress-cc-plugin


### Credits

- Michael Yoshitaka Erlewine (License v0.5)
- Brett Mellor (License v0.5)
- Bjorn Wijers
- Matt Lee
- Rob Myers
- Tarmo Toikkanen


### Contributors ✨

Thanks goes to these wonderful people ([X]emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://cog.dog/"><img src="https://avatars.githubusercontent.com/u/463038?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Alan Levine</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=cogdog" title="Code">💻</a></td>
    <td align="center"><a href="http://linkedin.com/in/brylie-christopher-oxley/"><img src="https://avatars.githubusercontent.com/u/17307?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Brylie Christopher Oxley</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=brylie" title="Code">💻</a></td>
    <td align="center"><a href="https://github.com/vestigialcode"><img src="https://avatars.githubusercontent.com/u/54473532?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Dibyajiban Sahoo</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=vestigialcode" title="Documentation">📖</a></td>
    <td align="center"><a href="http://hugo.solar"><img src="https://avatars.githubusercontent.com/u/894708?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Hugo Solar</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=hugosolar" title="Code">💻</a> <a href="https://github.com/creativecommons/wp-plugin-creativecommons/pulls?q=is%3Apr+reviewed-by%3Ahugosolar" title="Reviewed Pull Requests">👀</a></td>
    <td align="center"><a href="https://github.com/kusinkay"><img src="https://avatars.githubusercontent.com/u/1234511?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Juane Puig</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=kusinkay" title="Code">💻</a></td>
    <td align="center"><a href="http://kritigodey.com"><img src="https://avatars.githubusercontent.com/u/287034?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Kriti Godey</b></sub></a><br /><a href="#projectManagement-kgodey" title="Project Management">📆</a></td>
    <td align="center"><a href="https://bight.dev"><img src="https://avatars.githubusercontent.com/u/605361?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Ned Zimmerman</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=greatislander" title="Code">💻</a></td>
  </tr>
  <tr>
    <td align="center"><a href="http://www.nishantwrp.com"><img src="https://avatars.githubusercontent.com/u/36989112?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Nishant Mittal</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=nishantwrp" title="Documentation">📖</a></td>
    <td align="center"><a href="https://github.com/cillacode"><img src="https://avatars.githubusercontent.com/u/54538525?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Priscillia Umeakuekwe</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=cillacode" title="Documentation">📖</a></td>
    <td align="center"><a href="https://github.com/rczajka"><img src="https://avatars.githubusercontent.com/u/264402?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Radek Czajka</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=rczajka" title="Code">💻</a></td>
    <td align="center"><a href="https://rajeshroyal.com"><img src="https://avatars.githubusercontent.com/u/24524924?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Rajesh Royal</b></sub></a><br /><a href="#translation-Rajesh-Royal" title="Translation">🌍</a> <a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=Rajesh-Royal" title="Code">💻</a></td>
    <td align="center"><a href="http://rhea.art/"><img src="https://avatars.githubusercontent.com/u/21746?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Rhea Myers</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=rheaplex" title="Code">💻</a></td>
    <td align="center"><a href="https://www.thecrowned.org"><img src="https://avatars.githubusercontent.com/u/7880569?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Stefano Ottolenghi</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=TheCrowned" title="Code">💻</a></td>
    <td align="center"><a href="https://zehta.me/"><img src="https://avatars.githubusercontent.com/u/691322?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Timid Robot Zehta</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/pulls?q=is%3Apr+reviewed-by%3ATimidRobot" title="Reviewed Pull Requests">👀</a> <a href="#projectManagement-TimidRobot" title="Project Management">📆</a></td>
  </tr>
  <tr>
    <td align="center"><a href="https://github.com/zhaofeng-shu33"><img src="https://avatars.githubusercontent.com/u/23316477?v=4?s=100" width="100px;" alt=""/><br /><sub><b>赵丰 (Zhao Feng)</b></sub></a><br /><a href="https://github.com/creativecommons/wp-plugin-creativecommons/commits?author=zhaofeng-shu33" title="Documentation">📖</a> <a href="#translation-zhaofeng-shu33" title="Translation">🌍</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [X]all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!
