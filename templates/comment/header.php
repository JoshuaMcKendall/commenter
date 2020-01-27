<?php
/**
 * The template for displaying the comment header.
 *
 * Override this template by copying it to commenter/comment/header.php
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

<header class="comment-header">
	
	<div class="comment-author vcard">

		<?php if( $comment->commenter->get_url() && $comment->commenter->get_avatar() ) : ?>

			<a href="<?php echo esc_url( $comment->commenter->get_url() ); ?>" class="comment-avatar">

				<?php echo $comment->commenter->get_avatar(); ?>
					
			</a>

		<?php elseif( $comment->commenter->get_avatar() ) : ?>

			<span class="comment-avatar">

				<?php echo $comment->commenter->get_avatar(); ?>
					
			</span>

		<?php endif; ?>

		<?php do_action( 'commenter_before_commenter_citation', $comment ); ?>

		<div class="citation">

			<?php printf( wp_kses_post( '<cite class="fn">%s</cite>', 'commenter' ), $comment->commenter->get_link( $comment->comment_ID ) ); ?>

				<?php if( $comment->has_parent() ) : ?>

					<small class="ref has-parent">

						<?php esc_html_e( $comment->parent_commenter->get_display_name() ) ?>
							
					</small>

				<?php endif; ?>

		</div>

		<?php do_action( 'commenter_after_citation', $comment ); ?>

		<div class="comment-actions" >

			<div class="mini-dropdown">

				<button class="btn btn-link btn-link-secondary commenter-action-menu-btn">
					
					<span class="icon more-icon"></span>

				</button>

				<label>

				    <input type="checkbox">

				    <ul class="right menu">
				   
				   		<?php do_action( 'commenter_comment_actions', $comment ); ?>
				      
				    </ul>

				</label>

			</div>
		
		</div>

		<?php do_action( 'commenter_after_comment_actions', $comment ); ?>

		<div class="comment-meta commentmetadata">

			<small class="comment-post-time">

				<a href="<?php echo $comment->get_link(); ?>" class="link link-secondary" >

					<time datetime="<?php esc_attr_e( $comment->get_date( 'c' ) ); ?>">
						
					<?php printf( _x( '%s ago', '%s = human-readable time difference', 'commenter' ), $comment->get_time() ); ?>

					</time>

				</a>

			</small>

		</div>

	</div>

</header>