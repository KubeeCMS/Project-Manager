<?php
/**
 * Register Custom Post Type
 *
 * @package     ClientPortal\CPT
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Manually render the save button.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 */
function leco_cp_render_save_button_cb( $field_args, $field ) {
	$classes     = $field->row_classes();
	$label       = $field_args['name'];
	$name        = $field_args['_name'];
	$description = $field_args['description'];

	if ( call_user_func( $field_args['show_on_cb'] ) ) {
	?>
	<div class="custom-field-row <?php echo $classes; ?>">
		<p><input id="leco_cp_save" class="button button-primary button-large leco_cp_save" type="button" name="<?php echo $name; ?>" value="<?php echo $label; ?>"/></p>
		<p class="description"><?php echo $description; ?></p>
	</div>
	<?php
	}
}

/**
 * Manually render the manage modules button.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 */
function leco_cp_render_manage_modules_cb( $field_args, $field ) {
	$classes     = $field->row_classes();
	$label       = $field->args( 'name' );
	$description = $field->args( 'description' );

	if ( isset( $_GET['action'] ) && ! isset( $_GET['cp-action'] ) ) {
	?>
	<div class="custom-field-row <?php echo $classes; ?>">
		<p><a id="leco_cp_manage_module" href="<?php echo admin_url( 'post.php?post=' . sanitize_text_field( $_GET['post'] ) . '&action=edit&cp-action=manage-phases' ) . '#manage-phases'; ?>" class="button button-primary button-large"><?php echo $label; ?></a></p>
		<p class="description"><?php echo $description; ?></p>
	</div>
	<?php
	}
}

