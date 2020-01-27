<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter/includes/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Commenter
 * @subpackage Commenter/includes
 * @author     Joshua McKendall <commenter@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class Commenter_Activator {


	public $commenter_db_version = '1.0.0';


	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public static function activate( Commenter_Activator $commenter ) {
		
		$commenter->create_tables();
		$commenter->create_options();
		
	}

	private function create_tables() {

		global $wpdb;

		$flags_table = $wpdb->prefix . 'commenter_flags';
		$likes_table = $wpdb->prefix . 'commenter_likes';

		$this->create_table( $flags_table );
		$this->create_table( $likes_table );

	}

	private function create_table( $table_name ) {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		  comment_id bigint(20) UNSIGNED NOT NULL,
		  user_id bigint(20) UNSIGNED NOT NULL,
		  blog_id bigint(20) UNSIGNED NOT NULL,
		  commenter_id bigint(20) UNSIGNED,
		  created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		  PRIMARY KEY  (comment_id,user_id,blog_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

	}
	
	private function create_options() {
	
		add_option( 'commenter_db_version', $this->commenter_db_version );

	}
	

}