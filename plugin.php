<?php
/*
Plugin Name: Creative Commons
Description: Official Creative Commons plugin for WordPress. Allows
users to select and display Creative Commons licenses for their
content. Partially inspired by the License plugin by mitcho (Michael
Yoshitaka Erlewine) and Brett Mellor, as well as the original
WpLicense plugin by CC CTO Nathan R. Yergler.
Version: 2.0
Author: Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>
Plugin URI: http://wiki.creativecommons.org/WpLicense
License: GPLv2 or later versions
*/

define( 'CC__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


require_once( CC__PLUGIN_DIR . 'inc/class.creativecommons.php' );
require_once( CC__PLUGIN_DIR . 'widgets/creativecommons_widget.php' );

require( CC__PLUGIN_DIR . 'inc/save-image.php' );

function cc_1ca_add_theme_scripts () {
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style("wp-jquery-ui",
                     plugin_dir_url(__FILE__) . 'css/jquery-ui.css');
    
    wp_enqueue_script('clipboard.js',
                      'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js',
                      [],
                      '1.5.10',
                      true);
    
    wp_enqueue_style('toastr',
                     'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css',
                     false,
                     'latest',
                     'all');
    wp_enqueue_script('toastr',
                      'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js',
                      [],
                      'latest',
                      true);
    
    wp_enqueue_style('cc-button',
                     plugin_dir_url(__FILE__) . 'css/cc-button.css',
                     false,
                     '1.1',
                     'all');
    wp_enqueue_script('cc-button',
                      plugin_dir_url( __FILE__ ) . 'js/cc-button.js',
                      ['clipboard.js', 'toastr'],
                      '1.1',
                      true);
}

function cc_1ca_insert_footer () {
    echo "<script>
      var ccButton = new CCButton({nodeType:'span',
                                   nodeClass:'cc-attribution'});
      ccButton.insertButtonIntoLicenseBlocks();
</script>";
}

add_action('wp_enqueue_scripts', 'cc_1ca_add_theme_scripts');
// Low priority so we go after the scripts are included
add_action('wp_footer', 'cc_1ca_insert_footer', 1000);

error_log('hello');


?>