function leco_cp_register_metabox() {
	$prefix = 'leco_cp_';

	$cmb = new_cmb2_box( array(
		'id'           => $prefix . 'templates',
		'title'        => esc_html__( 'Create A New Project From Templates', 'leco-cp' ),
		'object_types' => array( 'leco_client' ),
		'show_on_cb'   => 'leco_cp_show_if_add_client',
	) );

	$templates        = get_posts(
		array(
			'post_type'   => 'leco_template',
			'numberposts' => - 1,
		)
	);
	$template_options = ( empty( $templates ) ) ? array() : array_combine(
		wp_list_pluck( $templates, 'ID' ),
		wp_list_pluck( $templates, 'post_title' )
	);
	$default_option = array( '0' => esc_html__( 'Don\'t use a Template', 'leco-cp' ) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Choose a Template', 'leco-cp' ),
		'desc'    => sprintf( esc_html__( 'Choose a template from the project templates you\'ve created. %s', 'leco-cp' ), '<strong>' . __( 'AFTER a project has been created, any further changes on the template WON\'T be applied to this project.', 'leco-cp' ) . '</strong>' ),
		'id'      => $prefix . 'template',
		'type'    => 'select',
		'options' => array_replace( $default_option, $template_options ),
	) );

	$cmb = new_cmb2_box( array(
		'id'           => $prefix . 'custom_branding',
		'title'        => esc_html__( 'Custom Branding', 'leco-cp' ),
		'object_types' => array( 'leco_client', 'leco_template' ),
		'save_fields'  => ( isset( $_POST['leco_cp_template'] ) && '0' !== $_POST['leco_cp_template'] ) ? false : true,
		'show_on_cb'   => 'leco_cp_show_if_not_manage_phases',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Use default settings for empty fields', 'leco-cp' ),
		'desc'    => esc_html__( 'If you select “Yes”, we’ll load the values from Client Portal Settings into the empty fields in your portal. If you select “No”, we will leave all empty fields blank. The default is “Yes”.', 'leco-cp' ),
		'id'      => $prefix . 'fallback_values',
		'type'    => 'select',
		'options' => array(
			'yes' => esc_html__( 'Yes', 'leco-cp' ),
			'no'  => esc_html__( 'No', 'leco-cp' ),
		),
		'default' => leco_cp_get_option( 'fallback_values', 'yes' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Client\'s Logo', 'leco-cp' ),
		'desc'    => esc_html__( 'Default value is your logo when you\'ve set one in Client Portal Settings. To replace with your client\'s logo, upload an image or enter a URL.', 'leco-cp' ),
		'id'      => $prefix . 'logo',
		'type'    => 'file',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'logo' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Fixed logo width', 'leco-cp' ),
		'desc' => esc_html__( 'If select "Yes", we\'ll set the logo image width to 270px.', 'leco-cp' ),
		'id'   => $prefix . 'logo_fixed_width',
		'type'             => 'select',
		'options'          => array(
			'yes'   => esc_html__( 'Yes', 'leco-cp' ),
			'no' => esc_html__( 'No', 'leco-cp' ),
		),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Header Image', 'leco-cp' ),
		'desc' => esc_html__( 'Upload an image or enter a URL.', 'leco-cp' ),
		'id'   => $prefix . 'header_background_image',
		'type' => 'file',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'header_background_image' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Primary Background Color', 'leco-cp' ),
		'id'      => $prefix . 'primary_color',
		'type'    => 'colorpicker',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'primary_color', '#52cdf5' ),
		'classes' => array( 'color', 'leco-cp-background' ),
		'options' => array(
			'alpha' => true,
		),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Primary Text Color', 'leco-cp' ),
		'id'      => $prefix . 'primary_text_color',
		'type'    => 'colorpicker',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'primary_text_color', '#ffffff' ),
		'classes' => array( 'color' ),
		'options' => array(
			'alpha' => true,
		),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Secondary Background Color', 'leco-cp' ),
		'id'      => $prefix . 'secondary_color',
		'type'    => 'colorpicker',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'secondary_color', '#ff5f5f' ),
		'classes' => array( 'color', 'leco-cp-background' ),
		'options' => array(
			'alpha' => true,
		),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Secondary Text Color', 'leco-cp' ),
		'id'      => $prefix . 'secondary_text_color',
		'type'    => 'colorpicker',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'secondary_text_color', '#ffffff' ),
		'classes' => array( 'color' ),
		'options' => array(
			'alpha' => true,
		),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Tertiary Background Color', 'leco-cp' ),
		'id'      => $prefix . 'tertiary_color',
		'type'    => 'colorpicker',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'tertiary_color', '#3c5063' ),
		'classes' => array( 'color', 'leco-cp-background' ),
		'options' => array(
			'alpha' => true,
		),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Tertiary Text Color', 'leco-cp' ),
		'id'      => $prefix . 'tertiary_text_color',
		'type'    => 'colorpicker',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'tertiary_text_color', '#ffffff' ),
		'classes' => array( 'color' ),
		'options' => array(
			'alpha' => true,
		),
	) );

	$cmb->add_field( array(
		'name'    => __( 'Your Phone', 'leco-cp' ),
		'id'      => 'phone',
		'type'    => 'text_medium',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'phone' ),
	) );

	$cmb->add_field( array(
		'name'    => __( 'Your Email', 'leco-cp' ),
		'id'      => 'email',
		'type'    => 'text_email',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'email' ),
	) );

	$general_information = wpautop( 'Hello and welcome! Here you can find everything you need during our project from all your files to how to contact me. You\'ll have full access to this area throughout the whole project and for 6 months after completion. However all of your files will still live on in <a href="#">your Dropbox folder.</a>
<h2>How to contact me</h2>
If you have any small questions or comments throughout the project, you can <a class="email" href="mailto:#">email me.</a> For anything larger it\'s best to wait until our next catch up call so we can go through it properly. Our call is scheduled for every <strong>[date-time]</strong>
<h2>Availability</h2>
My working week is <strong>[your-working-hours]</strong>

Occasionally I take time off for holidays so if this is due to happen during our project I will let you know in good time. Other than that you can expect intermittent availability around all major holidays including Christmas, New Year, Easter and (ok, not a major holiday) my birthday - [your-birthday-if-you-like]' );
	$gi = leco_cp_get_option( 'general_information', $general_information );
	if ( isset( $_REQUEST['action'] ) ) {
		$post_id = ( isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) ? $_GET['post'] : 0;

		$general_information = wpautop( get_post_meta( $post_id, $prefix . 'welcome', true ) ) . '<h2>How to contact me</h2>' . wpautop( get_post_meta( $post_id, $prefix . 'contact', true ) ) . '<h2>Availability</h2>' . wpautop( get_post_meta( $post_id, $prefix . 'availability', true ) );
		$gi = '';
	}

	$cmb->add_field( array(
		'name'            => esc_html__( 'General Information', 'leco-cp' ),
		'id'              => $prefix . 'general_information',
		'desc'            => esc_html__( 'Write some general information about this project, including: welcome messages, contact information and your availability.', 'leco-cp' ),
		'type'            => 'wysiwyg',
		'options'         => array( 'textarea_rows' => 12, ),
		'default'         => ( isset( $_REQUEST['action'] ) ) ? '' : $gi,
		'sanitization_cb' => false,
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Update Project', 'leco-cp' ),
		'id'   => $prefix . 'render_update_button',
		'_name' => 'leco_cp_save',
		'type' => 'button',
		'render_row_cb' => 'leco_cp_render_save_button_cb',
		'show_on_cb' => 'leco_cp_show_if_update_client',
		'classes' => 'align-right'
	) );

	$cmb = new_cmb2_box( array(
		'id'           => $prefix . 'info',
		'title'        => esc_html__( 'Project Information', 'leco-cp' ),
		'object_types' => array( 'leco_client', 'leco_template' ), // Post type
		'save_fields'  => ( isset( $_POST['leco_cp_template'] ) && '0' !== $_POST['leco_cp_template'] ) ? false : true,
		'show_on_cb'   => 'leco_cp_show_if_not_manage_phases',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Show project status?', 'leco-cp' ),
		'desc'    => esc_html__( 'This will hide the "Project Status" section (The following 3 settings won\'t be available. The default is "Yes".', 'leco-cp' ),
		'id'      => $prefix . 'show_project_status',
		'type'    => 'select',
		'options' => array(
			'show' => esc_html__( 'Yes', 'leco-cp' ),
			'hide' => esc_html__( 'No', 'leco-cp' ),
		),
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'show_project_status', 'show' ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Current Phase', 'leco-cp' ),
		'desc'    => esc_html__( 'Short & sweet in than 20 characters.', 'leco-cp' ),
		'id'      => $prefix . 'current_phase',
		'type'    => 'text_medium',
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Next Phase', 'leco-cp' ),
		'desc'    => esc_html__( 'Short & sweet in 20 characters.', 'leco-cp' ),
		'id'      => $prefix . 'next_phase',
		'type'    => 'text_medium',
	) );

	$cmb->add_field( array(
		'name'        => esc_html__( 'Estimated Completion Date', 'leco-cp' ),
		'id'          => $prefix . 'completion_date',
		'type'        => 'text_date',
        'description' => esc_html__( 'Here we use the date format "m/d/Y". However, on the frontend it will be displayed in your WordPress Date Format setting.' ),
	) );

	$cmb->add_field( array(
		'name'    => __( 'Phase X Text', 'leco-cp' ),
		'desc'	  => sprintf( __( 'Fancy to change the "Phase 1, 2, 3" terminology to something else? You can replace it here. Say if you enter "Section", you\'ll get them to be "Section 1, 2, 3" in this portal. You can also remove the numbers (1, 2, 3...) from it with CSS, %shere\'s how%s.', 'leco-cp' ), '<a href="https://client-portal.io/client/client-portal-support/module/how-do-i-edit-the-phase-1-2-3-etc-text/" target="_blank">', '</a>' ),
		'id'      => $prefix . 'phase_x_text',
		'type'    => 'text',
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'phase_x_text', __( 'Phase', 'leco-cp' ) ),
	) );

	$cmb->add_field( array(
		'name'    => esc_html__( 'Show the mark as complete ticks?', 'leco-cp' ),
		'desc'    => esc_html__( 'This will enable the client side "Mark as Complete" feature for each module. The default is "Yes".', 'leco-cp' ),
		'id'      => $prefix . 'show_mark_as_complete',
		'type'    => 'select',
		'options' => array(
			'show' => esc_html__( 'Yes', 'leco-cp' ),
			'hide' => esc_html__( 'No', 'leco-cp' ),
		),
		'default' => ( isset( $_REQUEST['action'] ) ) ? '' : leco_cp_get_option( 'show_mark_as_complete', 'show' ),
	) );

	if ( isset( $_REQUEST['action'] ) ) {
		$dropbox_url = get_post_meta( $post_id, 'leco_cp_dropbox', true );
	}

	$icon_meta = array_values( json_decode(file_get_contents(LECO_CLIENT_PORTAL_DIR . "/assets/icon/caviar.json"), true) );
	$icons = array_values( $icon_meta[4] );
	$file = wp_list_pluck( $icons, 'file' );

	$caviar_options = array_combine( array_map( 'leco_cp_remove_dotsvg', $file ), wp_list_pluck( $icons, 'name' ) );

	$default_options = array(
		'Bespoke' => esc_html__( 'About', 'leco-cp' ),
		'Blog'   => esc_html__( 'Branding', 'leco-cp' ),
		'Content'     => esc_html__( 'Content', 'leco-cp' ),
		'Cost'     => esc_html__( 'Payments', 'leco-cp' ),
		'Feedback'     => esc_html__( 'Feedback', 'leco-cp' ),
		'Finding'     => esc_html__( 'Inspiration', 'leco-cp' ),
		'Graphics'     => esc_html__( 'Wireframes', 'leco-cp' ),
		'Hiring'     => esc_html__( 'Taxes', 'leco-cp' ),
		'NotHappy'     => esc_html__( 'Receipts', 'leco-cp' ),
		'Promote'     => esc_html__( 'Proposal', 'leco-cp' ),
		'Quote'     => esc_html__( 'Quote', 'leco-cp' ),
		'Role'     => esc_html__( 'Terms', 'leco-cp' ),
		'SEO'     => esc_html__( 'Design', 'leco-cp' ),
		'Social'     => esc_html__( 'Social', 'leco-cp' ),
		'Testing'     => esc_html__( 'Development', 'leco-cp' ),
	);

	$new_options = array_merge( $default_options, $caviar_options );

	$group_field_id = $cmb->add_field( array(
		'id'          => $prefix . 'cta',
		'desc'       => esc_html__( 'The Call To Action section is consisted with a short paragraph and a clickable button.', 'leco-cp' ),
		'type'        => 'group',
		'repeatable'  => false,
		'options'     => array(
			'group_title'   => esc_html__( 'Call To Action', 'leco-cp' )
		)
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'       => esc_html__( 'Hide CTA section', 'leco-cp' ),
		'desc'       => esc_html__( 'If you don\'t need it, check this and it will be hidden on the project page.', 'leco-cp' ),
		'id'         => 'hidden',
		'type'       => 'checkbox'
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'       => esc_html__( 'Description', 'leco-cp' ),
		'desc'       => esc_html__( 'A short paragraph to tell more about the following action.', 'leco-cp' ),
		'id'         => 'description',
		'type'       => 'wysiwyg',
		'options' => array( 'textarea_rows' => 8, ),
		'default'    => 'Want to see all your files?'
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'       => esc_html__( 'Button Text', 'leco-cp' ),
		'desc'       => esc_html__( 'Text to Call To Action.', 'leco-cp' ),
		'id'         => 'button_text',
		'type'       => 'text',
		'default'    => __( 'Check it out', 'leco-cp' ),
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'       => esc_html__( 'Button URL', 'leco-cp' ),
		'desc'       => esc_html__( 'Where do you want to link people to?', 'leco-cp' ),
		'id'         => 'url',
		'type'       => 'text_url',
		'default'      => ( ! empty( $dropbox_url ) ) ? $dropbox_url : '#'
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'    => esc_html__( 'Open Link in New Tab?', 'leco-cp' ),
		'desc'    => esc_html__( 'Set if the Button URL should be opened in new tab. The default is "No".', 'leco-cp' ),
		'id'      => 'new_tab',
		'type'    => 'select',
		'options' => array(
			'current' => esc_html__( 'No', 'leco-cp' ),
			'new_tab' => esc_html__( 'Yes', 'leco-cp' ),
		),
	) );

	if ( isset( $_REQUEST['action'] ) ) {
		if ( ! empty( $post_id ) ) {
			$post = get_post( $post_id );
		}

		if ( ( isset( $post ) && empty( $post->post_password ) ) || ( isset( $_POST['post_password'] ) && empty ( $_POST['post_password'] ) ) ) { // if post password not set, show this option
			$cmb->add_field( array(
				'name'    => esc_html__( 'Public Project', 'leco-cp' ),
				'desc'    => sprintf( esc_html__( 'Set this to "Yes" for public projects. If set to "Yes" %s', 'leco-cp' ), '<strong style="color:red">' . __( 'anyone can view this project as long as they have the URL.', 'leco-cp' ) . '</strong>' ),
				'id'      => $prefix . 'public_portal',
				'type'    => 'select',
				'options' => array(
					'no'  => esc_html__( 'No', 'leco-cp' ),
					'yes' => esc_html__( 'Yes', 'leco-cp' ),
				),
			) );
		}

		$public_portal = get_post_meta( $post_id, 'leco_cp_public_portal', true );
		if ( ( isset( $post ) && empty( $post->post_password ) && ( 'no' == $public_portal || ! $public_portal ) ) || ( isset( $_POST['post_password'] ) && empty ( $_POST['post_password'] ) ) ) { // if post password not set, show this option
			$clients       = get_users( array( 'role__in' => apply_filters( 'leco_cp_client_roles', array( 'leco_client' ) ) ) );
			$clients_id    = wp_list_pluck( $clients, 'ID' );
			$user_login = wp_list_pluck( $clients, 'user_login' );
			$cmb->add_field( array(
				'name'             => esc_html__( 'Client Accounts', 'leco-cp' ),
				'desc'             => sprintf( esc_html__( 'Only the selected client account can view this page on front end. %s', 'leco-cp' ), '<strong style="color:red">' . __( 'If you set a post password to protect it, this option will be omitted.', 'leco-cp' ) . '</strong>' ),
				'id'               => $prefix . 'client',
				'type'             => 'select_multiple',
//				'show_option_none' => true,
				'options'          => ( empty( $clients ) ) ? array() : array_combine( $clients_id, $user_login ),
			) );
		}
	}

	$cmb->add_field( array(
		'name'             => esc_html__( 'Number of Phases', 'leco-cp' ),
		'id'               => $prefix . 'number_of_parts',
		'type'             => 'hidden',
		'default'          => '3',
		'save_field' => ( isset( $_REQUEST['action'] ) ) ? false : true,
	) );

