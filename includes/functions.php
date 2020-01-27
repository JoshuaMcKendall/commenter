<?php
/**
 * The functions for Commenter.
 *
 * All the functions for the Commenter plugin.
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

if ( ! function_exists( 'commenter_get_option' ) ) {

	/**
	 * @param string    $name
	 * @param null 		$default
	 *
	 * @return mixed
	 */
	function commenter_get_option( $name, $default = null ) {

		if ( strpos( $name, 'commenter_' ) !== 0 ) {

			$name = 'commenter_' . $name;

		}

		return get_option( $name, $default );
	}

}

if ( ! function_exists( 'commenter_update_option' ) ) {

	/**
	 * commenter_update_option
	 *
	 * @param string 	$name
	 * @param mixed 	$default
	 *
	 * @return mixed
	 */
	function commenter_update_option( $name, $default = null ) {

		return update_option( 'commenter_' . $name, $default );

	}

}

if( ! function_exists( 'commenter_session_get' ) ) {

	/**
	 * commenter_session_get
	 *
	 * @param string 	$name
	 * @param string 	$default
	 *
	 * @return mixed
	 */
	function commenter_session_get( $name = '', $default = '' ) {

		return Commenter()->_session->get( $name, $default );

	}

}

if( ! function_exists( 'commenter_session_set' ) ) {

	/**
	 * commenter_session_set
	 *
	 * @param string 	$name
	 * @param string 	$value
	 *
	 * @return mixed
	 */
	function commenter_session_set( $name = '', $value = '' ) {

		Commenter()->_session->set( $name, $value );

	}

}

if ( ! function_exists( 'commenter_get_template' ) ) {

	/**
	 * commenter_get_template
	 *
	 * @param string 	$template_name
	 * @param array 	$args
	 * @param bool 		$load
	 * @param string 	$template_path
	 * @param string 	$default_path
	 *
	 * @return type
	 */
	function commenter_get_template( $template_name, $args = array(), $load = false, $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
  
		$located = commenter_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
  
			return;
		}
		// Allow 3rd party plugin filter template file from their plugin
		$located = apply_filters( 'commenter_get_template', $located, $template_name, $args, $template_path, $default_path );

		do_action( 'commenter_before_template_part', $template_name, $template_path, $located, $args );


		if ( $load && '' != $located ) {

			include( $located );

		} else {

			return $located;
			
		}

		do_action( 'commenter_after_template_part', $template_name, $template_path, $located, $args );

		
	}

} 

if ( ! function_exists( 'commenter_template_path' ) ) {

	/**
	 * commenter_template_path
	 *
	 * @return string
	 */
	function commenter_template_path() {

		return apply_filters( 'commenter_template_path', 'commenter' );

	}

}

if ( ! function_exists( 'commenter_get_template_part' ) ) {

	function commenter_get_template_part( $slug, $name = '' ) {
		$template = '';

		// Look in yourtheme/slug-name.php and yourtheme/courses-manage/slug-name.php
		if ( $name ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				commenter_template_path() . "/{$slug}-{$name}.php"
			) );
		}

		// Get default slug-name.php
		if ( ! $template && $name && file_exists( COMMENTER_PATH . "templates/{$slug}-{$name}.php" ) ) {
			$template = COMMENTER_PATH . "templates/{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/courses-manage/slug.php
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}.php", commenter_template_path() . "{$slug}.php" ) );
		}

		// Allow 3rd party plugin filter template file from their plugin
		if ( $template ) {
			$template = apply_filters( 'commenter_get_template_part', $template, $slug, $name );
		}
		if ( $template && file_exists( $template ) ) {
			load_template( $template, false );
		}

		return $template;
	}

}

if ( ! function_exists( 'commenter_get_template_content' ) ) {
	function commenter_get_template_content( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		ob_start();
		commenter_get_template( $template_name, $args, $template_path, $default_path );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'commenter_locate_template' ) ) {

	function commenter_locate_template( $template_name, $template_path = '', $default_path = '' ) {

		if ( ! $template_path ) {
			$template_path = commenter_template_path();
		}

		if ( ! $default_path ) {
			$default_path = COMMENTER_PATH . 'templates/';
		}

		$template = null;
		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
		);
		// Get default template
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found
		return apply_filters( 'commenter_locate_template', $template, $template_name, $template_path );
	}

}

if( ! function_exists('commenter_add_notice') ) {

	function commenter_add_notice( $message, $type = 'default', $code = null ) {

		if ( ! $message ) {
			return;
		}

		$notices = Commenter()->_session->get( 'notices', array() );
		if ( ! isset( $notices[ $type ] ) ) {
			$notices[ $type ] = array();
		}
		$notices[ $type ][ $code ] = $message;
		Commenter()->_session->set( 'notices', $notices );
	}

}

if ( ! function_exists( 'commenter_get_notices' ) ) {

	function commenter_get_notices( $type = null ) {

		$notices = Commenter()->_session->get( 'notices', array() );

		if ( $type ) {
			return isset( $notices[ $type ] ) ? $notices[ $type ] : $notices;
		}

		return $notices;

	}

}

if ( ! function_exists( 'commenter_get_notices' ) ) {

	function commenter_get_notice( $type = 'default', $code = null ) {

		$notices = Commenter()->_session->get( 'notices', array() );

		if( commenter_has_notice( $type, $code ) ) {

			if( ! empty( $code ) ) {
				return $notices[ $type ][ $code ];
			}

			return $notices[ $type ];

		}

		return $notices;

	}

}

