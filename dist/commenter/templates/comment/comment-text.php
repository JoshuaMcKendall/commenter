<?php
/**
 * The template for displaying the comment text.
 *
 * Override this template by copying it to commenter/comment/comment-text.php
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

<div class="comment-text">

	<?php echo $comment->get_content(); ?>

</div>