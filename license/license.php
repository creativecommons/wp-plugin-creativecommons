<?php
/*
Plugin Name: License
Description: Allows users to specify a Creative Commons license for their content, with tight WordPress integration.
Version: 0.5
Author: mitcho (Michael Yoshitaka Erlewine), Brett Mellor
Author URI: http://ecs.mit.edu
*/

define('LICENSE_PLUGIN_URL', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));

add_action('admin_init', 'license_admin_init');
function license_admin_init() {
	/* Register our script. */
	wp_register_script('license', WP_PLUGIN_URL . '/license/admin.js');
	wp_enqueue_script("thickbox");
	wp_enqueue_style("thickbox");
	wp_enqueue_script('license');
}

add_action('init','license_author_info');
function license_author_info() {
	// "Use the init or any subsequent action to call this function. Calling it outside of an action can lead to troubles. See #14024 for details."
	// http://codex.wordpress.org/Function_Reference/wp_get_current_user		 ( same apparently applies to wp_get_current_user() because said "troubles" were encountered)

	// for displaying license "attribute to" options, and saving them
	global $license_displayname, $license_nickname;
	$current_user = wp_get_current_user();
	$license_displayname = $current_user->display_name;
	$license_nickname = $current_user->nickname;
}

add_action('post_submitbox_misc_actions','license_submitbox');
add_action('page_submitbox_misc_actions','license_submitbox');
add_action('save_post', 'license_save');
add_action('personal_options','license_userprofile_submitbox');
add_action('personal_options_update', 'license_save');

function license_userprofile_submitbox() {
	license_submitbox(true);
}

function license_submitbox($userprofile = false) {
 
	//&stylesheet=&partner_icon_url=
	$license = license_get_license();
	$nonce = wp_create_nonce( plugin_basename(__FILE__) );

	if ($userprofile) {
		echo '<tr id="license">
			<th scope="row">' . __('Default License', 'license') . '</th>
			<td><label for="license">';
	} else {
		echo '<div id="license" class="misc-pub-section misc-pub-section-last ">License: ';
	}

	echo '<span id="license-display"></span>
		<a href="http://creativecommons.org/choose/?partner=WordPress+License+Plugin&exit_url='.LICENSE_PLUGIN_URL.'licensereturn.php?url=[license_url]%26name=[license_name]%26button=[license_button]%26deed=[deed_url]&jurisdiction=' . __('us', 'license') . '&KeepThis=true&TB_iframe=true&height=500&width=600" title="' . __('Choose a Creative Commons license', 'license') . '" class="thickbox edit-license">' . __('Edit', 'license') . '</a><br>	

		<input type="hidden" name="license_nonce" id="license-nonce" value="'.$nonce.'" />
		<input type="hidden" value="'.$license['deed'].'" id="hidden-license-deed" name="hidden_license_deed"/>
		<input type="hidden" value="'.$license['image'].'" id="hidden-license-image" name="hidden_license_image"/>
		<input type="hidden" value="'.$license['name'].'" id="hidden-license-name" name="hidden_license_name"/>';

	// attribute_to setting is only available while editing user profile.	it is a property of the users's profile, not of the page/post
	if ($userprofile) {
		// pull these in for displaying "attribute to" options
		global $license_displayname, $license_nickname;

		// displayname will be the default
		echo '</td></tr><tr><th scope="row">Attribute License To:</th><td>
			<input type="radio" name="attribute_to" checked="true" value="displayname">' . sprintf(__('display name (%s, as selected below)', 'license'), $license_displayname) . '</input><br/>
			<input type="radio" name="attribute_to" '.checked($license['attribute_to'], 'nickname', false).' value="nickname">' . sprintf(__('nickname (%s)', 'license'), $license_nickname) . '</input><br/>
			<input type="radio" name="attribute_to" '.checked($license['attribute_to'], 'sitename', false).' value="sitename">' . sprintf(__('site name (%s)', 'license'), get_bloginfo('name')) . '</input>';
		}
	
/*
	<!--	<p>
	<a class="save-post-license hide-if-no-js button" href="#license">OK</a>
	<a class="cancel-post-license hide-if-no-js" href="#license">Cancel</a>
	</p>-->
*/

	if ($userprofile)
		echo '</td></tr>';
	else 
		echo '</div>';

	} // license_submitbox

