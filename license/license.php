<?php
/*
Plugin Name: WpLicense
Description: Official Creative Commons plugin for Wordpress. Allows users to select and display Creative Commons licenses for their content.
Version: 0.7
Author: Bjorn Wijers <burobjorn@burobjorn.nl>, mitcho (Michael Yoshitaka Erlewine), Brett Mellor
Author URI: http://burobjorn.nl, http://ecs.mit.edu
*/


if( ! class_exists('License') ) { 
  class License {
    
    private $plugin_url; 
    private $localization_domain = 'license';
    private $locale; 

    function __construct() {
      $this->plugin_url =  WP_PLUGIN_URL .'/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );

      // language setup
      $this->locale = get_locale();
      $mo     = dirname(__FILE__) . '/languages/' . $this->localization_domain . '-' . $this->locale . '.mo';
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
      $this->display_settings_warning( $echo = true );
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
      error_log('called default');
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
          error_log('called network');
          $license = ( $network_license = get_site_option( 'license' ) ) ? $network_license : $this->plugin_default_license();
          break;
        
        case 'site':
          error_log('called site');
          $license = ( $site_license = get_option( 'license') ) ? $site_license : $this->get_license( 'network' );
          break;

        case 'profile':
          error_log('called profile');
          $license = ( $user_license = get_user_option( 'license' ) ) ? $user_license : $this->get_license( 'site' );
          break;

        case 'post-page':
          error_log('called post-page');
          $license = ( $post_page_license = $this->get_post_page_license() ) ? $post_page_license : $this->get_license( 'profile' );
          break;

        // TODO need to check default structure below since this can cause way 
        // too many calls for the right license   
        case 'frontend':
          error_log('get license for the frontend');
          if( is_multisite() ) {
            error_log('get license: multisite');
            $license = $this->get_license( 'network' );
            error_log('got network license');
            if( $this->allow_site_override_network_license() ) { 
              error_log('site may override network license');
              $license = $this->get_license( 'site' );
              $site_license = $license; // keep track of site license cause we need to check it twice...
              error_log('got site license');
              if( array_key_exists( 'user_override_license', $site_license ) && 'true' == $site_license['user_override_license'] ) {
                error_log('user may override license');
                $license = $this->get_license( 'profile' );
                error_log('got user license');
              } 
              //error_log('content override' . print_r( $site_license['content_override_license'], true) );
              if( array_key_exists('content_override_license', $site_license) && 'true' == $site_license['content_override_license'] ) {
                error_log('content may override license');
                $license = $this->get_license( 'post-page' ); 
                error_log('got content license');
              }
            }
          } else {
            $license = $this->get_license( 'site' );
            if( array_key_exists( 'user_override_license', $license ) && 'true' == $license['user_override_license'] ) {
              $license = $this->get_license( 'profile' );
            } 
            if( array_key_exists('content_override_license', $license) && 'true' == $license['content_override_license'] ) {
              $license = $this->get_license( 'post-page' ); 
            }
          }
          break;
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
      $license  = $this->get_license( $location );
      // add lang option 
      $lang = ( isset($this->locale) && ! empty($this->locale) ) ? 'lang=' . esc_attr($this->locale) : '';

      $html = '';
      $html .= "<span id='license-display'></span>";
      $html .= '<br id="license"><a title="' . __('Choose a Creative Commons license', 'license') . '" class="thickbox edit-license" href="http://creativecommons.org/choose/?';
        $html .= 'partner=WordPress+License+Plugin&';
        $html .= $lang;  
        $html .= '&exit_url=' . $this->plugin_url . 'licensereturn.php?url=[license_url]%26name=[license_name]%26button=[license_button]%26deed=[deed_url]&';
        $html .= '&KeepThis=true&TB_iframe=true&height=500&width=600">' . __('Change license', 'license');
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
            'network_name' => sprintf( __('The network name: %s', $this->localization_domain), get_site_option('site_name') ), 
            'site_name'    => __("A site's name", $this->localization_domain),
            'display_name' => __('The author display name', $this->localization_domain),
            'other'        => __('Something completely differrent', $this->localization_domain)
          );
        break;
        
        case 'site':
          $attribution_options = array(
            'site_name'    => sprintf( __('The site name: %s', $this->localization_domain), get_bloginfo('site') ),
            'display_name' => __('The author display name', $this->localization_domain),
            'other'        => __('Something completely differrent', $this->localization_domain)
          );
        break;

        default: 
          if( $this->allow_user_override_site_license() ) {
            $attribution_options = array(
              'display_name' => __('The author display name', $this->localization_domain),
              'other'        => __('Something completely differrent', $this->localization_domain)
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
          $value = esc_html( $license['attribute_other'] );
          $url   = esc_url ( $license['attribute_other_url']);        
        } else {
          $class = 'hidden'; // hide the other input text field if the 'other' option is not ticked
          $value = '';
          $url = '';
        }

        $html .= "<p id='attribute-other-data' class='$class'>";
        $html .= "<label for='license-other-value'>". __('Attribution text:', $this->localization_domain) . "<br /><input type='text' value='$value' name='license[attribute_other]' id='license-other-value' class='large' size='45' /></label><br />"; //might need max length? TODO need to check hidden value if other prev was selected
        $html .= "<label for='license-other-value-url'>". __('Attribution url:', $this->localization_domain) . "<br /><input type='text' value='$url' name='license[attribute_other_url]' id='license-other-value-url' class='large' size='45' /></label>";
        $html .= '</p>';
      }
      if( $echo ) {
        echo $html; 
      } else {
        return $html;
      }
    }


    // add a warning text at the site/network settings
    function display_settings_warning( $echo = false ) {
      $html = '';
      $html .= '<p>'; 
      $html .= __('WARNING: Changing these license settings after content has been added may change the licenses authors on the site have selected, effectively relicensing possibly all content on the site!', $this->localization_domain);   
      $html .= '</p>';
      if( $echo ) {
        echo $html; 
      } else {
        return $html;
      }
    }

    // let a site admin or network set a warning to be displayed in the license 
    // display so they can warn people that this particalur license may be 
    // overridden by content differently licensed
    private function _setting_warn_multiple_licensed() {
      
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
      $html .= $this->display_settings_warning();

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
      $license['attribute_other_url']  = esc_html( $_POST[ 'license']['attribute_other_url' ] );

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




    // add filters to this function so themers can easily change the html 
    // output
    public function print_license_html( $echo = true ) {
      // TODO if the license is shown on a multiple items page (except the author 
      // archive page?) display the site (or network default license) 
      // default license including a warning that individual items may be 
      // licensed differently.  
      // add a filter to include the license with the excerpt & content filter
      // allow an option to switch this off


      $license = $this->get_license( 'frontend' );
      $html = '';
      if( is_array($license) && sizeof($license) > 0 ) {
        
        $deed_url      = esc_url( $license['deed'] ); 
        $image_url     = esc_url( $license['image'] );
        $license_name  = esc_html( $license['name'] );
        $warning       = 'TODO' . __('The license shown may overriden by individual content such as a single post or image.', $this->localization_domain);


        if( is_array($attribution = $this->_get_attribution( $license ) ) ) {
          $attribute_text = isset( $attribution['text'] ) ? $attribution['text'] : '';
          $attribute_url  = isset( $attribution['url'] ) ? $attribution['url'] : '';
        }
      

        // it's a single entity so use the post->title otherwise use the site's 
        // title and add a warning that multiple items might have different 
        // licenses.
        if( is_singular() ) {
          global $post;
          if( is_object( $post ) ) { 
            $title_work = esc_html( $post->post_title );
            $warning_text = '';
          }  
        } else {
          $title_work   = get_bloginfo( 'name' );   
          $warning_text = "<p class='license-warning'>" . esc_html( $warning ) . "</p>";
        }

        $html .= "<div class='license-wrap'>";
        $html .= "<a rel='license' href='$deed_url'>";
        $html .= "<img alt='" . __('Creative Commons License', $this->localization_domain) . "' style='border-width:0' src='$image_url' />";
        $html .= "</a><br />";
        $html .= "<span xmlns:dct='http://purl.org/dc/terms/' property='dct:title'>$title_work</span> "; 
        $html .= __('by', $this->localization_domain);
        $html .= " <a xmlns:cc='http://creativecommons.org/ns#' href='$attribute_url' property='cc:attributionName' rel='cc:attributionURL'>$attribute_text</a> "; 
        $html .= sprintf( __('is licensed under a <a rel="license" href="%s">%s</a>.', $this->localization_domain), $deed_url, $license_name );
        //$html .= '<br />'; 
        //$html .= __('Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="http://source.url" rel="dct:source">http://source.url</a>.', $this->localization_domain);
        //$html .='<br />';
        //$html .= __('Permissions beyond the scope of this license may be available at <a xmlns:cc="http://creativecommons.org/ns#" href="http://morepermissions.url" rel="cc:morePermissions">http://morepermissions.url</a>.', $this->localization_domain);
        $html .= $warning_text;
        $html .= '</div>';
      }
      if( $echo ) {
        echo $html;
      } else {
        return $html;
      }

    }
    

    private function _get_attribution( $license ) {
      if( is_array($license) && sizeof( $license ) > 0 ){
        $attribution_option = isset( $license['attribute_to'] ) ? $license['attribute_to'] : null;  
      }

      $attribution = array();
      switch($attribution_option) { 
      
        case 'network_name': 
          $attribution['text'] = esc_html( get_site_option('site_name') );
          $attribution['url']  = esc_url( network_site_url() );
          break;

        case 'site_name': 
          $attribution['text'] = esc_html( get_bloginfo('site') );
          $attribution['url']  = esc_url( site_url() );
          break;
        case 'display_name': 
	  // TODO: works for single posts, but "display name" for a site or network is N/A
	  $attribution['text'] = esc_html(get_the_author_meta('display_name'));
	  $attribution['url'] = esc_url(get_the_author_meta('user_url'));
          break;

        case 'other': 
          $other = isset( $license['attribute_other'] ) ? $license['attribute_other'] : '';
          $other_url = isset( $license['attribute_other_url'] ) ? $license['attribute_other_url'] : '';
          
          $attribution['text'] = esc_html( $other );
          $attribution['url']  = esc_url( $other_url );
          break;
      }
      return $attribution; 
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
