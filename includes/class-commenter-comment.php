<?php
/**
 * Commenter Comment class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * The comment class.
 *
 * This class defines all code that describes the properties and methods of a comment.
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

class Commenter_Comment {

	/**
	 * Comment ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $comment_ID;

	/**
	 * The commenter (author) of the comment.
	 *
	 * @since 1.0.0
	 * @var Comment_User
	 */
	public $commenter;


	/**
	 * The commenter (author) of the parent comment.
	 *
	 * @since 1.0.0
	 * @var Comment_User
	 */
	public $parent_commenter;

	/**
	 * The optional arguments of the comment.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $args;

	/**
	 * The depth of the comment.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $depth;

	/**
	 * The commenter (author) of the comment.
	 *
	 * @since 1.0.0
	 * @var WP_Comment
	 */
	public $wp_comment;

	/**
	 * ID of the post the comment is associated with.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $comment_post_ID = 0;

	/**
	 * Comment author name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_author = '';

	/**
	 * Comment author email address.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_author_email = '';

	/**
	 * Comment author URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_author_url = '';

	/**
	 * Comment author IP address (IPv4 format).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_author_IP = '';

	/**
	 * Comment date in YYYY-MM-DD HH:MM:SS format.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_date = '0000-00-00 00:00:00';

	/**
	 * Comment GMT date in YYYY-MM-DD HH::MM:SS format.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_date_gmt = '0000-00-00 00:00:00';

	/**
	 * Comment content.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_content;

	/**
	 * Comment karma count.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $comment_karma = 0;

	/**
	 * Comment approval status.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_approved = '1';

	/**
	 * Comment author HTTP user agent.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_agent = '';

	/**
	 * Comment type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $comment_type = '';

	/**
	 * Parent comment ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $comment_parent = 0;

	/**
	 * Comment author ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * Comment children.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $children;

	/**
	 * Whether children have been populated for this comment object.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $populated_children = false;

	/**
	 * Actions that are associated with the current comment relative to the current user.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $comment_actions;

	/**
	 * The amount of likes the comment has.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $like_count;

	/**
	 * The amount of flags the comment has.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $flag_count;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    int|object    $comment       	The comment ID or object.
	 */
	public function __construct( $comment = 0, $args = array(), $depth = 0 ) {

		if ( is_numeric( $comment ) && $comment > 0 ) {
			$comment = get_comment( $comment );
			$this->set_wp_comment( $comment );
		} elseif ( $comment instanceof self ) {
			$this->set_id( absint( $comment->get_id() ) );
		} elseif ( $comment instanceof WP_Comment && ! empty( $comment->comment_ID ) ) {
			$this->set_wp_comment( $comment );
		}

		$this->set_args( $args );
		$this->set_style();
		$this->set_depth( $depth );
		$this->set_actions();
		$this->set_like_count();
		$this->set_flag_count();

		add_filter( 'get_comment_author_url', 					array( $this->commenter, 'get_url' ), 		10, 1 );

		return $this;

	}

	/**
	 * Set id of comment in database
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->comment_ID = $id;
	}

	/**
	 * Get id of comment in database
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->comment_ID );
	}

	/**
	 * Get identifier of comment
	 *
	 * @return string
	 */
	public function get_identifier() {
		return 'comment-' . $this->get_id();
	}

	/**
	 * Set the WP_Comment
	 *
	 * @return int
	 */
	public function set_wp_comment( $wp_comment ) {
		
		if( $wp_comment instanceof WP_Comment ) {

			$this->wp_comment = $wp_comment;
			$this->set_id( $wp_comment->comment_ID );
			$this->set_commenter( $wp_comment );
			$this->set_post_id( $wp_comment->comment_post_ID );

			if( ! empty( $wp_comment->comment_parent ) ) {

				$this->comment_parent = $wp_comment->comment_parent;
				$parent_comment = get_comment( $wp_comment->comment_parent );

				$this->set_parent_commenter( $parent_comment );


			}



			$GLOBALS['comment'] = $this->wp_comment;

		}

	}

	/**
	 * Set the args of the comment
	 *
	 * @param array $args
	 */
	public function set_args( $args = array() ) {

		if( ! is_array( $args ) )
			$args = array();

		$this->args = $args;

	}

	/**
	 * Set the style of the comment tag
	 *
	 * @param array $args
	 */
	public function set_style() {

		$style = 'li';

		if( is_array( $this->args ) && array_key_exists( 'style', $this->args ) )
			$style = ( 'div' == $this->args['style'] ) ? 'div' : 'li';

		$this->style = $style;

	}

	/**
	 * Set the actions that are associated with the current comment relative to the current user
	 *
	 * @param int $depth
	 */
	public function set_depth( $depth ) {

		if( ! ( (int) $depth > 0 ) ) {

			$depth = 0;
			$current_comment_id = $this->get_id();

			while( $current_comment_id > 0  ) { 

				$comment = get_comment( $current_comment_id );
				$current_comment_id = $comment->comment_parent;
				$depth++;

			}

		}

		$this->depth = $depth;

	}

	/**
	 * Set the actions that are associated with the current comment relative to the current user
	 *
	 * @return int
	 */
	public function set_actions() {

		$current_commenter = commenter_get_current_user();
		$current_commenter_actions = $current_commenter->get_actions( 'GET' );
		$comment_actions = array();

		$enabled_comment_actions = apply_filters( 'commenter_comment_enabled_actions', array(

			'reply'			=> $this->reply_is_enabled(),
			'like'			=> $this->like_is_enabled(),
			'unlike'		=> $this->unlike_is_enabled(),
			'flag'			=> $this->flag_is_enabled(),
			'unflag'		=> $this->unflag_is_enabled(),
			'edit'			=> $this->edit_is_enabled(),
			'delete'		=> $this->delete_is_enabled(),
			'approve'		=> $this->approve_is_enabled(),
			'unapprove'		=> $this->unapprove_is_enabled(),
			'spam'			=> $this->spam_is_enabled(),
			'permalink'		=> $this->permalink_is_enabled()

		), $this );

		foreach( $enabled_comment_actions as $slug => $is_enabled ) {
			
			if( ! array_key_exists( $slug, $current_commenter_actions ) )
				continue;

			if( is_bool( $is_enabled ) && ! $is_enabled )
				continue;

			if( is_callable( $is_enabled ) && ! call_user_func_array( $is_enabled, array( $this ) ) )
				continue;

			$comment_actions[ $slug ] = $current_commenter_actions[ $slug ];

		}

		foreach ( $comment_actions as $slug => $comment_action ) {
			
			if( ! $comment_action instanceof Commenter_Action )
				continue;

			$comment_action->set_cid( $this->get_id() );

		}

		$this->comment_actions = apply_filters( 'commenter_comment_actions', $comment_actions );

	}

	/**
	 * Sets the like count of the current comment
	 */
	public function set_like_count() {

		$like_count = absint( get_comment_meta( $this->get_id(), 'commenter_like_count', true ) );

		if( ! empty( $like_count ) && is_numeric( $like_count ) ) {

			$this->like_count = $like_count;

		} else {

			$this->like_count = 0;

		}
		

	}

	/**
	 * Sets the like count of the current comment
	 */
	public function set_flag_count() {

		$flag_count = absint( get_comment_meta( $this->get_id(), 'commenter_flag_count', true ) );

		if( ! empty( $flag_count ) && is_numeric( $flag_count ) ) {

			$this->flag_count = $flag_count;

		} else {

			$this->flag_count = 0;

		}
		

	}

	/**
	 * Get the actions that are associated with the current comment relative to the current user
	 *
	 * @return int
	 */
	public function get_actions() {

		return ( isset( $this->comment_actions ) ) ? $this->comment_actions : null;

	}

	/**
	 * Checks whether the comment has a certain action.
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	public function has_action( $action ) {

		$actions = $this->get_actions();

		if( ! array_key_exists( $action, $actions ) ) {

			return false;

		}

		return true;

	}

	/**
	 * Get the action urls for the current comment.
	 *
	 * @return string
	 */
	public function get_action_url( $action, $args = array() ) {

		if( ! $this->has_action( $action ) )
			return $this->get_link();

		return $this->comment_actions[ $action ]->get_url( $this, $args );

	}

	/**
	 * Get the action items for the action menu
	 *
	 * @return int
	 */
	public function get_action_items() {

		$action_items = array();
		$allowed_action_items = apply_filters( 'commenter_allowed_action_items', array(

			'approve',
			'unapprove',
			'spam',
			'edit',
			'delete',
			'flag',
			'unflag',
			'permalink'

		) );

		foreach( $allowed_action_items as $slug ) {
			
			if( ! array_key_exists( $slug, $this->comment_actions ) ) {
				continue;
			}

			$action_items[ $slug ] = $this->comment_actions[ $slug ];

		}

		return apply_filters( 'commenter_action_items', $action_items, $allowed_action_items );

	}

	/**
	 * Get the action urls for the current comment.
	 *
	 * @return string
	 */
	public function get_action( $action, $args = array() ) {

		if( ! $this->has_action( $action ) )
			return false;

		return $this->comment_actions[ $action ];

	}

	/**
	 * Set the post id the comment was posted on
	 *
	 * @param int  $post_id
	 */
	public function set_post_id( $post_id ) {

		if( is_numeric( $post_id ) ) {

			$this->comment_post_ID = absint( $post_id );

		}
		
	}

	/**
	 * Get the post id the comment was posted on
	 *
	 * @return int  $post_id
	 */
	public function get_post_id() {
		
		return apply_filters( 'commenter_comment_get_comment_post_ID', $this->comment_post_ID, $this );

	}

	/**
	 * Get optional args that are passed to the comment from wp_list_comments
	 *
	 * @return array
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Get depth of comment in database
	 *
	 * @return int
	 */
	public function get_depth() {
		return $this->depth;
	}

	/**
	 * Get the comment parent id
	 *
	 * @return int
	 */
	public function get_comment_parent_id() {
		return $this->comment_parent;
	}

	/**
	 * Get the author of this comment
	 *
	 * @return Commenter_User
	 */
	public function set_commenter( $wp_comment ) {
		
		if( $wp_comment instanceof WP_Comment ) {

			if( $wp_comment->user_id > 0 ) {

				$this->commenter = new Commenter_User( $wp_comment->user_id );

			} else {

				$commenter = array (
			        'display_name'       	=> $wp_comment->comment_author,
			        'email' 				=> $wp_comment->comment_author_email,
			        'url'   				=> $wp_comment->comment_author_url
		    	);

				$this->commenter = new Commenter_User( $commenter );

			}

		}

	}

	/**
	 * Get the author of this comment
	 *
	 * @return Commenter_User
	 */
	public function get_commenter() {
		
		return $this->commenter;

	}

	/**
	 * Get the author of this comment's parent
	 *
	 * @return Commenter_User
	 */
	public function set_parent_commenter( $wp_comment ) {
		
		if( $wp_comment instanceof WP_Comment ) {

			if( $wp_comment->user_id > 0 ) {

				$this->parent_commenter = new Commenter_User( $wp_comment->user_id );


			} else {

				$commenter = array (
			        'display_name'       	=> $wp_comment->comment_author,
			        'email' 				=> $wp_comment->comment_author_email,
			        'url'   				=> $wp_comment->comment_author_url
		    	);

				$this->parent_commenter = new Commenter_User( $commenter );

			}

		}

	}

	/**
	 * Checks whether comment has parent
	 *
	 * @return bool
	 */
	public function has_parent() {
		return $this->wp_comment->comment_parent ? true : false;
	}

	/**
	 * Gets the like count of this comment
	 *
	 * @return int
	 */
	public function get_like_count() {

		if( ! is_numeric( $this->like_count ) )
			return 0;

		$like_count = $this->like_count;

		return apply_filters( 'commenter_comment_like_count', absint( $like_count ) );

	}

	/**
	 * Gets the like count of this comment
	 *
	 * @return int
	 */
	public function get_flag_count() {

		if( ! is_numeric( $this->flag_count ) )
			return 0;

		$flag_count = $this->flag_count;

		return apply_filters( 'commenter_comment_flag_count', absint( $flag_count ) );

	}

	/**
	 * Gets the content of this comment
	 *
	 * @param    array    	   $args       		comment args.
	 *
	 * @return string
	 */
	public function get_content( $args = array() ) {
		return apply_filters( 'get_comment_text', $this->wp_comment->comment_content, $this->wp_comment, $args );
	}


	/**
	 * The reply action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function reply_is_enabled( $args = array() ) {

		if ( ! comments_open( $this->comment_post_ID ) ) {
			return false;
		}	

		if( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			return false;
		}

		return true;
	}

	/**
	 * The like action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function like_is_enabled( $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( commenter_current_user_liked( $this ) ) {
			return false;
		}

		return true;

	}

	/**
	 * The unlike action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function unlike_is_enabled( $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( ! commenter_current_user_liked( $this ) ) {
			return false;
		}

		return true;

	}

	/**
	 * The flag action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function flag_is_enabled( $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if( $this->commenter->get_id() == get_current_user_id() ) {
			return false;
		}

		if( commenter_current_user_flagged( $this ) ) {
			return false;
		}

		return true;

	}

	/**
	 * The unflag action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function unflag_is_enabled( $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if( $this->commenter->get_id() == get_current_user_id() ) {
			return false;
		}

		if( ! commenter_current_user_flagged( $this ) ) {
			return false;
		}

		return true;

	}

	/**
	 * The edit action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function edit_is_enabled( $args = array() ) {
		return false;
	}

	/**
	 * The delete action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function delete_is_enabled( $args = array() ) {
		return false;
	}

	/**
	 * The approve action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function approve_is_enabled( $args = array() ) {
		return false;
	}

	/**
	 * The unapprove action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function unapprove_is_enabled( $args = array() ) {
		return false;
	}

	/**
	 * The spam action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function spam_is_enabled( $args = array() ) {
		return false;
	}

	/**
	 * The permalink action is enabled.
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public function permalink_is_enabled( $args = array() ) {
		return true;
	}

	/**
	 * Gets the author of this comment
	 *
	 * @return string
	 */
	public function get_author() {
		$author = $this->get_commenter();

		return $author->get_display_name();
	}

	/**
	 * Gets the date of this comment
	 *
	 * @return string
	 */
	public function get_date( $format = 'c' ) {
		return get_comment_date( $format, $this->comment_ID );
	}

	/**
	 * Gets the time of this comment
	 *
	 * @return string
	 */
	public function get_time( $format = 'U', $gmt = false, $translate = true ) {
		return human_time_diff( get_comment_time( $format, $gmt, $translate ), current_time( 'timestamp' ) );
	}

	/**
	 * Gets the link of this comment
	 *
	 * @param    array    $args       	The args to pass to the action.
	 *
	 * @return string
	 */
	public function get_link( $args = array() ) {

		if( $this->has_action( 'permalink' ) )
			return $this->get_action_url( 'permalink', $args );

		return '#comment-' . $this->get_id();
	}

	/**
	 * Gets the data of this comment
	 *
	 * @return string
	 */
	public function get_data( $data = array(), $format = 'json' ) {
		
		$defaults = apply_filters( 'commenter_comment_get_data_defaults', array(

			'id'			=> $this->get_id(),
			'parent'		=> $this->get_comment_parent_id(),
			'identifier'	=> $this->get_identifier(),
			'date'			=> $this->get_date(),
			'time'			=> $this->get_time(),
			'author'		=> $this->get_author(),
			'link'			=> $this->get_link(),
			'likes'			=> $this->get_like_count()

		) );

		$data = wp_parse_args( $data, $defaults );

		switch ( $format ) {
			case 'array':
				return $data;
				break;

			case 'json':
				$data = json_encode( $data );
				break;
			
			default:
				return json_encode( $data );
				break;
		}

		return $data;

	}

	/**
	 * Renders the comment header.
	 *
	 * @since    1.0.0
	 *
	 * @param    int|object    $comment       	The comment ID or object.
	 * @param    array    	   $args       		comment args.
	 * @param    int    	   $depth       	The depth of the comment.
	 *
	 */
	public function render_comment_header( $comment, $args, $depth, $thread ) {

		$comment_header_template = commenter_get_template( 'comment/comment-header.php', array( 'comment' => $comment, 'args' => $args, 'depth' => $depth, 'thread' => $thread ) );

		include apply_filters( 'commenter_comment_header_template', $comment_header_template, $comment, $args, $depth );

	}

	/**
	 * Renders the comment content.
	 *
	 * @since    1.0.0
	 *
	 * @param    int|object    $comment       	The comment ID or object.
	 * @param    array    	   $args       		comment args.
	 * @param    int    	   $depth       	The depth of the comment.
	 *
	 */
	public function render_comment_content( $comment, $args, $depth, $thread ) {

		$comment_content_template = commenter_get_template( 'comment/comment-content.php', array( 'comment' => $comment, 'args' => $args, 'depth' => $depth, 'thread' => $thread ) );

		include apply_filters( 'commenter_comment_content_template', $comment_content_template, $comment, $args, $depth );

	}

	/**
	 * Renders the comment footer.
	 *
	 * @since    1.0.0
	 *
	 * @param    int|object    $comment       	The comment ID or object.
	 * @param    array    	   $args       		comment args.
	 * @param    int    	   $depth       	The depth of the comment.
	 *
	 */
	public function render_comment_footer( $comment, $args, $depth, $thread ) {

		$comment_footer_template = commenter_get_template( 'comment/comment-footer.php', array( 'comment' => $comment, 'args' => $args, 'depth' => $depth, 'thread' => $thread ) );

		include apply_filters( 'commenter_comment_footer_template', $comment_footer_template, $comment, $args, $depth );

	}

	/**
	 * Renders the comment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int|object    $comment       	The comment ID or object.
	 * @param    array    	   $args       		comment args.
	 * @param    int    	   $depth       	The depth of the comment.
	 *
	 */
	public function render( $echo = true ) {

		$comment = $this;
		$args 	 = $this->get_args();
		$depth 	 = $this->get_depth();

		if( (bool) $echo ) {

			$comment_template = commenter_get_template( 'comment.php', array( 

				'comment' 	=> $comment, 
				'args' 		=> $args, 
				'depth' 	=> $depth

			) );

			add_action( 'commenter_comment_' . $comment->get_id(), 	array( $comment, 'render_comment_header'  ), 	10, 4 );
			add_action( 'commenter_comment_' . $comment->get_id(), 	array( $comment, 'render_comment_content' ), 	20, 4 );
			add_action( 'commenter_comment_' . $comment->get_id(), 	array( $comment, 'render_comment_footer'  ), 	30, 4 );

			include apply_filters( 'commenter_comment_template', $comment_template, $comment, $args, $depth );			

		} else {

			ob_start();

			$comment_template = commenter_get_template( 'comment.php', array( 

				'comment' 	=> $comment, 
				'args' 		=> $args, 
				'depth' 	=> $depth

			) );

			add_action( 'commenter_comment_' . $comment->get_id(), 	array( $comment, 'render_comment_header'  ), 	10, 4 );
			add_action( 'commenter_comment_' . $comment->get_id(), 	array( $comment, 'render_comment_content' ), 	20, 4 );
			add_action( 'commenter_comment_' . $comment->get_id(), 	array( $comment, 'render_comment_footer'  ), 	30, 4 );

			include apply_filters( 'commenter_comment_template', $comment_template, $comment, $args, $depth );

			return ob_get_clean();

		}


	}

}
