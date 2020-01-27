<?php
/**
 * The template for displaying all the comments in a specific thread.
 *
 * Override this template by copying it to commenter/thread.php
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

<?php do_action( 'commenter_before_thread_container', $thread ); ?>

<div id="<?php esc_attr_e( $thread->get_identifier() ); ?>" class="<?php esc_attr_e( $thread->slug ); ?>-thread thread <?php if( $thread->is_current_thread() ) { echo 'current-thread'; } ?>">

	<?php if( $thread->is_current_thread() ) : ?>

		<?php if ( comments_open( $thread->get_id() ) ) : ?>

			<?php do_action( 'commenter_render_'. $thread->slug .'_comment_form', $thread ); ?>

		<?php else : ?>

			<div class="well well-sm comments-closed no-comments">

				<p class="comments-closed-msg"><small><?php _e( 'Comments are closed.', 'commenter' ); ?></small></p>

			</div>

		<?php endif; ?>

		<?php if ( $thread->has_comments() ) : ?>

			<ol class="comments-area <?php esc_attr_e( $thread->slug ) ?>-thread-comments thread-comments has-comments">

				<?php $thread->list_comments(); ?>

			</ol><!-- .comments-area -->

			<div class="navigation thread-navigation comments nav-links">

				<?php commenter_paginate_comments_links( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>

			</div>

			<div class="<?php esc_attr_e( $thread->get_slug() ) ?>-comments-loadmore-container comments-loadmore-container hidden">
				
				<?php $thread->loadmore_button(); ?>

			</div>

		<?php else : ?>

			<div class="well well-sm comments-closed no-comments">

				<p class="comments-closed-msg"><small><?php echo $thread->i18n[ 'empty' ]; ?></small></p>

			</div>

		<?php endif; ?>

		<?php do_action( 'commenter_after_open_comments_thread', $thread ); ?>

	<?php else : ?>

		<div class="loading-container">

	        <div class="thread-loader">

				<span></span>
				<span></span>
				<span></span>

	        </div>	
	        	
		</div><!-- .loading-container -->		

	<?php endif; //thread->is_current_thread() ?>

</div><!-- .thread-container -->

<?php do_action( 'commenter_after_thread_container', $thread ); ?>