if ( ! function_exists( 'commenter_has_notice' ) ) {

	function commenter_has_notice( $type = 'default', $code = null ) {

		$notices = Commenter()->_session->get( 'notices', array() );

		if ( $type ) {

			if( ! empty( $code ) ) {

				return isset( $notices[ $type ][ $code ] );

			}

			return isset( $notices[ $type ] );

		}

		return isset( $notices );

	}

}

if ( ! function_exists( 'commenter_print_notices' ) ) {

	function commenter_print_notices() {

		if ( $notices = Commenter()->_session->get( 'notices', array() ) ) {
			ob_start();
			foreach ( $notices as $type => $messages ) {
				commenter_get_template( 'notices/' . $type . '.php', array( 'messages' => $messages ), true );
			}
			$html = ob_get_clean();
			echo $html;
			Commenter()->_session->set( 'notices', array() );
		}
 
	}

} 

if ( ! function_exists( 'commenter_print_notice' ) ) {

	function commenter_print_notice( $type = 'success', $message ) {
		if ( 'success' === $type ) {
			$message = apply_filters( 'commenter_add_message', $message );
		}

		commenter_get_template( "notices/{$type}.php", array(
			'messages' => array( apply_filters( 'commenter_add_message_' . $type, $message ) )
		) );
	}
 
}

if( ! function_exists( 'commenter_get_current_user' ) ) {

	function commenter_get_current_user() {

		if( is_user_logged_in() ) {

			$user_id = get_current_user_id();

			return new Commenter_User( $user_id );

		} else {

			$commenter = wp_get_current_commenter();
			$display_name = ( ! empty( $commenter[ 'comment_author' ] ) ) ? $commenter[ 'comment_author' ] : __( 'Anonymous', 'commenter' );
			$email = $commenter[ 'comment_author_email' ];
			$url = $commenter[ 'comment_author_url' ];

			$user = array(

				'display_name'	=> $display_name,
				'email'			=> $email,
				'url'			=> $url

			);

			return new Commenter_User( $user );

		}

		return false;

	}

}

if( ! function_exists( 'commenter_has_commenter' ) ) {

	function commenter_has_commenter() {

		$commenter = commenter_get_current_user();

		if( $commenter->get_id() > 0 || ! empty( $commenter->get_email() ) ) {

			return true;

		}

		return false;	

	}

}

if( ! function_exists( 'commenter_get_discussion' ) ) {

	function commenter_get_discussion() {

		$discussion = null;

		if( property_exists( Commenter(), 'discussion' ) && ! is_null( Commenter()->discussion ) )
			$discussion = Commenter()->discussion;

		if( is_null( $discussion ) )
			$discussion = new Commenter_Discussion();

		return $discussion;

	}

}

if( ! function_exists( 'commenter_get_current_thread' ) ) {

	function commenter_get_current_thread() {

		return commenter_get_discussion()->get_current_thread();

	}

}

if( ! function_exists( 'commenter_get_thread' ) ) {

	function commenter_get_threads() {

		return commenter_get_discussion()->get_threads();

	}

}

if( ! function_exists( 'commenter_get_thread' ) ) {

	function commenter_has_thread( $thread ) {

		return commenter_get_discussion()->has_thread( $thread );

	}

}

if( ! function_exists( 'commenter_get_thread' ) ) {

	function commenter_get_thread( $thread ) {

		return commenter_get_discussion()->get_thread( $thread );

	}

}

if( ! function_exists( 'commenter_get_discussion_settings' ) ) {

	function commenter_get_discussion_settings() {

		return commenter_get_discussion()->get_settings();

	}

}

if( ! function_exists( 'commenter_get_discussion_setting' ) ) {

	function commenter_get_discussion_setting( $setting ) {

		return commenter_get_discussion_settings()->get_setting( $setting );

	}

}

if( ! function_exists( 'commenter_get_comment_id_fields' ) ) {

	function commenter_get_comment_id_fields( $id = 0 ) {

	    if ( empty( $id ) ) {
	        $id = get_the_ID();
	    }
	 
	    $replytoid = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : 0;
	    $result    = "<input type='hidden' name='comment_post_ID' value='$id' id='comment_post_ID' />\n";
	    $result   .= "<input type='hidden' name='comment_parent' id='comment_parent' value='$replytoid' />\n";
	 
	    /**
	     * Filters the returned comment id fields.
	     *
	     * @since 3.0.0
	     *
	     * @param string $result    The HTML-formatted hidden id field comment elements.
	     * @param int    $id        The post ID.
	     * @param int    $replytoid The id of the comment being replied to.
	     */
	    return apply_filters( 'comment_id_fields', $result, $id, $replytoid );		

	}

}

if( ! function_exists( 'commenter_comment_id_fields' ) ) {

	function commenter_comment_id_fields( $id = 0 ) {

		echo commenter_get_comment_id_fields( $id );

	}

}

