<?php

/**
 * The discussion class that replaces the themes default comments.
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

class Commenter_Discussion {

	public $id;

	public $title;

	public $sortings;

	public $threads;

	public $commenter;

	public $current_sorting;

	public $current_thread;

	public $current_page;

	public $settings;

	public $args;

	public function __construct( $args = array() ) {

		$defaults = apply_filters( 'commenter_discussion_default_args', array(

			'id'				=> get_the_ID(),
			'title'				=> __( 'Discussion', 'commenter' ),
			'sortings'			=> array(),
			'threads'			=> array(),
			'commenter'			=> null,
			'current_page'		=> get_query_var( 'cpage', 1 ),
			'current_sorting'	=> commenter_get_csort_value(),
			'current_thread'	=> commenter_get_cthread_value(),
			'request_time'		=> time(),
			'settings'			=> array()

		) );

		$this->args = wp_parse_args( $args, $defaults );

		$this->init_discussion();

		add_action( 'commenter_comments', 			array( $this, 'render' ) 					);

		add_filter( 'query_vars', 					array( $this, 'add_discussion_query_vars' ) 			);
		add_filter( 'comments_template',			array( $this, 'override_comments_template' ), 	10, 1 	);
		add_filter( 'get_comment_link',				array( $this, 'get_comment_link' ), 			10, 4 	);
		

	}

	public function init_discussion() {

		$this->title = $this->args['title'];
		$this->commenter = commenter_get_current_user();
		$this->set_id();
		$this->set_settings();
		$this->set_sortings();
		$this->set_threads();
		$this->set_current_thread();
		$this->set_current_sorting();
		$this->set_current_page();	

	}


	public function set_id( $id = null ) {

		$id = ( ! empty( $id ) && is_numeric( $id ) ) ? $id : $this->args['id'];

		if( ! is_numeric( $id ) && ! ( $id > 0 ) ) {
			$id = get_the_ID();
		}

		$this->id = absint( $id );		

	}

	public function get_id() {

		return $this->id;

	}

	public function get_identifier() {

		return apply_filters( 'commenter_discussion_get_identifier', 'discussion-' . $this->get_id(), $this );

	}

	public function set_settings( $settings = array() ) {

		$default_settings = apply_filters( 'commenter_discussion_default_settings', array(

			'default_thread'	=> commenter_get_option( 'discussion_default_thread' ),
			'default_sorting'	=> commenter_get_option( 'discussion_default_sorting' ),
			'discussion_info'	=> commenter_get_option( 'discussion_information' ),

		), $this );

		if( is_array( $this->args ) && array_key_exists( 'settings', $this->args ) ) {
			$settings = $this->args['settings'];
		}

		$settings = wp_parse_args( $settings, $default_settings );

		$this->settings = $settings;

	}

	public function set_sortings() {

		$sortings = array();

		if( is_array( $this->args ) && array_key_exists( 'sortings', $this->args ) && is_array( $this->args['sortings'] ) ) {
			$sortings = $this->args['sortings'];
		}

		$default_sortings = apply_filters( 'commenter_discussion_default_sortings', array(

			'best' 	=> array(

				'file'		=> COMMENTER_INC . 'sortings/class-commenter-best-sorting.php',
				'callback'	=> array( 'Commenter_Best_Sorting', 'init' ),
				'args'		=> array()

			),
			'newest' 	=> array(

				'file'		=> COMMENTER_INC . 'sortings/class-commenter-newest-sorting.php',
				'callback'	=> array( 'Commenter_Newest_Sorting', 'init' ),
				'args'		=> array()
				

			),
			'oldest' 	=> array(

				'file'		=> COMMENTER_INC . 'sortings/class-commenter-oldest-sorting.php',
				'callback'	=> array( 'Commenter_Oldest_Sorting', 'init' ),
				'args'		=> array()

			)

		), $this );

		$sortings = wp_parse_args( $sortings, $default_sortings );

		$this->sortings = $sortings;

		foreach ( $this->sortings as $slug => $sorting ) {

			$sorting_callback = $sorting['callback'];
			$sorting_class = $sorting_callback[0];
			$sorting_file = $sorting['file'];
			$sorting_args = $sorting['args'];

			if( ! is_string( $slug ) )
				continue;
		
			if( ! file_exists( $sorting_file ) ) 
				continue;

			if( empty( $sorting_callback ) )
				continue;

			include_once $sorting_file;

			if( ! is_callable( $sorting_callback ) )
				continue;

			$sorting_args['id'] = $this->get_id();

			$this->sortings[ $slug ] = call_user_func_array( $sorting_callback, array( $sorting_args ) );

		}		

	}

	public function add_discussion_query_vars( $query_vars ) {

 		$vars = apply_filters( 'commenter_action_query_vars', array( 'csort', 'cid', 'caction', 'cthread' ) );
   
   		$query_vars = array_merge( $query_vars, $vars );

		return $query_vars;

	}

	public function set_threads() {

		$threads = array();

		if( is_array( $this->args ) && array_key_exists( 'threads', $this->args ) && is_array( $this->args['threads'] ) ) {
			$threads = $this->args['threads'];
		}

		$default_threads = apply_filters( 'commenter_threads', array(

			'community'				=> array(

				'file'		=> COMMENTER_INC . 'threads/class-commenter-community-thread.php',
				'callback'	=> array( 'Commenter_Community_Thread', 'init' ),
				'args'		=> array(

					'slug'				=> 'community',
					'page'				=> $this->get_current_page(),
					'title'				=> __( 'Community', 'commenter' ),
					'enabled'			=> true,
					'position'			=> 10,

				)

			),
			'commenter'				=> array(

				'file'		=> COMMENTER_INC . 'threads/class-commenter-commenter-thread.php',
				'callback'	=> array( 'Commenter_Commenter_Thread', 'init' ),
				'args'		=> array(

					'slug'				=> 'commenter',
					'page'				=> $this->get_current_page(),
					'title'				=> $this->commenter->get_display_name(),
					'enabled'			=> commenter_has_commenter(),
					'position'			=> 15,
					'args'				=> $this->commenter->get_args(),
					'settings_info'		=> array(

						'title'			=> __( 'Commenter', 'commenter' )

					)

				)

			)

		), $this );

		$this->threads = wp_parse_args( $threads, $default_threads );

		foreach ( $default_threads as $slug => $thread ) {

			if( ! is_string( $slug ) )
				continue;

			if( ! isset( $thread['args']['id'] ) || ! is_numeric( $thread['args']['id'] ) )
				$thread['args']['id'] = $this->get_id();

			if( ! isset( $thread['args']['sortings'] ) || empty( $thread['args']['sortings'] ) )
				$thread['args']['sortings'] = $this->get_sortings();

			if( ! file_exists( $thread['file'] ) ) 
				continue;

			if( empty( $thread['callback'] ) )
				continue;

			include_once $thread['file'];

			if( ! is_callable( $thread['callback'] ) )
				continue;

			$thread = call_user_func_array( $thread['callback'], array( $thread['args'] ) );

			if( $thread instanceof Commenter_Abstract_Thread && $thread->is_enabled() ) {

				$this->threads[ $slug ] = $thread;

			}

		}

	}

	public function get_threads( $options = array() ) {

		$default_options = apply_filters( 'commenter_discussion_get_threads_default_options', array(

			'enabled_only' => true

		) );

		$options = wp_parse_args( $options, $default_options );
		$threads = $this->threads;

		if( $options['enabled_only'] ) {

			foreach ( $threads as $slug => $thread ) {

				if( is_array( $thread ) ) {

					unset( $threads[ $slug ] );

				}

			}

		}

		return $threads;

	}

	public function get_thread( $thread ) {

		if( ! is_array( $this->threads ) && ! array_key_exists( $thread, $this->threads ) ) {

			return false;

		}

		return $this->threads[ $thread ];

	}

	public function set_current_thread( $thread = '' ) {

		$thread = ( ! empty( $thread ) ) ? $thread : $this->args[ 'current_thread' ];

		if( ! empty( $thread ) ) {

			if( is_string( $thread ) && array_key_exists( $thread, $this->threads ) ) {

				$this->current_thread = $thread;

			} else {

				$default_thread = $this->settings[ 'default_thread' ];

				if( is_string( $default_thread ) && array_key_exists( $default_thread, $this->threads ) ) {

					$this->current_thread = $default_thread;

				}
				
			}

		} else {

			$default_thread = $this->settings[ 'default_thread' ];

			if( is_string( $default_thread )  && array_key_exists( $default_thread, $this->threads ) ) {

				$this->current_thread = $default_thread;

			}

		}

		if( $this->thread_exists( $this->current_thread ) ) {

			$this->threads[ $this->current_thread ]->set_is_current_thread( true );

		} else {

			foreach ( $this->threads as $slug => $thread ) {
				
				if( $thread instanceof Commenter_Abstract_Thread ) {

					$this->current_thread = $slug;
					$this->threads[ $this->current_thread ]->set_is_current_thread( true );

					break;

				}

			}

		}

	}

	public function has_thread( $thread ) {

		return $this->thread_exists( $thread );
 
	}

	public function thread_exists( $thread ) {

		$threads = $this->get_threads();

		if( array_key_exists( $thread, $threads ) ) {

			$thread = $threads[ $thread ];

			if( $thread instanceof Commenter_Abstract_Thread ) {

				return true;

			}

		}

		return false;

	}

	public function get_current_thread() {

		if( is_array( $this->threads ) && array_key_exists( $this->current_thread, $this->threads ) ) {

			$current_thread = $this->threads[ $this->current_thread ];

			if( $current_thread instanceof Commenter_Abstract_Thread ) {

				if( $current_thread->is_enabled() ) {

					return $current_thread;

				}

			}

		}

		return null;

	}

	public function get_sortings() {

		return apply_filters( 'commenter_discussion_get_sortings', $this->sortings, $this );

	}

	public function set_current_sorting( $current_sorting = '' ) {

		$current_sorting = commenter_get_csort_value( $this->args['current_sorting'] );
		$sortings = $this->get_sortings();
		$first_sorting = array_values( $sortings )[0];

		if( ! array_key_exists( $current_sorting, $sortings ) )
			$current_sorting = $first_sorting->get_slug();

		$current_thread = $this->get_current_thread();
		$current_thread->set_current_sorting( $current_sorting );

		$this->current_sorting = $current_thread->get_current_sorting();

	}

	public function get_current_sorting() {

		return apply_filters( 'commenter_discussion_get_current_sorting', $this->current_sorting, $this );

	}

	public function set_current_page() {

		$current_thread = $this->get_current_thread();
		$current_page = $this->args['current_page'];

		if( empty( $current_page ) || ! is_numeric( $current_page ) )
			$current_page = 1;

		if( ! empty( $current_thread ) && $current_thread instanceof Commenter_Abstract_Thread )
			$current_thread->set_current_page( $current_page );

		$this->current_page = $current_page;

	}

	public function get_current_page() {

		return $this->current_page;

	}

	public function get_page_count() {

		return $this->get_current_thread()->get_page_count();

	}

	public function get_settings() {

		return $this->settings;

	}

	public function get_setting( $setting ) {

		return array_key_exists( $setting, $this->settings ) ? $this->settings[ $setting ] : null;

	}

	public function is_current_thread( $thread ) {

		$cthread = get_query_var( 'cthread', $this->settings['default_thread'] );

		if( $thread instanceof Commenter_Abstract_Thread ) {

			if( array_key_exists( $cthread, $this->threads ) && $cthread == $thread->slug ) {

				return true;

			}			

		} else if( is_string( $thread ) ) {	

			if( array_key_exists( $cthread, $this->threads ) && $cthread == $thread ) {

				return true;

			}

		}

		return false;

	}

	public function get_info() {

		return wp_kses_post( $this->settings[ 'discussion_info' ] );

	}

	public function has_info() {

		return true;

	}

	public function get_comments() {

		$current_thread = $this->get_current_thread();

		if( isset( $current_thread ) && $current_thread instanceof Commenter_Abstract_Thread )
			return $current_thread->list_comments( array( 'echo' => false ) );

	} 


	public function render_current_thread() {

		$thread = $this->get_current_thread();

		if( is_null( $thread ) )
			return;

		if( $thread instanceof Commenter_Abstract_Thread && $thread->is_enabled() ) {

			return $thread->render();

		}

	}

	public function render_threads() {

		$threads = $this->get_threads();

		foreach ( $threads as $slug => $thread ) {
			
			if( $thread instanceof Commenter_Abstract_Thread && $thread->is_enabled() ) {

				$thread->render();

			}

		}

	}

	public function override_comments_template( $theme_template ) {

		return commenter_get_template( 'commenter-comments.php' );

	}

	public function get_comment_link( $link, $comment, $args, $cpage ) {

		if( ! ( $comment instanceof Commenter_Comment ) ) {

			$comment = new Commenter_Comment( $comment );

		}

		if( is_admin() ) {

			return $comment->get_link( array( 'cthread' => COMMENTER_PRIMARY_THREAD ) );

		}

		return $comment->get_link();

	}

	public function get_data() {

		$id 					= $this->get_id();
		$title 					= $this->title;
		$identifier 			= $this->get_identifier();
		$threads 				= $this->get_threads();
		$sortings 				= $this->get_sortings();
		$current_thread 		= $this->get_current_thread();
		$current_thread_slug 	= $current_thread->get_slug();
		$current_sorting 		= $current_thread->get_current_sorting();
		$current_sorting_slug 	= $current_sorting->get_slug();
		$current_page 			= $current_thread->get_current_page();
		$commenter_cookies 		= array(

			'cookiehash'			=> COOKIEHASH,
			'comment_author'		=> 'comment_author_' . COOKIEHASH,
			'comment_author_email'	=> 'comment_author_email_' . COOKIEHASH,
			'comment_author_url'	=> 'comment_author_url_' . COOKIEHASH,

		);
		$request_time 			= time();

		foreach ( $threads as $slug => $thread ) {

			if( ! $thread instanceof Commenter_Abstract_Thread )
				continue;
			
			$threads[ $slug ] = $thread->get_data();

		}

		foreach ( $sortings as $slug => $sorting ) {
			
			$sortings[ $slug ] = $sorting->get_data();

		}

		$data = array( 

			'id'				=> $id,
			'identifier'		=> $identifier,
			'title'				=> $title,
			'threads'			=> $threads,
			'sortings'			=> $sortings,
			'current_thread' 	=> $current_thread_slug,
			'current_sorting'	=> $current_sorting_slug,
			'current_page'		=> $current_page,
			'request_time'		=> $request_time,
			'commenter_cookies'	=> $commenter_cookies,
			'is_loading'		=> false

		);

		return apply_filters( 'commenter_discussion_get_data', $data, $this );

	}


	public function render( $echo = true ) {

		if( (bool) $echo ) {

			commenter_get_template( 'discussion.php', array(

				'discussion' 		=> $this, 
				'current_thread' 	=> $this->get_current_thread(),
				'threads' 			=> $this->get_threads() 

			), true );

		} else {

			ob_start();

			commenter_get_template( 'discussion.php', array(

				'discussion' 		=> $this,
				'current_thread' 	=> $this->get_current_thread(),
				'threads' 			=> $this->get_threads()

			), true );

			return ob_get_clean();

		}

	}

}