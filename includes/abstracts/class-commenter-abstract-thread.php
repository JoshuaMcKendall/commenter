<?php
/**
 * Commenter Abstract Thread class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

abstract class Commenter_Abstract_Thread {

	public $id;

	public $slug;

	public $title;

	public $settings_info;

	public $args;

	public $position;

	public $enabled;

	public $page;

	public $page_count;

	public $sortings;

	public $current_sorting;

	public $current_page;

	public $is_current_thread;

	public $thread_actions;

	public $comment_form;

	public $style;

	public $max_depth;

	public $comments_count;

	public $comments_query;

	public $can_comment_in;

	public $comments;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $config       The configuration options of this view.
	 */
	public function __construct( $config = array() ) {

		$defaults = apply_filters( 'commenter_thread_default_config', array( 

			'id'				=> 0,
			'slug'				=> '',
			'title'				=> '',
			'sortings'			=> array(),
			'args'				=> array(

				'status' 		=> 'approve',
				'page'			=> get_query_var( 'cpage', 1 ), 
				'per_page'		=> get_option( 'comments_per_page' ),
				'meta_key'		=> ''

			),
			'position'			=> 10,
			'enabled'			=> false,
			'page_count'		=> 0,
			'current_sorting'	=> commenter_get_csort_value(),
			'current_page'		=> get_query_var( 'cpage', 1 ),
			'is_current_thread'	=> false,
			'thread_actions'	=> array(),
			'comments_count'	=> 0,
			'comment_form'		=> null,
			'style'				=> 'ul',
			'max_depth'			=> '',
			'comments_query'	=> new WP_Comment_Query,
			'can_comment_in'	=> '__return_true',
			'settings_info'		=> array(

				'title'			=> ''

			),
			'i18n'				=> array(

				'empty'			=> __( 'No Comments', 'commenter' ),
				'closed'		=> __( 'Comments are closed', 'commenter' )
			)

		) );

		$config = wp_parse_args( $config, $defaults );

		foreach ( $config as $config_key => $config ) {
			
			$this->$config_key = $config;

		}

		$this->load();

		add_filter( 'paginate_links', 		array( $this, 'filter_paginate_links' ), 		10, 1 	);

	}

	public function load() {

		$this->set_id();
		$this->set_slug();
		$this->set_is_current_thread();
		$this->set_current_sorting();
		$this->set_current_page();
		//$this->set_max_depth();
		$this->set_args();
		$this->set_comments();	
		$this->set_comments_count();
		$this->set_page_count();
		$this->set_comment_form();

	}

	public function filter_paginate_links( $link ) {

		$query_args = array( 'cid','caction', 'cpage' );

		if( filter_input( INPUT_GET, 'cid' ) || filter_input( INPUT_GET, 'caction' ) || filter_input( INPUT_GET, 'cpage' ) ) {

			return remove_query_arg( $query_args, $link );

		}

    	return $link;		

	}

	public function set_id( $id = null ) {

		$id = ( ! empty( $id ) ) ? $id : $this->id;

		if( is_null( $id ) || ! is_numeric( $id ) ) {

			$this->id = get_the_ID();

		} else {

			$this->id = $id;

		}

		if( ! is_array( $this->args ) ) {

			return false;

		}

		$this->args[ 'post_id' ] = $this->id;	

	}

	public function get_id() {

		if( ! isset( $this->id ) || ! is_numeric( $this->id ) ) {
			return 0;
		}

		return  absint( $this->id );

	}

	public function set_slug( $slug = null ) {

		$slug  = ( ! empty( $slug ) ) ? $slug : $this->slug;

		if( ! empty( $slug ) && is_string( $slug ) ) {

			$slug = sanitize_key( $this->slug );

		} else {

			$slug = 'abstract';

			error_log( 'Thread slug cannot be empty.' );

		}

		$this->slug = apply_filters( 'commenter_set_' . $slug . '_thread_slug', sanitize_key( $slug ), $this );

	}

	public function get_identifier() {

		$identifier = $this->get_slug() . '-thread-' . $this->get_id();

		return apply_filters( 'commenter_abstract_thread_get_identifier', $identifier, $this );

	}

	public function set_is_current_thread( $is_current_thread = null ) {

		if( isset( $is_current_thread ) && is_bool( $is_current_thread ) ) {
			$this->is_current_thread = $is_current_thread;
			return;
		}

		$is_current_thread = false;
		$thread_slug = $this->get_slug();
		$current_thread_slug = commenter_get_cthread_value();

		$is_current_thread = ( $thread_slug == $current_thread_slug ) ? true : $is_current_thread;

		$this->is_current_thread = $is_current_thread;
	}

	public function is_current_thread() {

		return is_bool( $this->is_current_thread ) ? $this->is_current_thread : false;

	}

	public function set_args() {

		if( ! is_array( $this->args ) ) {

			return false;

		}

		if( commenter_has_commenter() ) {

			$commenter_user = commenter_get_current_user();

			// Include the unapproved comments of the current user ( Whether they have an account, or are a guest with an email ) 
			// to show they have been successfully posted, but are pending approval by a moderator.
			if( $commenter_user->get_id() > 0 ) {

				$this->args[ 'include_unapproved' ] = array( $commenter_user->get_email(), $commenter_user->get_id() );

			} else {

				$this->args[ 'include_unapproved' ] = array( $commenter_user->get_email() );		

			}

		}

		if( isset( $this->current_page ) && is_numeric( $this->current_page ) ) {
			$this->args['page'] = $this->current_page;
		}

		$this->args['count'] = false;

		// Merge the comment query args with sorting args 
		if( is_array( $this->args ) && is_array( $this->sortings ) ) {

			if( array_key_exists( $this->current_sorting, $this->sortings ) && is_object( $this->sortings[ $this->current_sorting ] ) ) {

				$this->args = array_merge( $this->args, $this->sortings[ $this->current_sorting ]->get_args() );

			}

		}

	}

	public function get_args() {

		if( ! is_array( $this->args ) ) {

			return false;

		}

		return $this->args;

	}

	public function get_sortings() {

		if( is_array( $this->sortings ) ) {

			return $this->sortings;

		}

		return array();

	}

	public function set_current_sorting( $sorting = '' ) {

		$current_sorting = ( ! empty( $sorting ) ) ? $sorting : $this->current_sorting;
		$sortings = $this->sortings;

		if( ! is_string( $current_sorting ) ) {

			$current_sorting = commenter_get_csort_value();

		}

		if( ( is_array( $sortings ) && ! empty( $sortings ) ) && array_key_exists( $current_sorting, $sortings ) ) {

			$this->current_sorting = $current_sorting;

		} else {

			$this->current_sorting = commenter_get_option( 'discussion_default_sorting' );

		}

		foreach ( $sortings as $slug => $sorting ) {

			if( $this->current_sorting === $slug ) {
				
				$sorting->set_is_current_sorting( true );

			} else {

				$sorting->set_is_current_sorting( false );

			}

		}

	}

	public function get_current_sorting() {

		$sortings = $this->get_sortings();

		if( ( is_array( $sortings ) && ! empty( $sortings ) ) && array_key_exists( $this->current_sorting, $sortings ) ) {

			return $sortings[ $this->current_sorting ];

		}

		return commenter_get_option( 'commenter_discussion_default_sorting' );

	}

	public function set_comments() {

		if( ! is_array( $this->args ) )
			return false;

		$this->comments = $this->comments_query->query( $this->args );

	}

	public function set_comments_count() {

		if( ! is_array( $this->args ) ) {

			return false;

		}

		if( ! array_key_exists( 'count', $this->args ) || ( is_bool( $this->args[ 'count' ] ) &&  ! $this->args['count'] ) ) {

			$this->args[ 'count' ] = true;

		}

		$this->comments_count = $this->comments_query->query( $this->args );

	}

	public function get_comments() {

		return $this->comments;

	}

	public function get_comments_count() {

		return $this->comments_count;

	}

	public function get_comments_count_before_or_after( $comment ) {

		return $this->get_current_sorting()->get_comments_count_before_or_after( $comment );

	}

	public function get_page_of_comment( $comment ) {

		$comment_page = $this->get_current_sorting()->get_page_of_comment( $comment );
		$current_page = $this->get_current_page();

		if( $comment_page == $current_page ) {

			$comment_page = $current_page;

		}

		return apply_filters( 'commenter_thread_get_page_of_comment', $comment_page );

	}


	public function has_comments() {

		if( ! empty( $this->comments ) && count( $this->comments ) > 0 ) {

			return true;

		}

		return false;

	}

	public function set_page_count() {

		$this->page_count = get_comment_pages_count( $this->get_comments() );

	}

	public function set_current_page( $page = null ) {

		$page = ( ! empty( $page ) ) ? $page : $this->current_page;
 
		if( ! is_null( $page ) && is_numeric( $page ) && (int) $page > 0 && $this->is_current_thread() ) {

			$page = intval( $page );

		} else {

			$page = 1;

		}

		$this->current_page = $page; 

		if( is_array( $this->args ) && array_key_exists( 'page', $this->args ) )
			$this->args['page'] = $page;

	}

	public function set_comment_form() {

		if( ! $this->get_id() > 0 )
			return;
		
		if( ! $this->comment_form instanceof Commenter_Comment_Form )
			$this->comment_form = new Commenter_Comment_Form( array( 'post_id' => $this->get_id(), 'thread' => $this ) );

		$this->comment_form->set_thread( $this );

		$action_tag = 'commenter_render_'. $this->slug .'_comment_form';
		$action_callback = array( $this, 'render_comment_form' );

		if( $this->is_current_thread() && ! has_action( $action_tag, $action_callback ) ) 
			add_action( $action_tag, $action_callback );

	}

	public function get_comment_form() {

		if( $this->comment_form instanceof Commenter_Comment_Form ) {

			return $this->comment_form;

		}

		return null;

	}

	public function get_current_page() {

		return $this->current_page;
 
	}

	public function render_comment_form() {

		if( $this->is_current_thread() ) {

			$comment_form = $this->comment_form->render( false );
			$thread_slug = $this->get_slug();
			$action_slug = $this->comment_form->get_current_action();
			$context_slug = $thread_slug . '_' . $action_slug;

			echo apply_filters( 'commenter_render_'. $context_slug .'_comment_form', $comment_form, $this );

		}	

	}

	public function get_page_count() {

		return $this->page_count;

	}


	public function list_comments( $args = array() ) {

		global $post;

		$defaults = apply_filters( 'commenter_list_comments_defaults', array(
				
			'style'					=> $this->style,
			'short_ping'			=> true,
			'max_depth'				=> $this->max_depth,
			'callback'				=> 'commenter_comment',
			'sort_by'				=> get_query_var( 'csort' ),
			'order'					=> get_query_var( 'csort' ),
			'thread'				=> get_query_var( 'cthread', $this->slug ),
			'reverse_top_level'		=> false,
			'page'					=> get_query_var( 'cpage', $this->current_page ),
			'per_page'				=> get_option( 'comments_per_page' ),
			'default_page'			=> get_option( 'default_comments_page' ),
			'comments'				=> $this->comments,
			'echo'					=> true

		) );

		$args = wp_parse_args( $args, $defaults );

		if( is_bool( $args['echo'] ) && $args['echo'] ) {

			wp_list_comments( array(

				'style'      			=> $args['style'],
				'short_ping' 			=> $args['short_ping'],
				'callback'   			=> $args['callback'],
				'reverse_top_level'		=> $args['reverse_top_level'],
				'page'					=> $args['page'],
				'per_page'				=> $args['per_page'],
				'echo'					=> $args['echo']

			), $args['comments'] ); 

		} else if( is_bool( $args['echo'] ) && ! $args['echo'] ) {

			return wp_list_comments( array(

				'style'      			=> $args['style'],
				'short_ping' 			=> $args['short_ping'],
				'callback'   			=> $args['callback'],
				'reverse_top_level'		=> $args['reverse_top_level'],
				'page'					=> $args['page'],
				'per_page'				=> $args['per_page'],
				'echo'					=> $args['echo']

			), $args['comments'] ); 

		}

		return false;

	}

	public function get_slug() {

		return apply_filters( 'commenter_get_' . $this->slug . '_thread_slug', $this->slug, $this );

	}

	public function get_title() {

		return apply_filters( 'commenter_get_' . $this->slug . '_thread_title', $this->title, $this );

	}

	public function get_settings_title() {

		if( is_array( $this->settings_info ) && array_key_exists( 'title', $this->settings_info ) ) {

			if( ! empty( $this->settings_info[ 'title' ] ) ) {

				return $this->settings_info[ 'title' ];

			}

		}

		return $this->get_title();

	}

	public function get_url() {

		return apply_filters( 'commenter_abstract_thread_get_url', add_query_arg( array( 

			'cthread' 	=> $this->slug,
			'csort'		=> $this->get_current_sorting()->get_slug()

		), get_the_permalink( $this->get_id() ) ) . '#comments' );

	}

	public function get_data() {

		$id 				= $this->get_id();
		$slug 				= $this->get_slug();
		$identifier 		= $this->get_identifier();
		$title 				= $this->get_title();
		$page_count 		= $this->get_page_count();
		$is_current_thread 	= $this->is_current_thread();
		$current_page 		= $this->get_current_page();
		$comments_count 	= $this->get_comments_count();
		$position 			= $this->position;

		$data = array( 

			'id'					=> $id,
			'slug'					=> $slug,
			'identifier'			=> $identifier,
			'title'					=> $title,
			'current_page'			=> $current_page,
			'page_count'			=> $page_count,
			'is_current_thread'		=> $is_current_thread,
			'comments_count'		=> $comments_count,
			'position'				=> $position,
			'is_loaded'				=> ( $this->is_current_thread() ) ? true : false,
			'is_loading'			=> false,
			'new_comments'			=> array()

		);

		return apply_filters( 'commenter_abstract_thread_get_data', $data, $this );

	}

	public function is_enabled() {

		return is_bool( $this->enabled ) ? $this->enabled : false;

	}

	public function get_comments_pagenum_link( $args = array() ) {

		global $wp_rewrite;

		$thread = $this;

		$defaults = array(

			'pagenum'	=> 1,
			'max_page'	=> 0,
			'cthread'	=> get_query_var( 'cthread', $this->get_slug() ),
			'csort'		=> get_query_var( 'csort', $this->get_current_sorting()->get_slug() )

		);

		$args = wp_parse_args( $args, $defaults );

		$pagenum = (int) $args['pagenum'];
		$max_page = $args['max_page'];
		$current_thread = $args['cthread'];
		$current_sorting = $args['csort'];

		$result = add_query_arg( array(

			'cpage' 	=> $pagenum,
			'cthread'	=> $current_thread,
			'csort'		=> $current_sorting

		), get_permalink( $this->id ) );

		$result .= '#comments';

		/**
		 * Filters the comments page number link for the current request.
		 *
		 * @since 1.0.0
		 *
		 * @param string $result The comments page number link.
		 */
		return apply_filters( 'commenter_get_comments_pagenum_link', $result, $args );

	}

	public function get_previous_comments_link( $label = '' ) {

		if ( ! is_singular() )
			return;

		$page = get_query_var('cpage', $this->get_current_page() );

		if ( intval( $page ) <= 1 )
			return;

		$prevpage = intval( $page ) - 1;

		if ( empty( $label ) )
			$label = __('&laquo; Older Comments');

		$args = array(

			'pagenum'	=> $prevpage

		);

		/**
		 * Filters the anchor tag attributes for the previous comments page link.
		 *
		 * @since 2.7.0
		 *
		 * @param string $attributes Attributes for the anchor tag.
		 */
		return '<a href="' . esc_url( $this->get_comments_pagenum_link( $args ) ) . '" ' . apply_filters( 'previous_comments_link_attributes', '' ) . '>' . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) .'</a>';

	}

	public function previous_comments_link( $label = '' ) {

		echo $this->get_previous_comments_link( $label );

	}

	public function get_next_comments_link( $label = '', $max_page = 0 ) {

		global $wp_query;

		if ( ! is_singular() )
			return;

		$page = get_query_var('cpage', $this->get_current_page() );

		if ( ! $page ) {
			$page = 1;
		}

		$nextpage = intval( $page ) + 1;

		if ( empty( $max_page ) )
			$max_page = $this->get_page_count();

		if ( empty( $max_page ) )
			$max_page = $wp_query->max_num_comment_pages;

		if ( $nextpage > $max_page )
			return;

		if ( empty( $label ) )
			$label = __( 'Newer Comments &raquo;' );

		$args = array(

			'pagenum'	=> $nextpage,
			'max_page'	=> $max_page

		);

		/**
		 * Filters the anchor tag attributes for the next comments page link.
		 *
		 * @since 2.7.0
		 *
		 * @param string $attributes Attributes for the anchor tag.
		 */
		return '<a href="' . esc_url( $this->get_comments_pagenum_link( $args ) ) . '" ' . apply_filters( 'next_comments_link_attributes', '' ) . '>'. preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) .'</a>';		
		
	}

	public function next_comments_link( $label = '', $max_page = 0 ) {

		echo $this->get_next_comments_link( $label, $max_page );

	}

	public function get_loadmore_button( $label = '' ) {

		if( $this->current_page > $this->page_count || $this->page_count == 1 ) 
			return;

		if ( empty( $label ) )
			$label = __( 'Load more comments', 'commenter' );

		?>

		<button class="<?php esc_attr_e( $this->get_slug() ); ?>-comments-loadmore comments-loadmore btn btn-pill btn-primary" data-thread-slug="<?php esc_attr_e( $this->get_slug() ); ?>">

			<?php esc_html_e( $label ); ?>
							
		</button>

		<?php		

	}

	public function loadmore_button( $label = '' ) {

		echo $this->get_loadmore_button( $label );

	}

	public function render_sortings() {

		$current_sorting = $this->get_current_sorting();
		$sortings = $this->get_sortings();

		commenter_get_template( 'discussion/sortings.php', array( 'current_sorting' => $current_sorting, 'sortings' => $sortings ), true );

	}


	public function render( $echo = true ) {

		if( is_bool( $echo ) && $echo ) {

			commenter_get_template( 'thread.php', array( 'thread' => $this ), true );

		} else {

			ob_start();

			commenter_get_template( 'thread.php', array( 'thread' => $this ), true );

			return ob_get_clean();

		}

	}

	public static function init( $config = array() ) {
		
		return new self( $config );

	}

}
