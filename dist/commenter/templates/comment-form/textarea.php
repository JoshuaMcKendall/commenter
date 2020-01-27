<?php
/**
 * The parent template that holds the textarea for the main comment form.
 *
 * Override this template by copying it to commenter/comment-form/textarea.php
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

<div class="comment-form-textarea form-control-wrap required-control">

	<textarea name="comment" cols="58" rows="6" tabindex="1" class="form-control commenter-comment-form-textarea" placeholder="<?php _e( 'Leave a comment', 'commenter' ); ?>" required="required" ><?php echo wp_kses( $value, allowed_tags() ); ?></textarea>

	 <?php do_action( 'commenter_after_comment_form_textarea', $comment, $thread, $commenter ); ?>

</div>

