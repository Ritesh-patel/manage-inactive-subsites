<?php

if ( ! class_exists( 'MIS_Admin' ) ) {

	/**
	 * Class MIS_Admin
	 *
	 * Admin class file of plugin.
	 */
	class MIS_Admin {

		/**
		 * @var array settings_defaults
		 * Holds the default value for admin settings
		 */
		public static $settings_defaults = array(
			'time_value' => '5',
			'time_span' => '',
			'action' => 'archive',
		);

		public function __construct() {

			if ( is_multisite() ) {

				// init admin hooks if multisite installation
				add_action( 'network_admin_menu', array( $this, 'add_network_settings' ), 10 );
				add_action( 'admin_init', array( $this, 'save_settings' ), 10 );
			} else {

				// admin notice if not multisite installation and if current user is admin
				if ( current_user_can( 'manage_options' ) ) {
					add_action( 'admin_notices', array( $this, 'not_mu_admin_notice' ) );
				}
			}
		}

		public function not_mu_admin_notice() {

			// prepare deactivate plugin link
			$plugin_file = MIS_BASE_NAME;
			$deactivate_link = '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ) . '" aria-label="' . esc_attr( sprintf( __( 'Deactivate %s' ), 'Manage Inactive Subsites' ) ) . '">' . __( 'Deactivate', 'manage-inactive-subsites' ) . '</a>';
			?>
			<div class="error">
				<p><?php echo __( 'This is in not WordPress Multisite installation and hence Manage Inactive Subsites plugin is not needed. Please', 'manage-inactive-subsites' ) . ' ' . $deactivate_link . ' ' . __( 'Manage Inactive Subsites plugin.', 'manage-inactive-subsites' ) ?></p>
			</div>
			<?php
		}

		/**
		 * Manage network admin section
		 */
		public function add_network_settings() {
			$menu_page = add_submenu_page( 'settings.php', __( 'Site Inactivation', 'manage-inactive-subsites' ), __( 'Site Inactivation', 'manage-inactive-subsites' ), 'manage_sites', 'manage-inactive-subsites', array( $this, 'admin_settings_content' ) );
			add_action( 'load-' . $menu_page, array( $this, 'add_help_tab' ) );
		}

		/**
		 * Network admin settings content
		 */
		public function admin_settings_content() {
			$admin_options = mis_get_options();
			?>
			<div class="wrap mis-admin">
				<h2><?php _e( 'Manage Site Inactivation', 'manage-inactive-subsites' ) ?></h2>
				<form method="post" action="">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="mis_options_time_value"><?php _e( 'Time span to set site in inactive mode', 'manage-inactive-subsites' ) ?></label></th>
							<td>
								<input type="number" min="0" class="small-text" name="mis-options[time_value]" id="mis_options_time_value" value="<?php echo $admin_options['time_value'] ?>">
								<select name="mis-options[time_span]" id="mis_options_time_span">
									<option <?php selected( $admin_options['time_span'], '' ) ?> value=""><?php _e( '--', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'HOUR' ) ?> value="HOUR"><?php _e( 'Hours', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'DAY' ) ?> value="DAY"><?php _e( 'Days', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'WEEK' ) ?> value="WEEK"><?php _e( 'Weeks', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'MONTH' ) ?> value="MONTH"><?php _e( 'Months', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'YEAR' ) ?> value="YEAR"><?php _e( 'Years', 'manage-inactive-subsites' ) ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="mis_options_action"><?php _e( 'Inactive mode action', 'manage-inactive-subsites' ) ?></label></th>
							<td>
								<select name="mis-options[action]" id="mis_options_action">
									<option <?php selected( $admin_options['action'], 'archive' ) ?> value="archive"><?php _e( 'Archive', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['action'], 'deactivate' ) ?> value="deactivate"><?php _e( 'Deactivate', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['action'], 'delete' ) ?> value="delete"><?php _e( 'Delete', 'manage-inactive-subsites' ) ?></option>
								</select>
							</td>
						</tr>
					</table>
					<?php wp_nonce_field( 'mis_save_options', 'mis_save_options' ) ?>
					<?php submit_button( __( 'Save Settings', 'manage-inactive-subsites' ) ) ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Save admin settings
		 */
		public function save_settings() {

			$request_data = $_POST;

			// check if save settings call
			if ( ! isset( $request_data['mis_save_options'] ) ) {
				return;
			}

			// check for nonce
			if ( ! wp_verify_nonce( $request_data['mis_save_options'], 'mis_save_options' ) ) {
				wp_die( __( 'Cheating !', 'manage-inactive-subsites' ) );
			}

			// save settings
			if ( isset( $request_data['mis-options'] ) ) {
				mis_save_options( $request_data['mis-options'] );
			}
		}

		/**
		 * Add help tab for admin settings.
		 */
		public function add_help_tab() {
			$screen = get_current_screen();
			$screen->add_help_tab( array(
				'id' => 'mis_settings_help_tab',
				'title' => __( 'Site Inactivation Overview', 'manage-inactive-subsites' ),
				'content' => '<p>' . __( 'From this screen you can set when any sub-site should go into inactivation mode.', 'manage-inactive-subsites' ) . '</p>' .
				'<p>' . __( 'If any sub-site is not active for given time span, the inactive action will be applied to that sub-site.', 'manage-inactive-subsites' ) . '</p>',
			) );
		}
	}

}
