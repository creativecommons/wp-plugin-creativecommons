<?php
/**
 * Class: Creative Commons Widget
 *
 * User-specified Creative Commons License will display in the page footer by default.
 * User will be able to drag this widget to a sidebar as well.
 *
 * @package CC_WordPress_Plugin
 * @subpackage Widget Class
 */

if ( ! class_exists( 'CreativeCommons_Widget' ) ) {
	/**
	 * Widget class, extends the default WP_Widget.
	 */
	class CreativeCommons_Widget extends WP_Widget {

		/**
		 * This method is used to assign an id, name, class name, and description to the widget
		 * to show in admin area.
		 */
		public function __construct() {

			// Widget settings.
			$widget_ops = array(
				'classname'   => 'cc-license-widget',
				'description' => __( 'By default, user-specified Creative Commons License will display in the page footer. Alternatively, drag this widget to a sidebar or any other widget area. The license will appear there instead.', 'CreativeCommons' ),
			);

			// Widget control settings.
			$control_ops = array(
				'id_base' => 'cc-license-widget',
			);

			// Create the widget.
			parent::__construct(
				'cc-license-widget',
				__( 'CC License', 'CreativeCommons' ),
				$widget_ops,
				$control_ops
			);

			/*
			* if the widget is not active, (i.e. the plugin is installed but the widget has not been
			* dragged to a sidebar), then display the license in the footer as a default.
			*/
				add_action( 'wp_footer', array( &$this, 'print_license_footer' ) );

		}

		/**
		 * Instantiates CreativeCommons class and prints the license as widget
		 */
		public function print_license_widget() {
			$ccl     = CreativeCommons::get_instance();
			$license = $ccl->get_license( $license = 'site' );
			if ( $license['display_as_widget'] ) {
				$ccl->print_license_html();
			}
		}

		/**
		 * Instantiates CreativeCommons class and prints the license as footer
		 */
		public function print_license_footer() {
			$ccl     = CreativeCommons::get_instance();
			$license = $ccl->get_license( $license = 'site' );
			if ( $license['display_as_footer'] ) {
				$ccl->print_license_html();
			}
		}

		/**
		 * Defines the widget output that will be displayed on the site front end.
		 *
		 * @param  mixed $args This variable loads an array of arguments which
		 * can be used when building widget output.
		 * @param  mixed $instance Current values of this particular instance.
		 */
		public function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $args['before_widget'];
			echo $args['before_title'] . $title . $args['after_title'];
			$this->print_license_widget( );
			echo $args['after_widget'];
		}

		/**
		 * Widget Backend
		 * Adds setting fields to the widget which will be displayed in the WordPress admin area.
		 *
		 * @param  mixed $instance Current values of this particular instance.
		 */
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : ''; ?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>Leave it empty to remove the title.</p>
			<?php
		}

		/**
		 * Validates the new settings as appropriate and then assigns
		 * them to the current instance.
		 *
		 * @param  mixed $new_instance Contains the values added to the widget settings form.
		 * @param  mixed $old_instance Contains the existing settings — if any exist.
		 *
		 * @return $instance Returns updated instance.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance          = $old_instance;
			$instance['title'] = wp_strip_all_tags( $new_instance['title'] ); // Strips all unwanted tags.
			return $instance;
		}
	}
}
