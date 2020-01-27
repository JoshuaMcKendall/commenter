<?php
/**
 * The template that holds the author info form.
 *
 * Override this template by copying it to commenter/comment-form/author-info.php
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

<div class="comment-author-info" >

	<div class="form-group form-control-wrap <?php echo ( $req ? 'required-control' : '' ) ?>">

		<label for="author" ><?php _e( 'Name', 'commenter' ); ?></label>

		<input type="text" name="author" id="author" class="form-control control-sm author" placeholder="Name" value="<?php ( $commenter->get_display_name() != 'Anonymous' ) ? esc_attr_e( $commenter->get_display_name() ) : null; ?>" size="22" tabindex="2" <?php if ( $req ) echo "aria-required='true' required='required'"; ?> />

	</div>

	<div class="form-group form-control-wrap <?php echo ( $req ? 'required-control' : '' ) ?>">

		<label for="email"><?php _e( 'Email', 'commenter' ); ?> *</label>

		<input type="email" name="email" id="email" class="form-control control-sm email" placeholder="Email" value="<?php esc_attr_e( $commenter->get_email() ); ?>" size="22" tabindex="3" <?php if ( $req ) echo "aria-required='true' required='required'"; ?> />

	</div>

	<div class="form-group">

		<label for="url"><?php _e( 'Website', 'commenter' ); ?></label>

		<input type="url" name="url" id="url" class="form-control control-sm url" placeholder="Website" value="<?php esc_attr_e( $commenter->get_url() ); ?>" size="22" tabindex="4" />

	</div>

	<?php if( get_option( 'show_comments_cookies_opt_in' ) ) : ?>

		<div class="form-group remember-me-consent">

			<?php $consent  = ( empty( $commenter->get_email() ) ) ? '' : ' checked'; ?>

			<div class="toggle-container">

				<div class="form-toggle">

					<input id="wp-comment-cookies-consent" class="toggle-checkbox" name="wp-comment-cookies-consent" type="checkbox" tabindex="5" value="yes" <?php esc_attr_e( $consent ); ?> />

					<label class="toggle" for="wp-comment-cookies-consent">

					  <span class="disc"></span>

					</label>

					<small class="label-text graphite">

						<?php  _e( 'Save your name, email, and website in this browser for the next time you comment.', 'commenter' ); ?>
							
					</small>

				</div>

			</div>

		</div>

	<?php endif; ?>

	<p class="graphite email-footnote"><small>* <?php _e( 'Your email will not be published', 'commenter' ); ?></small></p>

</div> <!-- .comment-author-info -->