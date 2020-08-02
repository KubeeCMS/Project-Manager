<?php
/**
 * Scripts
 *
 * @package     ClientPortal\Scripts
 * @since       1.0.0
 *
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since  1.0.0
 * @global string $post_type The type of post that we are editing
 *
 * @param  string $hook Hook.
 *
 * @return void
 */
function leco_cp_admin_scripts( $hook ) {
	global $post_type;

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	if ( in_array( $post_type, array( 'leco_client', 'leco_template', 'leco_content_page' ) ) ) {
		wp_dequeue_script( 'select2' );
		wp_dequeue_style( 'select2' );

		wp_register_style( 'leco-cp-admin', LECO_CLIENT_PORTAL_URL . 'assets/css/admin' . $suffix . '.css', array() );
		wp_register_style( 'leco-cp-select2', LECO_CLIENT_PORTAL_URL . 'assets/css/select2.min.css', array() );
		wp_enqueue_style( 'leco-cp-select2' );
		wp_enqueue_style( 'leco-cp-admin' );

		wp_enqueue_script( 'leco_cp_select2', LECO_CLIENT_PORTAL_URL . 'assets/js/select2.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'leco_cp_admin_js', LECO_CLIENT_PORTAL_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery' ), LECO_CLIENT_PORTAL_VER );

		$vars = array(
			'iconURL'         => LECO_CLIENT_PORTAL_URL . 'assets/icon/',
			'_wpnonce'        => wp_create_nonce( 'leco_cp_ajax_nonce' ),
			'permalink'       => get_permalink(),
			'supportComments' => ( post_type_supports( 'leco_content_page', 'comments' ) ) ? 1 : 0,
		);
		wp_localize_script( 'leco_cp_admin_js', 'lecoCPAdminVars', $vars );
	} elseif ( 'leco_client_page_leco_cp_options' === $hook ) {
		wp_register_style( 'leco-cp-admin', LECO_CLIENT_PORTAL_URL . 'assets/css/admin' . $suffix . '.css', array(), LECO_CLIENT_PORTAL_VER );
		wp_enqueue_style( 'leco-cp-admin' );

		wp_enqueue_script( 'leco_cp_admin_settings_js', LECO_CLIENT_PORTAL_URL . 'assets/js/admin-settings' . $suffix . '.js', array( 'jquery' ), LECO_CLIENT_PORTAL_VER );
	}

	// Fix for Yoast SEO. There was JS errors because on this screen, we don't have WP media JS enqueued.
	if ( function_exists( 'wpseo_init' ) && isset( $_GET['cp-action'] ) ) {
		wp_enqueue_media();
	}
}

add_action( 'admin_enqueue_scripts', 'leco_cp_admin_scripts', 100 );

/**
 * Enqueue jQuery in front end template
 *
 * @since 4.7     Load template scripts and styles first.
 * @since unknown
 */
function leco_cp_enqueue_scripts() {
	if ( apply_filters( 'leco_cp_remove_theme_css_js', '__return_true' ) ) {
		leco_cp_remove_theme_css_js();
	}

	$leco_cp_template = apply_filters( 'leco_cp_get_template', leco_cp_get_option( 'template', 'tailwind' ) );
	if ( 'default' !== $leco_cp_template ) {
		do_action( 'leco_cp_enqueue_scripts' );

		return;
	}

	// The following scripts only work for default theme. Will be deprecated once we remove support for the default theme.
	wp_register_script( 'leco-cp-lity', LECO_CLIENT_PORTAL_URL . 'templates/default/assets/js/lity.min.js', array( 'jquery' ), LECO_CLIENT_PORTAL_VER );
	wp_register_script( 'leco-cp-scripts', LECO_CLIENT_PORTAL_URL . 'templates/default/assets/js/scripts.min.js', array( 'leco-cp-lity' ), LECO_CLIENT_PORTAL_VER, true );
	wp_register_style( 'leco-cp-lity', LECO_CLIENT_PORTAL_URL . 'templates/default/assets/css/lity.min.css', array(), LECO_CLIENT_PORTAL_VER );
	wp_register_style( 'leco-cp-content-page', LECO_CLIENT_PORTAL_URL . 'templates/default/assets/css/content-page.css', array(), LECO_CLIENT_PORTAL_VER );
	$deps = array( 'leco-cp-lity' );
	if ( get_query_var( 'leco_content_page' ) ) {
		$deps[] = 'leco-cp-content-page';
	}
	if ( is_page( 'client-portal-login' ) ) {
		$deps[] = 'dashicons';
	}
	wp_register_style( 'leco-cp-style', LECO_CLIENT_PORTAL_URL . 'templates/default/style.css', $deps, LECO_CLIENT_PORTAL_VER );

	wp_enqueue_script( 'leco-cp-scripts' );
	$vars = array(
		'_wpnonce' => wp_create_nonce( 'leco_cp_ajax_nonce' ),
		'post_id'  => get_the_ID(),
		'ajaxurl'  => admin_url( 'admin-ajax.php', 'relative' ),
	);
	wp_localize_script( 'leco-cp-scripts', 'lecoCPVars', $vars );

	wp_enqueue_style( 'leco-cp-style' );

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

	do_action( 'leco_cp_enqueue_scripts' );
}
