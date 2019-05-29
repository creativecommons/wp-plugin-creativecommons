<?php

class CreativeCommonsButton {

	const CCL_BUTTON_HEAD = '<div class="cc-attribution-element"><button class="cc-attribution-button cc-attribution-copy-button" data-clipboard-action="copy" data-clipboard-text="" ';

	const CC_BUTTON_TAIL = '"><span data-l10n-id="Share">Share</span></button>
<div class="cc-dropdown-wrapper"><button class="cc-attribution-format-select">HTML &#x25BC;</button>
<ul class="cc-dropdown-menu" aria-haspopup="true" aria-expanded="false">
	<li class="cc-dropdown-menu-item cc-dropdown-menu-item-selected"><a class="cc-dropdown-menu-item-link cc-format-html-rdfa" data-cc-format="html-rdfa" href="#">HTML</a></li>
	<li class="cc-dropdown-menu-item"><a class="cc-dropdown-menu-item-link cc-format-text" data-cc-format="text" href="#">Text</a></li></ul></div>
<button class="cc-attribution-help-button" data-l10n-id="?">?</button></div>';

	private static $instance = null;

	private function __construct() {
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	function mediaToText( $media ) {
		return preg_replace( '/^.+ src="([^"]+)".+$/', '<$1>', $media );
	}

	function htmlToText( $metadata ) {
		$result = preg_replace( '|<br[^>]*>|', ' ', $metadata );
		$result = preg_replace( '|<img [^>]+>|', '', $result );
		$result = preg_replace( '|</?span[^>]*>|', '', $result );
		// Remove links made empty by previous operations
		$result = preg_replace( '|<a[^>]+href[^>]+>\s*</a>|', '', $result );
		// Convert surviving links
		$result = preg_replace(
			'|<a[^>]+href="([^"]+)"[^>]*>([^>]+)</a>|',
			'$2 <$1>',
			$result
		);
		return trim( $result );
	}


	public function markup( $html_rdfa, $text, $button_height, $media ) {
		if ( $text === false ) {
			$text = htmlentities( $this->htmlToText( $html_rdfa ), ENT_QUOTES );
		}
		if ( $media !== false ) {
			$html_rdfa = $media . '<br>' . $html_rdfa;
			$text      = $this->mediaToText( $media ) . ' ' . $text;
		}
		$html_rdfa = htmlentities( $html_rdfa, ENT_QUOTES );
		$button    = self::CCL_BUTTON_HEAD
				. 'data-cc-attribution-html-rdfa="'
				. $html_rdfa
				. '" data-cc-attribution-text="'
				. $text
				. self::CCL_BUTTON_TAIL;
		return $button;
	}


	private function basedir() {
		return plugin_dir_url( dirname( __FILE__ ) );
	}


	public function cc_1ca_add_theme_scripts() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style(
			'wp-jquery-ui',
			$this->basedir() . 'css/jquery-ui.css'
		);
		wp_enqueue_script(
			'clipboard.js',
			'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js',
			[],
			'1.5.10',
			true
		);
		wp_enqueue_style(
			'cc-button',
			self::basedir() . 'css/cc-button.css',
			false,
			'1.1',
			'all'
		);
		wp_enqueue_script(
			'cc-button-support',
			self::basedir() . 'js/cc-button-support.js',
			[ 'jquery-ui-dialog', 'clipboard.js' ],
			'1.1',
			true
		);
	}


	public function cc_1ca_insert_footer() {
		echo "<script>
	  var ccButton = new CCButton();
	  ccButton.addEventListeners();
</script>";
	}

	public function init() {
		add_action(
			'wp_enqueue_scripts',
			array( $this, 'cc_1ca_add_theme_scripts' )
		);
		// Low priority so we go after the scripts are included
		add_action(
			'wp_footer',
			array( $this, 'cc_1ca_insert_footer' ),
			1000
		);
	}
};
