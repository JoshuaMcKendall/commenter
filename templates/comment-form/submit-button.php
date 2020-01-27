<?php
/**
 * The parent template that holds the logged in user vcard.
 *
 * Override this template by copying it to commenter/comment-form/submit-button.php
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

<div class="form-group form-buttons">

	<div class="comment-cancel-action">

		<div class="comment-actions" >

			<?php cancel_comment_reply_link( __( 'Cancel Reply', 'commenter' ) ); ?>
			
		</div>
		
	</div>

	<button type="submit" tabindex="6" class="btn btn-md btn-pill btn-primary commenter-comment-form-submit-btn">

		<?php _e( 'Post Comment','commenter' ); ?>	

	</button>

</div>

