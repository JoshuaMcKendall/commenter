<?php
/**
 * Commenter Newest Sorting class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * The oldest sorting class that sorts from oldest to newest comments in a thread.
 *
 *
 * @since      1.0.0
 * @package    Commenter
 * @subpackage Commenter/includes
 * @author     Joshua McKendall <commenter@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

class Commenter_Oldest_Sorting extends Commenter_Abstract_Sorting {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $config       	The connfiguration of this list.
	 */
	public function __construct( $config = array() ) {

		$defaults = apply_filters( 'commenter_abstract_sorting_args', array(

			'id'		=> 0,
			'slug'		=> 'oldest',
			'title'		=> __( 'Oldest', 'commenter' ),
			'args'		=> array( 'order' => 'ASC' ),
			'position'	=> 20,
			'enabled'	=> true,
			'query'		=> new WP_Comment_Query()

		) );

		$config = wp_parse_args( $config, $defaults );

		parent::__construct( $config );

	}

	/**
	 * Gets the number of comments before or after a comment.
	 *
	 * @since    1.0.0
	 * @param    Commenter_Comment    $comment       	The commenter comment object.
	 */
	public function get_comments_count_before_or_after( $comment ) {

		global $wpdb;

		if( ! is_array( $this->args ) )
			return false;


		$comment = commenter_get_comment( $comment );
		$current_thread = commenter_get_current_thread();
		$this->args = wp_parse_args( $this->args, $current_thread->get_args() );

		if( ! array_key_exists( 'count', $this->args ) || ! is_bool( $this->args[ 'count' ] ) ) {

			$this->args[ 'count' ] = true;

		}

		// $current_thread = commenter_get_current_thread();

		// $this->args = wp_parse_args( $this->args, $current_thread->get_args() );
		
		$this->args['fields'] = 'ids';
		$this->args['type'] = 'all';
		$this->args['post_id'] = $comment->get_post_id();
		$this->args['parent'] = 0;

		$this->args['date_query'] = array(

			array(

				'column' 			=> "$wpdb->comments.comment_date_gmt",
				'before'			=> $comment->wp_comment->comment_date_gmt

			)

		);


		$comment_count = $this->query->query( $this->args );

		return $comment_count;

	}

	/**
	 * get instance class
	 * @return Commenter_Abstract_Sorting
	 */
	public static function init( $args = array() ) {

		return new self( $args );
	}

}
