<?php

/**
 * @link              https://joshuamckendall.github.io/commenter
 * @since             1.0.0
 * @package           Commenter
 *
 * @wordpress-plugin
 * Plugin Name:       Commenter
 * Plugin URI:        https://joshuamckendall.github.io/commenter
 * Description:       Commenter is a slightly more advanced commenting system for WordPress blogs.
 * Version:           1.0.0
 * Author:            Joshua McKendall
 * Author URI:        https://joshuamckendall.github.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       commenter
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if( ! class_exists('Commenter') ) {

	final class Commenter { 

		private static $_instance = null;

		public $_session = null;

		/**
		 * Not allowed
		 * @since 1.0.0
		 */
		public function __clone() { 
			_doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '1.0.0' ); 
		}

		/**
		 * Commenter constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks(); 
		}

		public function define_constants() {
			$this->set_define( 'COMMENTER_PATH', plugin_dir_path( __FILE__ ) );
			$this->set_define( 'COMMENTER_URI', plugin_dir_url( __FILE__ ) );
			$this->set_define( 'COMMENTER_INC', COMMENTER_PATH . 'includes/' );
			$this->set_define( 'COMMENTER_INC_URI', COMMENTER_INC . 'includes/' );
			$this->set_define( 'COMMENTER_ASSETS_URI', COMMENTER_URI . 'assets/' );
			$this->set_define( 'COMMENTER_TEMPLATES_URI', COMMENTER_INC . 'templates/' );
			$this->set_define( 'COMMENTER_VER', '1.0.0' );
			$this->set_define( 'COMMENTER_MAIN_FILE', __FILE__ );
			$this->set_define( 'COMMENTER_MODERATOR_ROLE', 'moderator' );
			$this->set_define( 'COMMENTER_PRIMARY_THREAD', 'community' );
		}

		public function set_define( $name = '', $value = '' ) {
			if ( $name && ! defined( $name ) ) { 
				define( $name, $value );
			}
		}

		public function includes() {
			$this->_include( 'includes/functions.php' );
			$this->_include( 'includes/abstracts/class-commenter-abstract-sorting.php' );
			$this->_include( 'includes/abstracts/class-commenter-abstract-thread.php' );
			$this->_include( 'includes/abstracts/class-wp-async-request.php' );
			$this->_include( 'includes/abstracts/class-wp-background-process.php' );
			$this->_include( 'includes/class-commenter-activator.php' );
			$this->_include( 'includes/class-commenter-deactivator.php' );
			$this->_include( 'includes/class-commenter-assets.php' );
			$this->_include( 'includes/class-commenter-action.php' );
			$this->_include( 'includes/class-commenter-action-handler.php' );
			$this->_include( 'includes/class-commenter-settings.php' );
			$this->_include( 'includes/class-commenter-session.php' );
			$this->_include( 'includes/class-commenter-user.php' );
			$this->_include( 'includes/class-commenter-comment-form.php' );
			$this->_include( 'includes/class-commenter-comment.php' );
			$this->_include( 'includes/class-commenter-thread.php' );
			$this->_include( 'includes/class-commenter-discussion.php' );
			$this->_include( 'includes/class-commenter-ajax.php' );	

			$this->settings = Commenter_Settings::instance();

			if ( is_admin() ) {
				$this->_include( 'includes/admin/class-commenter-admin.php' ); 
			} else {
				$this->_include( 'includes/class-commenter-frontend-assets.php' );		
			}
		}

		/**
		 * Initialize the discussion
		 *
		 * @param $file
		 */
		public function init_discussion() {

			$this->discussion = new Commenter_Discussion();

		}

		/**
		 * Include single file
		 *
		 * @param $file
		 */
		public function _include( $file = null ) {
			if ( is_array( $file ) ) {
				foreach ( $file as $key => $f ) {
					if ( file_exists( COMMENTER_PATH . $f ) ) {
						require_once COMMENTER_PATH . $f;
					}
				}
			} else {
				if ( file_exists( COMMENTER_PATH . $file ) ) {
					require_once COMMENTER_PATH . $file;
				} elseif ( file_exists( $file ) ) {
					require_once $file;
				}
			}
		}

		/**
		 * load text domain
		 * @return null
		 */
		public function text_domain() {
			// Get mo file
			$text_domain = 'commenter';
			$locale      = apply_filters( 'plugin_locale', get_locale(), $text_domain );
			$mo_file     = $text_domain . '-' . $locale . '.mo';
			// Check mo file global
			$mo_global = WP_LANG_DIR . '/plugins/' . $mo_file;
			// Load translate file
			if ( file_exists( $mo_global ) ) {
				load_textdomain( $text_domain, $mo_global );
			} else {
				load_textdomain( $text_domain, COMMENTER_PATH . '/languages/' . $mo_file );
			}
		}

		public function init_hooks() {
			register_activation_hook( __FILE__, array( 'Commenter', 'activate_commenter' ) );
			register_deactivation_hook( __FILE__, array( 'Commenter', 'deactivate_commenter' ) );

			add_action( 'wp', array( $this, 'init_discussion' ) );
			add_action( 'plugins_loaded', array( $this, 'loaded' ) );
		}

		static function activate_commenter() {
			$commenter = new Commenter_Activator;
			$commenter::activate($commenter);
		}

		static function deactivate_commenter() {
			Commenter_Deactivator::deactivate();
		}

		/**
		 * Load components when plugin loaded
		 */
		public function loaded() {
			// load text domain
			$this->text_domain();
			$this->_session = new Commenter_Session();

			do_action( 'commenter_loaded', $this );
		}

		/**
		 * get instance class
		 * @return Commenter
		 */
		public static function instance() {
			if ( ! empty( self::$_instance ) ) {
				return self::$_instance;
			}

			return self::$_instance = new self();
		}

	}

	if ( ! function_exists( 'Commenter' ) ) {

		function Commenter() {

			return Commenter::instance();
		}

	}

	add_action( 'init', 'Commenter' );
}

$GLOBALS['Commenter'] = Commenter();