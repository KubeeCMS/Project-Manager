<?php
/**
 * Enqueue Tailwind scripts.
 *
 * @since 4.7
 */
function leco_cp_tailwind_enqueue_scripts() {
	wp_register_script( 'leco-cp-lity', LECO_CLIENT_PORTAL_URL . 'templates/tailwind/assets/js/lity.min.js', array( 'jquery' ), LECO_CLIENT_PORTAL_VER, true );
	wp_register_script( 'leco-cp-scripts', LECO_CLIENT_PORTAL_URL . 'templates/tailwind/assets/js/scripts.min.js', array( 'leco-cp-lity', 'plupload-all' ), LECO_CLIENT_PORTAL_VER, true );
	wp_register_style( 'leco-cp-lity', LECO_CLIENT_PORTAL_URL . 'templates/tailwind/assets/css/lity.min.css', array(), LECO_CLIENT_PORTAL_VER );

	$deps = array( 'leco-cp-lity' );
	if ( is_page( 'client-portal-login' ) ) {
		$deps[] = 'dashicons';
	}

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'leco-cp-style', LECO_CLIENT_PORTAL_URL . "templates/tailwind/build/style{$suffix}.css", $deps, LECO_CLIENT_PORTAL_VER );
	wp_enqueue_style( 'leco-cp-style' );

	wp_enqueue_script( 'leco-cp-scripts' );
	$vars = array(
		'_wpnonce'             => wp_create_nonce( 'leco_cp_ajax_nonce' ),
		'post_id'              => get_the_ID(),
		'ajaxurl'              => admin_url( 'admin-ajax.php', 'relative' ),
		'delete_client_upload' => wp_strip_all_tags( __( 'Are you sure to delete this file?', 'leco-cp' ) ),
		'flash_swf_url'        => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url'  => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters'              => array(
			'mime_types'    => array(
				array(
					'title'      => wp_strip_all_tags( __( 'Allowed Files', 'leco-cp' ) ),
					'extensions' => LECO_CP_Client_Upload::get_allowed_extension(),
				),
			),
			'max_file_size' => wp_max_upload_size() . 'b',
		),
		'failed_to_upload'     => wp_strip_all_tags( __( 'Failed to upload file: ', 'leco-cp' ) ),
		'error'                => wp_strip_all_tags( __( 'Error: ', 'leco-cp' ) ),
	);
	wp_localize_script( 'leco-cp-scripts', 'lecoCPVars', $vars );

	// for comments.
	if ( get_query_var( 'leco_content_page' ) && get_option( 'thread_comments' ) ) {
		$content_pages = get_posts(
			array(
				'post_type' => 'leco_content_page',
				'name'      => get_query_var( 'leco_content_page' ),
			)
		);
		if ( comments_open( $content_pages[0]->ID ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}
add_action( 'leco_cp_enqueue_scripts', 'leco_cp_tailwind_enqueue_scripts' );

/**
 * Add body classes.
 *
 * @since 4.7
 *
 * @param array $classes Classes.
 *
 * @return array
 */
function leco_cp_tailwind_body_class( $classes ) {
	if ( get_query_var( 'leco_content_page' ) ) {
		$classes[] = 'leco-content-page';
	}

	return $classes;
}
add_filter( 'body_class', 'leco_cp_tailwind_body_class' );
