<?php
/**
 * Commenter Action Handler class
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/includes/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * This class defines all code necessary to handle incoming action requests.
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
 * Request Handler
 */
class Commenter_Action_Handler {

	public $doing_ajax = false;

	public $current_user;

	public $request_method;

	public $errors = [];

	public function __construct() {

		if( ! empty( $_REQUEST ) && isset( $_REQUEST['caction'] ) ) {

			$this->request_method = $_SERVER['REQUEST_METHOD'];

			add_action( 'template_redirect', array( $this, 'do_action' ) );

			return true;

		}

		return false;

	}

	public function do_action() {

		$this->current_user = commenter_get_current_user();
		$cid 	 = commenter_get_cid_value();
		$caction = commenter_get_caction_value();
		$actions = $this->current_user->get_actions( $this->request_method );
		$comment = null;

		if( ! isset( $caction ) || ! is_string( $caction ) )
			return;

		if( ! is_array( $actions ) || ! array_key_exists( $caction, $actions ) )
			return;

		$action = $actions[ $caction ];

		if( ! $action instanceof Commenter_Action )
			return;

		if( ( is_numeric( $cid ) && (int) $cid > 0 ) ) {
			$action->set_cid( $cid );
			$action->set_nonce_name();
			$action->set_nonce_action_name();

			$comment = commenter_get_comment( $cid );
		}

		$args = array( 

			'action'		=> $action,
			'request' 		=> $_REQUEST,
			'errors'		=> $this->errors

		);

		if( $this->request_method === $action->get_method() ) {

			$permalink = get_the_permalink();

			if( ! $action->has_ajax() && wp_doing_ajax() ) {
				wp_send_json( array(
					'status'	=> 'error',
					'message'	=> __( 'An unexpected error occured.', 'commenter' )
				) );
				die();
			}

			if( $action->get_method() === 'GET' && ! $action->has_nonce() ) {
				$permalink = $action->get_url();
			}

			if( $action->must_login() && ! is_user_logged_in() ) {

				if( wp_doing_ajax() ) {
					wp_send_json( array(
						'status'	=> 'error',
						'message'	=> sprintf( __( 'You Must <a href="%s">Login</a>', 'commenter' ), wp_login_url( $permalink ) )
					) );
					die();
				}

				wp_redirect( wp_login_url( $permalink ) );
				die;
			}

			if( $action->has_nonce() && ! wp_verify_nonce( $_REQUEST[ $action->get_nonce_name() ], $action->get_nonce_action_name() ) ) {

				if( wp_doing_ajax() ) {
					wp_send_json( array(
						'status'	=> 'error',
						'message'	=> __( 'You don\'t have permission to do this.', 'commenter' )
					) );
					die();
				}

				wp_redirect( $permalink );
				die;
			}

			$action->execute( $comment, $args );		
		
		}

	}

}

new Commenter_Action_Handler();