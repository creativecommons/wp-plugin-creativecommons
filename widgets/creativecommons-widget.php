<?php
/**
 * Class: Creative Commons Widget
 *
 * User-specified Creative Commons License will display in the page footer by default.
 * User will be able to drag this widget to a sidebar as well.
 *
 * @package CC_WordPress_Plugin
 * @subpackage Widget Class
 * @since 2.0
 */

if ( ! class_exists( 'CreativeCommons_widget' ) ) {
	/**
	 * Widget class
	 *
	 * @since 2.0
	 */
	class CreativeCommons_widget extends WP_Widget {

		/**
		 * This method is used to assign an id, name, class name, and description to the widget
		 * to show in admin area.
		 */
		public function __construct() {

			// Widget settings.
			$widget_ops = array(
				'classname'   => 'license-widget',
				'description' => __( 'By default, user-specified Creative Commons License will display in the page footer. Alternatively, drag this widget to a sidebar or any other widget area. The license will appear there instead.', 'CreativeCommons' ),
			);

			// Widget control settings.
			$control_ops = array(
				'width'   => 300,
				'height'  => 350,
				'id_base' => 'license-widget',
			);

			// Create the widget.
			parent::__construct(
				'license-widget',
				__( 'CC License', 'CreativeCommons' ),
				$widget_ops,
				$control_ops
			);

			/*
			* if the widget is not active, (i.e. the plugin is installed but the widget has not been
			* dragged to a sidebar), then display the license in the footer as a default.
			*/
			if ( ! is_active_widget( false, false, 'license-widget', true ) ) {
				add_action( 'wp_footer', array( &$this, 'print_license' ) );
			}
		}

		/**
		 * Instantiates and prints the license
		 *
		 * @return void
		 */
		public function print_license() {
			$ccl = CreativeCommons::get_instance();
			$ccl->print_license_html();
		}

		/**
		 * Widget
		 *
		 * @param  mixed $args
		 * @param  mixed $instance
		 *
		 * @return void
		 */
		public function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $args['before_widget'];
			echo $args['before_title'] . $title . $args['after_title'];
			$this->print_license();
			echo $args['after_widget'];
		}

		/**
		 * Widget Backend
		 */
		public function form( $instance ) {
			if ( isset( $instance['title'] ) ) {
				$title = $instance['title'];
			}
		}

		// Updating widget replacing old instances with new
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
		}
	}

} else {
	error_log( 'Could not instantiate CreativeCommons_widget class. Perhaps a class with a similar name already exists?' );
}
