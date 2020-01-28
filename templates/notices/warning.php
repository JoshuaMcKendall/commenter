<?php
/**
 * The Template for displaying an error notice.
 *
 * Override this template by copying it to yourtheme/commenter/notices/error.php
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

<ul class="commenter-notices notices alert alert-warning">

	<?php foreach ( $messages as $code => $message ) { ?>
			
		<li  class="commenter-notice notice" ><?php echo sprintf( '%s', $message ); ?></li>

	<?php }	?>
	
</ul>

