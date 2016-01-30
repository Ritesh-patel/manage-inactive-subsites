<?php

if ( ! function_exists( 'mis_get_options' ) ) {

	/**
	 * Get plugin setting options
	 *
	 * @return mixed
	 */
	function mis_get_options() {
		return get_site_option( 'mis-options', MIS_Admin::$settings_defaults );
	}
}

if ( ! function_exists( 'mis_save_options' ) ) {

	/**
	 * Set plugin setting options
	 *
	 * @param $options
	 */
	function mis_save_options( $options ) {

		// parse the options with defaults
		$options = wp_parse_args( $options, MIS_Admin::$settings_defaults );

		// sanitise the option values
		$time_value = intval( $options['time_value'] );
		$time_value = ( $time_value >= 0 ) ? $time_value : 0 ;
		$time_span = sanitize_text_field( $options['time_span'] );
		$action = sanitize_text_field( $options['action'] );

		// store option values
		update_site_option( 'mis-options', array(
			'time_value' => $time_value,
			'time_span' => $time_span,
			'action' => $action,
		) );
	}
}
