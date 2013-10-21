<?php
/*
Plugin Name: License
Description: Allows users to specify a Creative Commons license for their content, with tight WordPress integration.
Version: 0.6
Author: mitcho (Michael Yoshitaka Erlewine), Brett Mellor
Author URI: http://ecs.mit.edu
*/


if( ! class_exists('License') ) { 
  class License {
    
    private $plugin_url; 
    private $localization_domain = 'license';


    function __construct() {
      $this->plugin_url =  WP_PLUGIN_URL .'/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );

      // language setup
      $locale = get_locale();
      $mo     = dirname(__FILE__) . '/languages/' . $this->localization_domain . '-' . $locale . '.mo';
      load_textdomain($this->localization_domain, $mo);

      
      // add admin.js to wp-admin pages and displays the site license settings 
      // in the Settings->General settings page unless you're running WordPress 
      // Multisite (Network) and the superadmin has disabled this.  
      add_action( 'admin_init', array(&$this, 'license_admin_init') );

      // TODO: probably not needed, it adds attribution choices to the user 
      // profile page, but this needs to be refactored anyways.    
      add_action( 'init',       array(&$this, 'license_author_info') );

      // Selecting a license for individual posts or pages is only possible if the settings of the site allow it
      // by default it does allow it.
      if( $this->allow_content_override_site_license() ) {
        add_action( 'post_submitbox_misc_actions', array(&$this, 'license_submitbox') );
        add_action( 'page_submitbox_misc_actions', array(&$this, 'license_submitbox') );
        add_action( 'save_post',                   array(&$this, 'license_save') );
      }

      // Selecting a license as a user for all your content is only possible if the settings of the site allow it, 
      // by default it does allow it.
      if( $this->allow_user_override_site_license() ) {
        add_action( 'personal_options',            array(&$this, 'license_userprofile_submitbox') );
        add_action( 'personal_options_update',     array(&$this, 'license_save') );
      }

      // this implements the license plugin as a widget.
      // TODO: Widget needs more testing with the new approach 
      add_action( 'widgets_init', array(&$this, 'license_as_widget') );
    
      // if the plugin is installed in multisite environment allow to set the 
      // options for all sites as default from the network options
      if( is_multisite() ) {
        add_action('wpmu_options', array(&$this, 'network_license_settings_html') , 10, 0);
        add_action('update_wpmu_options', array(&$this, 'save_network_license_settings'), 10, 0 );
      }
    }

    function register_site_settings() {
      register_setting( 'general', 'license' );
      add_settings_section( 'license-section', 'License Settings', array(&$this, 'settings_license_section'), 'general', 'license-section');

      add_settings_field( 'license', '<label for="license">' . __('License your site', 'license') . '</label>', array(&$this, 'setting_license_field'), 'general', 'license-section');
      add_settings_field( 'attribution_to', '<label for="attribution_to">' . __('Set attribution to', 'license') . '</label>', array(&$this, 'setting_attribution_field'), 'general', 'license-section');
      add_settings_field( 'allow_user_override', '<label for="allow_user_override">' . __('Allow users override site license', 'license') . '</label>', array(&$this, 'setting_user_override_license_field'), 'general', 'license-section');
      add_settings_field( 'allow_content_override', '<label for="allow_content_override">' . __('Allow license per post or page', 'license') . '</label>', array(&$this, 'setting_content_override_license_field'), 'general', 'license-section');
    
    }

    function settings_license_section() {
      // intentionally left blank 
    }


    function setting_license_field() {
      $this->select_license_html( $location = 'site', $echo = true );
    }

    function setting_attribution_field() {
      $this->select_attribute_to_html( $location = 'site', $echo = true );
    }

    // only used once in site admin
    function setting_user_override_license_field() {
      $license = $this->get_license( $location = 'site');
      $checked = ( array_key_exists('user_override_license', $license) ) ? checked( $license['user_override_license'], 'true', false ) : '';
      echo "<input name='license[user_override_license]' type='checkbox' " . $checked . " id='user-override-license' value='true' />";
    }
    
    // only used once in site admin
    function setting_content_override_license_field() {
      $license = $this->get_license( $location = 'site');
      $checked = ( array_key_exists('content_override_license', $license) ) ? checked( $license['content_override_license'], 'true', false ) : '';
      echo "<input name='license[content_override_license]' type='checkbox' " . $checked . " id='content-override-license' value='true' />";
    }

    /** 
     * Check if a site may override the network license.
     *
     * If a network license override is allowed, a site admin may change their 
     * site's license. The rights are cascading: if a site admin may change a 
     * site's license, she may also allow a user to select their own license. 
     * if a site admin may not change the license she will also not be allowed 
     * to let a user pick their own license. A siteadmin may also enable to 
     * have a license per content.
     *
     * @return bool true if the network license override is allowed, false 
     * otherwise
     */
    function allow_site_override_network_license() {
      $license = $this->get_license( $location = 'network' );
      // gotcha: using true as string instead of bool since it will be a string value 
      // returned from the settings form
      if( 'true' == $license['site_override_license'] ) {
        return true;
      } else {
        return false;
      }  
    }
    
    function allow_user_override_site_license() {
      if( is_multisite() && ! $this->allow_site_override_network_license() ) {
        return false;
      } else {
        $license = $this->get_license ( $location = 'site' );
        if( array_key_exists('user_override_license', $license) && 'true' == $license['user_override_license'] ) {
          return true;
        } else {
          return false;
        }
      }
    }

    function allow_content_override_site_license() {
      if( is_multisite() && ! $this->allow_site_override_network_license() ) {
        return false;
      } else {
        $license = $this->get_license ( $location = 'site' );
        if( array_key_exists('content_override_license', $license) && 'true' == $license['content_override_license'] ) {
          return true;
        } else {
          return false;
        }
      }
    }



    /**
     * Set a default license.
     * 
     * Hierarchy of license selection
     * If the plugin is used in a Multisite Network WordPress setup there is an 
     * option added to the network options to set a default license for all 
     * sites in this particular network. Each site will inherit this default. 
     * A sit owner may change this license (at least if the Multisite Admin 
     * allows it). On a site level (or single WordPress install) an admin may 
     * allow this site default license to be changed. If the site allows a 
     * user may change the default license for her/his posts. The same goes for 
     * posts/pages: if the site admin allows it these can be changed per post 
     * or page.  
     * 
     * 
     **/
    function plugin_default_license() {
      return $license = array(
        'deed'                     => 'http://creativecommons.org/licenses/by-sa/3.0/',
        'image'                    => 'http://i.creativecommons.org/l/by-sa/3.0/88x31.png',
        'attribute_to'             => '',
        'title'                    => get_bloginfo('name'),
        'name'                     => 'Creative Commons Attribution-Share Alike 3.0 License',
        'sitename'                 => get_bloginfo(''),
        'siteurl'                  => get_bloginfo('url'),
        'author'                   => get_bloginfo(),
        'site_override_license'    => true,
        'user_override_license'    => true,
        'content_override_license' => true
      );
    }


    
    
    // 1) Check if it's allowed to have a license per content => check the 
    // content for a license. No license found or not allowed? Continue 2
    // 2) Check if it's allowed to have a license per user => check the 
    // content author and grab his/her license preference. No license found 
    // or user are not allowed to choose their own license? Continue 3
    // 3) Check if the site is part of a network => No? continue step 4a
    // Yes? continue step 4b
    // 4a) the site is NOT part of a network => Get license from options or 
    // return plugin's default 
    // 4b) The site is part of a network => check if the site may choose it's 
    // own license => check the options for a license. Not allowed or no 
    // license found Continue step 5
    // 5) Check the multisite options for a license and return if not found 
    // return plugin default     
    function get_license( $location = null ) {
      switch ($location) {
        case 'network' :
          $license = get_site_option( 'license', $this->plugin_default_license() );
          break;
        
        case 'site':
          $license = get_option( 'license', $this->get_license( 'network' ) );
          break;

        case 'profile':
          $license = ( $user_license = get_user_option( 'license' ) ) ? $user_license : $this->get_license('site');
          break;

        default:
            if( is_multisite() ) {
                $license = get_site_option('license', $this->plugin_default_license() );
                if( $this->allow_site_override_network_license() ) { 
                   $license = get_option('license', $this->plugin_default_license() );
                   if( array_key_exists( 'user_override_license', $license ) && 'true' == $license['user_override_license'] ) {
                     $license = ( $user_license = $this->get_user_license() ) ? $user_license : $license;
                   } 
                   if( array_key_exists('content_override_license', $license) && 'true' == $license['content_override_license'] ) {
                     $license = ( $content_license = $this->get_content_license() ) ? $content_license : $license; 
                   }
                }
            } else {
                $license = get_option('license', $this->plugin_default_license() );
                if( array_key_exists( 'user_override_license', $license ) && 'true' == $license['user_override_license'] ) {
                  $license = ( $user_license = $this->get_user_license() ) ? $user_license : $license;
                } 
                if( array_key_exists('content_override_license', $license) && 'true' == $license['content_override_license'] ) {
                  $license = ( $content_license = $this->get_content_license() ) ? $content_license : $license; 
                }
            }
        } 
      return $license;
    }


    // return license or bool false
    // either return the user license of a specific user or the current user 
    function get_user_license( $user_id = null ) {
      if( is_null( $user_id ) ) { 
        global $user_id;
      }
      return get_user_option('license', $user_id);
    }


    // used in: 
    // - network settings
    // - site settings 
    // - profile settings (personal & others) 
    // - post/page edit screen (my own & others)
    function select_license_html( $location = null, $echo = true ) {
      // get the previously selected license from this site's options or the plugin's default license
      //$license = get_option('license', $this->plugin_default_license() );
      $license = $this->get_license( $location );
      
      $html = '';
      $html .= "<span id='license-display'></span>";
      $html .= '<br id="license"><a title="' . __('Choose a Creative Commons license', 'license') . '" class="thickbox edit-license" href="http://creativecommons.org/choose/?';
        $html .= 'partner=WordPress+License+Plugin&';
        $html .= 'exit_url=' . $this->plugin_url . 'licensereturn.php?url=[license_url]%26name=[license_name]%26button=[license_button]%26deed=[deed_url]&';
        $html .= 'jurisdiction=' . __('us', 'license') . '&KeepThis=true&TB_iframe=true&height=500&width=600">' . __('Change license', 'license');
      $html .=  '</a>';

      $html .= '<input type="hidden" value="'.$license['deed'].'" id="hidden-license-deed" name="license[deed]"/>';
      $html .= '<input type="hidden" value="'.$license['image'].'" id="hidden-license-image" name="license[image]"/>';
      $html .= '<input type="hidden" value="'.$license['name'].'" id="hidden-license-name" name="license[name]"/>';
      if( $echo ) {
        echo $html; 
      } else {
        return $html;
      }
    }

    function select_attribute_to_html( $location = null, $echo = true ) {
      $license = $this->get_license( $location ); 
      $html = "<input name='license[attribute_to]' type='text' id='override-license' value='" . $license['attribute_to'] . "' size='45' class='large-text' />";
      if( $echo ) {
        echo $html; 
      } else {
        return $html;
      }
    }




    /** 
     * Renders the license settings WordPress Network Settings page 
     *
     * Using the settings rendered by this function a superadmin may set a 
     * default license for the WordPress Network. This will be used for all 
     * sites. If the superadmin allows it, siteadmins may change their site's
     * license and choose a different license than the default Network license.
     *
     * Called by wpmu_options action 
     *
     **/
    function network_license_settings_html() {

      // get the previously selected license from the network options or the plugin's default license
      //$license = get_site_option('license', $this->plugin_default_license() );
      $location = 'network';
      $license = $this->get_license( $location ); 


      $html  = '';
      $html .= "<h3>" . __('License settings') . "</h3>\n";
      $html .= wp_nonce_field('license-update-network-options', $name = 'license_wpnonce', $referer = true, $echo = false);
      $html .= "<table class='form-table'>\n";
    
      $html .= "<tbody>\n";
      
      $html .= "<tr valign='top'>\n";
      $html .= "\t<th scope='row'><label for='license'>" .  __('Select a default license for the Network') . "</label></th>\n";
      $html .= "\t<td>";
      $html .= $this->select_license_html( $location, $echo = false );
      $html .= "</td>\n";
      $html .= "</tr>\n";

      $html .= "<tr valign='top'>\n";
      $html .= "\t<th scope='row'><label for='attribute_to'>" .  __("Set attribution to", 'license') . "</label></th>\n";
      $html .= "\t<td>";
      $html .= $this->select_attribute_to_html( $location, $echo = false );
      $html .= "</td>\n";
      $html .= "</tr>\n";
      
      $html .= "<tr valign='top'>\n";
      $html .= "\t<th scope='row'><label for='override-license'>" .  __("Allow siteadmins to change their site's license", 'license') . "</label></th>\n";
      $html .= "\t<td><input name='site_override_license' type='checkbox'" . checked( $license['site_override_license'], 'true' ) . " id='site_override-license' value='true' />";
      $html .= "</td>\n";
      $html .= "</tr>\n";

      $html .= "</tbody>\n";
      $html .= "</table>\n";
      
      echo $html;
    }



    /**
     * Saves the default license for the WordPress Network
     *
     * TODO: check if values are set
     */
    function save_network_license_settings() {
      if( wp_verify_nonce( $_POST['license_wpnonce'], 'license-update-network-options') ) {
        $license = array(
          'deed'                  => esc_url(  $_POST[ 'license']['deed']  ), 
          'image'                 => esc_url(  $_POST[ 'license']['image'] ),
          'name'                  => esc_attr( $_POST[ 'license']['name']  ),
          'attribute_to'          => esc_attr( $_POST[ 'license']['attribute_to'] ),
          'site_override_license' => esc_attr( $_POST[ 'site_override_license' ] )
        );
        update_site_option('license', $license);
      }
    }



    function license_admin_init() {
      /* Register our script. */
      wp_register_script('license', WP_PLUGIN_URL . '/license/admin.js');
      wp_enqueue_script("thickbox");
      wp_enqueue_style("thickbox");
      wp_enqueue_script('license');
      // if a siteadmin may change her site's license, show the settings 
      // otherwise don't bother 
      if( is_multisite() ) {
        if( $this->allow_site_override_network_license() ) {
          $this->register_site_settings();
        }
      } else {
        $this->register_site_settings();
      }
    }

    function license_author_info() {
      // "Use the init or any subsequent action to call this function. Calling it outside of an action can lead to troubles. See #14024 for details."
      // http://codex.wordpress.org/Function_Reference/wp_get_current_user		 ( same apparently applies to wp_get_current_user() because said "troubles" were encountered)

      // for displaying license "attribute to" options, and saving them
      global $license_displayname, $license_nickname;
      $current_user = wp_get_current_user();
      $license_displayname = $current_user->display_name;
      $license_nickname = $current_user->nickname;
    }


    function license_userprofile_submitbox() {
      $this->license_submitbox(true);
    }

    function license_submitbox($userprofile = false) {
     
      //&stylesheet=&partner_icon_url=
      $license = $this->license_get_license();
      $nonce = wp_create_nonce( plugin_basename(__FILE__) );

      if ($userprofile) {
        echo '<tr id="license">
          <th scope="row">' . __('Default License', 'license') . '</th>
          <td><label for="license">';
      } else {
        echo '<div id="license" class="misc-pub-section misc-pub-section-last ">License: ';
      }

    
      echo '<span id="license-display"></span>
        <a href="http://creativecommons.org/choose/?partner=WordPress+License+Plugin&exit_url=' . $this->plugin_url . 'licensereturn.php?url=[license_url]%26name=[license_name]%26button=[license_button]%26deed=[deed_url]&jurisdiction=' . __('us', 'license') . '&KeepThis=true&TB_iframe=true&height=500&width=600" title="' . __('Choose a Creative Commons license', 'license') . '" class="thickbox edit-license">' . __('Edit', 'license') . '</a><br>	

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

      $license = $this->license_get_license();

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

    function license_as_widget() {
      require_once('widgets/license_widget.php');
      register_widget( 'license_widget' );
    }
  }
  $license = new License();
} else {
  error_log('Could not instantiate class License due to already existing class License.'); 
}
