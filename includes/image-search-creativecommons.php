<?php
defined('ABSPATH') || exit;
define('CCPLUGIN_URI', plugin_dir_url(__FILE__));

//add a image search button to the window
function ccimage_add_button($editor_id)
{
    echo ' <a href="#cc-image-modal" id="open-ccimage-modal" data-editor="' . $editor_id . '" class="cc-image-btn wp-core-ui button" title="Insert CC license Image">Insert CC license Image</a>';
}
add_action('media_buttons', 'ccimage_add_button');

//enqueue script and stylesheet for button and modal window
function ccimage_load_scripts()
{
    wp_enqueue_script('ccimagescript', CCPLUGIN_URI . '/assets/image-search-script.js', array(), false, false);
    wp_enqueue_style('ccimagestyle', CCPLUGIN_URI . '/assets/image-search-style.css');

}

add_action('admin_enqueue_scripts', 'ccimage_load_scripts');


//modal window to show iframe content on button click

function ccimage_modal_content()
{
    ?>

		<!-- The Modal -->
		<div id="CC-Image-Modal" class="ccmodal">

		  <!-- Modal content -->
		  <div class="ccmodal-content">
			<span class="ccmodal-close">&times;</span>
			<div class="cc-image-iframe-loader-gif" id="cc-image-iframe-loader-gif">
				<span></span>
				<span></span>
				<span></span>
				<span></span>
				<span></span>
			</div>	
			<iframe src="" id="cc-image-iframe" style="border:0px # none;" name="myiFrame" scrolling="yes" frameborder="0" marginheight="0px" marginwidth="0px" height="98%" width="100%" allowfullscreen></iframe>
			<!-- <input type="text" name="key" id="cc-search-key" placeholder="your search query"></input>
			<button name="cc-search" id="cc-search-btn">Search</button> -->
				
			<!-- class to display api data 
			<div id="cc-api-data" class="cc-api-data"> -->
				
			</div>
		</div>
		  

		</div>

		<?php
}
add_action( 'admin_footer', 'ccimage_modal_content' );