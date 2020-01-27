<?php
/**
 * The parent template that holds the main discussion and overrides the template comments.php.
 *
 * Override this template by copying it to commenter/comments.php
 *
 * @author        Joshua McKendall
 * @package       Commenter/templates
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

do_action( 'commenter_comments_template' );

?>