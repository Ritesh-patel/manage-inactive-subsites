<?php

if ( ! class_exists( 'MIS_Sites' ) ) {

	/**
	 * Class MIS_Sites
	 *
	 * Site management
	 */
	class MIS_Sites {

		public function __construct() {

			// hook into registered cron
			add_action( 'mis_schedule_hourly_event', array( $this, 'manage_subsites' ) );
		}

		/**
		 * Figure out inactive sub-sites and apply the action set into settings.
		 */
		public function manage_subsites() {
			global $wpdb;

			$mis_options = mis_get_options();
			$time_value = intval( $mis_options['time_value'] );
			$time_unit = $mis_options['time_span'];
			$site_action = $mis_options['action'];

			// check if time value and time unit is set or not
			if ( $time_value > 0 && ! empty( $time_unit ) ) {

				//todo prepare statement for $time_unit
				$blog_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT blog_id FROM $wpdb->blogs WHERE TIMESTAMPDIFF( {$time_unit}, registered, last_updated ) > %d", $time_value
					)
				);

				// check if we got any inactive sites
				if ( ! empty( $blog_ids ) && is_array( $blog_ids ) ) {

					foreach ( $blog_ids as $blog_id ) {

						// do not let delete main site
						if ( is_main_site( $blog_id ) ) {
							continue;
						}

						if ( 'archive' == $site_action ) {

							// archive site
							update_blog_status( $blog_id, 'archived', '1' );

						} elseif ( 'deactivate' == $site_action ) {

							//deactivate site
							do_action( 'deactivate_blog', $blog_id );
							update_blog_status( $blog_id, 'deleted', '1' );

						} elseif ( 'delete' == $site_action ) {

							// delete site (remove tables as well)
							if ( ! function_exists( 'wpmu_delete_blog' ) ) {
								require_once( ABSPATH . '/wp-admin/includes/ms.php' );
							}
							wpmu_delete_blog( $blog_id, true );
						}

						do_action( 'mis_inactive_subsite', $blog_id, $site_action );
					}
				}
			}
		}
	}

}
