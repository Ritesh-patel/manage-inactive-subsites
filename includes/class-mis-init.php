<?php

if ( ! class_exists( 'MIS_Init' ) ) {

	/**
	 * Class MIS_Init
	 *
	 * Main class file of plugin.
	 */
	class MIS_Init {

		public function __construct() {

			// load all the dependencies
			$this->load_dependencies();

			// set locale
			$this->set_locale();

			// Init admin hooks
			$this->set_admin_hooks();

			// Init public hooks
			$this->set_public_hooks();
		}

		/**
		 * Load plugin language files
		 */
		private function set_locale() {
			load_plugin_textdomain( 'manage-inactive-subsites', false, basename( MIS_PATH ) . '/languages/' );
		}

		/**
		 * Function to get the class file name.
		 *
		 * @param $class_name
		 *
		 * @return string
		 */
		private function get_class_file_name( $class_name ) {
			return 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
		}

		/**
		 * Register autoload to load class files
		 */
		private function load_dependencies() {

			// include function file
			require_once MIS_PATH . 'helper/manage-inactive-subsites-functions.php';

			spl_autoload_register( array( $this, 'load_classes' ) );
		}

		/**
		 * Function to load class file, bound to `spl_autoload_register`
		 *
		 * @param $class_name
		 */
		private function load_classes( $class_name ) {
			$class_path = array(
				'admin/' . $this->get_class_file_name( $class_name ),
				'includes/' . $this->get_class_file_name( $class_name ),
			);

			foreach ( $class_path as $path ) {
				$path = MIS_PATH . $path;
				if ( file_exists( $path ) ) {
					include $path;
					break;
				}
			}
		}

		/**
		 * Init admin hooks
		 */
		private function set_admin_hooks() {
			$mis_admin = new MIS_Admin();
		}

		/**
		 * Init public hooks
		 */
		private function set_public_hooks() {
			$mis_sites = new MIS_Sites();
		}
	}

}
