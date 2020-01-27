<?php

/**
 * The commenter user class that extends WP_User and applies all the commenter specific methods and properties.
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter/includes
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * The commenter user class.
 *
 * The commenter user class that extends WP_User and applies all the commenter specific methods and properties.
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

class Commenter_User {

	/**
	 * @var int
	 */
	protected $_id = 0;

	/**
	 * @var WP_User object
	 */
	public $user = false;

	/**
	 * @var array
	 */
	protected $_actions = array();

	/**
	 * @var array
	 */
	protected $_capabilities = array();

	/**
	 * @var array
	 */
	protected $_data = array(
		'url'			=> '',
		'email'         => '',
		'user_login'    => '',
		'description'   => '',
		'first_name'    => '',
		'last_name'     => '',
		'nickname'      => '',
		'display_name'  => '',
		'date_created'  => '',
		'date_modified' => '',
		'role'          => '',
		'roles'         => array()
	);

	public function __construct( $user = 0, $args = array() ) {

		if ( is_numeric( $user ) && $user > 0 ) {
			$this->set_id( $user );
		} elseif ( $user instanceof self ) {
			$this->set_id( absint( $user->get_id() ) );
		} elseif ( ! empty( $user->ID ) ) {
			$this->set_id( absint( $user->ID ) );
		} elseif ( is_array( $user ) ) {
			$this->set_data( $user );
		}

		$this->load();

	}

	/**
	 * Load user data
	 *
	 * @return mixed;
	 */
	public function load() {

		$user_id = $this->get_id();

		if ( false !== ( $user_object = get_user_by( 'id', $user_id ) ) ) {

			$this->set_data(

				array(
					'url'				=> $user_object->user_url,
					'email'             => $user_object->user_email,
					'user_login'        => $user_object->user_login,
					'description'       => $user_object->description,
					'first_name'        => isset( $user_object->first_name ) ? $user_object->first_name : '',
					'last_name'         => isset( $user_object->last_name ) ? $user_object->last_name : '',
					'nickname'          => isset( $user_object->nickname ) ? $user_object->nickname : '',
					'display_name'      => $user_object->display_name,
					'date_created'      => $user_object->user_registered,
					'date_modified'     => get_user_meta( $user_id, 'last_update', true ),
					'role'              => ! empty( $user_object->roles[0] ) ? $user_object->roles[0] : 'subscriber',
					'roles'             => ! empty( $user_object->roles ) ? $user_object->roles : array( 'subscriber' ),

				)

			);

			$this->user = $user_object;

		}

		$this->set_actions();

		return true;
	}

	/**
	 * Magic function to get user data
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function __get( $key ) {
		$return = false;

		if ( ! empty( $this->user->data->{$key} ) ) {
			$data = $this->user->data->{$key};
		} else {
			if ( isset( $this->{$key} ) ) {
				$data = $this->{$key};
			} elseif ( strpos( $key, '_commenter_' ) === false ) {
				$key    = '_commenter_' . $key;
				$data = get_user_meta( $this->get_id(), $key, true );
				if ( ! empty( $value ) ) {
					$this->$key = $data;
				}
			}
		}

		return $data;
	}

	/**
	 * Set id of user in database
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->_id = $id;
	}

	/**
	 * Get id of user in database
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->_id );
	}

	/**
	 * Set object data.
	 *
	 * @param mixed $key_or_data
	 * @param mixed $value
	 * @param bool  $extra
	 */
	public function set_data( $key_or_data, $value = '' ) {
		if ( is_array( $key_or_data ) ) {
			foreach ( $key_or_data as $key => $value ) {
				$this->set_data( $key, $value );
			}
		} elseif ( $key_or_data ) {
			$data    = $this->_data;
			$changes = $this->_changes;

			try {
				if ( ! is_string( $key_or_data ) && ! is_numeric( $key_or_data ) ) {
					throw new Exception( 'error' );
				}
				// Only change the data if it already exists
				if ( array_key_exists( $key_or_data, $this->_data ) ) {
					$this->_data[ $key_or_data ] = $value;
				}

			}
			catch ( Exception $ex ) {
				print_r( $key_or_data );
				print_r( $ex->getMessage() );
				die(__FILE__ . '::'.__FUNCTION__);;
			}
			
		}
	}

	/**
	 * Get object data
	 *
	 * @param string $name - Optional. Name of data want to get, true if return all.
	 * @param mixed  $default
	 *
	 * @return array|mixed
	 */
	public function get_data( $name = '', $default = '' ) {
		if ( is_string( $name ) ) {
			// Check in data first then check in extra data
			return array_key_exists( $name, $this->_data ) ? $this->_data[ $name ] : $default;

		} elseif ( is_array( $name ) ) {
			$data = array();
			foreach ( $name as $key ) {
				$data[ $key ] = $this->get_data( $key, $default );
			}

			return $data;
		} elseif ( true === $name ) {
			return $this->_data;
		}

		return false;
	}

	/**
	 * Check whether commenter has specified data
	 *
	 * @param string $name - Optional. Name of data want to get, true if return all.
	 * @param mixed  $default
	 *
	 * @return bool
	 */
	public function has( $name, $default = '' ) {

		if ( is_string( $name ) && ! empty( $this->get_data( $name, $default ) ) ) {

			return true;

		}

		return false;

	}


	/**
	 * Set the actions and capabilities for the user.
	 */
	public function set_actions() {

		$actions = apply_filters( 'commenter_user_actions', array(

			'POST' 			=> array(

				'comment'		=> array(
					'slug'				=> 'comment',
					'title'				=> __( 'Comment', 'commenter' ),
					'user_can'			=> array( $this, 'can_comment' ),
					'callback'			=> array( $this, 'comment' ),
					'method'			=> 'POST',
					'must_login'		=> ( 1 == get_option('comment_registration') ),
					'has_nonce'			=> false,
					'has_ajax'			=> true,
					'enabled'			=> true
				),
				'reply'			=> array(
					'slug'				=> 'reply',
					'title'				=> __( 'Reply', 'commenter' ),
					'user_can'			=> array( $this, 'can_comment' ),
					'callback'			=> array( $this, 'comment' ),
					'method'			=> 'POST',
					'must_login'		=> ( 1 == get_option('comment_registration') ),
					'has_nonce'			=> false,
					'has_ajax'			=> true,
					'enabled'			=> true

				),
				'edit'		=> array(
					'slug'				=> 'edit',
					'title'				=> __( 'Edit', 'commenter' ),
					'user_can'			=> array( $this, 'can_update' ),
					'callback'			=> array( $this, 'update' ),
					'method'			=> 'POST',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'has_ajax'			=> false,
					'enabled'			=> false
				),

			),
			'GET'			=> array(

				'reply'			=> array(
					'slug'				=> 'reply',
					'title'				=> __( 'Reply', 'commenter' ),
					'user_can'			=> array( $this, 'can_reply' ),
					'callback'			=> array( $this, 'reply' ),
					'method'			=> 'GET',
					'must_login'		=> ( 1 == get_option('comment_registration') ),
					'has_nonce'			=> false,
					'enabled'			=> true

				),
				'like'			=> array(
					'slug'				=> 'like',
					'title'				=> __( 'Like', 'commenter' ),
					'user_can'			=> array( $this, 'can_like' ),
					'callback'			=> array( $this, 'like' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'has_ajax'			=> true,
					'enabled'			=> true

				),
				'unlike'		=> array(
					'slug'				=> 'unlike',
					'title'				=> __( 'Unlike', 'commenter' ),
					'user_can'			=> array( $this, 'can_unlike' ),
					'callback'			=> array( $this, 'unlike' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'has_ajax'			=> true,
					'enabled'			=> true

				),
				'flag'			=> array(
					'slug'				=> 'flag',
					'title'				=> __( 'Flag', 'commenter' ),
					'user_can'			=> array( $this, 'can_flag' ),
					'callback'			=> array( $this, 'flag' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> true

				),
				'unflag'		=> array(
					'slug'				=> 'unflag',
					'title'				=> __( 'Flagged', 'commenter' ),
					'user_can'			=> array( $this, 'can_unflag' ),
					'callback'			=> array( $this, 'unflag' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> true

				),
				'edit'			=> array(
					'slug'				=> 'edit',
					'title'				=> __( 'Edit', 'commenter' ),
					'user_can'			=> array( $this, 'can_edit' ),
					'callback'			=> array( $this, 'edit' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> false

				),
				'delete'		=> array(
					'slug'				=> 'delete',
					'title'				=> __( 'Delete', 'commenter' ),
					'user_can'			=> array( $this, 'can_delete' ),
					'callback'			=> array( $this, 'delete' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> false
				),
				'approve'		=> array(
					'slug'				=> 'approve',
					'title'				=> __( 'Approve', 'commenter' ),
					'user_can'			=> array( $this, 'can_approve' ),
					'callback'			=> array( $this, 'approve' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> false
				),
				'unapprove'		=> array(
					'slug'				=> 'unapprove',
					'title'				=> __( 'Unapprove', 'commenter' ),
					'user_can'			=> array( $this, 'can_unapprove' ),
					'callback'			=> array( $this, 'unapprove' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> false
				),
				'spam'			=> array(
					'slug'				=> 'spam',
					'title'				=> __( 'Spam', 'commenter' ),
					'user_can'			=> array( $this, 'can_spam' ),
					'callback'			=> array( $this, 'spam' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> false
				),
				'unspam'		=> array(
					'slug'				=> 'unspam',
					'title'				=> __( 'Unspam', 'commenter' ),
					'user_can'			=> array( $this, 'can_unspam' ),
					'callback'			=> array( $this, 'unspam' ),
					'method'			=> 'GET',
					'must_login'		=> true,
					'has_nonce'			=> true,
					'enabled'			=> false
				),
				'permalink'		=> array(
					'slug'				=> 'permalink',
					'title'				=> __( 'Permalink', 'commenter' ),
					'user_can'			=> array( $this, 'can_permalink' ),
					'callback'			=> array( $this, 'permalink' ),
					'method'			=> 'GET',
					'must_login'		=> false,
					'has_nonce'			=> false,
					'has_ajax'			=> true,
					'enabled'			=> true
				)

			)		

		) );

		foreach ( $actions as $method => $method_actions ) {

			foreach ( $method_actions as $slug => $action ) {

				if( array_key_exists( 'enabled', $action ) && ! $action['enabled'] )
					continue;

				if( empty( $action ) || ! is_array( $action ) )
					continue;

				if( ! is_string( $slug ) && $slug !== $action['slug'] )
					continue;

				if( ! isset( $action['method'] ) )
					continue;

				if( $action['method'] !== $method ) {

					if( array_key_exists( $action['method'], $actions ) ) {
						$method = $action['method'];
					} else {
						continue;
					}
					
				}

				$this->_actions[ $method ][ $slug ] = new Commenter_Action( $slug, $action );

			}

		}

	}

	/**
	 * Get the registered actions for the user.
	 *
	 *
	 * @return array
	 */
	public function get_actions( $method = null ) {

		$actions = $this->_actions;

		if( isset( $method ) && array_key_exists( $method, $this->_actions ) ) {

			$actions = $this->_actions[ $method ];

		}

		return apply_filters( 'commenter_get_user_actions', $actions );

	}

	/**
	 * Get a specific action for the user.
	 *
	 *
	 * @return array
	 */
	public function get_action( $method, $action ) {

		if( $this->has_action( $method, $action ) ) {

			return $this->_actions[ $method ][ $action ];

		}

		return null;

	}

	/**
	 * Checks whether the commenter has a certain action.
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	public function has_action( $method, $action ) {

		$actions = $this->get_actions( $method );

		if( array_key_exists( $action, $actions ) ) {

			return true;

		}

		return false;

	}

	public function comment( $comment = null, $args ) {

		try {

			if( ! isset( $args['request'] ) || ! is_array( $args['request'] ) ) {
				throw new Exception( __( 'Invalid request.', 'commenter' ) );
			}

			$request = $args['request'];
			$comment_post_ID = ( isset( $request['comment_post_ID'] ) ) ? $request['comment_post_ID'] : get_the_ID();
			$author = ( isset( $request['author'] ) ) ? $request['author'] : '';
			$email = ( isset( $request['email'] ) ) ? $request['email'] : '';
			$url = ( isset( $request['url'] ) ) ? $request['url'] : '';
			$comment = ( isset( $request['comment'] ) ) ? $request['comment'] : '';
			$cthread = ( isset( $request['cthread'] ) ) ? $request['cthread'] : commenter_get_current_thread()->get_slug();
			$comment_parent = ( isset( $request['cid'] ) ) ? $request['cid'] : 0;
			$_wp_unfiltered_html_comment = '';
			

			$comment_data = array(

				'comment_post_ID' 				=> $comment_post_ID,
				'author'						=> $author,
				'email'							=> $email,
				'url'							=> $url,
				'comment'						=> $comment,
				'comment_parent'				=> $comment_parent,
				'_wp_unfiltered_html_comment'	=> $_wp_unfiltered_html_comment,

			);

			$comment = wp_handle_comment_submission( wp_unslash( $comment_data ) );

			if ( is_wp_error( $comment ) ) {
				$data = intval( $comment->get_error_data() );
				if ( ! empty( $data ) ) {
					throw new Exception( $comment->get_error_message() );
				} else {
					exit;
				}
			}

			$user            = wp_get_current_user();
			$cookies_consent = ( isset( $request['wp-comment-cookies-consent'] ) );

			/**
			 * Perform other actions when comment cookies are set.
			 *
			 * @since 3.4.0
			 * @since 4.9.6 The `$cookies_consent` parameter was added.
			 *
			 * @param WP_Comment $comment         Comment object.
			 * @param WP_User    $user            Comment author's user object. The user may not exist.
			 * @param boolean    $cookies_consent Comment author's consent to store cookies.
			 */
			do_action( 'set_comment_cookies', $comment, $user, $cookies_consent );

			if( ! wp_doing_ajax() ) {

				$current_thread = $cthread;
				$fragment = '#'. $current_thread .'-comment-' . $comment->comment_ID;
				$location = empty( $request['redirect_to'] ) ? get_comment_link( $comment ) : $request['redirect_to'] . $fragment;

				// Add specific query arguments to display the awaiting moderation message.
				if ( 'unapproved' === wp_get_comment_status( $comment ) && ! empty( $comment->comment_author_email ) ) {
					$location = add_query_arg(
						array(
							'unapproved'      => $comment->comment_ID,
							'moderation-hash' => wp_hash( $comment->comment_date_gmt ),
						),
						$location
					);
				}

				/**
				 * Filters the location URI to send the commenter after posting.
				 *
				 * @since 2.0.5
				 *
				 * @param string     $location The 'redirect_to' URI sent via $_POST.
				 * @param WP_Comment $comment  Comment object.
				 */
				$location = apply_filters( 'comment_post_redirect', $location, $comment );

				wp_safe_redirect( $location );
				exit;

			} else {

				wp_send_json( array(

					'status'	=> 'success',
					'comment'	=> commenter_comment( $comment, array( 'echo_comment' => false ) ),

				) );

				die();

			}

		} catch (  Exception $error  ) {

			commenter_add_notice( apply_filters( 'comment_errors', $error->getMessage() ), 'error' );

			do_action( 'commenter_action_comment_failed' );

		}

		if( wp_doing_ajax() ) {

			commenter_print_notices();

			$message = ob_get_clean();

			wp_send_json( array( 

				'status'	=> 'error',
				'message' 	=> $message 

			) );

			die();

		} else {

			add_action( 'commenter_comment_form_notification', 'commenter_print_notices', 10 );

			if( isset( $comment_parent ) && $comment_parent > 0 ) {

				commenter_set_reply_comment_form( $comment_parent );

			}

		}


	}

	/**
	 * Replies to a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function reply( $comment, $args = array() ) {

		$request = ( isset( $args['request'] ) ) ? $args['request'] : null;
		$action = ( isset( $args['action'] ) ) ? $args['action'] : null;
		$cid = ( isset( $request['cid'] ) ) ? $request['cid'] : 0;

		if( ! $comment instanceof Comment_Comment && ( is_numeric( $cid ) && (int) $cid > 0 ) )
			$comment = commenter_get_comment( $cid );

		if( empty( $comment ) )
			return;

		$current_thread = commenter_get_current_thread();
		$comment_page = $current_thread->get_page_of_comment( $comment );
		$current_page = get_query_var( 'cpage' );

		if( $comment_page != $current_page ) {

			commenter_go_to_comment( $comment, array( 

				'caction' 	=> 'reply',
				'cpage'		=> $comment_page,
				'target' 	=> 'respond'

			) );

		}

		commenter_set_reply_comment_form( $comment );

	}

	/**
	 * Likes a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function like( $comment, $args = array() ) {

		try {

			$request = ( isset( $args['request'] ) ) ? $args['request'] : null;
			$thread = ( isset( $request['cthread'] ) ) ? $request['cthread'] : commenter_get_cthread_value();

			if( ! $comment instanceof Comment_Comment && ( isset( $request['cid'] ) && (int) $request['cid'] > 0 ) )
				$comment = commenter_get_comment( $request['cid'] );

			if( empty( $comment ) )
				throw new Exception( __( 'Comment doesn\'t exist.', 'commenter' ) );

			$comment_status = wp_get_comment_status( $comment->get_id() );

			if( $comment_status !== 'approved' )
				throw new Exception( __( 'Couldn\'t like the comment.', 'commenter' ) );

			$liked = commenter_like_comment( $comment, $args );

			if( $liked ) {

				if( wp_doing_ajax() ) {

					wp_send_json( array(

						'status'		=> 'success',
						'unlike_link' 	=> commenter_get_comment_unlike_link( $comment )

					) );

					die();

				}

				$fragment = 'comment-' . $comment->get_id();

				if( ! empty( $thread ) && is_string( $thread ) )
					$fragment = $thread . '-' . $fragment;	

				commenter_go_to_comment( $comment, array( 'target' => $fragment ) );				

			} else {

				throw new Exception( __( 'Couldn\'t like the comment.', 'commenter' ) );
				
			}
			
		} catch ( Exception $e ) {

			if ( $e ) {

				commenter_add_notice( $e->getMessage(), 'error' );

			}

		}

		if( wp_doing_ajax() ) {

			commenter_print_notices();

			$message = ob_get_clean();

			wp_send_json( array(

				'status'  => 'error',
				'message' => $message

			) );

			die();

		} else {

			if( $comment instanceof Comment_Comment )
				$cid = $comment->get_id();			

			if( empty( $cid ) && ( isset( $request['cid'] ) && (int) $request['cid'] > 0 ) )
				$cid = $request['cid'];

			if( is_numeric( $cid ) && (int) $cid > 0 )
				add_action( 'commenter_after_comment_' . $cid, 'commenter_print_notices', 10 );

		}

	}

	/**
	 * Unlikes a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function unlike( $comment, $args = array() ) {

		try {

			$request = ( isset( $args['request'] ) ) ? $args['request'] : null;
			$thread = ( isset( $request['cthread'] ) ) ? $request['cthread'] : commenter_get_cthread_value();

			if( ! $comment instanceof Comment_Comment && ( isset( $request['cid'] ) && (int) $request['cid'] > 0 ) )
				$comment = commenter_get_comment( $request['cid'] );

			if( empty( $comment ) )
				throw new Exception( __( 'Comment doesn\'t exist.', 'commenter' ) );

			$comment_status = wp_get_comment_status( $comment->get_id() );

			if( $comment_status !== 'approved' )
				throw new Exception( __( 'Couldn\'t unlike comment.', 'commenter' ) );

			$unliked = commenter_unlike_comment( $comment, $args );

			if( $unliked ) {

				if( wp_doing_ajax() ) {

					wp_send_json( array(

						'status'		=> 'success',
						'like_link' 	=> commenter_get_comment_like_link( $comment )

					) );

					die;

				}

				$fragment = 'comment-' . $comment->get_id();

				if( ! empty( $thread ) && is_string( $thread ) )
					$fragment = $thread . '-' . $fragment;	

				commenter_go_to_comment( $comment, array( 'target' => $fragment ) );

			} else {

				throw new Exception( __( 'Couldn\'t unlike the comment.', 'commenter' ) );

			}
			
		} catch ( Exception $e ) {

			if ( $e ) {

				commenter_add_notice( $e->getMessage(), 'error' );

			}
		
		}

		if( wp_doing_ajax() ) {

			commenter_print_notices();

			$message = ob_get_clean();

			wp_send_json( array(

				'status'  => 'error',
				'message' => $message

			) );

			die();

		}	

	}

	/**
	 * Flags a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function flag( $comment, $args = array() ) {

		$request = ( isset( $args['request'] ) ) ? $args['request'] : null;
		$thread = ( isset( $request['cthread'] ) ) ? $request['cthread'] : commenter_get_cthread_value();

		if( ! $comment instanceof Comment_Comment && ( isset( $request['cid'] ) && (int) $request['cid'] > 0 ) )
			$comment = commenter_get_comment( $request['cid'] );

		if( empty( $comment ) )
			return;

		if( $this->can_flag( $comment ) ) {

			$flagged = commenter_flag_comment( $comment, $args );

			if( $flagged ) {

				$fragment = 'comment-' . $comment->get_id();

				if( ! empty( $thread ) && is_string( $thread ) )
					$fragment = $thread . '-' . $fragment;	

				commenter_go_to_comment( $comment, array( 'target' => $fragment ) );

			}

		}

	}

	/**
	 * Unflags a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function unflag( $comment, $args = array() ) {

		$request = ( isset( $args['request'] ) ) ? $args['request'] : null;
		$thread = ( isset( $request['cthread'] ) ) ? $request['cthread'] : commenter_get_cthread_value();

		if( ! $comment instanceof Comment_Comment && ( isset( $request['cid'] ) && (int) $request['cid'] > 0 ) )
			$comment = commenter_get_comment( $request['cid'] );

		if( empty( $comment ) )
			return;

		if( $this->can_unflag( $comment ) ) {

			$unflagged = commenter_unflag_comment( $comment, $args );

			if( $unflagged ) {

				$fragment = 'comment-' . $comment->get_id();

				if( ! empty( $thread ) && is_string( $thread ) )
					$fragment = $thread . '-' . $fragment;	

				commenter_go_to_comment( $comment, array( 'target' => $fragment ) );

			}

		}
		
	}

	/**
	 * Sets up the editing context for a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function edit( $comment, $args = array() ) {	
		
	}

	/**
	 * Updates a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function update( $comment, $args = array() ) {
		
	}

	/**
	 * Deletes a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function delete( $comment, $args = array() ) {
		
	}

	/**
	 * Approves a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function approve( $comment, $args = array() ) {
		
	}

	/**
	 * Unapproves a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function unapprove( $comment, $args = array() ) {
		
	}

	/**
	 * Marks a comment as spam.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function spam( $comment, $args = array()) {
		
	}

	/**
	 * Unmarks a comment as spam.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function unspam( $comment, $args = array()) {
		
	}

	/**
	 * Gets the permalink of  a comment.
	 *
	 * @param int|WP_Comment|Commenter_Comment $comment
	 *
	 */
	public function permalink( $comment, $args = array() ) {

		$request = ( isset( $args['request'] ) ) ? $args['request'] : null;
		$thread = ( isset( $request['cthread'] ) ) ? $request['cthread'] : commenter_get_cthread_value();

		if( ! $comment instanceof Comment_Comment && ( isset( $request['cid'] ) && (int) $request['cid'] > 0 ) )
			$comment = commenter_get_comment( $request['cid'] );

		if( empty( $comment ) )
			return;

		$fragment = 'comment-' . $comment->get_id();

		if( ! empty( $thread ) )
			$fragment = $thread . '-' . $fragment;
			
		commenter_go_to_comment( $comment, array( 'target' => $fragment ) );
	}

	/**
	 * Determines whether current user can comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_comment( $comment = null, $args = array() ) {

		if( ! isset( $args['request'] ) ) {
			return false;
		}

		if( ! isset( $args['request']['comment_post_ID'] ) || ! is_numeric( $args['request']['comment_post_ID'] ) ) {
			return false;
		}

		if ( ! comments_open( $args['request']['comment_post_ID'] ) ) {
			return false;
		}

		if( get_option('comment_registration') && ! is_user_logged_in() ) {
			return false;
		}

		return true;

	}

	/**
	 * Replies to a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_reply( $comment, $args = array() ) {

		if ( ! comments_open( $comment->comment_post_ID ) ) {
			return false;
		}	

		if( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			return false;
		}

		return true;
	}

	/**
	 * Likes a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return bool|WP_Error
	 */
	public function can_like( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( commenter_current_user_liked( $comment ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Unlikes a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_unlike( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( ! commenter_current_user_liked( $comment ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Flags a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_flag( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if( $comment->commenter->get_id() == get_current_user_id() ) {
			return false;
		}

		if( commenter_current_user_flagged( $comment ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Unflags a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_unflag( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if( $comment->commenter->get_id() == get_current_user_id() ) {
			return false;
		}

		if( ! commenter_current_user_flagged( $comment ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Sets up the editing context for a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_edit( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {

			return false;

		}

		if( $comment->commenter->get_id() != get_current_user_id() ) {

			if( current_user_can( 'moderate_comments' ) ) {

				return true;

			}

			return false;

		}

		return true;

	}

	/**
	 * Updates a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_update( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {

			return false;

		}

		if( $comment->commenter->get_id() != get_current_user_id() ) {

			if( current_user_can( 'moderate_comments' ) ) {

				return true;

			}

			return false;

		}

		return true;

	}

	/**
	 * Deletes a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_delete( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {

			return false;

		}

		if( $comment->commenter->get_id() != get_current_user_id() ) {

			if( current_user_can( 'moderate_comments' ) ) {

				return true;

			}

			return false;

		}

		return true;

	}

	/**
	 * Approves a pending comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_approve( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if( ( '1' == $comment->comment_approved ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Unapproves a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_unapprove( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if( ( '0' == $comment->comment_approved ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Can the user mark a comment as spam.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_spam( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Can the user mark a comment as spam.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_unspam( $comment, $args = array() ) {

		if( ! is_user_logged_in() ) {
			return false;
		}

		if( ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Can the user link to a comment.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return array
	 */
	public function can_permalink( $comment, $args = array() ) {
		return true;
	}

	/**
	 * The comment action is enabled.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return bool
	 */
	public function comment_is_enabled( $args = array() ) {

		if( get_option('comment_registration') && ! is_user_logged_in() ) {
			return false;
		}

		return true;

	}

	/**
	 * The delete action is enabled.
	 *
	 * @param int|WP_Comment $comment
	 *
	 * @return bool
	 */
	public function update_is_enabled( $comment, $args = array() ) {
		return false;
	}

	/**
	 * Check if user has at least one role.
	 *
	 * @param array|string $roles
	 *
	 * @return array
	 */
	public function has_role( $roles ) {
		settype( $roles, 'array' );

		return array_intersect( $roles, $this->get_roles() );
	}


	/**
	 * Detect the type of user
	 *
	 * @param string|int $type
	 *
	 * @return bool
	 */
	public function is( $type ) {
		$is = false;
		if ( $type === 'current' ) {
			$is = $this->is( get_current_user_id() );
		} elseif ( is_string( $type ) ) {
			$name = preg_replace( '!Commenter_User(_?)!', '', get_class( $this ) );
			$is   = strtolower( $name ) == strtolower( $type );
		} elseif ( is_numeric( $type ) ) {
			$is = $this->get_id() && ( $this->get_id() == $type );
		}

		return $is;
	}

	/**
	 * Check if user is moderator.
	 *
	 * @return bool
	 */
	public function is_moderator() {

		$roles = $this->get_data( 'roles' ) ? $this->get_data( 'roles' ) : array();

		return in_array( COMMENTER_MODERATOR_ROLE, $roles );
	}

	/**
	 * Check what the user can do
	 *
	 * @param $action
	 * @param $object
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function can( $action ) {
		$args = func_get_args();
		unset( $args[0] );
		$method   = 'can_' . preg_replace( '!-!', '_', $action );
		$callback = array( $this, $method );
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, $args );
		} else {
			throw new Exception( sprintf( __( 'This user doesn\'t have permission to do action: %s', 'commenter' ), $action ) );
		}
	}

	/**
	 * Check if the action is enabled
	 *
	 * @param $action
	 * @param $object
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function action_is_enabled( $action, $object = null ) {
		$args = func_get_args();
		unset( $args[0] );
		$method   = preg_replace( '!-!', '_', $action ) . '_is_enabled';
		$callback = array( $this, $method );
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, $args );
		} else {
			throw new Exception( sprintf( __( 'This action isn\'t enabled: %s', 'commenter' ), $action ) );
		}
	}

	/**
	 * Check if the action is enabled
	 *
	 * @return array
	 */
	public function get_args() {

		$args = apply_filters( 'commenter_user_thread_args', array(

			'status' 		=> 'all', 
			'author_email' 	=> $this->get_email(),

		) );

		if( is_user_logged_in() ) {

			$args[ 'user_id' ] = $this->get_id();

		}

		return $args;

	}

	/**
	 * @return array
	 */
	public function get_roles() {
		return (array) $this->get_data( 'roles' );
	}

	/**
	 * Return TRUE if user already exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return ! ! get_user_by( 'id', $this->get_id() );
	}

	/**
	 * Check if the user is logged in.
	 *
	 * @return bool
	 */
	public function is_logged_in() {
		return $this->get_id() == get_current_user_id();
	}

	/**
	 * @return string
	 */
	public function get_role() {
		return $this->is_admin() ? 'admin' : ( $this->is_moderator() ? COMMENTER_MODERATOR_ROLE : 'subscriber' );
	}

	/**
	 * @return string
	 */
	public function get_identifier() {
		return $this->get_id() > 0 ? $this->get_id() : $this->get_email();
	}

	/**
	 * @return string
	 */
	public function get_link( $comment_ID = 0 ) {
		return get_comment_author_link( $comment_ID );
	}	

	/**
	 * @return string
	 */
	public function get_avatar( $size = 90, $default = null, $alt = '', $args = null ) {

		if( empty( $alt ) )
			$alt = $this->get_display_name() . '\'s avatar.';

		return get_avatar( $this->get_identifier(), $size, $default, $alt, $args );
	}

	/**
	 * @return string|null
	 */
	public function get_url() {
		return $this->get_data( 'url' );
	}

	/**
	 * @return array|mixed
	 */
	public function get_email() {
		return $this->get_data( 'email' );
	}

	/**
	 * Return user_login of the user.
	 *
	 * @return string
	 */
	public function get_username() {
		return $this->get_data( 'user_login' );
	}

	/**
	 * Return user bio information.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->get_data( 'description' );
	}

	/**
	 * Return user first name.
	 *
	 * @return string
	 */
	public function get_first_name() {
		return $this->get_data( 'first_name' );
	}

	/**
	 * Return user last name.
	 *
	 * @return string
	 */
	public function get_last_name() {
		return $this->get_data( 'last_name' );
	}

	/**
	 * Return user nickname.
	 *
	 * @return string
	 */
	public function get_nickname() {
		return $this->get_data( 'nickname' );
	}

	/**
	 * Return user display name.
	 *
	 * @return string
	 */
	public function get_display_name() {
		return $this->get_data( 'display_name' );
	}


}