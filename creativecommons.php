<?php
/*
Plugin Name: Creative Commons
Description: Official Creative Commons plugin for WordPress. Allows users to select and display Creative Commons licenses for their content. Partially inspired by the License plugin by mitcho (Michael Yoshitaka Erlewine) and Brett Mellor, as well as the original WpLicense plugin by CC CTO Nathan R. Yergler.
Version: 2.0-beta
Author: Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>
Plugin URI: http://wiki.creativecommons.org/WpLicense
License: GPLv2 or later versions
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('CC__PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once CC__PLUGIN_DIR . 'includes/class-creativecommons.php';
require_once CC__PLUGIN_DIR . 'widgets/creativecommons-widget.php';
require CC__PLUGIN_DIR . 'includes/class-creativecommons-image.php';
require CC__PLUGIN_DIR . 'includes/class-creativecommons-button.php';

CreativeCommonsButton::get_instance()->init();
CreativeCommonsImage::get_instance()->init();