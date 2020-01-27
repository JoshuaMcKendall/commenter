<?php
/**
 * Commenter Request Handler class
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/includes/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * This class defines all code necessary to handle incoming requests.
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
class Commenter_Request_Handler {

	public $doing_ajax = false;

	public $errors = [];

	public function __construct() {


	}


}

new Commenter_Request_Handler();