if( ! function_exists( 'commenter_get_comment' ) ) {

	function commenter_get_comment( &$comment = null, $args = array(), $depth = 0, $output = OBJECT ) {

		$_comment = null;

	    if ( empty( $comment ) && isset( $GLOBALS['comment'] ) ) {
	        $comment = $GLOBALS['comment'];
	    }

	    if ( ! array_key_exists( 'max_depth', $args ) || '' === $args['max_depth'] ) {
	        if ( get_option( 'thread_comments' ) ) {
	            $args['max_depth'] = get_option( 'thread_comments_depth' );
	        } else {
	            $args['max_depth'] = -1;
	        }
	    }
	 
		if ( is_numeric( $comment ) && $comment > 0 ) {
			$_comment = new Commenter_Comment( get_comment( $comment ), $args, $depth );
		} elseif ( $comment instanceof Commenter_Comment ) {
			$_comment = $comment;
		} elseif ( $comment instanceof WP_Comment && ! empty( $comment->comment_ID ) ) {
			$_comment = new Commenter_Comment( $comment, $args, $depth );
		}
	 
	    if ( ! $_comment ) {
	        return null;
	    }
	 
	    /**
	     * Fires after a comment is retrieved.
	     *
	     * @since 2.3.0
	     *
	     * @param mixed $_comment Comment data.
	     */
	    $_comment = apply_filters( 'commenter_get_comment', $_comment );
	 
	    if ( $output == OBJECT ) {
	        return $_comment;
	    } elseif ( $output == ARRAY_A ) {
	        return $_comment->to_array();
	    } elseif ( $output == ARRAY_N ) {
	        return array_values( $_comment->to_array() );
	    }

	    return $_comment;

	}

}

if( ! function_exists( 'commenter_comment' ) ) {

	function commenter_comment( $comment = 0, $args = array(), $depth = 0 ) {

		$comment = commenter_get_comment( $comment, $args, $depth );
		$defaults = apply_filters( 'commenter_comment_default_args', array( 

			'echo_comment'	=> true

		) );

		$args = wp_parse_args( $args, $defaults );

		if( (bool) $args['echo_comment'] ) {

			$comment->render( $args['echo_comment'] );

		} else {

			return $comment->render( $args['echo_comment'] );

		}	

	}

}

if( ! function_exists( 'commenter_get_comment_permalink' ) ) {

	function commenter_get_comment_permalink( $comment, $args = array() ) {

		if ( is_numeric( $comment ) && $comment > 0 ) {
			$comment = new Commenter_Comment( get_comment( $comment ) );
		} elseif ( $comment instanceof Commenter_Comment ) {
			$comment = $comment;
		} elseif ( $comment instanceof WP_Comment && ! empty( $comment->comment_ID ) ) {
			$comment = new Commenter_Comment( $comment );
		} else {
			return false;
		}

		$comment_id = $comment->get_id();

		$defaults = apply_filters( 'commenter_get_comment_permalink_default_args', array(

			'cid'			=> $comment_id,
			'cpage'			=> get_query_var( 'cpage', commenter_get_page_of_comment( $comment_id ) ),
			'cthread'		=> commenter_get_cthread_value(),
			'csort'			=> commenter_get_csort_value(),
			'caction'		=> false,
			'link'			=> get_permalink(),
			'nonce'			=> array(),
			'target'		=> ''

		) );

		$args = wp_parse_args( $args, $defaults );

		$cid = $args['cid'];
		$cpage = $args['cpage'];
		$csort = $args['csort'];
		$caction = $args['caction'];
		$cthread = $args['cthread'];
		$link = $args['link'];
		$target = $args['target'];
		$link =  ( ! empty( $target ) ) ? $link . '#' . $target : $link;
		$query_args =  array(

			'cid'		=> $cid,
			'cpage'		=> $cpage,
			'cthread'	=> $cthread,
			'caction'	=> $caction,
			'csort'		=> $csort

		);

		if( isset( $args['nonce'] ) && ( is_array( $args['nonce'] ) && ! empty( $args['nonce'] ) ) ) {

			$nonce_name = $args['nonce'][0];
			$nonce_value = $args['nonce'][1];
			$query_args[ $nonce_name ] = $nonce_value;

		}

		$comment_permalink = add_query_arg( $query_args, $link );

		return apply_filters( 'commenter_get_comment_permalink', $comment_permalink );		

	}

}


if( ! function_exists( 'commenter_go_to_comment' ) ) {

	function commenter_go_to_comment( $comment, $args = array() ) {

		if ( is_numeric( $comment ) && $comment > 0 ) {
			$comment = new Commenter_Comment( get_comment( $comment ) );
		} elseif ( $comment instanceof Commenter_Comment ) {
			$comment = $comment;
		} elseif ( $comment instanceof WP_Comment && ! empty( $comment->comment_ID ) ) {
			$comment = new Commenter_Comment( $comment );
		} else {
			return false;
		}

		$comment_permalink = commenter_get_comment_permalink( $comment, $args );

		wp_safe_redirect( $comment_permalink );

		die;

	}

}

if ( ! function_exists( 'commenter_get_page_of_comment' ) ) {

	/**
	 * Calculate what page number a comment will appear on for comment paging.
	 *
	 * @since 2.7.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int   $comment_ID Comment ID.
	 * @param array $args {
	 *      Array of optional arguments.
	 *      @type string     $type      Limit paginated comments to those matching a given type. Accepts 'comment',
	 *                                  'trackback', 'pingback', 'pings' (trackbacks and pingbacks), or 'all'.
	 *                                  Default is 'all'.
	 *      @type int        $per_page  Per-page count to use when calculating pagination. Defaults to the value of the
	 *                                  'comments_per_page' option.
	 *      @type int|string $max_depth If greater than 1, comment page will be determined for the top-level parent of
	 *                                  `$comment_ID`. Defaults to the value of the 'thread_comments_depth' option.
	 * } *
	 * @return int|null Comment page number or null on error.
	 */
	function commenter_get_page_of_comment( $comment_ID, $args = array() ) {

		$current_thread = commenter_get_current_thread();
		$page = $current_thread->get_page_of_comment( $comment_ID );

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
		return apply_filters( 'commenter_get_page_of_comment', (int) $page, $args, $comment_ID );
	}

}

