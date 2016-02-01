<?php

if ( ! class_exists( 'MIS_Deactivator' ) ) {

	/**
	 * Class MIS_Deactivator
	 *
	 * Class for plugin deactivation
	 */
	class MIS_Deactivator {

		/**
		 * Function bind to plugin deactivation
		 */
		public static function deactivate() {

			// Clear cron on plugin deactivation
			wp_clear_scheduled_hook( 'mis_schedule_hourly_event' );
		}
	}

}
