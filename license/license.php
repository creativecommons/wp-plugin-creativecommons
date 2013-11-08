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

      // Selecting a license for individual posts or pages is only possible if the settings of the site allow it
      // by default it does allow it.
      if( $this->allow_content_override_site_license() ) {
        add_action( 'post_submitbox_misc_actions', array(&$this, 'post_page_license_settings_html') );
        add_action( 'page_submitbox_misc_actions', array(&$this, 'post_page_license_settings_html') );
        add_action( 'save_post',                   array(&$this, 'save_license') );
      }

      // Selecting a license as a user for all your content is only possible if the settings of the site allow it, 
      // by default it will allow it.
      if( $this->allow_user_override_site_license() ) {
        add_action( 'personal_options',            array(&$this, 'user_license_settings_html') );
        add_action( 'personal_options_update',     array(&$this, 'save_license') );
      }

      // this implements the license plugin as a widget.
      // TODO: Widget needs more testing with the new approach 
      add_action( 'widgets_init', array(&$this, 'license_as_widget') );
    
      // if the plugin is installed in multisite environment allow to set the 
      // options for all sites as default from the network options
      if( is_multisite() ) {
        add_action('wpmu_options', array(&$this, 'network_license_settings_html') , 10, 0);
        add_action('update_wpmu_options', array(&$this, 'save_license'), 10, 0 );
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
          $license = ( $user_license = get_user_option( 'license' ) ) ? $user_license : $this->get_license( 'site' );
          break;

        case 'post-page':
          $license = ( $post_page_license = $this->get_post_page_license() ) ? $post_page_license : $this->get_license( 'profile' );
          break;

        // TODO need to check default structure below  
        default:
         /*   if( is_multisite() ) {
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
            }*/
        } 
      return $license;
    }


    // will use a variable prefix with underscore to make *really* sure this 
    // postmeta value will NOT be displayed to other users. The option is the 
    // same structure as everywhere: a serialized array.
    // (http://codex.wordpress.org/Function_Reference/add_post_meta)
    function get_post_page_license() {
      global $post; // TODO check if this can be done without a global and if we're always getting what we want
      $license = get_post_meta( $post->ID, '_license', true ); 
      if( is_array($license) && sizeof($license) > 0 ) {
        return $license;
      } else { 
        return false; 
      } 
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


    // return default attribution options
    function get_attribution_options( $location ) {
      switch ( $location ) {
      
      case 'network':
          $attribution_options = array(
            'network_name' => sprintf( __('Set attribution to the network name: %s', $this->localization_domain), get_site_option('site_name') ), 
            'site_name'    => __("Set attribution to a site's name", $this->localization_domain),
            'display_name' => __('Set attribution to the author display name', $this->localization_domain),
            'other'        => __('Set attribution to something completely differrent', $this->localization_domain)
          );
        break;
        
        case 'site':
          $attribution_options = array(
            'site_name'    => sprintf( __('Set attribution to the site name: %s', $this->localization_domain), get_bloginfo('site') ),
            'display_name' => __('Set attribution to the author display name', $this->localization_domain),
            'other'        => __('Set attribution to something completely differrent', $this->localization_domain)
          );
        break;

        default: 
          if( $this->allow_user_override_site_license() ) {
            $attribution_options = array(
              'display_name' => __('Set attribution to the author display name', $this->localization_domain),
              'other'        => __('Set attribution to something completely differrent', $this->localization_domain)
            );
          }            
        break;
      }
      
      return $attribution_options;     
    }




    function select_attribute_to_html( $location = null, $echo = true ) {
      $license = $this->get_license( $location ); 
      $attribute_options = $this->get_attribution_options( $location );


      $html = '';
      if( is_array($attribute_options) && sizeof($attribute_options) > 0 ) {
        foreach( $attribute_options as $attr_val => $attr_text) {
          $checked = '';
          $id = "license-$attr_val";
          $checked = checked($license['attribute_to'], $attr_val, false);
          $html .= "<label for='$id'><input type='radio' class='license-attribution-options' id='$id' $checked name='license[attribute_to]' value='$attr_val' />" . $attr_text . "</label><br/>\n";
        }
        if ( 'other' == $license['attribute_to'] ) {
          $class = ''; 
          $value =  esc_html( $license['attribute_other'] );
        } else {
          $class = 'hidden'; // hide the other input text field if the 'other' option is not ticked
          $value = '';
        }
        
        $html .= "<input type='text' value='$value' name='license[attribute_other]' id='license-other-value' class='large $class' size='45' />"; //might need max length? TODO need to check hidden value if other prev was selected
      }
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
      $html .= wp_nonce_field('license-update', $name = 'license_wpnonce', $referer = true, $echo = false);
      $html .= "<table class='form-table'>\n";
    
      $html .= "<tbody>\n";
      
      $html .= $this->_license_settings_html( $location ); 
      
      $html .= "<tr valign='top'>\n";
      $html .= "\t<th scope='row'><label for='override-license'>" .  __("Allow siteadmins to change their site's license", 'license') . "</label></th>\n";
      $html .= "\t<td><input name='site_override_license' type='checkbox'" . checked( $license['site_override_license'], 'true', false ) . " id='site_override-license' value='true' />";
      $html .= "</td>\n";
      $html .= "</tr>\n";

      $html .= "</tbody>\n";
      $html .= "</table>\n";
      
      echo $html;
    }

    // save license from network settings, user profile and post/page interface
    function save_license( $post_id = false ) {
      if( isset($_POST['license_wpnonce']) && wp_verify_nonce( $_POST['license_wpnonce'], 'license-update') ) { 
        if ( defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
          $this->_save_user_license();
        } elseif( is_multisite() && defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN ) {
          $this->_save_network_license();
        } else {
          // presume we're in a post or page wp-admin environment
          // might need to deal with autosave 
          
          $this->_save_post_page_license( $post_id );
        }
      } else { 
        return $post_id;
      }
    }



    // check the data before saving it
    // TODO: also needs to be used by the site settings 
    // use $from ala get_license to determine where the data is coming from 
    // and what should be mandatory return an array or WP_Error?
    private function _verify_license_data( $from ) {
      
      $license = array();

      $license['deed']             = esc_url(  $_POST[ 'license']['deed']  ); 
      $license['image']            = esc_url(  $_POST[ 'license']['image'] );
      $license['name']             = esc_attr( $_POST[ 'license']['name']  );
      $license['attribute_to']     = esc_attr( $_POST[ 'license']['attribute_to'] );
      $license['attribute_other']  = esc_html( $_POST[ 'license']['attribute_other' ] );

      switch( $from ) {
        case 'network': 
          if( isset( $_POST['site_override_license'] ) ) {
            $license['site_override_license'] = esc_attr( $_POST['site_override_license'] );
          }
          break;
      }
      return $license;
    }



    // validates & verifies the data and then saves the license in the site_options
    private function _save_network_license( ) {
      $license = $this->_verify_license_data( $from = 'network' ); 
      return update_site_option('license', $license);
    }

    // TODO: need to decide if the user option is global for all 
    // sites/blogs in a network. Also need to make sure that we're using 
    // the current profile user_id which might not be the 
    // current_user (e.g. admin changing license for a user).
    // validates & verifies the data and then saves the license in the user_options
    private function _save_user_license( ) {
      $license = $this->_verify_license_data( $from = 'profile' );
      $user_id = get_current_user_id();
      return update_user_option( $user_id, 'license', $license, $global = false );
    }

    // save post/page metadata due to it being an array it should not be shown 
    // in the post or page custom fields interface
    // using _license to hide it from the custom fields 
    private function _save_post_page_license( $post_id ) {
      $license = $this->_verify_license_data( $from = 'post-page' );
      return update_post_meta( $post_id,  '_license', $license);
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

    
    // render the html settings for the user profile 
    // TODO: add global option: if allowed use this license across all my sites 
    // in this network.
    function user_license_settings_html(){
      $location = 'profile';
      $html     = wp_nonce_field('license-update', $name = 'license_wpnonce', $referer = true, $echo = false);
      $html     .= $this->_license_settings_html( $location); 
      echo $html;
    } 
    
    function post_page_license_settings_html(){
      $location = 'post-page';
      $html  = '<div id="license" class="misc-pub-section misc-pub-section-last ">';
      $html .= wp_nonce_field('license-update', $name = 'license_wpnonce', $referer = true, $echo = false);
      $html .= '<strong>' . __('Licensed:', $this->localization_domain) . '</strong>';
      
      $html .= '<p>';
      $html .= $this->select_license_html( $location, $echo = false );  
      $html .= '</p>';
      
      $html .= '<p>';
      $html .= $this->select_attribute_to_html( $location, $echo = false );
      $html .= '</p>';
      $html .= '</div>';
      
      echo $html;
    }



    // DRY wrapper function used for profile settings and network settings
    private function _license_settings_html( $location ){
      $html ='';
      $html .= "<tr valign='top'>\n";
      $html .= "\t<th scope='row'><label for='license'>" .  __('Select a default license') . "</label></th>\n";
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
      
      return $html;
    }
    
    
    
    // TODO: rewrite this below
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