function commenter_get_comment_like_count( $comment, $args = array() ) {

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();
	$like_count = $comment->get_like_count();

	return apply_filters( 'commenter_get_comment_like_count', $like_count, $comment, $args );

}

function commenter_like_comment( $comment, $args = array() ) {

	global $wpdb;

	$defaults = apply_filters( 'commenter_like_comment_default_args', array(

		'blog_id'	=> get_current_blog_id(),
		'user_id'	=> get_current_user_id()

	) );

	$args = wp_parse_args( $args, $defaults );

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();
	$blog_id = $args['blog_id'];
	$user_id = $args['user_id'];
	$commenter_id = $comment->user_id;
	$post_id = $comment->get_post_id();
	$user_likes_cache_key = 'commenter_comment_likes_' . $blog_id . $user_id . $post_id;
	$user_likes_cache = commenter_session_get( $user_likes_cache_key, array() );

	if( ! $comment_id || ! $user_id || ! $blog_id ) {
		return false;
	}

	$likes_table = $wpdb->base_prefix . 'commenter_likes';

	$comment_liked = $wpdb->insert(
		$likes_table,
		array(
				'comment_id'	=> $comment_id,
				'user_id'		=> $user_id,
				'blog_id'		=> $blog_id,
				'commenter_id'	=> $commenter_id,
		)
	);

	if( $comment_liked ) {

		if( is_array( $user_likes_cache ) ) {

			$user_likes_cache[ $comment_id ] = true;

			commenter_session_set( $user_likes_cache_key, $user_likes_cache );

		}

		$like_count = $wpdb->get_var( $wpdb->prepare(
			"
				SELECT 	COUNT(*) 
			 	FROM 	$likes_table 
			 	WHERE 	comment_id = %s
			 			AND blog_id = %s
			",
			$comment_id,
			$blog_id

		) );

		update_comment_meta( $comment_id, 'commenter_like_count', $like_count );

		return $like_count;

	}

	return false;

}

function commenter_unlike_comment( $comment, $args = array() ) {

	global $wpdb;

	$defaults = apply_filters( 'commenter_unlike_comment_default_args', array(

		'blog_id'	=> get_current_blog_id(),
		'user_id'	=> get_current_user_id()

	) );

	$args = wp_parse_args( $args, $defaults );

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();
	$blog_id = $args['blog_id'];
	$user_id = $args['user_id'];
	$commenter_id = $comment->user_id;
	$post_id = $comment->get_post_id();
	$user_likes_cache_key = 'commenter_comment_likes_' . $blog_id . $user_id . $post_id;
	$user_likes_cache = commenter_session_get( $user_likes_cache_key, array() );

	if( ! $comment_id || ! $user_id || ! $blog_id ) {
		return false;
	}

	$likes_table = $wpdb->base_prefix . 'commenter_likes';
	$like_where = array( 'comment_id' => $comment_id, 'user_id' => $user_id, 'blog_id' => $blog_id );

	$like_deleted = $wpdb->delete( $likes_table, $like_where, '%d' );

	if( $like_deleted ) {

		if( is_array( $user_likes_cache ) ) {

			$user_likes_cache[ $comment_id ] = false;

			commenter_session_set( $user_likes_cache_key, $user_likes_cache );

		}

		$like_count = $wpdb->get_var( $wpdb->prepare(
			"
				SELECT 	COUNT(*) 
			 	FROM 	$likes_table 
			 	WHERE 	comment_id = %s
			 			AND blog_id = %s
			",
			$comment_id,
			$blog_id

		) );

		if( $like_count > 0 ) {

			update_comment_meta( $comment_id, 'commenter_like_count', $like_count );

			return $like_count;

		} else {

			delete_comment_meta( $comment_id, 'commenter_like_count' );

			return true;

		}

		
		return true;
		

	}

	return false;

}

function commenter_current_user_liked( $comment, $args = array() ) {

	if( is_user_logged_in() ) {

		global $wpdb;

		$defaults = array(

			'blog_id'	=> get_current_blog_id(),
			'user_id'	=> get_current_user_id()

		);

		$args = wp_parse_args( $args, $defaults );

		$likes_table = $wpdb->base_prefix . 'commenter_likes';
		$comment_id = $comment->get_id();
		$user_id = $args['user_id'];
		$blog_id = $args['blog_id'];
		$post_id = $comment->get_post_id();
		$user_likes_cache_key = 'commenter_comment_likes_' . $blog_id . $user_id . $post_id;
		$user_likes_cache = commenter_session_get( $user_likes_cache_key, array() );
		$like_found = false;

		if( ! is_array( $user_likes_cache ) )
			$user_likes_cache = array();

		if( array_key_exists( $comment_id, $user_likes_cache ) && is_bool( $user_likes_cache[ $comment_id ] ) ) {

			$like_found = $user_likes_cache[ $comment_id ];

			return $like_found;

		}

		$like_found = $wpdb->get_results( $wpdb->prepare( 

			"
				SELECT comment_id, user_id, blog_id, commenter_id
				FROM   $likes_table
			 	WHERE 	comment_id = %d
			 			AND user_id = %d
			 			AND blog_id = %d
			",
			$comment_id,
			$user_id,
			$blog_id

		 ), OBJECT_K );

		if( empty( $like_found ) ) {

			$user_likes_cache[ $comment_id ] = false;

			commenter_session_set( $user_likes_cache_key, $user_likes_cache );

			return $user_likes_cache[ $comment_id ];

		}

		if( $user_id == $like_found[ $comment_id ]->user_id ) {

			$user_likes_cache[ $comment_id ] = true;

			commenter_session_set( $user_likes_cache_key, $user_likes_cache );

			return $user_likes_cache[ $comment_id ];
		} 

		return false;

	}

	return false;

}

