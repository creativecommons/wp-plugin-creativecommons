<?php
/**
 * Plugin Name: Creative Commons
 * Description: Official Creative Commons plugin for WordPress. Allows users to select and display Creative Commons licenses for their content.
 * Partially inspired by the License plugin by mitcho
 * (Michael Yoshitaka Erlewine) and Brett Mellor, as well as the original WpLicense plugin by CC CTO
 * Nathan R. Yergler.
 * Version: 2019.7.2
 * Author: Ahmad Bilal (https://ahmadbilal.dev), Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>
 * Author URI: http://CreativeCommons.org/
 * License: GPLv2 or later versions
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @link http://wiki.creativecommons.org/WpLicense
 *
 * @package CC_WordPress_Plugin
 *
 * GitHub URI: https://github.com/creativecommons/wp-plugin-creativecommons
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CC__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Require class files.
require_once CC__PLUGIN_DIR . 'includes/class-creativecommons.php';
require_once CC__PLUGIN_DIR . 'widgets/creativecommons-widget.php';
require CC__PLUGIN_DIR . 'includes/class-creativecommons-image.php';
require CC__PLUGIN_DIR . 'includes/class-creativecommons-button.php';

// Instantiate classes.
CreativeCommons::get_instance()->init();
CreativeCommonsButton::get_instance()->init();
CreativeCommonsImage::get_instance()->init();

/**
 * Gutenberg Blocks Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

/**
 * Creates a new category for CC blocks.
 *
 * @param  mixed $categories
 * @param  mixed $post
 */
function creative_commons_block_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'cc-licenses',
				'title' => __( 'Creative Commons Licenses', 'creative-commons' ),
			),
		)
	);
}
add_filter( 'block_categories', 'creative_commons_block_category', 10, 2 );
