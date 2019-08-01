<?php
/**
 * Plugin Name: Creative Commons
 * Plugin URI: https://github.com/creativecommons/wp-plugin-creativecommons
 * Description: Official Creative Commons plugin for licensing your content. With Creative Commons licenses, keep your copyright AND share your creativity.
 * Version: v2019.7.2
 * Author: Ahmad Bilal (https://ahmadbilal.dev), Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>, Timid Robot Zehta
 * Author URI: http://CreativeCommons.org/
 * License: GPLv2 or later versions
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CC_WordPress_Plugin
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