function commenter_get_comment_like_placeholder( $comment, $text = null, $args = array() ) {

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();

	// if( ! $comment->has_action( 'like' ) )
	// 	return null;

	$like_count = get_comment_meta( $comment_id, 'commenter_like_count', true );
	$like_count = ( $like_count > 0 ) ? $like_count : '';
	$text = ( ! empty( $text ) ) ? $text : '<span class="heart not-interactive" ></span>';

	$like_comment_button = sprintf(
            __('%s', 'commenter'),
            '<span id="like-comment-'.esc_attr( $comment_id ).'" class="like-btn not-interactive"><span class="like-count not-interactive">'.esc_html( $like_count ).'</span>'. $text .'</span>'
        );

	return $like_comment_button;

}

function commenter_get_comment_unlike_link( $comment, $text = false, $args = array() ) {

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();
	$commenter = commenter_get_current_user();

	if( ! $comment->has_action( 'unlike' ) )
		return null;

	$like_count = commenter_get_comment_like_count( $comment );

	if( empty( $like_count ) || $like_count == 0 ) {

		$like_count = '';

	}

	$unlike_action = $comment->get_action( 'unlike' );
	$unlike_url = $unlike_action->get_url( $comment );
	$unlike_name = $unlike_action->get_name();
	$unlike_title = ( isset( $text ) ) ? $text : $unlike_action->get_title();
	
	$unlike_comment_button = sprintf(
            __('%s', 'commenter'),
            '<span class="'.esc_attr( $unlike_name ).'-btn heart-btn">
            	<span class="like-count">'.esc_html( $like_count ).'</span>
            	<a id="like-comment-' . esc_attr( $comment_id ). '" href="' . esc_url( $unlike_url ) . '" aria-label="'. esc_attr( $unlike_title ) .'" class="like-comment-' . esc_attr( $comment_id ) . ' heart btn-link btn-link-secondary '. esc_attr( $unlike_name ) .'-action-link" data-comment-id="'. esc_attr( $comment_id ) .'">
            		<span class="like-unlike-title">' . esc_html( $unlike_title ) . '</span>
            		<span class="animated-heart"></span>
            	</a>
            </span>'
        );

	return apply_filters( 'commenter_get_comment_unlike_link', $unlike_comment_button, $comment, $args );

}

function commenter_get_comment_like_link( $comment, $text = false, $args = array() ) {

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();
	$commenter = commenter_get_current_user();

	if( ! $comment->has_action( 'like' ) )
		return null;

	$like_count = commenter_get_comment_like_count( $comment );

	if( empty( $like_count ) || $like_count == 0 ) {

		$like_count = '';

	}

	$like_action = $comment->get_action( 'like' );
	$like_url = $like_action->get_url( $comment );
	$like_name = $like_action->get_name();
	$like_title = ( isset( $text ) ) ? $text : $like_action->get_title();
	
	$like_comment_button = sprintf(
            __('%s', 'commenter'),
            '<span class="'.esc_attr( $like_name ).'-btn heart-btn">
            	<span class="like-count">'.esc_html( $like_count ).'</span>
            	<a id="like-comment-' . esc_attr( $comment_id ). '" href="' . esc_url( $like_url ) . '" aria-label="'. esc_attr( $like_title ) .'" class="like-comment-' . esc_attr( $comment_id ) . ' heart btn-link btn-link-secondary '. esc_attr( $like_name ) .'-action-link" data-comment-id="'. esc_attr( $comment_id ) .'">
            		<span class="like-unlike-title">' . esc_html( $like_title ) . '</span>
            		<span class="animated-heart"></span>
            	</a>
            </span>'
        );

	return apply_filters( 'commenter_get_comment_like_link', $like_comment_button, $comment, $args );

}

function commenter_comment_like_link( $comment, $text = false, $args = array() ) {

	$like_comment_button = commenter_get_comment_like_placeholder( $comment );

	if( is_user_logged_in() ) {

		if( commenter_current_user_liked( $comment ) ) {

			$like_comment_button = commenter_get_comment_unlike_link( $comment, $text, $args );

		} else {

			$like_comment_button = commenter_get_comment_like_link( $comment, $text, $args );

		}
		
	}

	echo $like_comment_button;

}

