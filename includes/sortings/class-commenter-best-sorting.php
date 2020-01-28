<?php
/**
 * Commenter Best Sorting class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * The best sorting class that sorts the thread by most liked comments.
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

class Commenter_Best_Sorting extends Commenter_Abstract_Sorting {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $config       	The connfiguration of this list.
	 */
	public function __construct( $config = array() ) {

		$defaults = apply_filters( 'commenter_abstract_sorting_args', array(

			'id'		=> 0,
			'slug'		=> 'best',
			'title'		=> __( 'Best', 'commenter' ),
			'args'		=> array( 

					'meta_query' => array(

						'relation'					=> 'OR',
						'like_clause' 				=> array(

							'relation'				=> 'AND',
							'like_count_exists' 	=> array(

								'key'				=> 'commenter_like_count',
								'compare'			=> 'EXISTS'

							),
							'like_count_not_zero' 	=> array(

								'key'				=> 'commenter_like_count',
								'value'				=> 0,
								'compare'			=> '>'
							)

						),
						'nolike_clause' 			=> array(

								'key'				=> 'commenter_like_count',
								'compare'			=> 'NOT EXISTS'

						)

					),
					'order'		=> 'DESC',
					'orderby' 	=> array( 'nolike_clause' => 'DATE' )

			),
			'position'	=> 30,
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
		
		$comment_like_count = $comment->get_like_count();

		$this->args['fields'] = 'ids';
		$this->args['type'] = 'all';
		$this->args['post_id'] = $comment->comment_post_ID;
		$this->args['parent'] = 0;

		$this->args['meta_query'] = array(

			'relation'	=> 'AND',
			'like_clause' => array(

				'key'		=> 'commenter_like_count',
				'compare'	=> 'EXISTS'

			),
			'like_amount_clause' => array (

				'key'		=> 'commenter_like_count',
				'value'		=> $comment_like_count,
				'compare'	=> '>'

			)		

		);

		$this->args['orderby'] = array( 'like_clause' => 'DATE' );

		//The number of comments that have more likes than the current comment.
		$better_comment_count = absint( $this->query->query( $this->args ) );

		$this->args['date_query'] = array(

			array(

				'column' 		=> "$wpdb->comments.comment_date_gmt",
				'after'			=> $comment->wp_comment->comment_date_gmt //FIX COMMENTER COMMENT AND MAKE IT SO DATE IS PART OF THE COMMENTER COMMENT OBJECT

			)

		);

		$this->args['meta_query'] = array(

			'relation'	=> 'AND',
			'like_clause' => array(

				'key'		=> 'commenter_like_count',
				'compare'	=> 'EXISTS'

			),
			'like_amount_clause' => array (

				'key'		=> 'commenter_like_count',
				'value'		=> $comment_like_count,
				'compare'	=> '='

			)		

		);

		if( $comment_like_count == 0 ) {

			$this->args['meta_query'] = array(

				'relation'	=> 'OR',
				'like_clause' => array(

					'key'		=> 'commenter_like_count',
					'compare'	=> 'NOT EXISTS'

				),
				'nolike_clause' => array (

					'key'		=> 'commenter_like_count',
					'value'		=> 0,
					'compare'	=> '='

				)
			);

		}

		// The count of comments that were made after ( newer comments ) the current comment was posted with the same amount of likes.
		$after_comment_count = absint( $this->query->query( $this->args ) );

		$comment_count = $better_comment_count + $after_comment_count;

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
