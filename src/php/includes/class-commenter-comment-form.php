<?php

/**
 * The comment form class that displays the comment form.
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter/includes
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * The discussion class that replaces the themes default comments.
 *
 * This class defines all code that is the entry point to the new commenting system.
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

class Commenter_Comment_Form {

	public $post_id;

	public $identifier;

	public $form_action;

	public $comment_parent_id;

	public $thread;

	public $method;

	public $comment;

	public $commenter;

	public $actions;

	public $action;

	public $parent_comment;

	public $parent_commenter;

	public $citation;

	public $date;

	public $classes;

	public $is_cancellable;

	public $menu_actions;

	public $i18n;

	public $require_name_email;

	public $nonce_field;

	public $args;

	public function __construct( $args = array() ) {

		$defaults = apply_filters( 'commenter_comment_form_default_args', array(

			'post_id'				=> 0,
			'comment_parent_id'		=> 0,
			'thread'				=> null,
			'method'				=> 'POST',
			'comment'				=> null,
			'commenter'				=> commenter_get_current_user(),
			'parent_comment'		=> null,
			'parent_commenter'		=> null,
			'citation'				=> '',
			'date'					=> current_time( 'M j, Y' ),
			'classes'				=> 'comment-body comment-form',
			'is_cancellable'		=> false,
			'menu_actions'			=> array(

				'login'					=> array(

					'slug'					=> 'login',
					'title'					=> __( 'Log in', 'commenter' ),
					'enabled'				=> ! is_user_logged_in(),
					'link'					=> wp_login_url( get_the_permalink() . '#comments' )

				),
				'register'				=> array(

					'slug'					=> 'register',
					'title'					=> __( 'Register', 'commenter' ),
					'enabled'				=> ( ! is_user_logged_in() && get_option( 'users_can_register' ) ),
					'link'					=> wp_registration_url()

				),
				'logout'				=> array(

					'slug'					=> 'login',
					'title'					=> __( 'Log Out', 'commenter' ),
					'enabled'				=> is_user_logged_in(),
					'link'					=> wp_logout_url( get_the_permalink() . '#comments' )

				),

			),
			'action'		=> 'comment',
			'i18n'					=> array(

				'submit_button'			=> __( 'Post Comment', 						 'commenter' ),
				'cancel_link'			=> __( 'Cancel', 							 'commenter' ),
				'textarea_placeholder'	=> __( 'Leave a comment', 					 'commenter' ),
				'name_placeholder'		=> __( 'Name',								 'commenter' ),
				'email_placeholder'		=> __( 'Email',								 'commenter' ),
				'website_placeholder'	=> __( 'Website',							 'commenter' ),
				'email_notice'			=> __( '*Your email will not be published.', 'commenter' )

			),
			'require_name_email'	=> get_option( 'require_name_email' )


		) );

		$this->args = wp_parse_args( $args, $defaults );

		$this->set_post_id();
		$this->set_method();
		$this->set_comment_parent_id();
		$this->set_thread();
		$this->set_identifier();
		$this->set_comment();
		$this->set_commenter();
		$this->set_actions();
		$this->set_action();
		$this->set_form_action();
		$this->set_nonce_field();
		$this->set_parent_comment();
		$this->set_parent_commenter();
		$this->set_citation();
		$this->set_date();
		$this->set_classes();
		$this->set_is_cancellable();
		$this->set_menu_actions();
		$this->set_i18n();
		$this->set_require_name_email();
		

	}

	public function set_post_id( $post_id = 0 ) {

		$post_id = ( ! empty( $post_id ) ) ? $post_id : $this->args['post_id'];

	    if( ! empty( $post_id ) && is_int( $post_id ) ) {

	        $post_id = $post_id;

	    } else if( $this->has_comment() && property_exists( $this->comment, 'comment_post_ID' ) ) {

			$post_id = $this->comment->comment_post_ID;

		} else {

			$post_id = get_the_ID();

		}

		$this->post_id = apply_filters( 'commenter_comment_form_set_post_id', absint( $post_id ), $this );

	}

	public function set_form_action( $form_action = '' ) {

		$id = $this->get_post_id();
		$form_action = ( ! empty( $form_action ) ) ? $form_action : get_the_permalink( $id ) . '#' . $this->get_identifier();

		// if( filter_var( $form_action, FILTER_VALIDATE_URL ) === FALSE ) {
		// 	$form_action = get_the_permalink( $id ) . '#' . $this->get_identifier();
		// }

		if( ! wp_http_validate_url( $form_action ) ) {
			$form_action = get_the_permalink( $id ) . '#' . $this->get_identifier();
		}

		$this->form_action = apply_filters( 'commenter_comment_form_set_form_action', $form_action );

	}

	/**
	 * Set the HTTP method.
	 *
	 * @since 1.0.0
	 *
	 * @param method $method The HTTP method verb.
	 */
	public function set_method( $method = '' ) {

		$method = ( ! empty( $method ) ) ? $method : $this->args['method'];
		$allowed_methods = array( 'GET', 'POST' );

		if( in_array( $method, $allowed_methods ) ) {

			$this->method = $method;

		}

		$this->method = 'POST';

	}

	public function set_comment_parent_id( $comment_parent_id = 0 ) {

		$comment_parent_id = ( ! empty( $comment_parent_id ) ) ? $comment_parent_id : $this->args['comment_parent_id'];
		
	    if( ! empty( $comment_parent_id ) && is_int( $comment_parent_id ) ) {

	        $comment_parent_id = $comment_parent_id;

	    } else if( $this->has_comment() && $this->has_parent() && property_exists( $this->comment, 'comment_parent' ) ) {

			$comment_parent_id = $this->comment->comment_parent;

		} else {

			$comment_parent_id = 0;

		}

		$this->comment_parent_id = apply_filters( 'commenter_comment_form_set_comment_parent_id', absint( $comment_parent_id ), $this );

	}

	public function set_thread( $thread = null ) {

		$thread = ( ! empty( $thread ) ) ? $thread : $this->args['thread'];

		if( ! $thread instanceof Commenter_Abstract_Thread )
			$thread = null;

		$this->thread = apply_filters( 'commenter_comment_form_set_thread', $thread, $this );

	}

	public function set_identifier( $identifier = '' ) {

		$identifier = ( isset( $identifier ) ) ? $identifier : '';
		$identifier_base = 'comment-form';

		if( empty( $identifier ) || ! is_string( $identifier ) ) {

			if( isset( $this->post_id ) && is_numeric( $this->post_id ) )
				$identifier = $identifier_base . '-' . $this->post_id;

			if( $this->thread instanceof Commenter_Abstract_Thread ) 
				$identifier = $this->thread->get_slug() . '-' . $identifier;

		}

		$this->identifier = apply_filters( 'commenter_comment_form_set_identifier', $identifier, $this );

	}

	public function set_comment( $comment = null ) {

		$comment = ( ! empty( $comment ) ) ? $comment : $this->args['comment'];

		if( $comment instanceof Commenter_Comment ) {

			$comment = $comment;

		} else {

			$comment = null;

		}

		$this->comment = apply_filters( 'commenter_comment_form_set_comment', $comment, $this );

	}

	public function set_commenter( $commenter = null ) {

		$commenter = ( ! empty( $commenter ) ) ? $commenter : $this->args['commenter'];

		if( $commenter instanceof Commenter_User ) {

			$commenter = $commenter;

		} else {

			$commenter = commenter_get_current_user();

		}

		$this->commenter = apply_filters( 'commenter_comment_form_set_commenter', $commenter, $this );

	}

	public function set_actions() {

		$current_commenter_actions = $this->commenter->get_actions( 'POST' );
		$form_actions = array();

		$enabled_form_actions = apply_filters( 'commenter_form_enabled_actions', array(

			'comment' 	=> true,
			'reply'		=> true,
			'update'	=> true

		), $this );

		foreach( $enabled_form_actions as $slug => $is_enabled ) {
			
			if( ! array_key_exists( $slug, $current_commenter_actions ) )
				continue;

			if( is_bool( $is_enabled ) && ! $is_enabled )
				continue;

			if( is_callable( $is_enabled ) && ! call_user_func_array( $is_enabled, array( $this ) ) )
				continue;

			$form_actions[ $slug ] = $current_commenter_actions[ $slug ];

		}

		$this->actions = apply_filters( 'commenter_form_actions', $form_actions );

	}

	public function set_action( $action = null ) {

		$action = ( ! empty( $action ) ) ? $action : 'comment';

		if( is_string( $action ) && ! array_key_exists( $action, $this->actions ) ) {
			$action = reset( $this->actions );
		}

		if( is_string( $action ) && array_key_exists( $action, $this->actions ) ) {
			$action = $this->actions[ $action ];
		}

		$this->action = apply_filters( 'commenter_comment_form_set_action', $action, $this );

	}

	public function set_nonce_field( $nonce_action_name = '', $nonce_name = '', $referer = true, $echo = false ) {

		if( isset( $this->action ) && empty( $nonce_action_name ) && empty( $nonce_name ) ) {

			$nonce_action_name = $this->action->get_nonce_action_name();
			$nonce_name = $this->action->get_nonce_name();

		}

		$this->nonce_field = commenter_nonce_field( $nonce_action_name, $nonce_name, $referer, false );
		$thread_slug = $this->thread->get_slug();

		if( $this->action->has_nonce() || $echo ) {

			add_action( 'commenter_after_'. $thread_slug .'_comment_form_hidden_fields', array( $this, 'render_nonce_field' ), 10, 2 );

		}

	}

	public function get_nonce_field() {

		return ( isset( $this->nonce_field ) ) ? $this->nonce_field : null;

	}

	public function set_parent_comment( $parent_comment = null ) {

		$parent_comment = ( ! empty( $parent_comment ) ) ? $parent_comment : $this->args['parent_comment'];
		
		$parent_comment = commenter_get_comment( $parent_comment );

		$this->parent_comment = apply_filters( 'commenter_comment_form_set_parent_comment', $parent_comment, $this );

	}

	public function set_parent_commenter( $parent_commenter = null ) {

		$this->parent_commenter = apply_filters( 'commenter_comment_form_set_parent_commenter', $parent_commenter, $this );

	}

	public function set_citation( $citation = '' ) {

		$this->citation = apply_filters( 'commenter_comment_form_set_citation', $citation, $this );

	}

	public function set_date( $date = null, $format = 'M j, Y' ) {

		$date = ( ! empty( $date ) ) ? $date : $this->args['date'];

		if( ! empty( $date ) ) {

			$date = $date;

		} else {

			$date = current_time( $format );

		}

		$this->date = apply_filters( 'commenter_comment_form_set_date', $date, $format, $this );

	}

	public function set_classes( $classes = '', $overwrite = false ) {

		$classes = ( isset( $classes ) ) ? $classes : '';

		if( $overwrite ) {

			if( is_string( $classes ) && ! empty( $classes ) ) {

				$classes = $classes;

			} else if( is_array( $classes ) && ! empty( $classes ) ) {

				foreach ( $classes as $key => $class ) {
					
					if( ! is_string( $class ) )
						continue;

					$class = trim( $class );

					$classes .= $class . ' ';

				}

			} else {

				$classes = trim( $this->args['classes'] );

			}

		} else {

			if( is_string( $classes ) && ! empty( $classes ) ) {

				$classes = trim( $this->args['classes'] ) . ' ' . trim( $classes );

			} else if( is_array( $classes ) && ! empty( $classes ) ) {

				foreach ( $classes as $key => $class ) {
					
					if( ! is_string( $class ) )
						continue;

					$class = trim( $class );

					$classes = trim( $this->args['classes'] ) . ' ' . $class . ' ';

				}

			} else {

				$classes = trim( $this->args['classes'] );

			}		

		}


		$this->classes = apply_filters( 'commenter_comment_form_set_classes', $classes, $this );

	}

	public function set_is_cancellable( $is_cancellable = null ) {

		$is_cancellable = ( ! empty( $is_cancellable ) ) ? $is_cancellable : $this->args['is_cancellable'];

		if( is_bool( $is_cancellable ) ) {

			$this->is_cancellable = $is_cancellable;

		} else {

			$this->is_cancellable = false;

		}

	}

	public function set_menu_actions( $menu_actions = array() ) {

		$menu_actions = ( ! empty( $menu_actions ) ) ? $menu_actions : $this->args['menu_actions'];

		if( ! empty( $menu_actions ) && is_array( $menu_actions ) ) {

			foreach ( $menu_actions as $slug => $menu_action ) {
				
				if( array_key_exists( 'enabled', $menu_action ) && ! $menu_action['enabled'] ) {

					unset( $menu_actions[ $slug ] );

				}

			}

		} else {

			$menu_actions = array();

		}

		$this->menu_actions = apply_filters( 'commenter_comment_form_set_menu_actions', $menu_actions, $this );

	}

	public function set_i18n( $i18n = array() ) {

		$i18n = ( ! empty( $i18n ) ) ? $i18n : $this->args['i18n'];

		if( ! empty( $i18n ) && is_array( $i18n ) ) {

			$i18n = $i18n;

		} else {

			$i18n = array();

		}

		$this->i18n = apply_filters( 'commenter_comment_form_set_i18n', $i18n, $this );

	}

	public function set_require_name_email() {

		$this->require_name_email = apply_filters( 'commenter_comment_form_set_require_name_email', $this->args[ 'require_name_email' ], $this );

	}

	public function has_comment() {

		if( ( isset( $this->comment ) && ! empty( $this->comment ) ) && $comment instanceof Commenter_Comment ) {

			return true;

		}

		return false;

	}

	public function has_parent() {

		if( $this->has_comment() && $this->comment->has_parent() ) {

			return true;

		} else if( ! empty( $this->parent_comment ) ) {

			return true;

		}

		return false;

	}

	public function is_cancellable() {

		if( isset( $this->is_cancellable ) && is_bool( $this->is_cancellable ) ) {

			return apply_filters( 'commenter_form_is_cancellable', $this->is_cancellable, $this );

		}

		return false;

	}

	public function get_form_attr() {

		$attributes = apply_filters( 'commenter_comment_form_attributes', array(

			'action'	=> $this->get_form_action(),
			'method'	=> $this->get_method(),
			'id'		=> $this->get_identifier(),
			'class'		=> $this->get_classes(),

		) );

		$attribute_string = '';

		foreach ( $attributes as $attribute => $value ) {

			if( ! is_string( $attribute ) || empty( $attribute ) )
				continue;

			if( ! is_string( $value ) || empty( $value ) )
				continue;

			$form_attr = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			
			$attribute_string .= ' ' . $form_attr;

		}

		return $attribute_string;

	}

	public function get_identifier() {

		return $this->identifier;

	}

	public function get_form_action() {

		return $this->form_action;

	}

	public function get_method() {

		return $this->method;

	}

	public function get_classes() {

		return $this->classes;

	}

	public function get_current_action() {

		return $this->action->get_slug();

	}

	public function get_comment_parent_id() {

		return apply_filters( 'commenter_form_get_comment_parent_ID', $this->comment_parent_id, $this );

	}

	public function get_post_id() {
		
		return apply_filters( 'commenter_form_get_comment_post_ID', (int) $this->post_id, $this );

	}

	public function get_textarea_value() {

		if( empty( $this->comment ) )
			return null;

		if( ! $this->comment instanceof Commenter_Comment )
			return null;

		return $this->comment->get_content();

	}

	/**
	 * Retrieve HTML content for cancel comment reply link.
	 *
	 * @since 2.7.0
	 *
	 * @param string $text Optional. Text to display for cancel reply link. Default empty.
	 * @return string
	 */
	public function get_cancel_link( $text = '' ) {
		if ( empty( $text ) ) {
			$text = $this->i18n['cancel_link'];
		}

		$link  = esc_html( remove_query_arg( array( 'caction', 'cid' ) ) ) . '#' . $this->get_identifier();
		$alt_text = $this->i18n['cancel_link'];

		$classes = 'form-cancel-link btn btn-default btn-pill';

		if( ! $this->is_cancellable() )
			$classes .= ' hidden';

		$cancel_link = sprintf(
			'<a rel="nofollow" href="%s" class="%s" aria-label="%s">%s</a>',
			esc_url( $link ),
			esc_attr( $classes ),
			esc_attr( $alt_text ),
			$text
		);

		/**
		 * Filters the cancel comment reply link HTML.
		 *
		 * @since 2.7.0
		 *
		 * @param string $cancel_link 	 The HTML-formatted cancel link.
		 * @param string $link           Cancel comment reply link URL.
		 * @param string $text           Cancel comment reply link text.
		 */
		return apply_filters( 'commenter_cancel_link', $cancel_link, $link, $text );
	}

	/**
	 * Display HTML content for cancel comment reply link.
	 *
	 * @since 2.7.0
	 *
	 * @param string $text Optional. Text to display for cancel reply link. Default empty.
	 */
	public function cancel_link( $text = '' ) {
		echo $this->get_cancel_link( $text );
	}

	public function render_nonce_field( $form = null, $thread = null ) {

		if( empty( $form ) )
			$form = $this;

		if( ! $thread instanceof Commenter_Abstract_Thread )
			$thread = $this->thread;

		if( empty( $thread ) )
			return;

		echo $this->get_nonce_field();

	}

	public function render_author_vcard( $thread, $commenter, $comment ) {

		$args = array( 

			'form'		 	=> $this,
			'commenter'		=> $this->commenter,
			'comment'		=> $this->comment,
			'thread'		=> $this->thread,
			'menu_actions'	=> $this->menu_actions,
			'i18n'			=> $this->i18n,
			'req'			=> $this->require_name_email

		);

		commenter_get_template( 'comment-form/author-vcard.php', $args, true );

	}

	public function render_textarea( $thread, $commenter, $comment ) {

		$args = array( 

			'form'		 	=> $this,
			'commenter'		=> $commenter,
			'comment'		=> $comment,
			'thread'		=> $thread,
			'value'			=> $this->get_textarea_value(),
			'actions'		=> $this->menu_actions,
			'i18n'			=> $this->i18n,
			'req'			=> $this->require_name_email

		);

		commenter_get_template( 'comment-form/textarea.php', $args, true );

	}

	public function render_author_info( $thread, $commenter, $comment ) {

		if( is_user_logged_in() ) 
			return;

		$args = array( 

			'form'		 	=> $this,
			'commenter'		=> $commenter,
			'comment'		=> $comment,
			'thread'		=> $thread,
			'actions'		=> $this->menu_actions,
			'i18n'			=> $this->i18n,
			'req'			=> $this->require_name_email

		);		

		commenter_get_template( 'comment-form/author-info.php', $args, true );

	}

	public function render_hidden_fields( $thread, $commenter, $comment ) {

		$args = array( 

			'form'		 	=> $this,
			'commenter'		=> $commenter,
			'comment'		=> $comment,
			'thread'		=> $thread,
			'actions'		=> $this->menu_actions,
			'i18n'			=> $this->i18n,
			'req'			=> $this->require_name_email

		);		

		commenter_get_template( 'comment-form/hidden-fields.php', $args, true );

	}

	public function render_action_bar( $thread, $commenter, $comment ) {

		$args = array( 

			'form'		 	=> $this,
			'commenter'		=> $commenter,
			'comment'		=> $comment,
			'thread'		=> $thread,
			'actions'		=> $this->menu_actions,
			'i18n'			=> $this->i18n,
			'req'			=> $this->require_name_email

		);

		commenter_get_template( 'comment-form/action-bar.php', $args, true );

	}

	public function render( $echo = true ) {

		add_action( 'commenter_comment_form', array( $this, 'render_author_vcard' 	), 10, 3 );
		add_action( 'commenter_comment_form', array( $this, 'render_textarea' 	  	), 20, 3 );
		add_action( 'commenter_comment_form', array( $this, 'render_author_info'  	), 30, 3 );
		add_action( 'commenter_comment_form', array( $this, 'render_hidden_fields'  ), 40, 3 );
		add_action( 'commenter_comment_form', array( $this, 'render_action_bar'   	), 50, 3 );

		if( $echo ) {

			commenter_get_template( 'comment-form.php', array( 

				'form'			=> $this,
				'thread' 		=> $this->thread,
				'commenter' 	=> $this->commenter,

			), true );

		} else {

			ob_start();

			commenter_get_template( 'comment-form.php', array( 

				'form'			=> $this,
				'thread' 		=> $this->thread,
				'commenter' 	=> $this->commenter,

			), true );
			
			return ob_get_clean();

		}

	}

}