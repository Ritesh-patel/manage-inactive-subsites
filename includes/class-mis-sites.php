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

			$mis_options = mis_get_options();
			$site_action = $mis_options['action'];

			// get ids of inactive sites
			$inactive_site_ids = self::get_inactive_site_ids();

			// check if we got any inactive sites
			if ( ! empty( $inactive_site_ids ) && is_array( $inactive_site_ids ) ) {

				foreach ( $inactive_site_ids as $site_id ) {

					// do not let delete main site
					if ( is_main_site( $site_id ) ) {
						continue;
					}

					if ( 'archive' == $site_action ) {

						// archive site
						update_blog_status( $site_id, 'archived', '1' );

					} elseif ( 'deactivate' == $site_action ) {

						//deactivate site
						do_action( 'deactivate_blog', $site_id );
						update_blog_status( $site_id, 'deleted', '1' );

					} elseif ( 'delete' == $site_action ) {

						// delete site (remove tables as well)
						if ( ! function_exists( 'wpmu_delete_blog' ) ) {
							require_once( ABSPATH . '/wp-admin/includes/ms.php' );
						}
						wpmu_delete_blog( $site_id, true );
					}

					do_action( 'mis_inactive_subsite', $site_id, $site_action );
				}
			}
		}


		/**
		 * Static function to get ids of inactive sites
		 *
		 * @return array
		 */
		public static function get_inactive_site_ids() {

			global $wpdb;

			$mis_options = mis_get_options();
			$time_value = intval( $mis_options['time_value'] );
			$time_unit = $mis_options['time_span'];

			$site_ids = array();

			switch( $time_unit ){
				case "HOUR"     : $time_unit_sql = "HOUR";
					break;
				case "DAY"      : $time_unit_sql = "DAY";
					break;
				case "WEEK"     : $time_unit_sql = "WEEK";
					break;
				case "MONTH"    : $time_unit_sql = "MONTH";
					break;
				case "YEAR"     : $time_unit_sql = "YEAR";
					break;
				default         : $time_unit_sql = "";
			}

			// check if time value and time unit is set or not
			if ( $time_value > 0 && ! empty( $time_unit_sql ) ) {

				$site_ids = $wpdb->get_col(
					$wpdb->prepare(
							"SELECT blog_id
							FROM $wpdb->blogs
							WHERE TIMESTAMPDIFF( {$time_unit_sql}, registered, last_updated ) > %d
							LIMIT 0, 100", $time_value
					)
				);
			}

			return $site_ids;
		}
	}

}
