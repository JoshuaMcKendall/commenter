<?php

/**
 * The Commenter Frontend Assets class
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/includes/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/includes
 */

/**
 * The Commenter Frontend Assets class
 *
 * This class loads the front-end assets for the contact form.
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


class Commenter_Frontend_Assets {

	/**
	 * Register scripts
	 * @since 1.4.1.4
	 */
	public static function init() {

		add_action( 'commenter_before_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );
		
	}

	/**
	 * Register scripts
	 *
	 * @param type $hook
	 */
	public static function register_scripts( $hook ) {

		Commenter_Assets::register_script( 'commenter-js', COMMENTER_ASSETS_URI . 'js/public/commenter-public.js', array( 'jquery' ), COMMENTER_VER );

		Commenter_Assets::register_style(  'commenter-styles', COMMENTER_ASSETS_URI . 'css/public/commenter-public.css', array(), COMMENTER_VER );

		Commenter_Assets::localize_script( 'commenter-js', 'Commenter_Data', apply_filters( 'commenter_script_data', array(

			'ajax_url'         	=> admin_url( 'admin-ajax.php' ),
			'discussion'		=> json_encode( commenter_get_discussion_data() )

		) ) );

	}

}

Commenter_Frontend_Assets::init();
