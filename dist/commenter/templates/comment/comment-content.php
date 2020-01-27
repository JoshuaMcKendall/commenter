<?php
/**
 * The template for displaying the comment content.
 * NOTE: Do not close $tag element
 *
 * Override this template by copying it to commenter/comment/comment-content.php
 *
 * @author        Joshua McKendall
 * @package       Commenter/templates
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

?>

<div class="comment-content comment-text">

	<?php echo $comment->get_content(); ?>

</div>