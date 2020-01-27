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

<div class="action-bar">

	<div class="form-group form-buttons">

		<div class="comment-action-buttons">

			<a href="#comment-form-help" class="btn btn-link discussion-info-btn"><span class="icon help-icon"></span></a>

			<div class="comment-actions btn-group" >

				<?php $form->cancel_link(); ?>

				<button type="submit" tabindex="6" class="btn btn-md btn-pill btn-primary commenter-comment-form-submit-btn">

					<?php echo $i18n['submit_button']; ?>	

				</button>
				
			</div>
			
		</div>

	</div>
	
</div>