<?php
/**
 * The parent template that holds the hidden data fields for the main comment form.
 *
 * Override this template by copying it to commenter/comment-form/hidden-fields.php
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

<input type="hidden" name="comment_post_ID" value="<?php esc_attr_e( $form->get_post_id() ); ?>" />

<input type="hidden" name="cid" value="<?php esc_attr_e( $form->get_comment_parent_id() ); ?>" />

<input type="hidden" name="caction" value="<?php esc_attr_e( $form->get_current_action() ); ?>" />

<input type="hidden" name="cthread" value="<?php esc_attr_e( $thread->get_slug() ); ?>" />

<input type="hidden" name="csort" value="<?php esc_attr_e( $thread->get_current_sorting()->get_slug() ); ?>" />

<?php do_action( 'commenter_after_' . $thread->slug . '_comment_form_hidden_fields', $form, $thread ); ?>