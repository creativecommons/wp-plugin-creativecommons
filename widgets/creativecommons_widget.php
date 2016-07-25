<?php

if( ! class_exists( 'CreativeCommons_widget') ) {
  class CreativeCommons_widget extends WP_Widget {

    private $localization_domain = 'CreativeCommons';

    function CreativeCommons_widget() {
      /* Widget settings. */
      $widget_ops = array(
        'classname' => 'license-widget',
        'description' => __('User-specified Creative Commons License will display in the page footer by default. Alternatively, drag this widget to a sidebar and the license will appear there instead.', $this->localization_domain) );

      /* Widget control settings. */
      $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'license-widget' );

      /* Create the widget. */
      $this->__construct( 'license-widget', __('License', $this->localization_domain), $widget_ops, $control_ops );

      // if the widget is not active, (i.e. the plugin is installed but the widget has not been dragged to a sidebar),
      // then display the license in the footer as a default
      if ( ! is_active_widget(false, false, 'license-widget', true) ) {
        add_action('wp_footer', array(&$this, 'print_license') );
      }
    }

    function print_license() {
      $l = new CreativeCommons;
      $l->print_license_html();
    }

      function widget( $args, $instance ) {
      extract( $args );
      $title = __('License', $this->localization_domain);
      echo $before_widget;
      echo $before_title . $title . $after_title;
      $this->print_license();
      echo $after_widget;
    }

  }
} else {
  error_log('Could not instantiate CreativeCommons_widget class. Perhaps a class with a similar name already exists?');
}
