<?php
/*
  Author: Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>
  License: GPLv2 or later versions
*/

class CreativeCommons {

    // MAKE SURE THE PLUGIN HEADER HAS THE SAME VERSION NUMBER!
    const VERSION = '2.0-beta';

    private $plugin_url;
    private $localization_domain = 'CreativeCommons';
    private $locale;

    private static $instance = null;


    private function __construct()
    {
    }


    public function init()
    {
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));

        // language setup
        $this->locale = get_locale();
        $mofile = plugin_dir_path(dirname(__FILE__))
                . '/languages/' . $this->locale . '.mo';
        load_textdomain($this->localization_domain, $mofile);

        // add admin.js to wp-admin pages and displays the site license
        // settings in the Settings->General settings page unless you're
        // running WordPress Multisite (Network) and the superadmin has
        // disabled this.
        add_action('admin_init', array(&$this, 'license_admin_init'));
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));

        // Selecting a license for individual posts or pages is only
        // possible if the settings of the site allow it
        // by default it does allow it.
        if ($this->allow_content_override_site_license()) {
            add_action(
                'post_submitbox_misc_actions',
                array(&$this, 'post_page_license_settings_html')
            );
            add_action(
                'page_submitbox_misc_actions',
                array(&$this, 'post_page_license_settings_html')
            );
            add_action(
                'save_post',
                array(&$this, 'save_license')
            );
        }

        // Selecting a license as a user for all your content is only
        // possible if the settings of the site allow it,
        // by default it will allow it.
        if ($this->allow_user_override_site_license()) {
            add_action(
                'personal_options',
                array(&$this, 'user_license_settings_html')
            );
            add_action(
                'personal_options_update',
                array(&$this, 'save_license')
            );
        }

        // this implements the license plugin as a widget.
        // TODO: Widget needs more testing with the new approach
        add_action('widgets_init', array(&$this, 'license_as_widget'));

        // if the plugin is installed in multisite environment allow to set
        // the options for all sites as default from the network options
        if (is_multisite()) {
            add_action(
                'wpmu_options',
                array(&$this, 'network_license_settings_html'),
                10,
                0
            );
            add_action(
                'update_wpmu_options',
                array(&$this, 'save_license'),
                10,
                0
            );
        }
    }


    public static function get_instance()
    {

        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;

    }


    function wphub_register_settings()
    {
        add_option('wphub_use_api', '1');
        add_option('wphub_api_callback', 'alpha');
        register_setting('default', 'wphub_use_api');
        register_setting('default', 'wphub_api_callback');
    }


    function wphub_register_options_page()
    {
        add_options_page(
            'Page title',
            'Sidebar Text',
            'manage_options',
            'wphub-options',
            'wphub_options_page'
        );
    }

    /**
     * Register and add settings
     */

    public function page_init()
    {
        register_setting(
            'cc-admin',
            'license',
            array(&$this, '_wrapper_settings_api_verify')
        );
        add_settings_section(
            'license-section',
            '',
            array(&$this, 'settings_license_section'),
            'cc-admin'
        );

        add_settings_field(
            'license_current',
            __(
                'Current default license',
                $this->localization_domain
            ),
            array(&$this, 'setting_license_default_field'),
            'cc-admin',
            'license-section',
            array('label_for' => 'license_current')
        );

        add_settings_field(
            'license',
            '',
            array(&$this, 'setting_license_field'),
            'cc-admin',
            'license-section'
        );

        add_settings_field(
            'warning_txt',
            __(
                'Add license warning text',
                $this->localization_domain
            ),
            array(&$this, 'setting_warning_field'),
            'cc-admin',
            'license-section',
            array('label_for' => 'warning_txt')
        );

        add_settings_field(
            'attribution_to',
             __(
                 'Set attribution to',
                 $this->localization_domain
             ),
            array(&$this, 'setting_attribution_field'),
            'cc-admin',
            'license-section',
            array('label_for' => 'attribution_to')
        );

        add_settings_field(
            'allow_user_override',
             __(
                 'Allow users to override site&#8209;wide license',
                 $this->localization_domain
             ),
            array(&$this, 'setting_user_override_license_field'),
            'cc-admin',
            'license-section',
            array('label_for' => 'allow_user_override')
        );

        add_settings_field(
            'allow_content_override',
             __(
                 'Allow a different license per post/page',
                 $this->localization_domain
             ),
            array(&$this, 'setting_content_override_license_field'),
            'cc-admin',
            'license-section',
            array('label_for' => 'allow_content_override')
        );
    }


    function settings_license_section()
    {
        $this->display_settings_warning($echo = true);
    }


    function setting_license_field()
    {
        $this->select_license_html($location = 'site', $echo = true);
    }


    function setting_license_default_field()
    {
        $license = $this->get_license($location = 'site');
        $deed = esc_html($license['deed']);
        $name = esc_html($license['name']);
        $image = esc_html($license['image']);
        echo "<div style='text-align: center; background: white; border: solid 2px #666; padding: 1em;'><img id='cc-current-license-image' src='$image'><br /><a href='$deed' target='_blank'>$name</a></div><div id='license-display' style='background: white; border: 2px solid red; padding: 1em; margin-top: 1em; display: none;'>" . "<h3>" . __('WARNING: Changing these license settings after content has been added may change the licenses authors on the site have selected, effectively relicensing possibly all content on the site!', $this->localization_domain) . "</h3>" . "<p style='text-align: center;'><span id='license-display-image'></span></p></div>";
    }


    function setting_warning_field()
    {
        $license = $this->get_license($location = 'site');
        $warning = esc_html($license['warning_txt']);
        echo "<input name='license[warning_txt]' type='text' size='80' maxlength='250' id='warning-txt' value='$warning' />";
    }


    function setting_attribution_field()
    {
        $this->select_attribute_to_html($location = 'site', $echo = true);
    }


    // only used once in site admin
    function setting_user_override_license_field()
    {
        $license = $this->get_license($location = 'site');
        $checked = (array_key_exists('user_override_license', $license))
                 ? checked($license['user_override_license'], 'true', false)
                 : '';
        echo "<input name='license[user_override_license]' type='checkbox' " . $checked . " id='user-override-license' value='true' />";
    }


    // only used once in site admin
    function setting_content_override_license_field()
    {
        $license = $this->get_license($location = 'site');
        $checked = (array_key_exists('content_override_license', $license))
                 ? checked(
                     $license['content_override_license'],
                     'true',
                     false
                 )
                 : '';
        echo "<input name='license[content_override_license]' type='checkbox' " . $checked . " id='content-override-license' value='true' />";
    }


    /**
     * Check if a site may override the network license.
     *
     * If a network license override is allowed, a site admin may change
     * their site's license. The rights are cascading: if a site admin may
     * change a site's license, she may also allow a user to select their
     * own license.
     * if a site admin may not change the license she will also not be
     * allowed to let a user pick their own license. A siteadmin may also
     * enable to have a license per content.
     *
     * @return bool true if the network license override is allowed, false
     * otherwise
     */
    function allow_site_override_network_license()
    {
        $license = $this->get_license($location = 'network');
        // gotcha: using true as string instead of bool since it will be a
        // string value returned from the settings form
        if ('true' == $license['site_override_license']) {
            return true;
        } else {
            return false;
        }
    }


    function allow_user_override_site_license()
    {
        if (is_multisite()
            && ! $this->allow_site_override_network_license()
        ) {
            return false;
        } else {
            $license = $this->get_license ($location = 'site');
            if (array_key_exists('user_override_license', $license)
                && 'true' == $license['user_override_license']
            ) {
                return true;
            } else {
                return false;
            }
        }
    }


    function allow_content_override_site_license()
    {
        if (is_multisite()
            && ! $this->allow_site_override_network_license()
        ) {
            return false;
        } else {
            $license = $this->get_license ($location = 'site');
            if (array_key_exists('content_override_license', $license)
                && 'true' == $license['content_override_license']
            ) {
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
     * If the plugin is used in a Multisite Network WordPress setup there is
     * an option added to the network options to set a default license for
     * all sites in this particular network. Each site will inherit this
     * default.
     * A sit owner may change this license (at least if the Multisite Admin
     * allows it). On a site level (or single WordPress install) an admin
     * may allow this site default license to be changed. If the site allows
     * a user may change the default license for her/his posts. The same
     * goes for posts/pages: if the site admin allows it these can be
     * changed per post or page.
     *
     *
     **/
    function plugin_default_license()
    {
        $this->_logger('Got default settings');
        return $license = array(
            'deed'                     => 'http://creativecommons.org/licenses/by-sa/4.0/',
            'image'                    => 'https://licensebuttons.net/l/by-sa/4.0/88x31.png',
            'attribute_to'             => '',
            'title'                    => get_bloginfo('name'),
            'name'                     => 'Creative Commons Attribution-Share Alike 4.0 License',
            'sitename'                 => get_bloginfo(''),
            'siteurl'                  => get_bloginfo('url'),
            'author'                   => get_bloginfo(),
            'site_override_license'    => true,
            'user_override_license'    => true,
            'content_override_license' => true,
            'version'                  => self::VERSION,
            'warning_txt'              => __('The license shown may be overriden by individual content such as a single post or image.', $this->localization_domain)
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
    // 4b) The site is part of a network => check if the site may choose its
    // own license => check the options for a license. Not allowed or no
    // license found Continue step 5
    // 5) Check the multisite options for a license and return if not found
    // return plugin default
    function get_license($location = null)
    {
        switch ($location) {
        case 'network' :
            $this->_logger('called network');
            $license = ($network_license = get_site_option('license'))
                     ? $network_license : $this->plugin_default_license();
            break;

        case 'site':
            $this->_logger('called site');
            if (is_multisite()) {
                $this->_logger('multisite, check network settings');
                $license = ($site_license = get_option('license'))
                         ? $site_license : $this->get_license('network');
            } else {
                $this->_logger('single site, get site license or else default settings');
                $license = ($site_license = get_option('license'))
                         ? $site_license : $this->plugin_default_license();
            }
            break;

        case 'profile':
            $this->_logger('called profile');
            $license = ($user_license = get_user_option('license'))
                     ? $user_license : $this->get_license('site');
            break;

        case 'post-page':
            $this->_logger('called post-page');
            $license = ($post_page_license = $this->get_post_page_license())
                     ? $post_page_license : $this->get_license('profile');
            break;

            // TODO need to check default structure below since this can
            // cause way too many calls for the right license
        case 'frontend':
            $this->_logger('get license for the frontend');
            if (is_multisite()) {
                $this->_logger('get license: multisite');
                $license = $this->get_license('network');
                $this->_logger('got network license');
                if ($this->allow_site_override_network_license()) {
                    $this->_logger('site may override network license');
                    $license = $this->get_license('site');
                    // keep track of site license cause we need to check it
                    // twice...
                    $site_license = $license;
                    $this->_logger('got site license');
                    if (array_key_exists('user_override_license',
                                         $site_license)
                        && 'true' == $site_license['user_override_license']
                    ) {
                        $this->_logger('user may override license');
                        $license = $this->get_license('profile');
                        $this->_logger('got user license');
                    }
                    if (array_key_exists('content_override_license',
                                         $site_license)
                        && 'true'==$site_license['content_override_license']
                    ) {
                        $this->_logger('content may override license');
                        $license = $this->get_license('post-page');
                        $this->_logger('got content license');
                    }
                }
            } else {
                $license = $this->get_license('site');
                if (array_key_exists('user_override_license', $license)
                    && 'true' == $license['user_override_license']
                ) {
                    $license = $this->get_license('profile');
                }
                if (array_key_exists('content_override_license', $license)
                    && 'true' == $license['content_override_license']
                ) {
                    $license = $this->get_license('post-page');
                }
            }
            break;
        }
        return $license;
    }


    // will use a variable prefix with underscore to make *really* sure this
    // postmeta value will NOT be displayed to other users. The option is
    // the same structure as everywhere: a serialized array.
    // (http://codex.wordpress.org/Function_Reference/add_post_meta)
    function get_post_page_license()
    {
        // TODO check if this can be done without a global
        // and if we're always getting what we want
        global $post;
        $license = get_post_meta($post->ID, '_license', true);
        if (is_array($license) && sizeof($license) > 0) {
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
    function select_license_html($location = null, $echo = true)
    {
        // get the previously selected license from this site's options
        // or the plugin's default license
        //$license = get_option('license', $this->plugin_default_license());
        $license  = $this->get_license($location);
        // add lang option
        $lang = (isset($this->locale) && ! empty($this->locale))
              ? 'lang=' . esc_attr($this->locale) : '';

        $html = '';
        $html .= "<span id='license-display'></span>";
        $html .= '<br id="license"><a title="' . __('Choose a Creative Commons license', $this->localization_domain) . '" class="button button-secondary thickbox edit-license" href="https://creativecommons.org/choose/?';
        $html .= 'partner=CC+WordPress+Plugin&';
        $html .= $lang;
        $html .= '&exit_url=' . $this->plugin_url . 'license-return.php?url=[license_url]%26name=[license_name]%26button=[license_button]%26deed=[deed_url]&';
        $html .= '&KeepThis=true&TB_iframe=true&height=500&width=600">' . __('Change license', $this->localization_domain);
        $html .=  '</a>';

        $html .= '<input type="hidden" value="'.$license['deed'].'" id="hidden-license-deed" name="license[deed]"/>';
        $html .= '<input type="hidden" value="'.$license['image'].'" id="hidden-license-image" name="license[image]"/>';
        $html .= '<input type="hidden" value="'.$license['name'].'" id="hidden-license-name" name="license[name]"/>';
        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }


    // return default attribution options
    function get_attribution_options($location)
    {
        switch ($location) {
        case 'network':
            $attribution_options = array(
                'network_name' => sprintf(
                    __('The network name: %s',$this->localization_domain),
                    get_site_option('site_name')
                ),
                'site_name'    => __("A site's name",
                                     $this->localization_domain),
                'display_name' => __('The author display name',
                                     $this->localization_domain),
                'other'        => __('Something completely differrent',
                                     $this->localization_domain)
            );
            break;

        case 'site':
            $attribution_options = array(
                'site_name'    => sprintf(
                    __('The site name: %s', $this->localization_domain),
                    get_bloginfo('site')
                ),
                'display_name' => __('The author display name',
                                     $this->localization_domain),
                'other'        => __('Something completely differrent',
                                     $this->localization_domain)
            );
            break;

        default:
            if ($this->allow_user_override_site_license()) {
                $attribution_options = array(
                    'display_name' => __('The author display name',
                                         $this->localization_domain),
                    'other'        => __('Something completely differrent',
                                         $this->localization_domain)
                );
            }
            break;
        }

        return $attribution_options;
    }


    function select_attribute_to_html($location = null, $echo = true)
    {
        $license = $this->get_license($location);
        $attribute_options = $this->get_attribution_options($location);

        $html = '';
        if (is_array($attribute_options)
            && (sizeof($attribute_options) > 0)
        ) {
            foreach ($attribute_options as $attr_val => $attr_text) {
                $checked = '';
                $id = "license-$attr_val";
                $checked = checked(
                    $license['attribute_to'],
                    $attr_val,
                    false
                );
                $html .= "<label for='$id'><input type='radio' class='license-attribution-options' id='$id' $checked name='license[attribute_to]' value='$attr_val' />" . $attr_text . "</label><br/>\n";
            }
            if ('other' == $license['attribute_to']) {
                $class = '';
                $value = esc_html($license['attribute_other']);
                $url   = esc_url ($license['attribute_other_url']);
            } else {
                // hide the other input text field if the 'other' option is
                // not ticked
                $class = 'hidden';
                $value = '';
                $url = '';
            }

            $html .= "<p id='attribute-other-data' class='$class'>";
            $html .= "<label for='license-other-value'>". __('Attribution text:', $this->localization_domain) . "<br /><input type='text' value='$value' name='license[attribute_other]' id='license-other-value' class='large' size='45' /></label><br />"; //might need max length? TODO need to check hidden value if other prev was selected
            $html .= "<label for='license-other-value-url'>". __('Attribution url:', $this->localization_domain) . "<br /><input type='text' value='$url' name='license[attribute_other_url]' id='license-other-value-url' class='large' size='45' /></label>";
            $html .= '</p>';
        }
        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }


    // add a warning text at the site/network settings
    function display_settings_warning($echo = false)
    {
        $html = '';
        $html .= '<p>';
        $html .= __('', $this->localization_domain);
        $html .= '</p>';
        if ($echo) {
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
     * sites. If the superadmin allows it, siteadmins may change their
     * site's license and choose a different license than the default
     * Network license.
     *
     * Called by wpmu_options action
     *
     **/
    function network_license_settings_html()
    {
        // get the previously selected license from the network options or
        // the plugin's default license
        //$license = get_site_option('license', $this->plugin_default_license());
        $location = 'network';
        $license = $this->get_license($location);

        $html  = '';
        $html .= "<h3>" . __('License settings', $this->localization_domain) . "</h3>\n";
        $html .= $this->display_settings_warning();

        $html .= wp_nonce_field('license-update', $name = 'license_wpnonce', $referer = true, $echo = false);
        $html .= "<table class='form-table'>\n";
        $html .= "<tbody>\n";


        $html .= $this->_license_settings_html($location);

        $html .= "<tr valign='top'>\n";
        $html .= "\t<th scope='row'><label for='override-license'>" .  __("Allow siteadmins to change their site's license", $this->localization_domain) . "</label></th>\n";
        $html .= "\t<td><input name='site_override_license' type='checkbox'" . checked($license['site_override_license'], 'true', false) . " id='site_override-license' value='true' />";
        $html .= "</td>\n";
        $html .= "</tr>\n";

        $html .= "</tbody>\n";
        $html .= "</table>\n";

        echo $html;
    }


    // save license from network settings, user profile and post/page
    // interface
    function save_license($post_id = false)
    {
        if (filter_has_var(INPUT_POST, 'license_wpnonce')
            && wp_verify_nonce(filter_input(INPUT_POST, 'license_wpnonce'),
                               'license-update')
        ) {
            if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
                $this->_save_user_license();
            } elseif (is_multisite()
                      && defined('WP_NETWORK_ADMIN')
                      && WP_NETWORK_ADMIN
            ) {
                $this->_save_network_license();
            } else {
                // presume we're in a post or page wp-admin environment
                // might need to deal with autosave
                $this->_save_post_page_license($post_id);
            }
        } else {
            return $post_id;
        }
    }


    // wrapper so I can use the _verify_license_data function.
    // @TODO refactor in the future
    public function _wrapper_settings_api_verify($data)
    {
        return $this->_verify_license_data('site', $data);
    }


    // check the data before saving it
    // use $from ala get_license to determine where the data is coming from
    // and what should be mandatory return an array or WP_Error?
    private function _verify_license_data($from, $data = null)
    {
        $license = array();

        // if no data was provided assume the data is in $_POST['license']
        if (is_null($data)
            && isset($_POST['license'])
        ) {
            $data = $_POST['license'];
        }

        // always save the current version
        $license['version']          = self::VERSION;
        $license['deed']             = esc_url( $data['deed'] );
        $license['image']            = esc_url( $data['image']);
        $license['name']             = esc_attr($data['name'] );
        $license['attribute_to']     = esc_attr($data['attribute_to']);
        $license['attribute_other']  = esc_html($data['attribute_other' ]);
        $license['attribute_other_url']
            = esc_html($data['attribute_other_url']);

        switch($from) {
            // @TODO need to check this!
        case 'network':
            if (filter_has_var(INPUT_POST, 'site_override_license')) {
                $license['site_override_license'] = esc_attr(filter_input(
                    INPUT_POST,
                    'site_override_license'
                ));
            }
            break;
        case 'site':
            $license['user_override_license']
                = esc_attr($data['user_override_license']);
            $license['content_override_license']
                = esc_attr($data['content_override_license']);
            $license['warning_txt']
                = esc_html($data['warning_txt']);
            break;
        }
        return $license;
    }


    // validates & verifies the data and then saves the license in the
    // site_options
    private function _save_network_license()
    {
        $license = $this->_verify_license_data($from = 'network');
        return update_site_option('license', $license);
        alert('hello');
    }


    // TODO: need to decide if the user option is global for all
    // sites/blogs in a network. Also need to make sure that we're using
    // the current profile user_id which might not be the
    // current_user (e.g. admin changing license for a user).
    // validates & verifies the data and then saves the license in the
    // user_options
    private function _save_user_license()
    {
        $license = $this->_verify_license_data($from = 'profile');
        $user_id = get_current_user_id();
        return update_user_option(
            $user_id,
            'license',
            $license,
            $global = false
        );
    }


    // save post/page metadata due to it being an array it should not be
    // shown in the post or page custom fields interface
    // using _license to hide it from the custom fields
    private function _save_post_page_license($post_id)
    {
        $license = $this->_verify_license_data($from = 'post-page');
        return update_post_meta($post_id,  '_license', $license);
    }


    function license_admin_init()
    {
        /* Register our script. */
        wp_register_script('license', $this->plugin_url . '/js/admin.js');
        wp_enqueue_script("thickbox");
        wp_enqueue_style("thickbox");
        wp_enqueue_script('license');
    }


    // render the html settings for the user profile
    // TODO: add global option: if allowed use this license across all my
    // sites in this network.
    function user_license_settings_html()
    {
        $location = 'profile';
        $html     = wp_nonce_field(
            'license-update',
            $name = 'license_wpnonce',
            $referer = true,
            $echo = false
        );
        $html     .= $this->_license_settings_html($location);
        echo $html;
    }

    function post_page_license_settings_html()
    {
        $location = 'post-page';
        $html  = '<div id="license" class="misc-pub-section misc-pub-section-last ">';
        $html .= wp_nonce_field('license-update', $name = 'license_wpnonce', $referer = true, $echo = false);
        $html .= '<strong>' . __('Licensed:', $this->localization_domain) . '</strong>';

        $html .= '<p>';
        $html .= $this->select_license_html($location, $echo = false);
        $html .= '</p>';

        $html .= '<strong>' . __('Set attribution to',$this->localization_domain) . '</strong>';

        $html .= '<p>';
        $html .= $this->select_attribute_to_html($location, $echo = false);
        $html .= '</p>';
        $html .= '</div>';

        echo $html;
    }


    // DRY wrapper function used for profile settings and network settings
    private function _license_settings_html($location)
    {
        $html ='';
        $html .= "<tr valign='top'>\n";
        $html .= "\t<th scope='row'><label for='license'>" .  __('Select a default license', $this->localization_domain) . "</label></th>\n";
        $html .= "\t<td>";
        $html .= $this->select_license_html($location, $echo = false);
        $html .= "</td>\n";
        $html .= "</tr>\n";

        $html .= "<tr valign='top'>\n";
        $html .= "\t<th scope='row'><label for='attribute_to'>" .  __("Set attribution to", $this->localization_domain) . "</label></th>\n";
        $html .= "\t<td>";
        $html .= $this->select_attribute_to_html($location, $echo = false);
        $html .= "</td>\n";
        $html .= "</tr>\n";

        return $html;
    }


    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Creative Commons',
            'manage_options',
            'cc-admin',
            array($this, 'create_admin_page')
        );
    }


    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('my_option_name');
        ?>
        <div class="wrap">
        <h2>Creative Commons licenses</h2>

        <div style="background: white; border: 3px solid #ddd; padding: 0.5em; display: inline-table;">

        <img src="https://ccstatic.org/presskit/icons/cc.large.png" align="right" style="padding: 1em; width: 20%; height: auto !important; " />

        <h3>About Creative Commons</h3>

        <p><a href="https://creativecommons.org"
        target="_blank">Creative Commons</a> <?php echo __('is a
       nonprofit organization that enables the sharing and use of
       creativity and knowledge through free legal tools.',
                                                           $this->localization_domain); ?></p>

        <p><?php echo __('Our free, easy-to-use copyright licenses
           provide a simple, standardized way to give the public
           permission to share and use your creative work — on
           conditions of your choice. CC licenses let you easily
           change your copyright terms from the default of "all rights
           reserved" to "some rights reserved."',
            $this->localization_domain); ?></p>

        <p><?php echo __('Creative Commons licenses are not an
           alternative to copyright. They work alongside copyright and
           enable you to modify your copyright terms to best suit your
            needs.', $this->localization_domain); ?></p>

        <p><?php echo __('Please consider making a <a href="https://donate.creativecommons.org" target="_blank">donation (tax deductible in the US) to support our work</a>.', $this->localization_domain); ?></p>

        <h4>Sign up for our newsletter</h4>

        <form id="Edit" target="_blank" action="https://donate.creativecommons.org/civicrm/profile/create?gid=30&amp;reset=1" method="post" name="Edit">
        <p><input id="email-Primary" class="form-control input-lg" maxlength="64" name="email-Primary" size="30" autofocus placeholder="mattl@example.com" type="email" required></p>
        <p><input class="btn btn-success btn-block" id="_qf_Edit_next" accesskey="S" name="_qf_Edit_next" type="submit" value="Subscribe"><input name="postURL" type="hidden" value="https://creativecommons.org/thank-you"><input name="cancelURL" type="hidden" value="https://creativecommons.org/newsletter"><input name="group[121]" type="hidden" value="1"><input name="_qf_default" type="hidden" value="Edit:cancel"></p>
        </form>

        </div>
        <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('cc-admin');
                do_settings_sections('cc-admin');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }


    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['id_number'])) {
            $new_input['id_number'] = absint($input['id_number']);
        }

        if (isset($input['title'])) {
            $new_input['title'] = sanitize_text_field($input['title']);
        }

        return $new_input;
    }


    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }


    /**
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
            isset($this->options['id_number'])
            ? esc_attr($this->options['id_number']) : ''
        );
    }


    /**
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset($this->options['title'])
            ? esc_attr($this->options['title']) : ''
        );
    }


    // add filters to this function so themers can easily change the html
    // output
    public function print_license_html($location = 'frontend', $echo = true)
    {
        // TODO if the license is shown on a multiple items page (except
        // the author archive page?) display the site (or network default
        // license)
        // default license including a warning that individual items may be
        // licensed differently.
        // add a filter to include the license with the excerpt & content
        // filter allow an option to switch this off

        $license = $this->get_license($location);
        $html = '';
        if (is_array($license) && sizeof($license) > 0) {
            $deed_url      = esc_url($license['deed']);
            $image_url     = esc_url($license['image']);
            $license_name  = esc_html($license['name']);
            // needs check
            $warning       = (array_key_exists('warning_txt', $license))
                           ?  esc_html($license['warning_txt']) : '';

            $attribution = $this->_get_attribution($license);
            if (is_array($attribution)) {
                $attribute_text = isset($attribution['text'])
                                ? $attribution['text'] : '';
                $attribute_url  = isset($attribution['url'])
                                ? $attribution['url'] : '';
            }

            // it's a single entity so use the post->title otherwise use
            // the site's title and add a warning that multiple items might
            // have different licenses.
            if (is_singular()) {
                global $post;
                if (is_object($post)) {
                    $title_work = esc_html($post->post_title);
                    $warning_text = '';
                }
            } else {
                $title_work   = get_bloginfo('name');
                $attribute_url = esc_html(site_url());
                $warning_text = "<p class='license-warning'>" . esc_html($warning) . "</p>";
            }
            $html = "<div class='license-wrap'>"
                  . $this->license_html_rdfa(
                      $deed_url,
                      $license_name,
                      $image_url,
                      $title_work,
                      is_singular(),
                      $attribute_url,
                      $attribute_text,
                      false,
                      false,
                      $warning_text
                  )
                  . '</div>';
        }
        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }


    public function license_html_rdfa($deed_url, $license_name, $image_url,
                                      $title_work, $is_singular, $attribute_url,
                                      $attribute_text, $source_work_url,
                                      $extra_permissions_url, $warning_text)
    {
        $html = '';
        $html .= "<a rel='license' href='$deed_url'>";
        $html .= "<img alt='" . __('Creative Commons License', $this->localization_domain) . "' style='border-width:0' src='$image_url' />";
        $html .= "</a><br />";
        $html .= "<span xmlns:dct='http://purl.org/dc/terms/' property='dct:title'>$title_work</span> ";
        if ($is_singular && $attribute_text) {
            $html .= __('by', $this->localization_domain);
            if ($attribute_url != '') {
                $html .= " <a xmlns:cc='http://creativecommons.org/ns#' href='$attribute_url' property='cc:attributionName' rel='cc:attributionURL'>$attribute_text</a> ";
            } else {
                $html .= $attribute_text;
            }
        }
        $html .= sprintf(__('is licensed under a <a rel="license" href="%s">%s</a>.', $this->localization_domain), $deed_url, $license_name);
        if ($source_work_url) {
            $html .= '<br />';
            $html .= sprintf(__('Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="%s" rel="dct:source">%s</a>.', $this->localization_domain), $source_work_url, $source_work_url);
        }
        if ($extra_permissions_url) {
            $html .='<br />';
            $html .= sprintf(__('Permissions beyond the scope of this license may be available at <a xmlns:cc="http://creativecommons.org/ns#" href="%s" rel="cc:morePermissions">%s</a>.', $this->localization_domain), $extra_permissions_url, $extra_permissions_url);
        }
        if ($warning_text) {
            $html .= '<br />';
            $html .= $warning_text;
        }
        return $html;
    }


    public function cc0_html_rdfa($title_work, $attribute_url, $attribute_text)
    {
        $result = '<p xmlns:dct="http://purl.org/dc/terms/" xmlns:vcard="http://www.w3.org/2001/vcard-rdf/3.0#">
  <a rel="license"
     href="http://creativecommons.org/publicdomain/zero/1.0/">
    <img src="http://i.creativecommons.org/p/zero/1.0/88x31.png" style="border-style: none;" alt="CC0" /></a>
  <br />
  To the extent possible under law,';
        if ($attribute_url) {
            $result .= '
  <a rel="dct:publisher"
     href="' . $attribute_url . '">
    <span property="dct:title">' . $attribute_text . '</span></a>';
        } else {
            $result .= '
  <span resource="[_:publisher]" rel="dct:publisher">
    <span property="dct:title">' . $attribute_text . '</span></span>';
        }
        $result .= '  has waived all copyright and related or neighboring rights to
  <span property="dct:title">' . $title_work . '</span>.'
/*This work is published from:
<span property="vcard:Country" datatype="dct:ISO3166"
      content="' . $country_code . '" about="' . $attribute_url . '">
      ' . $country_name . '</span>.*/
        . '</p>';
        return $result;
    }


    private function _get_attribution($license)
    {
        if (is_array($license) && sizeof($license) > 0) {
            $attribution_option = isset($license['attribute_to'])
                                ? $license['attribute_to'] : null;
        }

        $attribution = array();

        switch($attribution_option) {
        case 'network_name':
            $attribution['text'] = esc_html(get_site_option('site_name'));
            $attribution['url']  = esc_url(get_permalink());
            break;

        case 'site_name':
            $attribution['text'] = esc_html(get_bloginfo('site'));
            $attribution['url']  = esc_url(get_permalink());
            break;

        case 'display_name':
            // If displaying multiple posts, the display_name (author) will
            // be reverted to site name later.
            $attribution['text']
                = esc_html(get_the_author_meta('display_name'));
            $attribution['url'] = esc_url(get_permalink());
            break;

        case 'other':
            $other = isset($license['attribute_other'])
                   ? $license['attribute_other'] : '';
            $other_url = isset($license['attribute_other_url'])
                       ? $license['attribute_other_url'] : '';

            $attribution['text'] = esc_html($other);
            $attribution['url']  = esc_url($other_url);
            break;
        }
        return $attribution;
    }

    function license_as_widget() {
        register_widget('CreativeCommons_widget');
    }


    // log all errors if wp_debug is active
    private function _logger($string)
    {
        if (defined('WP_DEBUG') && (WP_DEBUG == true)) {
            error_log($string);
        } else {
            return;
        }
    }

}
