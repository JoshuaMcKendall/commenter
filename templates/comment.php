<?php
/**
 * The template for displaying a comment in the thread.
 *
 * NOTE: DO NOT CLOSE $tag ELEMENT 
 * <<?php esc_attr_e( $tag ); ?> <?php comment_class( $comment_classes, $comment, $post_id ); ?> id="comment-<?php comment_ID(); ?>">
 *
 * Override this template by copying it to commenter/comment.php
 *
 * @author        Joshua McKendall
 * @package       Commenter/templates
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

$tag = $comment->style;

$post_id = $comment->comment_post_ID;

$comment_id = $comment->comment_ID;

$comment_identifier = $comment->get_identifier();

$thread = commenter_get_current_thread();

$thread_slug = $thread->get_slug();

$thread_comment_identifier = ( ! empty( $thread ) ) ? $thread_slug . '-' . $comment_identifier : $comment_identifier;

$comment_classes = $comment_identifier;

$parent_class = empty( $args['has_children'] ) ? '' : $comment_classes .= ' parent';

$awaiting_moderation_class = ( '0' == $comment->comment_approved ) ? $comment_classes .= ' awaiting-moderation' : '';

?>

<<?php esc_attr_e( $tag ); ?> <?php comment_class( $comment_classes, $comment, $post_id ); ?> id="<?php esc_attr_e( $thread_comment_identifier ); ?>" data-comment-data="<?php esc_attr_e( $comment->get_data() ); ?>">

	<?php do_action( 'commenter_before_comment_' . $comment->get_id(), $comment, $args, $depth, $thread ); ?>

	<article class="comment comment-body <?php echo $comment->has_parent() ? 'has-parent' : ''; ?>">

		<?php do_action( 'commenter_comment_' . $comment->get_id(), $comment, $args, $depth, $thread ); ?>

	</article>

	<?php do_action( 'commenter_after_comment_' . $comment->get_id(), $comment, $args, $depth, $thread ); ?>