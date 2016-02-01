<?php

if ( ! class_exists( 'MIS_Activator' ) ) {

	/**
	 * Class MIS_Activator
	 *
	 * Class for plugin activation
	 */
	class MIS_Activator {

		/**
		 * Function bind to plugin activation hook
		 */
		public static function activate() {

			// start cron on plugin activation only if multisite installation
			if ( is_multisite() ) {
				wp_schedule_event( time(), 'hourly', 'mis_schedule_hourly_event' );
			}
		}
	}

}
