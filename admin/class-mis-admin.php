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
				add_filter( 'network_admin_plugin_action_links_' . MIS_BASE_NAME, array( $this, 'add_action_links' ), 10, 4 );
			} else {

				// admin notice if not multisite installation and if current user is admin
				if ( current_user_can( 'manage_options' ) ) {
					add_action( 'admin_notices', array( $this, 'not_mu_admin_notice' ) );
				}
			}
		}

		/**
		 * Add settings link in plugin action
		 */
		public function add_action_links( $actions, $plugin_file, $plugin_data, $context ) {

			$plugin_links = array(
				'<a href="' . network_admin_url( 'settings.php?page=manage-inactive-subsites' ) . '">' . esc_html__( 'Settings', 'manage-inactive-subsites' ) . '</a>',
			);

			return array_merge( $actions, $plugin_links );
		}

		/**
		 * Admin notice if simple WordPress installation
		 */
		public function not_mu_admin_notice() {

			// prepare deactivate plugin link
			$plugin_file = MIS_BASE_NAME;
			$deactivate_link = '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ) . '" aria-label="' . esc_attr( sprintf( esc_html__( 'Deactivate %s' ), 'Manage Inactive Subsites' ) ) . '">' . esc_html__( 'Deactivate', 'manage-inactive-subsites' ) . '</a>';
			?>
			<div class="error">
				<p><?php echo esc_html__( 'This is in not WordPress Multisite installation and hence Manage Inactive Subsites plugin is not needed. Please', 'manage-inactive-subsites' ) . ' ' . $deactivate_link . ' ' . esc_html__( 'Manage Inactive Subsites plugin.', 'manage-inactive-subsites' ) ?></p>
			</div>
			<?php
		}

		/**
		 * Manage network admin section
		 */
		public function add_network_settings() {
			$menu_page = add_submenu_page( 'settings.php', esc_html__( 'Site Inactivation', 'manage-inactive-subsites' ), esc_html__( 'Site Inactivation', 'manage-inactive-subsites' ), 'manage_sites', 'manage-inactive-subsites', array( $this, 'admin_settings_content' ) );
			add_action( 'load-' . $menu_page, array( $this, 'add_help_tab' ) );
		}

		/**
		 * Network admin settings content
		 */
		public function admin_settings_content() {
			$admin_options = mis_get_options();
			?>
			<div class="wrap mis-admin">
				<h2><?php esc_html_e( 'Manage Site Inactivation', 'manage-inactive-subsites' ) ?></h2>
				<form method="post" action="">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="mis_options_time_value"><?php esc_html_e( 'Inactive Duration', 'manage-inactive-subsites' ) ?></label></th>
							<td>
								<input type="number" min="0" class="small-text" name="mis-options[time_value]" id="mis_options_time_value" value="<?php echo esc_attr( $admin_options['time_value'] ) ?>">
								<select name="mis-options[time_span]" id="mis_options_time_span">
									<option <?php selected( $admin_options['time_span'], '' ) ?> value=""><?php esc_html_e( '--', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'HOUR' ) ?> value="HOUR"><?php esc_html_e( 'Hours', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'DAY' ) ?> value="DAY"><?php esc_html_e( 'Days', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'WEEK' ) ?> value="WEEK"><?php esc_html_e( 'Weeks', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'MONTH' ) ?> value="MONTH"><?php esc_html_e( 'Months', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['time_span'], 'YEAR' ) ?> value="YEAR"><?php esc_html_e( 'Years', 'manage-inactive-subsites' ) ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Select a schedule of how long the site will be inactive before taking action.', 'manage-inactive-subsites' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="mis_options_action"><?php esc_html_e( 'Inactive Action', 'manage-inactive-subsites' ) ?></label></th>
							<td>
								<select name="mis-options[action]" id="mis_options_action">
									<option <?php selected( $admin_options['action'], 'archive' ) ?> value="archive"><?php esc_html_e( 'Archive', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['action'], 'deactivate' ) ?> value="deactivate"><?php esc_html_e( 'Deactivate', 'manage-inactive-subsites' ) ?></option>
									<option <?php selected( $admin_options['action'], 'delete' ) ?> value="delete"><?php esc_html_e( 'Delete', 'manage-inactive-subsites' ) ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Select the action to perform on expire.', 'manage-inactive-subsites' ) ?></p>
							</td>
						</tr>
					</table>
					<?php wp_nonce_field( 'mis_save_options', 'mis_save_options' ) ?>
					<?php submit_button( esc_html__( 'Save Settings', 'manage-inactive-subsites' ) ) ?>
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
				wp_die( esc_html__( 'Cheating !', 'manage-inactive-subsites' ) );
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
				'id' => 'mis_overview_help_tab',
				'title' => esc_html__( 'Site Inactivation Overview', 'manage-inactive-subsites' ),
				'content' => '<p>' . esc_html__( 'From this screen you can set when any sub-site should go into inactivation mode.', 'manage-inactive-subsites' ) . '</p>' .
				'<p>' . esc_html__( 'If any sub-site is not active for given time span, the inactive action will be applied to that sub-site.', 'manage-inactive-subsites' ) . '</p>',
			) );
		}
	}

}
