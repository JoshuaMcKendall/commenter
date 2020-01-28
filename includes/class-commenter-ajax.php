<?php
/**
 * Commenter Ajax class
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter/includes/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * This class defines all code necessary to handle AJAX requests.
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

/**
 * Ajax Process
 */
class Commenter_Ajax {

	public function __construct() {

		$actions = apply_filters( 'commenter_ajax_actions', array(

			'get_discussion' => array( 

				'must_login'	=> false,
				'callback'		=> array( $this, 'get_discussion' )

			),
			'get_thread' => array( 

				'must_login'	=> false,
				'callback'		=> array( $this, 'get_thread' )

			),
			'get_comments' => array( 

				'must_login'	=> false,
				'callback'		=> array( $this, 'get_comments' )

			),
			'do_action' => array(

				'must_login'	=> false,
				'callback'		=> array( $this, 'do_action' )

			)

		) );

		foreach ( $actions as $slug => $action ) {

			if( ! array_key_exists( 'must_login', $action ) )
				continue;

			if( ! is_bool( $action['must_login'] ) )
				continue;

			if( ! array_key_exists( 'callback', $action ) )
				continue;

			if( ! is_callable( $action['callback'] ) )
				continue;

			add_action( 'wp_ajax_' . $slug, $action['callback'] );

			if ( $action['must_login'] ) {

				add_action( 'wp_ajax_nopriv_' . $slug, array( $this, 'must_login' ) );

			} else {

				add_action( 'wp_ajax_nopriv_' . $slug, $action['callback'] );			

			}

		}
	}

	public function get_discussion() {

		$id = 0;
		$current_thread = '';
		$current_sorting = '';
		$current_page = 1;

		if( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) )
			$id = (int) $_REQUEST['post_id'];

		if( isset( $_REQUEST['cthread'] ) && is_string( $_REQUEST['cthread'] ) )
			$current_thread = commenter_get_cthread_value();

		if( isset( $_REQUEST['csort'] ) && is_string( $_REQUEST['csort'] ) )
			$current_sorting = commenter_get_csort_value();

		if( isset( $_REQUEST['cpage'] ) && is_numeric( $_REQUEST['cpage'] ) )
			$current_page = (int) $_REQUEST['cpage']; 

		$discussion = new Commenter_Discussion( array( 

			'id' 				=> $id, 
			'current_sorting' 	=> $current_sorting,
			'current_thread'	=> $current_thread,
			'current_page'		=> $current_page

		) );

		wp_send_json( array(

			'status'			=> 'success',	
			'discussion'		=> $discussion->render( false ),
			'discussion_data'	=> $discussion->get_data()

		) );

		die();		

	}

	public function get_thread() {

		$id = 0;
		$current_thread = '';
		$current_sorting = '';
		$current_page = 1;

		if( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) )
			$id = (int) $_REQUEST['post_id'];

		if( isset( $_REQUEST['cthread'] ) && is_string( $_REQUEST['cthread'] ) )
			$current_thread = commenter_get_cthread_value();

		if( isset( $_REQUEST['csort'] ) && is_string( $_REQUEST['csort'] ) )
			$current_sorting = commenter_get_csort_value();

		if( isset( $_REQUEST['cpage'] ) && is_numeric( $_REQUEST['cpage'] ) )
			$current_page = (int) $_REQUEST['cpage']; 

		$discussion = new Commenter_Discussion( array( 

			'id' 				=> $id, 
			'current_sorting' 	=> $current_sorting,
			'current_thread'	=> $current_thread,
			'current_page'		=> $current_page

		) );

		wp_send_json( array(

			'status'			=> 'success',	
			'thread'			=> $discussion->get_current_thread()->render( false ),
			'current_page'		=> $discussion->get_current_page(),
			'page_count'		=> $discussion->get_page_count(),
			'thread_data'		=> $discussion->get_current_thread()->get_data()

		) );

		die();		

	}

	public function get_comments() {

		$id = 0;
		$current_thread = '';
		$current_sorting = '';
		$current_page = 1;

		if( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) )
			$id = (int) $_REQUEST['post_id'];

		if( isset( $_REQUEST['cthread'] ) && is_string( $_REQUEST['cthread'] ) )
			$current_thread = commenter_get_cthread_value();

		if( isset( $_REQUEST['csort'] ) && is_string( $_REQUEST['csort'] ) )
			$current_sorting = commenter_get_csort_value();

		if( isset( $_REQUEST['cpage'] ) && is_numeric( $_REQUEST['cpage'] ) )
			$current_page = (int) $_REQUEST['cpage'];

		$discussion = new Commenter_Discussion( array( 

			'id' 				=> $id, 
			'current_sorting' 	=> $current_sorting,
			'current_thread'	=> $current_thread,
			'current_page'		=> $current_page

		) );

		wp_send_json( array(

			'status'			=> 'success',	
			'comments'			=> $discussion->get_comments(),
			'current_page'		=> $discussion->get_current_page(),
			'page_count'		=> $discussion->get_page_count(),
			'current_thread'	=> $discussion->get_current_thread()->get_data()

		) );

		die();
		
	}

	public function do_action() {

		$action_handler = new Commenter_Action_Handler();

		if( ! $action_handler ) {

			wp_send_json( array( 

				'status'	=> 'error',
				'message'	=> __( 'Action handler could not be initialized', 'commenter' )

			) );

			die();

		}

		$action_handler->do_action();

	}

	// ajax nopriv: user is not signin
	public function must_login() {

		wp_send_json( array(

			'status'  => 'error',
			'message' => sprintf( __( 'You Must <a href="%s">Login</a>', 'commenter' ), wp_login_url() )

		) );

		die();

	}

}

new Commenter_Ajax();