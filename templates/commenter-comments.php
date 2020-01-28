<?php
/**
 * The parent template that holds the main discussion and overrides the theme's comments.php template.
 *
 * Override this template by copying it to commenter/commenter-comments.php
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

<div id="commenter-<?php esc_attr_e( get_the_ID() ); ?>" class="commenter comments">

	<?php if ( post_password_required() ) : ?>

		<div class="well well-sm comments-no-access">

			<p>
				<small>

					<?php  _e( 'This post is password protected. Enter the password to view comments.', 'commenter' ); ?>
				
				</small>

			</p>

		</div>	

	<?php else : ?>

		<?php do_action( 'commenter_comments', get_the_ID() ); ?>

	<?php endif; ?>

</div><!-- #comments-<?php echo get_the_ID(); ?> -->