//	$cmb->add_field( array(
//		'name' => 'Manage Phases',
//		'desc' => esc_html__( 'If you need more phases, or you\'d like to delete some, or maybe even reorder them, you can click the "Manage Phases" button.', 'leco-cp' ),
//		'id'   => $prefix . 'render_manage_modules_cb',
//		'type' => 'button',
//		'render_row_cb' => 'leco_cp_render_manage_modules_cb',
//		'show_on_cb' => 'leco_cp_show_if_update_client',
//	) );

	$number_of_parts = 3;
	if ( isset( $_REQUEST['action'] ) ) { // dynamically set $number_of_parts, when on edit screen or on submitting the post
		if ( ! empty( $post_id ) ) {
			if ( metadata_exists( 'post', $post_id, 'leco_cp_number_of_parts' ) ) { // backward compatibility
				$number_of_parts = absint( get_post_meta( $post_id, 'leco_cp_number_of_parts', true ) );
			}
		} elseif ( isset( $_POST['leco_cp_number_of_parts'] ) && ! empty( $_POST['leco_cp_number_of_parts'] ) ) {
			$number_of_parts = absint( $_POST['leco_cp_number_of_parts'] );
		}
	}

	$cmb->add_field( array(
		'name' => esc_html__( 'Save & Show Modules', 'leco-cp' ),
		'desc' => esc_html__( 'Saving client information and generating default modules.', 'leco-cp' ),
		'id'   => $prefix . 'render_save_button_cb',
		'_name' => 'leco_cp_save',
		'type' => 'button',
		'render_row_cb' => 'leco_cp_render_save_button_cb',
		'show_on_cb' => 'leco_cp_show_if_add_client',
		'classes' => 'align-right'
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Update Project', 'leco-cp' ),
		'id'   => $prefix . 'render_update_button',
		'_name' => 'leco_cp_save',
		'type' => 'button',
		'render_row_cb' => 'leco_cp_render_save_button_cb',
		'show_on_cb' => 'leco_cp_show_if_update_client',
        'classes' => 'align-right'
	) );

	$default_titles = array(
		esc_html__( 'Discovery', 'leco-cp' ),
		esc_html__( 'Website', 'leco-cp' ),
		esc_html__( 'Assets', 'leco-cp' )
	);

	for ( $i = 0; $i < $number_of_parts; $i++ ) {
		$number = $i+1;
		/**
		 * Repeatable Field Groups
		 */
		$cmb_group = new_cmb2_box( array(
			'id'           => $prefix . 'part_' . $i,
			'title'        => esc_html__( 'Phase ' . $number, 'leco-cp' ),
			'object_types' => array( 'leco_client', 'leco_template' ),
			'show_on_cb' => 'leco_cp_show_if_update_client',
		) );

		$cmb_group->add_field( array(
			'name'    => esc_html__( 'Title', 'leco-cp' ),
			'id'      => $prefix . 'part_' . $i . '_title',
			'type'    => 'text',
			'default' => ( $i < 3 ) ? $default_titles[ $i ] : '',
		) );

		// $group_field_id is the field id string
		$group_field_id = $cmb_group->add_field( array(
			'id'          => $prefix . 'part_' . $i . '_module',
			'type'        => 'group',
			'options'     => array(
				'group_title'   => esc_html__( 'Phase ' . $number . ' - Module {#}', 'leco-cp' ), // {#} gets replaced by row number
				'add_button'    => esc_html__( 'Add Another Module', 'leco-cp' ),
				'remove_button' => esc_html__( 'Remove Module', 'leco-cp' ),
				'sortable'      => true,
				'closed'        => ( 'opened' != leco_cp_get_option( 'module_status', 'closed' ) ) ? true : false
			),
		) );

		/**
		 * Group fields works the same, except ids only need
		 * to be unique to the group. Prefix is not needed.
		 *
		 * The parent field's id needs to be passed as the first argument.
		 */
		$cmb_group->add_group_field( $group_field_id, array(
			'name'    => esc_html__( 'Title', 'leco-cp' ),
			'id'      => 'title',
			'type'    => 'text',
			'classes' => array( 'module-title' ),
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'        => esc_html__( 'Description', 'leco-cp' ),
			'description' => esc_html__( 'Write a short description for this entry', 'leco-cp' ),
			'id'          => 'description',
			'type'        => 'textarea_small',
			'classes'     => array( 'module-desc' ),
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'       => esc_html__( 'Icon', 'leco-cp' ),
			'id'               => 'icon',
			'type'             => 'select',
			'options'          => $new_options,
			'attributes' => array(
					'class' => 'select2-icon'
			)
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'       => esc_html__( 'Module Type', 'leco-cp' ),
			'desc'       => esc_html__( 'You can set a URL, upload File(s) or select a content page.', 'leco-cp' ),
			'id'         => 'type',
			'type'       => 'select',
			'options'    => array(
				'url'          => esc_html__( 'URL', 'leco-cp' ),
				'files'        => esc_html__( 'Files', 'leco-cp' ),
				'private-files'        => esc_html__( 'Private Files', 'leco-cp' ),
				'client-uploads'        => esc_html__( 'Client Uploads', 'leco-cp' ),
				'content_page' => esc_html__( 'Content Page', 'leco-cp' ),
			),
			'classes'    => array( 'module-type-select' ),
			'before_row' => '<div class="module-type-group">'
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'    => esc_html__( 'URL', 'leco-cp' ),
			'id'      => 'url',
			'type'    => 'text_url',
			'classes' => array( 'module-type module-type-url' ),
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'         => esc_html__( 'Files', 'leco-cp' ),
			'desc'         => esc_html__( 'Upload or add multiple images/attachments.', 'leco-cp' ),
			'id'           => 'files',
			'type'         => 'file_list',
			'preview_size' => array( 100, 100 ), // Default: array( 50, 50 ),
			'classes'      => array( 'module-type module-type-files' ),
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'         => esc_html__( 'Private Files', 'leco-cp' ),
			'desc'         => sprintf( '%s <strong style="color: red;">Please note we currently don\'t support the following file types: %s.</strong>', esc_html__( 'Upload or add multiple attachments.', 'leco-cp' ), implode( ', ', apply_filters( 'leco_cp_protected_directory_forbidden_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ) ) ) ),
			'id'           => 'private_files',
			'type'         => 'file_list',
			'preview_size' => array( 100, 100 ), // Default: array( 50, 50 ),
			'classes'      => array( 'module-type module-type-private-files' ),
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'    => esc_html__( 'Client Uploads', 'leco-cp' ),
			'id'      => 'client_uploads',
			'type'    => 'client_uploads',
			'classes' => array( 'module-type module-type-client-uploads' ),
		) );

		$content_pages = get_posts( array(
			'post_type'   => 'leco_content_page',
			'numberposts' => - 1,
		) );
		$ids           = ( empty( $content_pages ) ) ? array() : wp_list_pluck( $content_pages, 'ID' );
		$titles        = ( empty( $content_pages ) ) ? array() : wp_list_pluck( $content_pages, 'post_title' );

		$default_option = array( '0' => esc_html__( 'Create New', 'leco-cp' ) );
		$content_page_desc = ( post_type_supports( 'leco_content_page', 'comments' ) ) ? esc_html__( 'Select a content page. Some pages are grey out because there are comments attached to them.', 'leco-cp' ) : esc_html__( 'Select a content page.', 'leco-cp' );
		$cmb_group->add_group_field( $group_field_id, array(
			'name'    => esc_html__( 'Content Page', 'leco-cp' ),
			'id'      => 'content_page',
			'desc'    => $content_page_desc,
			'type'    => 'select',
			'options' => ( empty( $content_pages ) ) ? $default_option : array_replace( $default_option, array_combine( $ids, $titles ) ),
			'classes' => array( 'module-type module-type-content-page' ),
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'       => esc_html__( 'Open Link in New Tab?', 'leco-cp' ),
			'desc' => sprintf( esc_html__( 'Please set the default open link behaviour in %s. To set the behaviour ONLY for this module, choose from the dropdown above.', 'leco-cp' ), '<a href="' . admin_url( 'edit.php?post_type=leco_client&page=leco_cp_options' ) . '" target="cp-settings">' . __( 'Settings', 'leco-cp' ) . '</a>' ),
			'id'               => 'new_tab',
			'type'             => 'select',
			'options'          => array(
				'default' => esc_html__( 'Default', 'leco-cp' ),
				'new_tab' => esc_html__( 'Yes', 'leco-cp' ),
				'current'   => esc_html__( 'No', 'leco-cp' ),
			),
			'before_row' => '</div>'
		) );

		$cmb_group->add_group_field( $group_field_id, array(
			'name'       => esc_html__( 'Status', 'leco-cp' ),
			'desc' => esc_html__( 'Current status for this module.', 'leco-cp' ),
			'id'               => 'status',
			'type'             => 'select',
			'options'          => array(
				'active' => esc_html__( 'Active', 'leco-cp' ),
				'completed' => esc_html__( 'Completed', 'leco-cp' ),
				'inactive'   => esc_html__( 'Inactive', 'leco-cp' ),
			),
		) );

		$cmb_group->add_field( array(
			'name' => esc_html__( 'Update Project', 'leco-cp' ),
			'id'   => $prefix . 'render_update_button',
			'_name' => 'leco_cp_save',
			'type' => 'button',
			'render_row_cb' => 'leco_cp_render_save_button_cb',
			'show_on_cb' => 'leco_cp_show_if_update_client',
			'classes' => 'align-right'
		) );
	}

	$cmb = new_cmb2_box( array(
		'id'           => $prefix . 'manage_phases',
		'title'        => esc_html__( 'Manage Phases', 'leco-cp' ),
		'object_types' => array( 'leco_client', 'leco_template' ),
		'save_fields'  => ( isset( $_POST['leco_cp_template'] ) && '0' !== $_POST['leco_cp_template'] ) ? false : true,
		'show_on_cb' => 'leco_cp_show_if_manage_phases'
	) );

	$group_field_id = $cmb->add_field( array(
		'id'          => $prefix . 'phases',
		'type'        => 'group',
		'desc'       => esc_html__( 'Add new phases, reorder or delete them here.', 'leco-cp' ),
		'options'     => array(
			'group_title'   => esc_html__( 'Phase {#}', 'leco-cp' ),
			'add_button'    => esc_html__( 'Add New Phase', 'leco-cp' ),
			'remove_button' => esc_html__( 'Remove Phase', 'leco-cp' ),
			'sortable'      => true
		),
		'show_on_cb' => 'leco_cp_show_if_manage_phases'
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'       => esc_html__( 'Phase Title', 'leco-cp' ),
		'id'         => 'title',
		'type'       => 'text'
	) );

	$cmb->add_group_field( $group_field_id, array(
		'name'       => esc_html__( 'Phase Number', 'leco-cp' ),
		'id'         => 'number',
		'type'       => 'text',
		'classes' => 'hidden',
		'attributes' => array(
			'readonly' => 'readonly'
		)
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Save', 'leco-cp' ),
		'id'   => $prefix . 'render_update_button',
		'desc' => esc_html__( 'You will be redirected to the project edit screen after saving the changes.', 'leco-cp' ),
		'_name' => 'leco_cp_save',
		'type' => 'button',
		'render_row_cb' => 'leco_cp_render_save_button_cb',
		'show_on_cb' => 'leco_cp_show_if_manage_phases',
		'classes' => 'align-right'
	) );
}
add_action( 'cmb2_admin_init', 'leco_cp_register_metabox' );

/**
 * Manipulate the "Phases" meta value
 *
 * @see https://github.com/CMB2/CMB2/issues/768#issuecomment-254224623
 * @return array
 */
function leco_cp_phases_meta_value() {
	$post_id = ( isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) ? $_GET['post'] : 0;
	$phases = array();

	$number_of_parts = get_post_meta( $post_id, 'leco_cp_number_of_parts', true );
	if ( ! $number_of_parts ) {
		$number_of_parts = 3;
	} // backward compatibility
	for ( $i = 0; $i < $number_of_parts; $i ++ ) {
		$phase = get_post_meta( $post_id, 'leco_cp_part_' . $i . '_title', true );
		if ( $phase ) {
			$phases[] = array(
				'title'  => $phase,
				'number' => $i,
			);
		} else {
			$phases[] = array(
				'title'  => '',
				'number' => $i
			);
		}
	}

	return $phases;
}
add_action( 'cmb2_override_leco_cp_phases_meta_value', 'leco_cp_phases_meta_value' );

/**
 * Manipulating data when post meta updated.
 *
 * @since unknown
 * @since 4.4     Add new action for "leco_cp_client" meta key.
 *
 * @param $meta_id
 * @param $object_id
 * @param $meta_key
 * @param $meta_value
 */
function leco_cp_updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
	if ( 'leco_cp_phases' === $meta_key ) {
		if ( defined('WP_LOAD_IMPORTERS') ) {
			return;
		}

		$phases = maybe_unserialize( $meta_value );
		// update number of phases
		update_post_meta( $object_id, 'leco_cp_number_of_parts', count( $phases ) );

		foreach ( $phases as $key => $phase ) { // Store old module before update them
			$old_module = "old_{$phase['number']}_module";
			$$old_module = get_post_meta( $object_id, "leco_cp_part_{$phase['number']}_module", true );
		}

		foreach ( $phases as $key => $phase ) {
			// Check if it's a new phase (would be no number)
			if ( "" === $phase['number'] ) {
				update_post_meta( $object_id, "leco_cp_part_{$key}_title", $phase['title'] );
				update_post_meta( $object_id, "leco_cp_part_{$key}_module", '' );
			} else { // update phases
				$old_module = "old_{$phase['number']}_module";
				update_post_meta( $object_id, "leco_cp_part_{$key}_title", $phase['title'] );
				update_post_meta( $object_id, "leco_cp_part_{$key}_module", $$old_module );
			}
		}
	} elseif ( 'leco_cp_client' === $meta_key ) {
		leco_cp_set_user_projects( $meta_value, $object_id );
	}
}
add_action( 'updated_postmeta', 'leco_cp_updated_postmeta', 10, 4 );

/**
 * Manipulating data before post meta updated.
 *
 * @since 4.4     Add action for "leco_cp_client" meta key.
 *
 * @param int|array $meta_ids   ID or array of IDs of metadata entry to update.
 * @param int       $object_id  Post ID.
 * @param string    $meta_key   Meta key.
 * @param mixed     $meta_value Meta value. This will be a PHP-serialized string representation of the value if
 *                              the value is an array, an object, or itself a PHP-serialized string.
 */
function leco_cp_update_postmeta( $meta_ids, $object_id, $meta_key, $meta_value ) {
	if ( 'leco_cp_client' === $meta_key ) {
		leco_cp_set_user_projects( $meta_value, $object_id, true );
	} elseif ( preg_match( '/leco_cp_part_\d+_module/', $meta_key ) === 1 ) {
		if ( $meta_value && ! wp_is_post_revision( $object_id ) ) {
			LECO_CP_Client_Upload::cleanup( $object_id, $meta_key, $meta_value );
		}
	}
}
add_action( 'delete_post_meta', 'leco_cp_update_postmeta', 10, 4 );
add_action( 'update_postmeta', 'leco_cp_update_postmeta', 10, 4 );

function leco_cp_added_post_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
	switch ( $meta_key ) {
		case 'leco_cp_phases':
			if ( defined('WP_LOAD_IMPORTERS') ) {
				return;
			}

			$phases = $meta_value;
			// update number of phases
			update_post_meta( $object_id, 'leco_cp_number_of_parts', count( $phases ) );

			foreach ( $phases as $key => $phase ) { // Store old module before update them
				$old_module = "old_{$phase['number']}_module";
				$$old_module = get_post_meta( $object_id, "leco_cp_part_{$phase['number']}_module", true );
			}

			foreach ( $phases as $key => $phase ) {
				// Check if it's a new phase (would be no number)
				if ( "" === $phase['number'] ) {
					update_post_meta( $object_id, "leco_cp_part_{$key}_title", $phase['title'] );
					update_post_meta( $object_id, "leco_cp_part_{$key}_module", '' );
				} else { // update phases
					$old_module = "old_{$phase['number']}_module";
					update_post_meta( $object_id, "leco_cp_part_{$key}_title", $phase['title'] );
					update_post_meta( $object_id, "leco_cp_part_{$key}_module", $$old_module );
				}
			}
			break;
		case 'leco_cp_template':
			if ( "0" === $meta_value ) {
				return;
			}

			// Update project with template settings
			$template = get_post( $meta_value );
			if ( empty( $template ) ) {
			    return;
            }

			$current_user    = wp_get_current_user();
			$new_post_author = $current_user->ID;

			$args = array(
				'comment_status' => $template->comment_status,
				'ping_status'    => $template->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $template->post_content,
				'post_excerpt'   => $template->post_excerpt,
				'post_parent'    => $template->post_parent,
				'post_password'  => $template->post_password,
				'to_ping'        => $template->to_ping,
				'menu_order'     => $template->menu_order,
				'ID'             => $object_id
			);

			wp_update_post( $args );

			// remove all default modules
			for ( $i=0; $i<3; $i++ ) {
				delete_post_meta( $object_id, "leco_cp_part_{$i}_title" );
				delete_post_meta( $object_id, "leco_cp_part_{$i}_module" );
			}

			global $wpdb;
			$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$meta_value" );
			if ( count( $post_meta_infos ) != 0 ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key        = $meta_info->meta_key;
					$meta_value      = addslashes( $meta_info->meta_value );
					$sql_query_sel[] = "SELECT $object_id, '$meta_key', '$meta_value'";
				}
				$sql_query .= implode( " UNION ALL ", $sql_query_sel );
				$wpdb->query( $sql_query );
			}

			break;
        case 'leco_cp_client':
	        leco_cp_set_user_projects( $meta_value, $object_id );
            break;
	}
}
add_action( 'added_post_meta', 'leco_cp_added_post_meta', 10, 4 );