if( ! function_exists('commenter_get_comment_reply_link') ) {

	/**
	 * Retrieve HTML content for reply to comment link.
	 *
	 * @since 2.7.0
	 * @since 4.4.0 Added the ability for `$comment` to also accept a WP_Comment object.
	 *
	 * @param array $args {
	 *     Optional. Override default arguments.
	 *
	 *     @type string $add_below  The first part of the selector used to identify the comment to respond below.
	 *                              The resulting value is passed as the first parameter to addComment.moveForm(),
	 *                              concatenated as $add_below-$comment->comment_ID. Default 'comment'.
	 *     @type string $respond_id The selector identifying the responding comment. Passed as the third parameter
	 *                              to addComment.moveForm(), and appended to the link URL as a hash value.
	 *                              Default 'respond'.
	 *     @type string $reply_text The text of the Reply link. Default 'Reply'.
	 *     @type string $login_text The text of the link to reply if logged out. Default 'Log in to Reply'.
	 *     @type int    $max_depth  The max depth of the comment tree. Default 0.
	 *     @type int    $depth      The depth of the new comment. Must be greater than 0 and less than the value
	 *                              of the 'thread_comments_depth' option set in Settings > Discussion. Default 0.
	 *     @type string $before     The text or HTML to add before the reply link. Default empty.
	 *     @type string $after      The text or HTML to add after the reply link. Default empty.
	 * }
	 * @param int|WP_Comment $comment Comment being replied to. Default current comment.
	 * @param int|WP_Post    $post    Post ID or WP_Post object the comment is going to be displayed on.
	 *                                Default current post.
	 * @return string|false|null Link to show comment form, if successful. False, if comments are closed.
	 */
	function commenter_get_comment_reply_link( $args = array(), $comment = null, $post = null ) {
		$defaults = array(
			'add_below'     => 'comment',
			'respond_id'    => 'respond',
			'reply_text'    => __( 'Reply' ),
			/* translators: Comment reply button text. %s: Comment author name. */
			'reply_to_text' => __( 'Reply to %s' ),
			'login_text'    => __( 'Log in to Reply' ),
			'max_depth'     => 0,
			'depth'         => 0,
			'before'        => '',
			'after'         => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
			return;
		}

		$comment = commenter_get_comment( $comment );

		if ( empty( $comment ) ) {
			return;
		}

		if ( empty( $post ) ) {
			$post = $comment->comment_post_ID;
		}

		$post = get_post( $post );
		$reply_url = $comment->get_action_url( 'reply', array( 'cpage' => get_query_var( 'cpage' ) ) );

		if ( ! comments_open( $post->ID ) ) {
			return false;
		}

		/**
		 * Filters the comment reply link arguments.
		 *
		 * @since 4.1.0
		 *
		 * @param array      $args    Comment reply link arguments. See get_comment_reply_link()
		 *                            for more information on accepted arguments.
		 * @param WP_Comment $comment The object of the comment being replied to.
		 * @param WP_Post    $post    The WP_Post object.
		 */
		$args = apply_filters( 'comment_reply_link_args', $args, $comment, $post );

		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			$link = sprintf(
				'<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
				esc_url( wp_login_url( get_permalink() ) ),
				$args['login_text']
			);
		} else {
			$data_attributes = array(
				'comment-id'      => $comment->comment_ID,
				'post-id'         => $post->ID,
				'below-element'   => $args['add_below'] . '-' . $comment->comment_ID,
				'respond-element' => $args['respond_id'],
			);

			$data_attribute_string = '';

			foreach ( $data_attributes as $name => $value ) {
				$data_attribute_string .= " data-${name}=\"" . esc_attr( $value ) . '"';
			}

			$data_attribute_string = trim( $data_attribute_string );

			$link = sprintf(
				"<a rel='nofollow' class='comment-reply-link' href='%s' %s aria-label='%s'>%s</a>",
				esc_url( $reply_url ) . '#' . $args['respond_id'],
				$data_attribute_string,
				esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
				$args['reply_text']
			);
		}

		/**
		 * Filters the comment reply link.
		 *
		 * @since 2.7.0
		 *
		 * @param string  $link    The HTML markup for the comment reply link.
		 * @param array   $args    An array of arguments overriding the defaults.
		 * @param object  $comment The object of the comment being replied.
		 * @param WP_Post $post    The WP_Post object.
		 */
		return apply_filters( 'commenter_comment_reply_link', $args['before'] . $link . $args['after'], $args, $comment, $post );
	}

}

/** 
 * Displays the HTML content for reply to comment link.
 *
 * @since 1.0.0
 *
 * @see get_comment_reply_link()
 *
 * @param array       $args    Optional. Override default options.
 * @param int         $comment Comment being replied to. Default current comment.
 */
function commenter_comment_reply_link( $args = array(), $comment = null, $post = null ) {
	echo commenter_get_comment_reply_link( $args, $comment, $post );
}

function commenter_get_flag_count( $comment ) {

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->get_id();

	if( ! $comment->has_action( 'flag' ) || ! $comment->has_action( 'unflag' ) ) {

		return null;

	}

	$flag_count = $comment->get_flag_count();

	return apply_filters( 'commenter_get_comment_flag_count', $flag_count, $comment, $args );

}

function commenter_flag_comment( $comment, $args = array() ) {

	global $wpdb;

	$defaults = array(

		'blog_id'	=> get_current_blog_id(),
		'user_id'	=> get_current_user_id()

	);

	$args = wp_parse_args( $args, $defaults );

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->comment_ID;
	$blog_id = $args['blog_id'];
	$user_id = $args['user_id'];
	$commenter_id = $comment->user_id;
	$post_id = $comment->get_post_id();
	$user_flags_cache_key = 'commenter_comment_flags_' . $blog_id . $user_id . $post_id;
	$user_flags_cache = commenter_session_get( $user_flags_cache_key, array() );

	if( ! $comment_id || ! $user_id || ! $blog_id ) {
		return false;
	}

	$flags_table = $wpdb->base_prefix . 'commenter_flags';

	$comment_flagged = $wpdb->insert(
		$flags_table,
		array(
				'comment_id'	=> $comment_id,
				'user_id'		=> $user_id,
				'blog_id'		=> $blog_id,
				'commenter_id'	=> $commenter_id,
		)
	);

	if( $comment_flagged ) {

		if( is_array( $user_flags_cache ) ) {

			$user_flags_cache[ $comment_id ] = true;

			commenter_session_set( $user_flags_cache_key, $user_flags_cache );

		}

		$flag_count = $wpdb->get_var( $wpdb->prepare(
			"
				SELECT 	COUNT(*) 
			 	FROM 	$flags_table 
			 	WHERE 	comment_id = %s
			 			AND blog_id = %s
			",
			$comment_id,
			$blog_id

		) );


		update_comment_meta( $comment_id, 'commenter_flag_count', $flag_count );

		return true;

	}

}

