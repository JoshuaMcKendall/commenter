<?php
/**
 * The template for displaying the comment header.
 *
 * Override this template by copying it to commenter/comment/comment-header.php
 *
 * @author        Joshua McKendall
 * @package       Commenter/templates
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

$parent_comment_identifier = 'comment-' . $comment->comment_parent;
$thread_slug = ( ! empty( $thread ) ) ? $thread->get_slug() : '';
$thread_comment_identifier = ( ! empty( $thread_slug ) ) ? $thread_slug . '-' . $parent_comment_identifier : $parent_comment_identifier;

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

		<?php do_action( 'commenter_before_commenter_citation', $comment, $args, $depth ); ?>

		<div class="citation">

			<?php printf( wp_kses_post( '<cite class="fn">%s</cite>', 'commenter' ), $comment->commenter->get_link( $comment->comment_ID ) ); ?>

				<?php if( $comment->has_parent() ) : ?>

					<small class="ref has-parent">

						<a href="<?php echo esc_url( '#' . $thread_comment_identifier ); ?>" class="parent-comment-link link link-secondary">

							<?php esc_html_e( get_comment_author( $comment->comment_parent ) ) ?>

						</a>
							
					</small>

				<?php endif; ?>

		</div>

		<div class="comment-actions" >

			<div class="mini-dropdown">

				<button class="btn btn-link btn-link-secondary commenter-action-menu-btn">
					
					<span class="icon more-icon"></span>

				</button>

				<label>

					<span class="screen-reader-text"><?php _e( 'Comment Actions', 'commenter' ); ?></span>

				    <input type="checkbox">

				    <ul class="right menu">
				   
				   		<?php foreach ( $comment->get_action_items() as $slug => $action ) : ?>
				   			
				   			<li class="<?php esc_attr_e( $slug ); ?>-action-item action-item">

				   				<a href="<?php echo esc_url( $action->get_url( $comment ) ); ?>" class="<?php esc_attr_e( $slug . '-action-link' ); ?>" data-action="<?php esc_attr_e( $slug ); ?>">
				   					
				   					<?php esc_html_e( $action->get_title() ); ?>

				   				</a>

				   			</li>

				   		<?php endforeach; ?>
				      
				    </ul>

				</label>

			</div>

		</div>

		<?php do_action( 'commenter_after_commenter_citation', $comment, $args, $depth ); ?>

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