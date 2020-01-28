<?php
/**
 * The template part that renders the sortings for the thread.
 *
 * Override this template by copying it to commenter/discussion/sortings.php
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

<div class="mini-dropdown discussion-sortings">

	<a href="#sorting" class="current-sorting link link-secondary">
		
		<span><?php esc_html_e( $current_sorting->get_title() ); ?></span>

	</a>

	<label>

	    <input type="checkbox">

	    <ul class="sortings right menu">

	    	<?php foreach ( $sortings as $slug => $sorting ) : ?>

    			<li class="<?php esc_attr_e( $slug ); ?>-list-item list-item sorting-item">
    					
    				<a href="<?php echo esc_url( $sorting->get_url() ); ?>" class="link link-secondary <?php esc_attr_e( $slug ); ?>-sorting-link sorting-link" data-sorting-slug="<?php esc_attr_e( $slug ); ?>">

    					<?php esc_html_e( $sorting->get_title() ) ?>
    					
    				</a>	

    			</li>

	    	<?php endforeach; ?>
	      
	    </ul>

	</label>

</div>