function license_get_license($post_id = false) {
	global $user_id;
	$default = null;

	if (defined('WP_ADMIN') && WP_ADMIN)
		$default = get_user_option('license');
	
	if (empty($default))
		$default = array(
							'deed' => 'http://creativecommons.org/licenses/by-nc-sa/3.0/us/',
							'image' => 'http://i.creativecommons.org/l/by-nc-sa/3.0/us/88x31.png',
							'title' => get_bloginfo('url'),
							'name' => 'Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States License',
							'sitename' => get_bloginfo(),
							'siteurl' => get_bloginfo('url'),
							'author' => get_bloginfo()
						 );

	if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE)
		return $default;
	
	global $post;
	if ($post_id === false)
		$post_id = $post->ID;
	$this_post = get_post($post_id);
	$postdeed = get_post_meta($post_id,'_license_deed',true);
	
	if (empty($postdeed))
		return $default;
	else 
		return array( 'deed'	=> get_post_meta($post_id,'_license_deed',true),
									'image' => get_post_meta($post_id,'_license_image',true),
									'title' => get_the_title($post_id),
									'name' => get_post_meta($post_id,'_license_name',true),
									'sitename' => get_bloginfo(),
									'author' => get_the_author($this_post->post_author),
									'permalink' => get_permalink($post_id),
									'siteurl' => get_bloginfo('url')
								 );
	
}

function license_save($post_id) {

	if ( !isset($_POST['license_nonce']) ||
		!wp_verify_nonce( $_POST['license_nonce'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
		if (empty($_POST['hidden_license_deed']))
			return;
		update_user_option($post_id,'license',array(
			'deed' => $_POST['hidden_license_deed'],
			'image' => $_POST['hidden_license_image'],
			'name' => $_POST['hidden_license_name'],
									'author' => get_the_author($post_id),
			'title' => get_bloginfo(),
			'permalink' => get_bloginfo('url'),
			'sitename' => get_bloginfo(),
			'siteurl' => get_bloginfo('url'),
			'attribute_to' => $_POST['attribute_to']
								 ));
		return;
	}
	
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;

	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	}

	update_post_meta($post_id,'_license_deed',$_POST['hidden_license_deed']);
	update_post_meta($post_id,'_license_image',$_POST['hidden_license_image']);
	update_post_meta($post_id,'_license_name',$_POST['hidden_license_name']);
}

function license_print_license_html() {

	$license = license_get_license();

	// who authored this post/page?
	$authID = get_the_author_meta(ID);
	// how does this author prefer their license attribution to be displayed? (options are: display name, nickname, or sitename)
	// if preference is not set, display name will be used
	$usrLicOpt = get_user_option('license',$authID);
	$attribute_pref = $usrLicOpt['attribute_to'];
	if ($attribute_pref == 'sitename')
		$attribute_to = get_bloginfo();
	else if ($attribute_pref == 'nickname')
		$attribute_to = get_the_author_meta('nickname');
	else
		$attribute_to = get_the_author_meta('display_name');

	$imgstyle = apply_filters('license_img_style', "display:block; float:left; margin:0px 3px 3px 0px;");

	echo '<div class="license-wrap"><a rel="license" href="'.esc_url($license['deed']).'"><img style="' . $imgstyle . '" alt="' . __('Creative Commons License', 'license') . '" src="'.esc_url($license['image']).'" /></a> ';
	printf(__('<span%s>%s</span> is licensed by <span%s>%s</span> under a <a rel="license" href="%s">%s</a>.', 'license'),
		' xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type"',
		esc_html($license['title']),
		' xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName"',
		esc_html($attribute_to),
		esc_url($license['deed']),
		esc_html($license['name']));
	echo '</div>';
}
// this implements the license plugin as a widget. 
add_action( 'widgets_init', 'license_as_widget' );

function license_as_widget() {
	register_widget( 'license_widget' );
}
class license_widget extends WP_Widget {

	function license_widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'license-widget', 'description' => __('User-specified Creative Commons License will display in the page footer by default. Alternatively, drag this widget to a sidebar and the license will appear there instead.', 'license') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'license-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'license-widget', __('License', 'license'), $widget_ops, $control_ops );

		// if the widget is not active, (i.e. the plugin is installed but the widget has not been dragged to a sidebar),
		// then display the license in the footer as a default
		if (!is_active_widget(false, false, 'license-widget', true) ) {
			add_action('wp_footer','license_print_license_html');			
		}
	}

	function widget( $args ) {
		extract( $args );
		$title = __('License', 'license');
		echo $before_widget;
		echo $before_title . $title . $after_title;
		license_print_license_html();
		echo $after_widget;
	}

} //class