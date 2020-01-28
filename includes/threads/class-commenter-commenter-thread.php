<?php
/**
 * Commenter Commenter Thread class
 *
 * @author        Joshua McKendall
 * @package       Commenter/Class
 * @version       1.0.0
 */

/**
 * The primary thread class that displays all commenter comments.
 *
 * This class defines all code that displays all the commenter comments.
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

class Commenter_Commenter_Thread extends Commenter_Abstract_Thread {
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $config       	The connfiguration of this list.
	 */
	public function __construct( $config = array() ) {

		$default_config = array(

			'slug'				=> 'commenter',
			'page'				=> 1,
			'title'				=> 'Commenter',
			'enabled'			=> commenter_has_commenter(),
			'position'			=> 15,
			'args'				=> array(),
			'settings_info'		=> array(

				'title'			=> __( 'Commenter', 'commenter' )

			)

		);

		$config = wp_parse_args( $config, $default_config );

		parent::__construct( $config );

	}

	/**
	 * get instance class
	 *
	 * @return Commenter_Commenter_Thread
	 */
	public static function init( $config = array() ) {

		return new self( $config );

	}

}
