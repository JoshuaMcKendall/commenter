<?php
/**
 * The template for displaying all the threads in a discussion.
 *
 * Override this template by copying it to commenter/discussion.php
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

<div id="<?php esc_attr_e( $discussion->get_identifier() ); ?>" class="discussion">

	<div class="discussion-header">

		<div class="discussion-title"><strong><?php esc_html_e( $discussion->title ); ?></strong></div>

		<div class="discussion-sortings" >

			<?php $current_thread->render_sortings(); ?>

		</div><!-- .discussion-sortings -->
		
	</div><!-- .discussion-header -->

	<div class="discussion-threads">

		<div class="discussion-navigation">

			<ul class="commenter-thread-tabs thread-tabs tabs">

				<?php

					do_action( 'commenter_before_thread_tabs', $discussion );

					foreach ( $threads as $slug => $thread ) {

						$thread_tab_class = $slug . '-tab-item tab';
						$identifier = $thread->get_identifier();

						if( $thread->is_current_thread() ) {

							$thread_tab_class .= ' current-tab';

						}

				?>

				<li id="<?php esc_attr_e( $identifier ); ?>-tab" class="<?php esc_attr_e( $thread_tab_class ); ?>">

					<a href="<?php echo esc_url( $thread->get_url() ); ?>" class="link link-secondary <?php esc_attr_e( $slug ); ?>-tab-link tab-link" data-thread-slug="<?php esc_attr_e( $slug ); ?>">

						<?php esc_html_e( $thread->title ); ?>

						<span class="badge badge-md badge-default <?php esc_attr_e( $slug ); ?>-comment-count comment-count">

							<?php esc_html_e( $thread->get_comments_count() ); ?>

						</span>

					</a>
						
				</li>

				<?php

					}

					do_action( 'commenter_after_thread_tabs', $discussion );

				?>

			</ul><!-- .thread-tabs -->

			<?php if( $discussion->has_info() ) : ?>

				<div class="discussion-info-tab-item tab">

					<small>
						
						<a href="#discussion-<?php esc_attr_e( get_the_ID() ); ?>-info" class="link link-secondary discussion-info-tab-link tab-link" >

							<span class="icon info-icon"></span> 

							<span class="hidden">&nbsp;<?php _e( 'Info', 'commenter' ); ?></span>
								
						</a>

					</small>					

				</div>

			<?php endif; ?>
			
		</div><!-- .discussion-navigation -->

		<?php if( $discussion->has_info() ) : ?>

			<div id="discussion-<?php esc_attr_e( get_the_ID() ); ?>-info" class="discussion-info">

				<div class="discussion-info-close">

					<a href="#close-discussion-<?php esc_attr_e( get_the_ID() ); ?>-info"><i class="icon close-icon"></i></a>

				</div>
				
				<?php echo $discussion->get_info(); ?>

			</div>

		<?php endif; ?>

		<div class="discussion-notices">
			
		</div>

		<div class="discussion-loading-container hidden">

	        <div class="discussion-loader">

				<span></span>
				<span></span>
				<span></span>

	        </div>	
	        	
		</div><!-- .loading-container -->

		<div class="threads-container">
			
			<?php do_action( 'commenter_before_threads', $discussion ); ?>

			<?php $discussion->render_threads(); ?>

			<?php do_action( 'commenter_after_threads', $discussion ); ?>

		</div><!-- .threads-container -->

	</div><!-- .discussion-threads -->

</div><!-- .discussion -->