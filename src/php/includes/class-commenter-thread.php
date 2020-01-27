<?php
/**
 * Commenter Thread class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * The thread class that displays all the comments.
 *
 * This class defines all code that displays all the comments.
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

class Commenter_Thread extends Commenter_Abstract_Thread {
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $config       	The connfiguration of this list.
	 */
	public function __construct( $config = array() ) {

		parent::__construct( $config );

	}

	/**
	 * get instance class
	 *
	 * @return Commenter_Thread
	 */
	public static function init( $config = array() ) {

		return new self( $config );
		
	}
}