function commenter_unflag_comment( $comment, $args = array() ) {

	global $wpdb;

	$defaults = array(

		'blog_id'	=> get_current_blog_id(),
		'user_id'	=> get_current_user_id()

	);

	$args = wp_parse_args( $args, $defaults );

	$comment = commenter_get_comment( $comment );
	$comment_id = $comment->comment_ID;
	$blog_id = $args['blog_id'];
	$user_id = $args['user_id'];
	$commenter_id = $comment->user_id;
	$post_id = $comment->get_post_id();
	$user_flags_cache_key = 'commenter_comment_flags_' . $blog_id . $user_id . $post_id;
	$user_flags_cache = commenter_session_get( $user_flags_cache_key, array() );

	if( ! $comment_id || ! $user_id || ! $blog_id ) {
		return false;
	}

	$flags_table = $wpdb->base_prefix . 'commenter_flags';
	$flag_where = array( 'comment_id' => $comment_id, 'user_id' => $user_id, 'blog_id' => $blog_id );

	$flag_deleted = $wpdb->delete( $flags_table, $flag_where, '%d' );

	if( $flag_deleted ) {

		if( is_array( $user_flags_cache ) ) {

			$user_flags_cache[ $comment_id ] = false;

			commenter_session_set( $user_flags_cache_key, $user_flags_cache );

		}

		$flag_count = $wpdb->get_var( $wpdb->prepare(
			"
				SELECT 	COUNT(*) 
			 	FROM 	$flags_table 
			 	WHERE 	comment_id = %s
			 			AND blog_id = %s
			",
			$comment_id,
			$blog_id

		) );


		update_comment_meta( $comment_id, 'commenter_flag_count', $flag_count );

		return true;

	}

}


if( ! function_exists('commenter_current_user_flagged') ) {

	function commenter_current_user_flagged( $comment, $args = array() ) {

		if( is_user_logged_in() ) {

			global $wpdb;

			$defaults = array(

				'blog_id'	=> get_current_blog_id(),
				'user_id'	=> get_current_user_id()

			);

			$args = wp_parse_args( $args, $defaults );

			$flags_table = $wpdb->base_prefix . 'commenter_flags';
			$comment_id = $comment->get_id();
			$user_id = $args['user_id'];
			$blog_id = $args['blog_id'];
			$post_id = $comment->get_post_id();
			$user_flags_cache_key = 'commenter_comment_flags_' . $blog_id . $user_id . $post_id;
			$user_flags_cache = commenter_session_get( $user_flags_cache_key, array() );
			$flag_found = false;

			if( ! is_array( $user_flags_cache ) )
				$user_flags_cache = array();

			if( array_key_exists( $comment_id, $user_flags_cache ) && is_bool( $user_flags_cache[ $comment_id ] ) ) {

				$flag_found = $user_flags_cache[ $comment_id ];

				return $flag_found;

			}

			$flag_found = $wpdb->get_results( $wpdb->prepare( 

				"
					SELECT comment_id, user_id, blog_id, commenter_id
					FROM   $flags_table
				 	WHERE 	comment_id = %d
				 			AND user_id = %d
				 			AND blog_id = %d
				",
				$comment_id,
				$user_id,
				$blog_id

			 ), OBJECT_K );

			if( empty( $flag_found ) ) {

				$user_flags_cache[ $comment_id ] = false;

				commenter_session_set( $user_flags_cache_key, $user_flags_cache );

				return $user_flags_cache[ $comment_id ];

			}

			if( $user_id == $flag_found[ $comment_id ]->user_id ) {

				$user_flags_cache[ $comment_id ] = true;

				commenter_session_set( $user_flags_cache_key, $user_flags_cache );

				return $user_flags_cache[ $comment_id ];
			} 

			return false;

		}

		return false;

	}

}


