<?php
/**
 * Error Tracking
 *
 * Mostly borrowed from EDD.
 *
 * @package     LECO_Client_Portal
 * @subpackage  Functions/Errors
 * @copyright   Copyright (c) 2019, Laura Elizabeth
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print Errors
 *
 * Prints all stored errors. For use during checkout.
 * If errors exist, they are returned.
 *
 * @since 4.6
 *
 * @uses leco_cp_get_errors()
 * @uses leco_cp_clear_errors()
 * @return void
 */
function leco_cp_print_errors() {
	$errors = leco_cp_get_errors();
	if ( $errors ) {

		$classes = apply_filters( 'leco_cp_error_class', array(
			'leco-cp-errors',
			'leco-cp-alert',
			'leco-cp-alert-error'
		) );

		if ( ! empty( $errors ) ) {
			echo '<div class="' . implode( ' ', $classes ) . '">';
			// Loop error codes and display errors.
			foreach ( $errors as $error_id => $error ) {

				echo '<p class="leco-cp-error" id="' . $error_id . '">' . $error . '</p>';

			}

			echo '</div>';
		}

		leco_cp_clear_errors();

	}
}

add_action( 'leco_cp_print_errors', 'leco_cp_print_errors' );

/**
 * Get Errors
 *
 * Retrieves all error messages stored.
 * If errors exist, they are returned.
 *
 * @since 4.6
 *
 * @uses LECO_CP_Session::get()
 * @return mixed array if errors are present, false if none found
 */
function leco_cp_get_errors() {
	$errors = LECO_Client_Portal()->session->get( 'leco_cp_errors' );
	$errors = apply_filters( 'leco_cp_errors', $errors );

	return $errors;
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 4.6
 *
 * @uses LECO_CP_Session::get()
 *
 * @param string $error_id ID of the error being set.
 * @param string $error_message Message to store with the error.
 *
 * @return void
 */
function leco_cp_set_error( $error_id, $error_message ) {
	$errors = leco_cp_get_errors();
	if ( ! $errors ) {
		$errors = array();
	}
	$errors[ $error_id ] = $error_message;
	LECO_Client_Portal()->session->set( 'leco_cp_errors', $errors );
}

/**
 * Clears all stored errors.
 *
 * @since 4.6
 *
 * @return void
 */
function leco_cp_clear_errors() {
	LECO_Client_Portal()->session->set( 'leco_cp_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 4.6
 *
 * @param int $error_id ID of the error being set.
 */
function leco_cp_unset_error( $error_id ) {
	$errors = leco_cp_get_errors();
	if ( $errors ) {
		unset( $errors[ $error_id ] );
		LECO_Client_Portal()->session->set( 'leco_cp_errors', $errors );
	}
}

/**
 * Register die handler for leco_cp_die()
 *
 * @since 4.6
 * \ */
function _leco_cp_die_handler() {
	if ( defined( 'LECO_CP_UNIT_TESTS' ) ) {
		return '_leco_cp_die_handler';
	} else {
		die();
	}
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using leco_cp_die() in the unit tests.
 *
 * @since 4.6
 *
 * @param string $message Die message.
 * @param string $title Window title.
 * @param int    $status HTTP status code.
 *
 * @return void
 */
function leco_cp_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_leco_cp_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_leco_cp_die_handler', 10, 3 );

	wp_die( esc_html( $message ), esc_html( $title ), array( 'response' => intval( $status ) ) );
}
