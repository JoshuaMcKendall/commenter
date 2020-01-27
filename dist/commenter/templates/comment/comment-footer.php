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

<footer class="comment-footer">

	<div class="comment-reaction-bar reply">

		<small class="reaction reply-reaction" >

			<strong>

				<?php 

					commenter_comment_reply_link( array_merge( $args, array( 

						'add_below' 	=> ( 'div' == $comment->style ) ? 'comment' : 'div-comment', 
						'depth' 		=> $depth, 
						'max_depth' 	=> $args['max_depth']

					) ) );  

				?>
					
			</strong>

		</small>

		<small class="reaction like-reaction" >

			<strong>

				<?php commenter_comment_like_link( $comment ); ?>
					
			</strong>

		</small>

		<?php do_action( 'commenter_comment_reaction_bar', $comment ); ?>

	</div>

	<div class="comment-notifications">

		<small><a href="#unfocus" class="unfocus-link" ><?php _e('# Unfocus', 'commenter'); ?></a></small>

		<!-- <small><p class="alert alert-lg alert-warning">Hello there</p></small> -->
		
		<?php do_action( 'commenter_comment_' . $comment->get_id() . '_notifications', $comment ); ?>

	</div>

</footer>

