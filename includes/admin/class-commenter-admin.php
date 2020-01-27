<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/admin/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Commenter
 * @subpackage Commenter/admin
 * @author     Joshua McKendall <commenter@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class Commenter_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( ) {

		/**
		 * Fires when Commenter admin has been initialized.
		 *
		 * @since 1.0.0
		 *
		 * @param Commenter_Admin object.
		 */
		do_action( 'commenter_admin_init', $this );

		$this->_includes();

		add_action( 'init', array( $this, 'init' ) );
		//add_action( 'admin_init',		array( $this, 'init_discussion' ), 10 );

	}

	private function _includes() {

		include( COMMENTER_PATH . 'includes/admin/class-commenter-admin-discussion-settings.php' );
		include( COMMENTER_PATH . 'includes/admin/class-commenter-admin-comment-metaboxes.php' );
		include( COMMENTER_PATH . 'includes/admin/class-commenter-admin-comment-columns.php' );

	}

	public function init() {

		if( ! is_admin() )
			return;

		new Commenter_Admin_Discussion_Settings();
		new Commenter_Admin_Comment_Metaboxes();
		new Commenter_Admin_Comment_Columns();	

	}

	public function init_discussion() {

		$discussion = new Commenter_Discussion();

	}

}

new Commenter_Admin();