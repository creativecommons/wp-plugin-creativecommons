<?php 

class license_widget extends WP_Widget {

	function license_widget() {
		/* Widget settings. */
    $widget_ops = array( 
      'classname' => 'license-widget', 
      'description' => __('User-specified Creative Commons License will display in the page footer by default. Alternatively, drag this widget to a sidebar and the license will appear there instead.', 'license') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'license-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'license-widget', __('License', 'license'), $widget_ops, $control_ops );

		// if the widget is not active, (i.e. the plugin is installed but the widget has not been dragged to a sidebar),
		// then display the license in the footer as a default
		if ( ! is_active_widget(false, false, 'license-widget', true) ) {
			add_action('wp_footer', array(&$this, 'print_license') );			
		}
	}

  function print_license() {
    $l = new License;
    $l->print_license_html();
  }

	function widget( $args ) {
		extract( $args );
		$title = __('License', 'license');
		echo $before_widget;
		echo $before_title . $title . $after_title;
    $this->print_license();  
    echo $after_widget;
	}

} //class

?>
