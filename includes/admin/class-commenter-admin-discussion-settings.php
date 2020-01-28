<?php

/**
 * The class that adds the discussion settings.
 *
 * @link       https://github.com/JoshuaMcKendall/Commenter-Plugin/admin/
 * @since      1.0.0
 *
 * @package    Commenter
 * @subpackage Commenter/admin
 */

/**
 * The class that adds the discussion settings.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Commenter
 * @subpackage Commenter/admin
 * @author     Joshua McKendall <commenter@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class Commenter_Admin_Discussion_Settings { 

	public $discussion;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		/**
		 * Fires when Commenter admin discussion settings has been initialized.
		 *
		 * @since 1.0.0
		 *
		 * @param Commenter_Admin object.
		 */
		do_action( 'commenter_admin_discussion_settings_init', $this );

		add_action( 'admin_init',		array( $this, 'init_discussion' ),			10	);
		add_action( 'admin_init', 		array( $this, 'init_discussion_settings' ), 20 	);

	}

	public function init_discussion() {

		$this->discussion = new Commenter_Discussion();

	}

	public function init_discussion_settings() {

		$this->add_default_thread_field();
		$this->add_default_sorting_field();
		$this->add_discussion_information();

	}

	public function add_default_thread_field() {

		$name = 'commenter_discussion_default_thread';

		register_setting( 'discussion', $name, array( 

			'type'				=> 'string',
			'description'		=> __( 'Sets the default thread of the discussion', 'commenter' ),
			'sanitize_callback'	=> 'sanitize_text_field',
			'show_in_rest'		=> true,
			'default'			=> COMMENTER_PRIMARY_THREAD

		) );

		add_settings_field( 

			$name, 
			__( 'Default Thread', 'commenter' ), 
			array( $this, 'render_default_thread_field' ),
			'discussion', 
			'default', 
			$args = array(

				'label_for'		=> __( 'Default Thread', 'commenter' )

			) 

		);

	}

	public function add_default_sorting_field() {

		$name = 'commenter_discussion_default_sorting';

		register_setting( 'discussion', $name, array( 

			'type'				=> 'string',
			'description'		=> __( 'Sets the default sorting of the comment thread', 'commenter' ),
			'sanitize_callback'	=> 'sanitize_text_field',
			'show_in_rest'		=> true,
			'default'			=> 'best'

		) );

		add_settings_field( 

			$name, 
			__( 'Default Sorting', 'commenter' ), 
			array( $this, 'render_default_sorting_field' ),
			'discussion', 
			'default', 
			$args = array(

				'label_for'		=> __( 'Default Sorting', 'commenter' )

			) 

		);

	}

	public function add_discussion_information() {

		$name = 'commenter_discussion_information';

		register_setting( 'discussion', $name, array( 

			'type'				=> 'string',
			'description'		=> __( 'Information relevant to the discussion, such as community policy etc.', 'commenter' ),
			'sanitize_callback'	=> 'wp_kses_post',
			'show_in_rest'		=> true

		) );

		add_settings_field( 

			$name, 
			__( 'Discussion Information', 'commenter' ), 
			array( $this, 'render_discussion_information_field' ),
			'discussion', 
			'default', 
			$args = array(

				'label_for'		=> __( 'Discussion Information', 'commenter' )

			) 

		);		

	}

	public function render_default_thread_field( $args ) {

		$name = 'commenter_discussion_default_thread';
		$threads = $this->discussion->get_threads();
		$option = esc_attr( commenter_get_option( $name, current( array_keys( $threads ) ) ) );

		?>

		   <select name="<?php esc_attr_e( $name ); ?>">

		   		<?php foreach ( $threads as $slug => $thread ) : ?>

		   			<option value="<?php esc_attr_e( $slug ); ?>" <?php selected( $option, $slug ); ?>>
		   				
		   				<?php esc_html_e( $thread->get_settings_title() ); ?>
		   					
		   			</option>

		   		<?php endforeach; ?>

		    </select>	

    	<?php

	}

	public function render_default_sorting_field( $args ) {

		$name = 'commenter_discussion_default_sorting';
		$current_thread = $this->discussion->get_current_thread();
		$sortings = $current_thread->get_sortings();
		$option = esc_attr( commenter_get_option( $name, current( array_keys( $sortings ) ) ) );

		?>

		   <select name="<?php esc_attr_e( $name ); ?>">

		   		<?php foreach ( $sortings as $slug => $sorting ) : ?>

		   			<option value="<?php esc_attr_e( $slug ); ?>" <?php selected( $option, $slug ); ?>>
		   				
		   				<?php esc_html_e( $sorting->get_title() ); ?>
		   					
		   			</option>

		   		<?php endforeach; ?>

		    </select>	

    	<?php

	}

	public function render_discussion_information_field( $args ) {


		$name = 'commenter_discussion_information';
		$content = wp_kses_post( wpautop( commenter_get_option( $name, '' ) ) );

		wp_editor( $content, $name, array( 

			'media_buttons'		=> false,
			'wpautop'			=> false

		) );	

	}

}