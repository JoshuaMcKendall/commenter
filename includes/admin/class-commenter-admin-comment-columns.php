<?php

/**
 * The class that adds the the likes and flags columns to the comments table.
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/admin/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/admin
 */

/**
 * The class that adds the the likes and flags columns to the comments table.
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

class Commenter_Admin_Comment_Columns { 

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
		do_action( 'commenter_admin_comment_columns_init', $this );

		add_filter( 'manage_edit-comments_columns', 	array( $this, 'add_comments_columns' ), 		10, 2 );
		add_action( 'manage_comments_custom_column', 	array( $this, 'add_comments_columns_content' ), 10, 2 );
		add_action( 'admin_head', 						array( $this, 'add_comments_columns_css' )			  );

	}

	public function add_comments_columns( $columns ) {

		$commenter_columns = array(
			'commenter_likes' => '<span class="dashicons dashicons-heart"></span> Likes',
			'commenter_flags' => '<span class="dashicons dashicons-flag"></span> Flags'
		);
		$columns = array_slice( $columns, 0, 3, true ) + $commenter_columns + array_slice( $columns, 3, NULL, true );
	 
		return $columns;		

	}

	public function add_comments_columns_content( $column, $comment_ID ) {

		global $comment;

		$comment = commenter_get_comment( $comment );

		switch ( $column ) :
			case 'commenter_likes' : {
				echo $comment->get_like_count();
				break;
			}
			case 'commenter_flags' : {
				echo $comment->get_flag_count();
				break;
			}
		endswitch;

	}

	public function add_comments_columns_css() {

		echo '<style>#commenter_likes, #commenter_flags {width: 100px;}</style>';
		
	}

}