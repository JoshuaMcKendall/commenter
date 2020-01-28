<?php
/**
 * The parent template that holds the logged in user vcard.
 *
 * Override this template by copying it to commenter/comment-form/author-vcard.php
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

<div class="comment-author vcard">

	<?php if( $commenter->has( 'url' ) ) : ?>

		<a class="comment-avatar" href="<?php echo esc_url( $commenter->get_url() );  ?>">

	<?php endif; ?>

	<?php echo $commenter->get_avatar(); ?>

	<?php if( $commenter->has( 'url' ) ) : ?>

		</a>

	<?php endif; ?>

	<div class="citation">

		<?php if( $commenter->has( 'url' ) ) : ?>

			<a href="<?php echo esc_url( $commenter->get_url() );  ?>">

		<?php endif; ?>
		
		<?php printf( wp_kses_post( '<cite class="fn">%s</cite>', 'commenter' ), $commenter->get_display_name() ); ?>

		<?php if( $commenter->has( 'url' ) ) : ?>

			</a>

		<?php endif; ?>

		<?php if( $form->has_parent() ) : ?>

			<small class="ref has-parent">

				<a href="<?php echo '#comment-' . $form->parent_comment->get_id(); ?>" class="parent-comment-link link link-secondary">

					<?php esc_html_e( get_comment_author( $form->parent_comment->get_id() ) ) ?>

				</a>
					
			</small>

		<?php else : ?>

			<small class="ref has-parent hidden">

				<a href="#no-parent" class="parent-comment-link link link-secondary">

					

				</a>
					
			</small>

		<?php endif; ?>	

	</div>

	<div class="comment-actions" >

		<div class="mini-dropdown">

			<button class="btn btn-link btn-link-secondary commenter-action-menu-btn">
				
				<i class="icon more-icon"></i>

			</button>

			<label>

				<span class="screen-reader-text"><?php _e( 'Commenter Actions', 'commenter' ); ?></span>

			    <input type="checkbox">

			    <ul class="right menu">
			   
			   		<?php foreach ( $menu_actions as $slug => $action ) : ?>
			   			
			   			<li class="<?php esc_attr_e( $slug ); ?>-action-item action-item">

			   				<a href="<?php echo esc_url( $action['link'] ); ?>" id="<?php esc_attr_e( $slug . '-' . $form->post_id ); ?>">
			   					
			   					<?php esc_html_e( $action['title'] ); ?>

			   				</a>

			   			</li>

			   		<?php endforeach; ?>
			      
			    </ul>

			</label>

		</div>

	</div>

	<div class="comment-meta commentmetadata">

		<small>

			<?php echo '<time class="graphite" datetime="' . current_time( 'c' ) . '">' . current_time( 'M j, Y' ) . '</time>'; ?>

		</small>
		
	</div>

</div>