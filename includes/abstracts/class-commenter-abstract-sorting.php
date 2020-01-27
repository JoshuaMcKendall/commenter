<?php
/**
 * Commenter Abstract Sorting class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * The abstract sorting class that forms the basis for all sortings.
 *
 * This class defines all code that is the basis for all sortings of the main discussion.
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

class Commenter_Abstract_Sorting {

	public $id;

	public $slug;

	public $title;

	public $url;

	public $args;

	public $position;

	public $enabled;

	public $query;

	public $is_current_sorting;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $config       	The connfiguration of this list.
	 */
	public function __construct( $config = array() ) {

		$defaults = apply_filters( 'commenter_abstract_sorting_args', array(

			'id'					=> 0,
			'slug'					=> '',
			'title'					=> '',
			'args'					=> array(),
			'position'				=> 10,
			'enabled'				=> true,
			'is_current_sorting'	=> false,
			'query'					=> new WP_Comment_Query()

		) );

		$config = wp_parse_args( $config, $defaults );

		foreach ( $config as $key => $value ) {
			
			$this->$key = $value;

		}

	}

	public function get_id() {

		if( ! is_int( $this->id ) )
			return 0;

		return $this->id;

	}

	/**
	 * Gets the title for the sorting.
	 *
	 * @since    1.0.0
	 */
	public function get_title() {

		if( ! is_string( $this->title ) )
			return '';

		return apply_filters( 'commenter_sorting_get_title', $this->title );

	}

	/**
	 * Gets the args for the sorting.
	 *
	 * @since    1.0.0
	 */
	public function get_args() {

		return apply_filters( 'commenter_sorting_get_args', $this->args );

	}

	/**
	 * Gets the args for the sorting.
	 *
	 * @since    1.0.0
	 */
	public function get_slug() {

		if( ! is_string( $this->slug ) )
			return '';

		return apply_filters( 'commenter_abstract_sorting_slug', $this->slug );

	}

	/**
	 * Gets the url for the sorting.
	 *
	 * @since    1.0.0
	 */
	public function get_url() {

		$url = add_query_arg( array(

			'csort'		=> $this->get_slug(),
			'cthread'	=> commenter_get_cthread_value()

		), get_the_permalink( $this->get_id() ) );

		return apply_filters( 'commenter_abstract_sorting_url', $url . '#comments' );

	}

	/**
	 * Gets the number of comments before or after a comment.
	 *
	 * @since    1.0.0
	 * @param    Commenter_Comment    $comment       	The commenter comment object.
	 */
	public function get_comments_count_before_or_after( $comment ) {

		return 0;

	}

	/**
	 * Gets the page that the comment currently resides on.
	 *
	 * @since    1.0.0
	 * @param    Commenter_Comment    $comment       	The commenter comment object.
	 */
	public function  get_page_of_comment( $comment, $args = array() ) {

		if ( is_numeric( $comment ) && $comment > 0 ) {
			$comment = commenter_get_comment( $comment );
		} elseif ( $comment instanceof Commenter_Comment ) {
			$comment = $comment;
		} elseif ( $comment instanceof WP_Comment ) {
			$comment = new Commenter_Comment( $comment );
		} else {
			return 0;
		}

		$comment_ID = $comment->get_id();
		$page = null;
		$per_page = get_option( 'comments_per_page' );
		$max_depth = get_option('thread_comments_depth');

		if ( null === $page ) {
			if ( '' === $max_depth ) {
				if ( get_option('thread_comments') )
					$max_depth = get_option('thread_comments_depth');
				else
					$max_depth = -1;
			}

			// Find this comment's top level parent if threading is enabled
			if ( $max_depth > 1 && 0 != $comment->comment_parent )
				return $this->get_page_of_comment( $comment->comment_parent );

			
			$older_comment_count = $this->get_comments_count_before_or_after( $comment );

			// No older comments? Then it's page #1.
			if ( 0 == $older_comment_count ) {
				$page = 1;

			// Divide comments older than this one by comments per page to get this comment's page number
			} else {
				$page = ceil( ( $older_comment_count + 1 ) / $per_page );
			}
		}

		/**
		 * Filters the calculated page on which a comment appears.
		 *
		 * @since 4.4.0
		 * @since 4.7.0 Introduced the `$comment_ID` parameter.
		 *
		 * @param int   $page          Comment page.
		 * @param array $args {
		 *     Arguments used to calculate pagination. These include arguments auto-detected by the function,
		 *     based on query vars, system settings, etc. For pristine arguments passed to the function,
		 *     see `$original_args`.
		 *
		 *     @type string $type      Type of comments to count.
		 *     @type int    $page      Calculated current page.
		 *     @type int    $per_page  Calculated number of comments per page.
		 *     @type int    $max_depth Maximum comment threading depth allowed.
		 * }
		 * @param array $original_args {
		 *     Array of arguments passed to the function. Some or all of these may not be set.
		 *
		 *     @type string $type      Type of comments to count.
		 *     @type int    $page      Current comment page.
		 *     @type int    $per_page  Number of comments per page.
		 *     @type int    $max_depth Maximum comment threading depth allowed.
		 * }
		 * @param int $comment_ID ID of the comment.
		 */
		return apply_filters( 'commenter_sorting_get_page_of_comment', (int) $page, $args, $comment_ID );

	}

	public function set_is_current_sorting( $is_current_sorting ) {

		$this->is_current_sorting = ( is_bool( $is_current_sorting ) ) ? $is_current_sorting : false;

	}

	/**
	 * get whether the sorting is the current requested sorting
	 * @return bool
	 */
	public function get_is_current_sorting() {

		return (bool) $this->is_current_sorting;

	}

	/**
	 * get sorting instance data
	 * @return array
	 */
	public function get_data() {


		$slug 				= $this->get_slug();
		$title 				= $this->get_title();
		$is_current_sorting = $this->get_is_current_sorting();

		$data = array( 

			'slug'					=> $slug,
			'title'					=> $title,
			'is_current_sorting'	=> $is_current_sorting

		);

		return apply_filters( 'commenter_abstract_sorting_get_data', $data, $this );

	}

	/**
	 * get instance class
	 * @return Commenter_Abstract_Sorting
	 */
	public static function init( $args = array() ) {

		return new self( $args );
	}

}
