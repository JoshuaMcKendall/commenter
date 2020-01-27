<?php
/**
 * The parent template that holds the main comment form.
 *
 * Override this template by copying it to commenter/comment-form.php
 *
 * @author        Joshua McKendall
 * @package       Commenter/templates
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

$comment = isset( $comment ) ? $comment : '';
$comment_form_container_identifier = $thread->get_slug() . '-comment-form-container-' . $thread->get_id();
$comment_form_identifier = $thread->get_slug() . '-comment-form-' . $thread->get_id();

?>

<div id="<?php esc_attr_e( $comment_form_container_identifier ); ?>" class="respond-form-container comment-form-container">

	<?php if ( get_option('comment_registration') && ! is_user_logged_in() ) : ?>

		<div class="comments-open-loggedout">
			
			<p>

				<?php _e( 'You must be', 'commenter' ); ?> 

				<a href="<?php echo wp_login_url( get_permalink() . '#comments' ); ?>">

					<?php _e( 'logged in', 'commenter' ); ?>
						
				</a> 

				<?php _e( 'to post a comment.', 'commenter' ); ?>

			</p>

		</div> <!-- .comments-open-loggedout -->

	<?php else : ?>

		<?php do_action( 'commenter_before_comment_form', $thread, $commenter, $comment ); ?>

		<form <?php echo $form->get_form_attr() ?> >

			<div class="screen-reader-text" >

				<h2><?php comment_form_title( 'Leave a Reply', __( 'Leave a Reply to %s' ) ); ?></h2>

			</div>

			<?php do_action( 'commenter_comment_form', $thread, $commenter, $comment ); ?>

			<div class="notices-container">
				
				<?php do_action( 'commenter_comment_form_notification', $thread, $commenter, $comment ); ?>

			</div>

			<div id="comment-form-help" class="comment-form-help-container">

				<div class="comment-form-help-close">

					<a href="#close-comment-form-help"><i class="icon close-icon"></i></a>

				</div>
				
				<p><?php _e( 'You can use these tags:', 'commenter' ); ?><code><?php echo allowed_tags(); ?></code></p>

			</div>

		</form>

		<?php do_action( 'commenter_after_comment_form', $thread, $commenter, $comment ); ?>

	<?php endif; ?>

</div><!-- respond-form -->