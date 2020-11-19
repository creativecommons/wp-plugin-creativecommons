<?php
/**
 * Plugin Name: Creative Commons
 * Plugin URI: https://github.com/creativecommons/wp-plugin-creativecommons
 * Description: Official Creative Commons plugin for licensing your content. With Creative Commons licenses, keep your copyright AND share your creativity.
 * Version: 2020.11.1
 * Author: Ahmad Bilal <https://ahmadbilal.dev>, Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>, Timid Robot Zehta
 * Author URI: http://CreativeCommons.org/
 * License: GPLv2 or later versions
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: CreativeCommons
 * @package CC_WordPress_Plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CCPLUGIN__DIR', plugin_dir_path( __FILE__ ) );

define( 'CCPLUGIN__URL', plugin_dir_url( __FILE__ ) );

// Require class files.
require_once CCPLUGIN__DIR . 'includes/class-creativecommons.php';
require_once CCPLUGIN__DIR . 'widgets/creativecommons-widget.php';
require CCPLUGIN__DIR . 'includes/class-creativecommons-image.php';

// Instantiate classes.
CreativeCommons::get_instance()->init();
CreativeCommonsImage::get_instance()->init();

/**
 * Gutenberg Blocks Initializer.
 */
require_once CCPLUGIN__DIR . 'src/init.php';

/**
 * Creates a new category for CC blocks.
 *
 * @param  mixed $categories Array of block categories.
 */
function creative_commons_block_category( $categories ) {
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
