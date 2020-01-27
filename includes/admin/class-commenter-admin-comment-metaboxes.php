<?php

/**
 * The class that adds the comment edit screen metaboxes.
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/admin/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/admin
 */

/**
 * The class that adds the comment edit screen metaboxes.
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

class Commenter_Admin_Comment_Metaboxes { 

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {

		/**
		 * Fires when Commenter admin edit screen has been initialized.
		 *
		 * @since 1.0.0
		 *
		 * @param Commenter_Admin object.
		 */
		do_action( 'commenter_admin_comment_metaboxes_init', $this );

		add_action( 'admin_init',		array( $this, 'init' )			 			);

	}

	public function init() {

		if( isset( $_GET['c'] ) && isset( $_GET['action'] ) ) {

			if( $_GET['action'] == 'editcomment' && is_numeric( $_GET['c'] ) ) {

				add_meta_box(

					'commenter_likes_metabox', 
					'<span class="dashicons dashicons-heart"></span> ' . __( 'Likes', 'commenter' ), 
					array( $this, 'render_commenter_likes_metabox' ), 
					'comment', 
					'normal' 
				);

			}

		}

	}

	public function render_commenter_likes_metabox( $comment ) {

		global $wpdb;

		$likes_table = $wpdb->base_prefix . 'commenter_likes';
		$comment_id = $comment->comment_ID;
		$blog_id = get_current_blog_id();
		$likes = $wpdb->get_results( $wpdb->prepare(

			"
				SELECT *
					FROM   $likes_table
			 		WHERE 	comment_id = %d
			 		AND blog_id = %d
			",
			$comment_id,
			$blog_id

		) );

		include_once COMMENTER_INC . 'admin/views/metaboxes/commenter-likes-metabox.php';

	}

}