if( ! function_exists('commenter_paginate_comments_links') ) {

	function commenter_paginate_comments_links( $args = array() ) {

	   global $wp_rewrite;
	 
	    if ( ! is_singular() ) {
	        return;
	    }

	    $current_thread = commenter_get_current_thread();
	    $page = get_query_var( 'cpage', $current_thread->get_current_page() );
	    if ( ! $page ) {
	        $page = 1;
	    }
	    $max_page = $current_thread->get_page_count();
	    $defaults = array(
	        'base'			=> add_query_arg( 'cpage', '%#%' ),
	        'format'       	=> '',
	        'total'        	=> $max_page,
	        'current'      	=> $page,
	        'echo'         	=> true,
	        'type'         	=> 'plain',
	        'add_fragment' 	=> '#comments',
	    );
	    if ( $wp_rewrite->using_permalinks() ) {
	        $defaults['base'] = user_trailingslashit( trailingslashit( get_permalink() ) . $wp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
	    }
	 
	    $args       = wp_parse_args( $args, $defaults );
	    $page_links = paginate_links( $args );
	 
	    if ( $args['echo'] && 'array' !== $args['type'] ) {
	        echo $page_links;
	    } else {
	        return $page_links;
	    }

	}

}

if( ! function_exists('commenter_set_reply_comment_form') ) {

	function commenter_set_reply_comment_form( $parent_comment, $args = array() ) {

		$defaults = apply_filters( 'commenter_set_reply_comment_form_default_args', array(

			'current_user'		=> commenter_get_current_user(),
			'current_thread'	=> commenter_get_current_thread(),
			'current_page'		=> get_query_var( 'cpage' ),
			'action'			=> 'reply',

		) );

		$args 			= wp_parse_args( $args, $defaults );
		$comment 		= commenter_get_comment( $parent_comment );
		$current_user 	= $args['current_user'];
		$current_thread = $args['current_thread'];
		$current_page 	= $args['current_page'];
		$action 		= $args['action'];

		if( ! $comment instanceof Commenter_Comment )
			return; 
		
		if( ! $current_user instanceof Commenter_User )
			return;

		if( ! $current_thread instanceof Commenter_Abstract_Thread )
			return;

		if( ! $action instanceof Commenter_Action )
			$action = $current_user->get_action( 'GET', $action );

		if( empty( $action ) )
			return;

		remove_action( 'commenter_render_'. $current_thread->slug .'_comment_form', array( $current_thread, 'render_comment_form' ) );

		$comment_form = $current_thread->get_comment_form();
		$comment_form->set_is_cancellable( true );
		$comment_form->set_parent_comment( $comment );
		$comment_form->set_comment_parent_id( $comment->get_id() );
		$comment_form->set_action( $action->get_slug() );

		if( $action instanceof Commenter_Action ) {
			$comment_form->set_form_action( $action->get_url( $comment, array( 'cid' => $comment->get_id(), 'fragment' => '#respond' ) ) );
		}

		$context_slug = $current_thread->get_slug() . '_comment';
		
		add_action( 'commenter_after_comment_' . $comment->get_id(), array( $current_thread, 'render_comment_form' ), 90, 1 );		

	}

}

if( ! function_exists('commenter_get_discussion_data') ) {

	function commenter_get_discussion_data() {

		$discussion = commenter_get_discussion();

		$discussion_data = $discussion->get_data();

		return apply_filters( 'commenter_get_discussion_data', $discussion_data );

	}

}

if( ! function_exists('commenter_get_cthread_value') ) {

	function commenter_get_cthread_value( $default = null ) {

		$cthread = null;

		if( isset( $_REQUEST['cthread'] ) && ! empty( $_REQUEST['cthread'] ) )
			$cthread = trim( $_REQUEST['cthread'] );

		if( empty( $cthread ) && ( ! empty( $default ) && is_string( $default ) ) )
			$cthread = trim( $default );

		if( ! $cthread || ! is_string( $cthread ) )
			$cthread = commenter_get_option( 'discussion_default_thread' );

		return apply_filters( 'commenter_get_cthread_value', $cthread );

	}

}

if( ! function_exists('commenter_get_cid_value') ) {

	function commenter_get_cid_value( $default = null ) {

		$cid = null;

		if( isset( $_REQUEST['cid'] ) && ! empty( $_REQUEST['cid'] ) )
			$cid = absint( (int) trim( $_REQUEST['cid'] ) );

		if( empty( $cid ) && ( ! empty( $default ) && is_numeric( $default ) ) )
			$cid = absint( (int) trim( $default ) );

		if( ! $cid || ! is_numeric( $cid ) )
			$cid = 0;

		return apply_filters( 'commenter_get_cid_value', absint( $cid ) );

	}

}

if( ! function_exists('commenter_get_caction_value') ) {

	function commenter_get_caction_value( $default = null ) {

		$caction = null;

		if( isset( $_REQUEST ) && array_key_exists( 'caction', $_REQUEST ) ) 
			$caction = trim( $_REQUEST['caction'] );

		if( ! is_string( $caction ) && ( ! empty( $default ) && is_string( $default ) ) )
			$caction = trim( $default );

		if(! $caction || ! is_string( $caction ) )
			$caction = '';

		return apply_filters( 'commenter_get_caction_value', $caction );

	}

}


if( ! function_exists('commenter_get_csort_value') ) {

	function commenter_get_csort_value( $default = null ) {

		$csort = null;

		if( isset( $_REQUEST['csort'] ) && ! empty( $_REQUEST['csort'] ) )
			$csort = trim( $_REQUEST['csort'] );

		if( empty( $csort ) && ( ! empty( $default ) && is_string( $default ) ) )
			$csort = trim( $default );

		if( ! $csort || ! is_string( $csort ) )
			$csort = commenter_get_option( 'discussion_default_sorting' );

		return apply_filters( 'commenter_get_csort_value', $csort );

	}

}

if( ! function_exists('commenter_nonce_field') ) {

	function commenter_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		$name        = esc_attr( $name );
	    $nonce_field = '<input type="hidden" name="' . $name . '" value="' . wp_create_nonce( $action ) . '" />';
	 
	    if ( $referer ) {
	        $nonce_field .= wp_referer_field( false );
	    }
	 
	    if ( $echo ) {
	        echo $nonce_field;
	    }
	 
	    return $nonce_field;
	}

}

