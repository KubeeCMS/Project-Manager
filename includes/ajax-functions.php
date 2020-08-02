<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     LECO_CP
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2019, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function leco_cp_get_permalink() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	if ( isset( $_POST['permalink'] ) ) {
		$name = get_post_field( 'post_name', $_POST['post_id'] );
		wp_die( trailingslashit( urldecode( $_POST['permalink'] ) . 'module/' . $name ) );
	} else {
		wp_die( get_permalink( intval( $_POST['post_id'] ) ) );
	}
}
add_action( 'wp_ajax_leco_cp_get_permalink', 'leco_cp_get_permalink' );

function leco_cp_ajax_mark_as_complete() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	extract( $_POST );
	$post_id = intval( $post_id );
	$phase = intval( $phase ) - 1;
	$key = intval( $key ) - 1;
	$modules = get_post_meta( $post_id, "leco_cp_part_{$phase}_module", true );
	$modules[$key]['status'] = 'completed';
	$r = update_post_meta( $post_id, "leco_cp_part_{$phase}_module", $modules );

	if ( $r ) {
		// only send email notification for projects.
		if ( 'leco_client' === get_post_type( $post_id ) ) {
			leco_cp_send_admin_module_completed_notification( $post_id, $phase, $key );
		}

		wp_die( intval( $post_id ) );
	} else {
		wp_die(-1);
	}
}
add_action( 'wp_ajax_leco_cp_mark_as_complete', 'leco_cp_ajax_mark_as_complete' );

/**
 * Dismiss notice AJAX helper.
 *
 * @since 4.7.1
 */
function leco_cp_dismiss_notice() {
	$notice = sanitize_text_field( $_POST['notice'] );

	// check nonce.
	check_ajax_referer( 'leco_cp_hide_notice-' . $notice, 'nonce' );

	// update option.
	update_option( 'leco_cp_hide_notice-' . $notice, 1, 'no' );

	// send JSON.
	wp_send_json( array( 'response' => 'success' ) );
}
add_action( 'wp_ajax_leco_cp_dismiss_notice', 'leco_cp_dismiss_notice' );

/**
 * AJAX helper function for client uploads.
 *
 * @since 4.8
 */
function leco_cp_client_upload() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	LECO_CP_Client_Upload::upload();
}

add_action( 'wp_ajax_leco_cp_client_upload', 'leco_cp_client_upload' );

/**
 * AJAX helper function to save client uploads to metadata.
 *
 * @since 4.8
 */
function leco_cp_client_upload_complete() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	LECO_CP_Client_Upload::save();
}

add_action( 'wp_ajax_leco_cp_client_upload_complete', 'leco_cp_client_upload_complete' );

/**
 * AJAX helper function to delete client uploads.
 *
 * @since 4.8
 */
function leco_cp_client_upload_delete() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	LECO_CP_Client_Upload::delete();
}

add_action( 'wp_ajax_leco_cp_client_upload_delete', 'leco_cp_client_upload_delete' );
