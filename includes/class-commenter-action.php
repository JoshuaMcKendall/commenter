<?php

/**
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * The commenter action class.
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

class Commenter_Action {

	/**
	 * The comment ID.
	 *
	 * @var int
	 */
	protected $cid;

	/**
	 * The action name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The action title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The action slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The action url.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The HTTP method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Whether the action has a nonce.
	 *
	 * @var bool
	 */
	protected $has_nonce;

	/**
	 * The nonce for the action.
	 *
	 * @var string
	 */
	protected $nonce;

	/**
	 * The nonce name for the action.
	 *
	 * @var string
	 */
	protected $nonce_name;

	/**
	 * The nonce action name for the action.
	 *
	 * @var string
	 */
	protected $nonce_action_name;

	/**
	 * The whitelisted object types for the action.
	 *
	 * @var array
	 */
	protected $object_types;

	/**
	 * The object type of the action.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * Whether the action is enabled.
	 *
	 * @var bool
	 */
	protected $enabled;

	/**
	 * The user must be logged in to execute action.
	 *
	 * @var bool
	 */
	protected $must_login;

	/**
	 * The action capability handler.
	 *
	 * @var callable | Array 
	 */
	protected $user_can_callback;

	/**
	 * The action handler.
	 *
	 * @var callable | Array 
	 */
	protected $callback;

	/**
	 * Whether the action has ajax.
	 *
	 * @var Bool 
	 */
	protected $has_ajax;


	public function __construct( $name, $args = array(), $comment = null ) {

		$defaults = apply_filters( 'commenter_default_action_args', array( 
			'cid'					=> 0,
			'title'					=> '',
			'slug'					=> '',
			'callback'				=> '',
			'user_can'				=> '',
			'method'				=> 'GET',
			'enabled'				=> false,
			'must_login'			=> true,
			'has_nonce'				=> true,
			'nonce_name'			=> '',
			'nonce_action_name'		=> '',
			'has_ajax'				=> false
		 ) );

		$args = wp_parse_args( $args, $defaults );

		$this->set_name( $name );
		$this->set_cid( $args['cid'] );
		$this->set_title( $args['title'] );
		$this->set_slug( $args['slug'] );
		$this->set_must_login( $args['must_login'] );
		$this->set_method( $args['method'] );
		$this->set_has_nonce( $args['has_nonce'] );
		$this->set_nonce_name( $args['nonce_name'] );
		$this->set_nonce_action_name( $args['nonce_action_name'] );
		$this->set_user_can_callback( $args['user_can'] );
		$this->set_callback( $args['callback'] );
		$this->set_has_ajax( $args['has_ajax'] );

	}


	/**
	 * Get the action name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the action name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The action name.
	 */
	protected function set_name( $name ) {
		$this->name = sanitize_key( $name );
	}

	/**
	 * Get the comment ID.
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_cid() {
		return $this->cid;
	}

	/**
	 * Set the comment ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $cid The action comment ID.
	 */
	public function set_cid( $cid = 0 ) {
		$this->cid = (int) absint( $cid );
	}

	/**
	 * Get the action title.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action title.
	 */
	public function get_title() {

		/**
		 * Filter the action title.
		 *
		 * @since 1.0.0
		 *
		 * @param string $title The action title.
		 * @param string $name  The action name.
		 */
		return apply_filters( 'commenter_get_action_title', $this->title, $this->get_name() );
	}

	/**
	 * Set the action title.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The action title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get the user action capability callback.
	 *
	 * @since 1.0.0
	 *
	 * @return callable The action callback.
	 */
	public function get_user_can_callback() {
		return $this->user_can_callback;
	}

	/**
	 * Set user capapbility to do action.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $user_can The callback.
	 */
	public function set_user_can_callback( $user_can = null ) {

		if( ! is_callable( $user_can ) )
			$user_can = '';

		$this->user_can_callback = $user_can;

	}

	/**
	 * Get the action callback.
	 *
	 * @since 1.0.0
	 *
	 * @return callable The action callback.
	 */
	public function get_callback() {
		return $this->callback;
	}

	/**
	 * Set the action callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The action callback.
	 */
	public function set_callback( $callback ) {
		if( ! is_callable( $callback ) ) {
			$callback = '';
		}

		$this->callback = $callback;
	}

	/**
	 * Set the HTTP method.
	 *
	 * @since 1.0.0
	 *
	 * @param method $method The HTTP method verb.
	 */
	public function set_method( $method ) {

		$allowed_methods = array( 'GET', 'POST' );

		if( in_array( $method, $allowed_methods ) ) {

			$this->method = $method;

		}

	}

	/**
	 * Get the HTTP method.
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_method() {
		return ( isset( $this->method ) ) ? $this->method : 'GET';		
	}

	/**
	 * Set the action callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback The action callback.
	 */
	public function get_nonce() {
		$this->nonce = $nonce;
	}

	/**
	 * Get the nonce action name.
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_nonce_action_name() {
		if( ! $this->has_nonce() ) {
			return false;
		}

		return apply_filters( 'commenter_action_get_nonce_action_name', $this->nonce_action_name, $this );
	}

	/**
	 * Get the nonce name.
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_nonce_name() {
		if( ! $this->has_nonce() ) {
			return false;
		}

		return apply_filters( 'commenter_action_get_nonce_name', $this->nonce_name, $this );
	}

	/**
	 * Set must login for action.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $nonce Whether the action should generate a nonce or not.
	 */
	public function set_must_login( $must_login ) {
		$this->must_login = ( is_bool( $must_login ) ) ? (bool) $must_login : false;
	}

	/**
	 * Checks whether user must login to execute action.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $nonce Whether the action should generate a nonce or not.
	 */
	public function must_login() {
		return ( is_bool( $this->must_login ) ) ? (bool) $this->must_login : false;
	}

	/**
	 * Set whether the action should generate a nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $nonce Whether the action should generate a nonce or not.
	 */
	public function set_has_nonce( $has_nonce ) {
		$this->has_nonce = (bool) $has_nonce;
	}

	/**
	 * Set the nonce action name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $nonce_action_name Whether the action should generate a nonce or not.
	 */
	public function set_nonce_action_name( $nonce_action_name = '' ) {
		if( ! $this->has_nonce() ) {
			return false;
		}

		if( empty( $nonce_action_name ) ) {

			$user_id = get_current_user_id();
			$nonce_action_name = $user_id.'_'.$this->name .'_comment_'.$this->cid;
		}

		$this->nonce_action_name = $nonce_action_name;
	}

	/**
	 * Set the nonce name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $nonce_name The custom nonce name.
	 */
	public function set_nonce_name( $nonce_name = '' ) {
		if( ! $this->has_nonce() ) {
			return false;
		}

		if( empty( $nonce_name ) ) {
			$nonce_name = '_'.$this->name.'_comment';
		}

		$this->nonce_name = $nonce_name;
	}

	/**
	 * Set the action callback.
	 *
	 * @since 1.0.0
	 *
	 * @return bool 
	 */
	public function has_nonce() {
		if( isset( $this->has_nonce ) && is_bool( $this->has_nonce ) ) {
			return $this->has_nonce;
		}

		return false;
	}

	/**
	 * Executes the current action.
	 *
	 * @since 1.0.0
	 */
	public function user_can_execute( $comment, $args = array() ) {

		if( ! is_callable( $this->get_user_can_callback() ) )
			return false;
 
		return call_user_func_array( $this->get_user_can_callback(), array( 'comment' => $comment, 'args' => $args ) );

	}

	/**
	 * Executes the current action.
	 *
	 * @since 1.0.0
	 */
	public function execute( $comment, $args = array() ) {

		if( ! $this->user_can_execute( $comment, $args ) )
			return false;

		if( ! is_callable( $this->get_callback() ) ) 
			return false;
 
		return call_user_func_array( $this->get_callback(), array( 'comment' => $comment, 'args' => $args ) );

	}

	/**
	 * Get the action slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action slug.
	 */
	public function get_slug() {
		/**
		 * Filter the action slug.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug The action slug.
		 * @param string $name The action name.
		 */
		return apply_filters( 'commenter_get_action_slug', $this->slug, $this->get_name() );
	}

	/**
	 * Set the action slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The action slug.
	 */
	public function set_slug( $slug ) {
		if ( empty( $slug ) ) {
			$slug = $this->get_name();
		}
		$this->slug = $slug;
	}

	/**
	 * Whether the action is AJAX capable.
	 *
	 * @since 1.0.0
	 *
	 * @return bool $has_ajax The action ajax capability.
	 */
	public function has_ajax() {

		return ( is_bool(  $this->has_ajax ) ) ? $this->has_ajax : false;

	}

	/**
	 * Set the action ajax.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $has_ajax The action ajax capability.
	 */
	public function set_has_ajax( $has_ajax = null ) {

		$this->has_ajax = false;

		if( is_bool( $has_ajax ) && $has_ajax ) {	

			$this->has_ajax = $has_ajax;

		}

	}

	/**
	 * Get the action URL.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $args an array of extra url args.
	 *
	 * @return string The action URL.
	 */
	public function get_url( $comment = null, $args = array() ) {

		$defaults = apply_filters( 'commenter_get_action_url_default_params', array(
			'cid'		=> $this->cid,
			'caction'	=> $this->name,
			'cpage'		=> null,
			'csort'		=> commenter_get_csort_value(),
			'cthread'	=> commenter_get_cthread_value(),
			'fragment'	=> '',

		) );

		$args = wp_parse_args( $args, $defaults );	

		$query_args = array( 

			'cid'		=> $args['cid'],
			'cpage'		=> $args['cpage'],
			'caction' 	=> $args['caction'],
			'csort'		=> $args['csort'],
			'cthread'	=> $args['cthread']

		);

		$post_id = get_the_ID();

		if( ! $comment instanceof Commenter_Comment )
			$comment = commenter_get_comment( $args['cid'] );

		if( $comment )
			$post_id = $comment->comment_post_ID;

		
		$permalink = get_the_permalink( $post_id );
		$link = ( isset( $args['fragment'] ) ) ? $permalink . $args['fragment'] : $permalink;

		$url = add_query_arg( $query_args, $link );

		if( ( $this->has_nonce() || $this->must_login() ) && is_user_logged_in() ) {

			$this->set_nonce_name();
			$this->set_nonce_action_name();

			$url = wp_nonce_url( $url, $this->get_nonce_action_name(), $this->get_nonce_name() );

		}


		/**
		 * Filter the action URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url     The action URL.
		 * @param string $name    The action name.
		 * @param WP_Comment  $comment  The comment object.
		 * @param bool   $network Whether to retrieve the URL for the current network or current blog.
		 */
		return apply_filters( 'commenter_get_action_url', $url, $query_args, $link );
	}

}