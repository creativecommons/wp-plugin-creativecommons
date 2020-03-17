<?php
/**
 * CC WordPress Plugin: Main Class
 *
 * @package CC_WordPress_Plugin
 * @subpackage Main_Class
 */
class CreativeCommons {

	// Make sure the plugin header has the same version number.
	const VERSION = '2020.01.1';

	/**
	 * Plugin URL.
	 *
	 * @since 2.0
	 * @var mixed $plugin_url
	 */
	private $plugin_url;

	/**
	 * Store text-domain. Only used to load text-domain.
	 * Do not use this variable with strings, use 'CreativeCommons' instead
	 *
	 * @since 2.0
	 * @var mixed $localization_domain
	 */
	private $localization_domain = 'CreativeCommons';

	/**
	 * To store locale.
	 *
	 * @since 2.0
	 * @var mixed $locale
	 */
	private $locale;

	/**
	 * Instance
	 *
	 * @since 2.0
	 * @var null $instance
	 */
	private static $instance = null;


	/**
	 * Constructor
	 *
	 * @return void
	 */
	private function __construct() {
	}


	/**
	 * Intializer
	 *
	 * @return void
	 */
	public function init() {
		$this->plugin_url = plugin_dir_url( dirname( __FILE__ ) );

		// language setup.		
		$lang_dir = dirname( dirname( plugin_basename( __FILE__ ) ) ) 
                    . '/languages/';
		load_plugin_textdomain( $this->localization_domain, false, $lang_dir );

		/*
		 * add admin.js to wp-admin pages and displays the site
		 * license settings in the Settings->General settings page
		 * unless you're running WordPress Multisite (Network) the
		 * superadmin has disabled this.
		 */
		add_action( 'admin_init', array( &$this, 'license_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

		// Adds CC License widget to display the license.
		add_action( 'widgets_init', array( &$this, 'license_as_widget' ) );

	}


	/**
	 * Gets an instance
	 *
	 * @return $instance
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Register and add plugin settings.
	 */
	public function page_init() {
		register_setting(
			'cc-admin',
			'license',
			array( &$this, '_wrapper_settings_api_verify' )
		);
		/**
		* This section includes:
		* License selector.
		* license_current settings field which previews the current/selected license.
		*/
		add_settings_section(
			'license-chooser',
			'',
			array( &$this, 'settings_license_chooser' ),
			'cc-admin'
		);
		/**
		 * This section includes:
		 * Additional attribution text.
		 * More attribtion settings that will be added in the future.
		 */
		add_settings_section(
			'license-attribution-settings',
			'',
			array( &$this, 'admin_license_attr_settings' ),
			'cc-admin'
		);

		add_settings_field(
			'license_current',
			__(
				'Current License',
				'CreativeCommons'
			),
			array( &$this, 'settings_preview_current_license' ),
			'cc-admin',
			'license-chooser',
			array( 'label_for' => 'license_current' )
		);
		add_settings_field(
			'additional_attribution_txt',
			__(
				'Add additional attribution text',
				'CreativeCommons'
			),
			array( &$this, 'setting_additional_text_field' ),
			'cc-admin',
			'license-attribution-settings',
			array( 'label_for' => 'additional_attribution_txt' )
		);

		add_settings_field(
			'attribution_to',
			__(
				'Attribution Details',
				'CreativeCommons'
			),
			array( &$this, 'setting_attribution_field' ),
			'cc-admin',
			'license-attribution-settings',
			array( 'label_for' => 'attribution_to' )
		);

		add_settings_field(
			'display_as',
			__(
				'Display license as',
				'CreativeCommons'
			),
			array( &$this, 'display_license_as' ),
			'cc-admin',
			'license-attribution-settings',
			array( 'label_for' => 'display_as' )
		);
	}


	/**
	 * Html output call-back for license section shown in Settings > Creative Commons.
	 * Uses radio buttons, and the selected license is stored in the $license
	 * array as $license['choice'].
	 */
	public function settings_license_chooser() {
		$license = $this->get_license( $location = 'site' ); // Gets license array to store the selection.
		?>
		<table class="widefat" style="padding: 1.4em; padding-right: 3em;">
		<thead>
			<tr>
				<th>
					<h3><?php esc_html_e( 'Select the License', 'CreativeCommons' ); ?></h3>
					<p>
						<?php esc_html_e( 'Select your required default license for your website. Choose a license from Creative Commons licenses. If you are not sure about what license to use, let our ', 'CreativeCommons' ); ?>
						<strong><a href="https://creativecommons.org/choose/" target="blank">
						<?php esc_html_e( 'License Chooser', 'CreativeCommons' ); ?>
						</a></strong>
						<?php esc_html_e( ' help. The selected license can be displayed as a widget. In' ); ?>
						<strong>
							<?php esc_html_e( 'Appearance > Widgets', 'CreativeCommons' ); ?>
						</strong>
						<?php esc_html_e( 'drag CC License widget to the required area. You can also include the license in footer. We recommend using the widget for better compatibility with your theme. You can use individual licenses in posts or pages using Gutenberg blocks.', 'CreativeCommons' ); ?>
					</p>
				</th>
			</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<p><label>
					<input name="license[choice]" type="radio" value="by" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'by' ) : ''; ?> />
					<?php esc_html_e( 'Attribution 4.0 International License', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;" >(CC BY 4.0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/licenses/by/4.0" target="blank"><img src="%1$s" alt="CC BY"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/by.png' );
			?>
			</td>
		</tr>
		<tr>
			<td>
				<p class="cc-test-css"><label>
					<input name="license[choice]" type="radio" value="by-sa" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'by-sa' ) : ''; ?> /> <?php esc_html_e( 'Attribution-ShareAlike 4.0 International License', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;" >(CC BY-SA 4.0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/licenses/by-sa/4.0" target="blank"><img src="%1$s" alt="CC BY-SA"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/by-sa.png' );
			?>
			</td>
		</tr>
		<tr>
			<td>
				<p><label>
					<input name="license[choice]" type="radio" value="by-nc" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'by-nc' ) : ''; ?> /> <?php esc_html_e( 'Attribution-NonCommercial 4.0 International License', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;" >(CC BY-NC 4.0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/licenses/by-nc/4.0" target="blank"><img src="%1$s" alt="CC BY-NC"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/by-nc.png' );
			?>
			</td>
		</tr>
		<tr>
			<td>
				<p><label>
					<input name="license[choice]" type="radio" value="by-nc-sa" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'by-nc-sa' ) : ''; ?> /> <?php esc_html_e( 'Attribution-NonCommercial-ShareAlike 4.0 International License', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;" >(CC BY-NC-SA 4.0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/licenses/by-nc-sa/4.0" target="blank"><img src="%1$s" alt="CC BY-NC-SA"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/by-nc-sa.png' );
			?>
			</td>
		</tr>
		<tr>
			<td>
				<p><label>
					<input name="license[choice]" type="radio" value="by-nc-nd" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'by-nc-nd' ) : ''; ?> /> <?php esc_html_e( 'Attribution-NonCommercial-NoDerivatives 4.0 International License', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;" >(CC BY-NC-ND 4.0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/licenses/by-nc-nd/4.0" target="blank"><img src="%1$s" alt="CC BY-NC-ND"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/by-nc-nd.png' );
			?>
			</td>
		</tr>
		<tr>
			<td>
				<p><label>
					<input name="license[choice]" type="radio" value="by-nd" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'by-nd' ) : ''; ?> /> <?php esc_html_e( 'Attribution-NoDerivatives 4.0 International License', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;" >(CC BY-ND 4.0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/licenses/by-nd/4.0" target="blank"><img src="%1$s" alt="CC BY-ND"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/by-nd.png' );
			?>
			</td>
		</tr>
		<tr>
			<td>
				<p><label>
					<input name="license[choice]" type="radio" value="cc0" <?php isset( $license['choice'] ) ? checked( $license['choice'], 'cc0' ) : ''; ?> /> <?php esc_html_e( 'CC0 Universal Public Domain Dedication license', 'CreativeCommons' ); ?>
					<strong style="color:#fc7303;">(CC0)</strong>
				</p></label>
			</td>
			<td>
			<?php
				printf( '<a href="https://creativecommons.org/share-your-work/public-domain/cc0" target="blank"><img src="%1$s" alt="CC0"></a>', esc_attr( CCPLUGIN__URL ) . 'includes/images/cc0.png' );
			?>
			</td>
	</tr>
		</tbody>
	</table>
		<?php
	}

	/**
	 * Callback for 'license-attribution-settings' section.
	 * It is empty currently, but is required for the related settings-field to work.
	 *
	 * @return void
	 */
	public function admin_license_attr_settings() {
	}


	/**
	 * Function: settings_license_section
	 */
	public function settings_license_section() {
		$this->display_settings_warning(
			$echo = true
		);
	}

	/**
	 * Displays a preview of current selected license. Used in 'license-chooser' settings section.
	 */
	public function settings_preview_current_license() {
		$license = $this->get_license( $location = 'site' );
		$deed    = esc_html( $license['deed'] );
		$name    = esc_attr( $license['name'] );
		$image   = esc_attr( $license['image'] );

		// Closing the settings table by </table>. Do not use any more setting fields in license-chooser section.
		echo "</table><div style=' text-align: center; background: #fff; border: 1px solid #e5e5e5; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 1em;'><img id='cc-current-license-image' src='$image'><br /><a href='$deed' target='blank'>$name</a></div>";
	}


	/**
	 * Provides an option to the user to add an additional
	 * attribution text after the license.
	 */
	public function setting_additional_text_field() {

		$license  = $this->get_license( $location = 'site' );
		$add_txt  = esc_html( $license['additional_attribution_txt'] );
		echo "<input style='padding:0.5rem;' name='license[additional_attribution_txt]' type='text' size='120' maxlength='300' id='additional-attribution-txt' value='$add_txt' />";

	}


	/**
	 * Calls attribution details html callback function.
	 *
	 */
	public function setting_attribution_field() {
		$this->select_attribute_to_html( $location = 'site', $echo = true );
	}

	/**
	 * Display license as a widget or a footer or both.
	 */
	public function display_license_as() {

		$license  = $this->get_license( $location = 'site' );

		?>
		<br />
		<input name="license[display_as_widget]" type="checkbox" value="true" id="display_as_widget" <?php checked( $license['display_as_widget'], 'true' ); ?> />
		<label for="display_as_widget"><?php esc_html_e( 'Widget', 'CreativeCommons' ); ?>
		<i><?php esc_html_e( '(recommended)', 'CreativeCommons' ); ?></i></label>
		<br />

		<input name="license[display_as_footer]" type="checkbox" value="true" id="display_as_footer" <?php checked( $license['display_as_footer'], 'true' ); ?> />
		<label for="display_as_footer"><?php esc_html_e( 'Footer', 'CreativeCommons' ); ?></label>
		<br />

		<?php
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
	 * @return bool true if the network license override is allowed,
	 * otherwise false
	 */
	public function allow_site_override_network_license() {
		$license = $this->get_license( $location = 'network' );

		/*
		 * Using true as string instead of bool since it will be a
		 * string value returned from the settings form
		 */
		if ( 'true' == $license['site_override_license'] ) {
			return true;
		} else {
			return false;
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
	 **/
	public function plugin_default_license() {
		$this->_logger( 'Got default settings' );
		$license = array(
			'deed'                       => 'http://creativecommons.org/licenses/by-sa/4.0/',
			'image'                      => CCPLUGIN__URL . 'includes/images/by-sa.png',
			'attribute_to'               => '',
			'title'                      => '',
			'title_url'                  => '',
			'author'                     => '',
			'author_url'                 => '',
			'name'                       => 'Creative Commons Attribution-Share Alike 4.0',
			'sitename'                   => get_bloginfo( '' ),
			'siteurl'                    => get_bloginfo( 'url' ),
			'site_override_license'      => true,
			'user_override_license'      => true,
			'content_override_license'   => true,
			'version'                    => self::VERSION,
			'additional_attribution_txt' => __( '', 'CreativeCommons' ),
			'choice'                     => '',
			'display_as_widget'          => 'false',
			'display_as_footer'          => 'false',
		);
		return $license;
	}


	/**
	 * Content for a license. No license found or not allowed? Continue 2
	 * 1) Check if it's allowed to have a license per content => check the
	 * 2) Check if it's allowed to have a license per user => check the
	 * content author and grab his/her license preference. No license found
	 * 3) Check if the site is part of a network => No? continue step 4a
	 * or user are not allowed to choose their own license? Continue 3
	 * 4a) the site is NOT part of a network => Get license from options or
	 * Yes? continue step 4b
	 * 4b) The site is part of a network => check if the site may choose its
	 * return plugin's default
	 * license found Continue step 5
	 * own license => check the options for a license. Not allowed or no
	 * return plugin default
	 * 5) Check the multisite options for a license and return if not found
	 *
	 * @param mixed $location null.
	 */
	public function get_license( $location = null ) {
		switch ( $location ) {
			case 'network':
				$this->_logger( 'called network' );
				$license = ( $network_license = get_site_option( 'license' ) )
					? $network_license : $this->plugin_default_license();
				break;

			case 'site':
				$this->_logger( 'called site' );
				if ( is_multisite() ) {
					$this->_logger( 'multisite, check network settings' );
					$license = ( $site_license = get_option( 'license' ) )
						? $site_license : $this->get_license( 'network' );
				} else {
					$this->_logger( 'single site, get site license or else default settings' );
					$license = ( $site_license = get_option( 'license' ) )
						? $site_license : $this->plugin_default_license();
				}
				break;

			case 'profile':
				$this->_logger( 'called profile' );
				$license = ( $user_license = get_user_option( 'license' ) )
					? $user_license : $this->get_license( 'site' );
				break;

			case 'post-page':
				$this->_logger( 'called post-page' );
				$license = ( $post_page_license = $this->get_post_page_license() )
					? $post_page_license : $this->get_license( 'profile' );
				break;

			// TODO: need to check default structure below
			// since this can cause way too many calls for the right license.
			case 'frontend':
				$this->_logger( 'get license for the frontend' );
				if ( is_multisite() ) {
					$this->_logger( 'get license: multisite' );
					$license = $this->get_license( 'network' );
					$this->_logger( 'got network license' );
				} else {
					$license = $this->get_license( 'site' );
					if ( array_key_exists( 'user_override_license', $license )
					&& 'true' == $license['user_override_license']
					) {
						$license = $this->get_license( 'profile' );
					}
					if ( array_key_exists( 'content_override_license', $license )
					&& 'true' == $license['content_override_license']
					) {
						$license = $this->get_license( 'post-page' );
					}
				}
				break;
		}
		return $license;
	}


	/**
	 * Function: get_post_page_license
	 *
	 * It will use a variable prefix with underscore to make really
	 * sure this postmeta value will NOT be displayed to other users. The option is
	 * the same structure as everywhere: a serialized array.
	 *
	 * (http://codex.wordpress.org/Function_Reference/add_post_meta)
	 */
	public function get_post_page_license() {
		// TODO check if this can be done without a global.
		global $post;
		$license = get_post_meta( $post->ID, '_license', true );
		if ( is_array( $license ) && count( $license ) > 0 ) {
			return $license;
		} else {
			return false;
		}
	}


	/**
	 * Function: get_attribution_options
	 *
	 * Returns default attribution options
	 *
	 * @param  mixed $location
	 *
	 * @return $attribution_options
	 */
	public function get_attribution_options( $location ) {
		switch ( $location ) {
			case 'network':
				$attribution_options = array(
					'network_name' => sprintf(
						__( 'The network name: %s', 'CreativeCommons' ),
						get_site_option( 'site_name' )
					),
					'site_name'    => __(
						"A site's name",
						'CreativeCommons'
					),
					'display_name' => __(
						'The author display name',
						'CreativeCommons'
					),
					'other'        => __(
						'Something completely differrent',
						'CreativeCommons'
					),
				);
				break;

			case 'site':
				$attribution_options = array(
					'site_name'    => sprintf(
						__( 'The site name: %s', 'CreativeCommons' ),
						get_bloginfo( 'site' )
					),
					'display_name' => __(
						'The author display name',
						'CreativeCommons'
					),
					'other'        => __(
						'Something completely differrent',
						'CreativeCommons'
					),
				);
				break;

			default:
				
				break;
		}

		return $attribution_options;
	}


	/**
	 * Funciton: select_attribute_to_html
	 *
	 * @param  mixed $location null.
	 * @param  mixed $echo true.
	 */
	public function select_attribute_to_html( $location = null, $echo = true ) {

		$license    = $this->get_license( $location = 'site' );
		$title      = ( isset( $license['title'] ) ) ? esc_html( $license['title'] ) : '';
		$title_url  = ( isset( $license['title_url'] ) ) ? esc_html( $license['title_url'] ) : '';
		$author     = ( isset( $license['author'] ) ) ? esc_html( $license['author'] ) : '';
		$author_url = ( isset( $license['author_url'] ) ) ? esc_html( $license['author_url'] ) : '';

		?>
		<table class="widefat" style="background:none; width:0%; border:none; box-shadow:none;">
			<tr>
				<td style="padding:10px 10px;">
					<span>
						<?php esc_html_e( 'Title', 'CreativeCommons' ); ?>
					</span>
				</td>
				<td style="padding:10px 10px;">
					<?php
					printf( '<input type="text" name="license[title]" value="%1$s" id="title" class="large" size="45" /><br />', esc_attr( $title ) );
					?>
				</td>
			</tr>
			<tr>
				<td style="padding:10px 10px;">
					<span>
						<?php esc_html_e( 'Title URL', 'CreativeCommons' ); ?>
					</span>
				</td>
				<td style="padding:10px 10px;">
					<?php printf( '<input type="text" name="license[title_url]" value="%1$s" id="title_url" class="large" size="45" /><br />', esc_attr( $title_url ) ); ?>
				</td>
			</tr>
			<tr>
				<td style="padding:10px 10px;">
					<span>
						<?php esc_html_e( 'Author', 'CreativeCommons' ); ?>
					</span>
				</td>
				<td style="padding:10px 10px;">
					<?php printf( '<input type="text" name="license[author]" value="%1$s" id="author" class="large" size="45" /><br />', esc_attr( $author ) ); ?>
				</td>
			</tr>
			<tr>
				<td style="padding:10px 10px;">
					<span>
						<?php esc_html_e( 'Author URL', 'CreativeCommons' ); ?>
					</span>
				</td>
				<td style="padding:10px 10px;">
					<?php printf( '<input type="text" name="license[author_url]" value="%1$s" id="author_url" class="large" size="45" /><br />', esc_attr( $author_url ) ); ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Function: display_settings_warning
	 *
	 * Adds a warning text at the site/network settings.
	 *
	 * @param  mixed $echo false.
	 */
	public function display_settings_warning( $echo = false ) {
		$html = '';
		$html .= '<p>';
		$html .= __( '', 'CreativeCommons' );
		$html .= '</p>';
		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Function: save_license
	 *
	 * Saves license from network settings, user profile and post/page interface.
	 *
	 * @param  mixed $post_id false.
	 */
	public function save_license( $post_id = false ) {
		if ( filter_has_var( INPUT_POST, 'license_wpnonce' )
			&& wp_verify_nonce(
				filter_input( INPUT_POST, 'license_wpnonce' ),
				'license-update'
			)
		) {
			if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
				$this->_save_user_license();
			} elseif ( is_multisite()
					&& defined( 'WP_NETWORK_ADMIN' )
					&& WP_NETWORK_ADMIN
			) {
				$this->_save_network_license();
			} else {
				// Assume we're in a post or page wp-admin environment might need to deal with autosave.
				$this->_save_post_page_license( $post_id );
			}
		} else {
			return $post_id;
		}
	}


	/**
	 * Function: _wrapper_settings_api_verify
	 *
	 * Wrapper so that we can use the _verify_license_data function.
	 * TODO: refactor in the future
	 *
	 * @param  mixed $data license data.
	 */
	public function _wrapper_settings_api_verify( $data ) {
		return $this->_verify_license_data( 'site', $data );
	}


	/**
	 * Checks the data before saving it
	 * You must include any addition in the $license array in this
	 * function for it to be saved.
	 *
	 * @param  mixed $from Data from Network or Site.
	 * @param  mixed $data Array of license data.
	 */
	private function _verify_license_data( $from, $data = null ) {
		$license = array();

		// if no data was provided assume the data is in $_POST['license'].
		if ( is_null( $data )
			&& isset( $_POST['license'] )
		) {
			$data = sanitize_text_field( wp_unslash( $_POST['license'] ) );
		}

		// Saves the license attribution information. MAke sure to save the current version.
		$license['version']             = self::VERSION;
		$license['attribute_to']        = ( isset( $data['attribute_to'] ) ) ? esc_attr( $data['attribute_to'] ) : '';
		$license['attribute_other']     = ( isset( $data['attribute_other'] ) ) ? esc_html( $data['attribute_other'] ) : '';
		$license['attribute_other_url'] = ( isset( $data['attribute_other_url'] ) ) ? esc_html( $data['attribute_other_url'] ) : '';
		$license['choice']              = ( isset( $data['choice'] ) ) ? esc_attr( $data['choice'] ) : '';
		$license['display_as_widget']   = ( isset( $data['display_as_widget'] ) ) ? esc_html( $data['display_as_widget'] ) : '';
		$license['display_as_footer']   = ( isset( $data['display_as_footer'] ) ) ? esc_html( $data['display_as_footer'] ) : '';

		// Gets the name, deed(url) and icon of the selected license and stores/saves it.
		switch ( $data['choice'] ) {
			case 'by':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution 4.0 International' );
				$license['deed']  = esc_url( 'http://creativecommons.org/licenses/by/4.0/' );
				break;
			case 'by-sa':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by-sa.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution-ShareAlike 4.0 International' );
				$license['deed']  = esc_url( 'http://creativecommons.org/licenses/by-sa/4.0/' );
				break;
			case 'by-nc':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by-nc.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution-NonCommercial 4.0 International' );
				$license['deed']  = esc_url( 'https://creativecommons.org/licenses/by-nc/4.0' );
				break;
			case 'by-nc-sa':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by-nc-sa.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International' );
				$license['deed']  = esc_url( 'https://creativecommons.org/licenses/by-nc-sa/4.0' );
				break;
			case 'by-nc-nd':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by-nc-nd.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International' );
				$license['deed']  = esc_url( 'https://creativecommons.org/licenses/by-nc-nd/4.0' );
				break;
			case 'by-nd':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by-nd.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution-NoDerivatives 4.0 International' );
				$license['deed']  = esc_url( 'https://creativecommons.org/licenses/by-nd/4.0' );
				break;
			case 'cc0':
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/cc0.png' );
				$license['name']  = esc_attr( 'Creative Commons CC0 Universal Public Domain Dedication' );
				$license['deed']  = esc_url( 'https://creativecommons.org/share-your-work/public-domain/cc0' );
				break;
			default:    // Uses 'CC BY-SA' as the default license.
				$license['image'] = esc_attr( CCPLUGIN__URL . 'includes/images/by-sa.png' );
				$license['name']  = esc_attr( 'Creative Commons Attribution-ShareAlike 4.0 International' );
				$license['deed']  = esc_url( 'http://creativecommons.org/licenses/by-sa/4.0/' );
		}

		switch ( $from ) {
			case 'network':
				if ( filter_has_var( INPUT_POST, 'site_override_license' ) ) {
					$license['site_override_license'] = esc_attr(
						filter_input(
							INPUT_POST,
							'site_override_license'
						)
					);
				}
				break;
			case 'site':
				$license['user_override_license']      =  ( isset( $data['user_override_license'] ) ) ? esc_attr( $data['user_override_license'] ) : '';
				$license['content_override_license']   = ( isset( $data['user_override_license'] ) ) ? esc_attr( $data['user_override_license'] ) : '';
				$license['additional_attribution_txt'] = ( isset( $data['additional_attribution_txt'] ) ) ? esc_html( $data['additional_attribution_txt'] ) : '';
				$license['title']                      = ( isset( $data['title'] ) ) ? esc_attr( $data['title'] ) : '';
				$license['title_url']                  = ( isset( $data['title_url'] ) ) ? esc_url( $data['title_url'] ) : '';
				$license['author']                     = ( isset( $data['author'] ) ) ? esc_attr( $data['author'] ) : '';
				$license['author_url']                 = ( isset( $data['author_url'] ) ) ? esc_url( $data['author_url'] ) : '';
				break;
		}
		return $license;
	}


	/**
	 * Function: _save_network_license
	 *
	 * Validates & verifies the data and then saves the license in the site_options.
	 */
	private function _save_network_license() {
		$license = $this->_verify_license_data( $from = 'network' );
		return update_site_option( 'license', $license );
	}


	/**
	 * Function: _save_user_license
	 *
	 * TODO: need to decide if the user option is global for all sites/blogs in a network.
	 * Also need to make sure that we're using
	 * the current profile user_id which might not be the
	 * current_user (e.g. admin changing license for a user).
	 * validates & verifies the data and then saves the license in the
	 * user_options
	 */
	private function _save_user_license() {
		$license    = $this->_verify_license_data( $from = 'profile' );
		$user_id    = get_current_user_id();
		return update_user_option(
			$user_id,
			'license',
			$license,
			$global = false
		);
	}


	/**
	 * Function: _save_post_page_license
	 *
	 * Save post/page metadata due to it being an array it should not be
	 * shown in the post or page custom fields interface using _license to hide it from the custom fields
	 *
	 * @param  mixed $post_id .
	 */
	private function _save_post_page_license( $post_id ) {
		$license = $this->_verify_license_data( $from = 'post-page' );
		return update_post_meta( $post_id, '_license', $license );
	}


	/**
	 * Function: license_admin_init
	 *
	 * @return void
	 */
	public function license_admin_init() {
		// Register our script.
		wp_register_script( 'license', $this->plugin_url . '/js/admin.js' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'license' );
	}

	/**
	 * Add options page, this page will be under "Settings"
	 */
	public function add_plugin_page() {
		add_options_page(
			'Settings Admin',
			'Creative Commons',
			'manage_options',
			'cc-admin',
			array( $this, 'create_admin_page' )
		);
	}


	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property.
		$this->options = get_option( 'my_option_name' );
		?>
		<div class="wrap">
		<h2>Creative Commons licenses</h2>
		<br />

		<div style="background: white; border: 1px solid #e5e5e5; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 2em; display: inline-table;">

		<?php
			printf( '<img src="%1$s" align="right" style="padding: 1em; width: 20%; height: auto !important; " />', esc_attr( CCPLUGIN__URL ) . 'assets/icon-256x256.png' );
		?>

		<h3>About Creative Commons</h3>

		<p><a href="https://creativecommons.org"
		target="_blank">Creative Commons</a>
		<?php
		esc_html_e(
			'is a
			nonprofit organization that enables the sharing and use of
			creativity and knowledge through free legal tools.',
			'CreativeCommons'
		);
		?>
		</p>

		<p>
		<?php
		esc_html_e(
			'Our free, easy-to-use copyright licenses
			provide a simple, standardized way to give the public
			permission to share and use your creative work — on
			conditions of your choice. CC licenses let you easily
			change your copyright terms from the default of "all rights
			reserved" to "some rights reserved."',
			'CreativeCommons'
		);
		?>
		</p>

		<p>
		<?php
		esc_html_e(
			'Creative Commons licenses are not an
		   alternative to copyright. They work alongside copyright and
		   enable you to modify your copyright terms to best suit your
			needs.',
			'CreativeCommons'
		);
		?>
		</p>

		<p><?php esc_html_e( 'Please consider making a ', 'CreativeCommons' ); ?><a href="https://donate.creativecommons.org" target="_blank"><?php esc_html_e( 'donation (tax deductible in the US) to support our work', 'CreativeCommons' ); ?></a>.</p>

		<h4><?php esc_html_e( 'Sign up for our newsletter', 'CreativeCommons' ); ?></h4>

		<form id="Edit" target="_blank" action="https://donate.creativecommons.org/civicrm/profile/create?gid=30&amp;reset=1" method="post" name="Edit">
		<p><input id="email-Primary" class="form-control input-lg" maxlength="64" name="email-Primary" size="30" autofocus placeholder="example@example.com" type="email" required></p>
		<p><input class="btn btn-success btn-block" id="_qf_Edit_next" accesskey="S" name="_qf_Edit_next" type="submit" value="Subscribe"><input name="postURL" type="hidden" value="https://creativecommons.org/thank-you"><input name="cancelURL" type="hidden" value="https://creativecommons.org/newsletter"><input name="group[121]" type="hidden" value="1"><input name="_qf_default" type="hidden" value="Edit:cancel"></p>
		</form>

		</div>
		<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields.
				settings_fields( 'cc-admin' );
				do_settings_sections( 'cc-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}


	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys.
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['id_number'] ) ) {
			$new_input['id_number'] = absint( $input['id_number'] );
		}

		if ( isset( $input['title'] ) ) {
			$new_input['title'] = sanitize_text_field( $input['title'] );
		}

		return $new_input;
	}


	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print 'Enter your settings below:';
	}


	/**
	 * Get the settings option array and print one of its values
	 */
	public function id_number_callback() {
		printf(
			'<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
			isset( $this->options['id_number'] )
			? esc_attr( $this->options['id_number'] ) : ''
		);
	}


	/**
	 * Get the settings option array and print one of its values
	 */
	public function title_callback() {
		printf(
			'<input type="text" id="title" name="my_option_name[title]" value="%s" />',
			isset( $this->options['title'] )
			? esc_attr( $this->options['title'] ) : ''
		);
	}


	/**
	 * Funciton: print_license_html
	 *
	 * @param  mixed $location
	 * @param  mixed $echo
	 */
	public function print_license_html( $location = 'frontend', $echo = true ) {
		/*
		 * TODO: if the license is shown on a multiple items page (except
		 * the author archive page?) display the site (or network default license)
		 * default license including a warning that individual items may be licensed differently.
		 * Add a filter to include the license with the excerpt & content
		 * filter allow an option to switch this off
		 */

		$license = $this->get_license( $location );
		$html = '';
		if ( is_array( $license ) && count( $license ) > 0 ) {
			$deed_url     = esc_url( $license['deed'] );
			$image_url    = esc_attr( $license['image'] );
			$license_name = esc_attr( $license['name'] );
			$title        = esc_attr( $license['title'] );
			$title_url    = esc_url( $license['title_url'] );
			$author       = esc_attr( $license['author'] );
			$author_url   = esc_url( $license['author_url'] );


			$additional_attribution_txt = ( array_key_exists( 'additional_attribution_txt', $license ) )
						? esc_html( $license['additional_attribution_txt'] ) : '';

			$attribution = $this->_get_attribution( $license );
			if ( is_array( $attribution ) ) {
				$attribute_text = isset( $attribution['text'] )
								? $attribution['text'] : '';
				$attribute_url  = isset( $attribution['url'] )
								? $attribution['url'] : '';
			}

			/*
			 * it's a single entity so use the post->title otherwise use
			 * the site's title and add a warning that multiple items might have different licenses.
			 */
			if ( is_singular() ) {
				global $post;
				if ( is_object( $post ) ) {
					$title_work = esc_html( $post->post_title );
				}
			} else {
				$title_work                 = get_bloginfo( 'name' );
				$attribute_url              = esc_html( site_url() );
				$additional_attribution_txt = "<p class='license-warning'>" . esc_html( $additional_attribution_txt ) . '</p>';
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
					$additional_attribution_txt,
					$title,
					$title_url,
					$author,
					$author_url
				)
				. '</div>';
		}
		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}


	/**
	 * Function: license_html_rdfa
	 */
	public function license_html_rdfa( $deed_url, $license_name, $image_url,
									$title_work, $is_singular, $attribute_url,
									$attribute_text, $source_work_url,
									$extra_permissions_url, $additional_attribution_txt, $title, $title_url, $author, $author_url ) {
		list($img_width, $img_height) = getimagesize( $image_url );
		$html = '';
		$html .= "<a rel='license' href='$deed_url'>";
		$html .= "<img alt='" . __( 'Creative Commons License', 'CreativeCommons' ) . "' style='border-width:0' src='$image_url' width='$img_width' height='$img_height'  />";
		$html .= '</a><br />';


		$html .= sprintf( __( 'Except where otherwise noted, ', 'CreativeCommons' ) );

		/*
		 * If title and author details not entered,
		 * replace them by 'the content on this site'.
		 */
		if ( $title ) {
			$html .= sprintf( __( '<a href="%1$s">%2$s</a> ' ), $title_url, $title );
		}
		else {
			$html .= sprintf( __( 'the content ', 'CreativeCommons' ) );
		}

		if ( $author ) {
			$html .= sprintf( __( 'by <a href="%1$s">%2$s</a>' ), $author_url, $author );
		}
		else {
			$html .= sprintf( __( 'on this site ', 'CreativeCommons' ) );
		}

		$html .= sprintf( __( ' is licensed under a <a rel="license" href="%1$s">%2$s</a> License.', 'CreativeCommons' ), $deed_url, $license_name );
		if ( $source_work_url ) {
			$html .= '<br />';
			$html .= sprintf( __( 'Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="%s" rel="dct:source">%s</a>.', 'CreativeCommons' ), $source_work_url, $source_work_url );
		}
		if ( $extra_permissions_url ) {
			$html .= '<br />';
			$html .= sprintf( __( 'Permissions beyond the scope of this license may be available at <a xmlns:cc="http://creativecommons.org/ns#" href="%s" rel="cc:morePermissions">%s</a>.', 'CreativeCommons' ), $extra_permissions_url, $extra_permissions_url );
		}

		if ( $additional_attribution_txt ) {
			$html .= '<br />';
			$html .= $additional_attribution_txt;
		}
		return $html;
	}


	/**
	 * Function: _get_attribution
	 *
	 * @param  mixed $license
	 */
	private function _get_attribution( $license ) {
		if ( is_array( $license ) && count( $license ) > 0 ) {
			$attribution_option = isset( $license['attribute_to'] )
								? $license['attribute_to'] : null;
		}

		$attribution = array();

		switch ( $attribution_option ) {
			case 'network_name':
				$attribution['text'] = esc_html( get_site_option( 'site_name' ) );
				$attribution['url']  = esc_url( get_permalink() );
				break;

			case 'site_name':
				$attribution['text'] = esc_html( get_bloginfo( 'site' ) );
				$attribution['url']  = esc_url( get_permalink() );
				break;

			case 'display_name':
				// If displaying multiple posts, the display_name (author) will be reverted to site name later.
				$attribution['text']
				= esc_html( get_the_author_meta( 'display_name' ) );
				$attribution['url'] = esc_url( get_permalink() );
				break;

			case 'other':
				$other = isset( $license['attribute_other'] )
				? $license['attribute_other'] : '';
				$other_url = isset( $license['attribute_other_url'] )
				? $license['attribute_other_url'] : '';

				$attribution['text'] = esc_html( $other );
				$attribution['url']  = esc_url( $other_url );
				break;
		}
		return $attribution;
	}

	/**
	 * Function: license_as_widget
	 *
	 * Registers widget, instantiates the CreativeCommons_Widget class.
	 */
	public function license_as_widget() {
		register_widget( 'CreativeCommons_Widget' );
	}


	/**
	 * Function: _logger
	 *
	 * Logs all errors if wp_debug is active.
	 *
	 *  @param  mixed $string error message.
	 *
	 * @return void
	 */
	private function _logger( $string ) {
		if ( defined( 'WP_DEBUG' ) && ( WP_DEBUG == true ) ) {
			error_log( $string );
		} else {
			return;
		}
	}

}
