<?php
/**
 * The template for displaying all the likes for a comment in the edit screen metabox.
 *
 *
 * @author        Joshua McKendall
 * @package       Commenter/admin
 * @version       1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

?>

<div class="<?php esc_attr_e( $comment->comment_ID ); ?>-likes-metabox likes-metabox" >

	<ol class="likes user-likes" style="list-style: none;">
		
		<?php foreach( $likes as $like ) : ?>

			<?php 

				$user_id = $like->user_id;
				$user_object = get_user_by( 'id', $user_id );

				// array(
				// 	'url'				=> $user_object->user_url,
				// 	'email'             => $user_object->user_email,
				// 	'user_login'        => $user_object->user_login,
				// 	'description'       => $user_object->description,
				// 	'first_name'        => isset( $user_object->first_name ) ? $user_object->first_name : '',
				// 	'last_name'         => isset( $user_object->last_name ) ? $user_object->last_name : '',
				// 	'nickname'          => isset( $user_object->nickname ) ? $user_object->nickname : '',
				// 	'display_name'      => $user_object->display_name,
				// 	'date_created'      => $user_object->user_registered,
				// 	'date_modified'     => get_user_meta( $user_id, 'last_update', true ),
				// 	'role'              => ! empty( $user_object->roles[0] ) ? $user_object->roles[0] : 'subscriber',
				// 	'roles'             => ! empty( $user_object->roles ) ? $user_object->roles : array( 'subscriber' ),

				// )

			?>

			<li id="like-<?php esc_attr_e( $like->comment_id ); ?>" class="commenter-like-item" style="position: relative; margin-bottom: 2em;">

				<?php if( ! empty( $user_object->user_url ) ) : ?>

					<a href="<?php echo esc_url( $user_object->user_url ); ?>" class="user-avatar" style="margin-right: 1em;">

						<?php echo get_avatar( $user_id, 45 ); ?>

					</a>

				<?php else : ?>

					<span class="comment-avatar" style="margin-right: 1em;">

						<?php echo get_avatar( $user_id, 45 ); ?>

					</span>

				<?php endif; ?>

				<div class="dashboard-comment-wrap comment-likes-wrap" style="display: inline-block;top: 0;line-height: 45px; position: absolute;">

					<p class="comment-meta">

						<cite class="comment-author">

							<?php if( ! empty( $user_object->user_url ) ) : ?>

								<a href="<?php echo esc_url( $user_object->user_url ); ?>" rel="external nofollow ugc" class="url">

									<?php esc_html_e( $user_object->display_name ); ?>
									
								</a>

							<?php else : ?>

								<?php esc_html_e( $user_object->display_name ); ?>

							<?php endif; ?>

						</cite> 

						<span>&nbsp; <span class="dashicons dashicons-heart"></span> <?php _e( 'Liked this comment.', 'commenter' ); ?></span>

					</p>
							
				</div>

			</li>

		<?php endforeach; ?>

	</ol>

</div><!-- .likes-metabox -->

