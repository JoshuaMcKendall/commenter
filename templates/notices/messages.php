<?php
/**
 * The Template for displaying messages notice.
 *
 * Override this template by copying it to yourtheme/commenter/notices/messages.php
 *
 * @author        Joshua McKendall
 * @package       Commenter/templates
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

if ( ! empty( $messages ) ) {
	foreach ( $messages as $code => $msgs ) {
		commenter_get_template( 'notices/' . $code . '.php', array( 'messages' => $msgs ) );
	}
}

