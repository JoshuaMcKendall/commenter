<?php
/**
 * The template for displaying the comment reaction bar.
 * NOTE: Do not close $tag element
 *
 * Override this template by copying it to commenter/comment/reaction-bar.php
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

<div class="comment-reaction-bar reply">

	<small class="reaction reply-reaction" >

		<strong>

			<?php 

				comment_reply_link( $args ); 

			?>
				
		</strong>

	</small>

	<?php echo get_comment_meta( $comment->get_id(), 'like_count', true ); ?>

	<?php do_action( 'commenter_comment_reaction_bar', $comment ); ?>

</div>