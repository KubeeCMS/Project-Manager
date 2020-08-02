<?php
/**
 * Login action (hook) functions.
 *
 * @package     LECO_Client_Portal
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2019, Laura Elizabeth
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reset password form init actions (set cookies etc.).
 *
 * @since 4.6
 */
function leco_cp_password_reset_init() {
	if ( ! is_page( 'client-portal-login' ) ) {
		return;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
	if ( 'rp' !== $action && 'resetpass' !== $action ) {
		return;
	}

	if ( isset( $_GET['leco-cp-error'] ) ) {
		return;
	}

	list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
	$rp_cookie       = 'wp-resetpass-' . COOKIEHASH;
	if ( isset( $_GET['key'] ) ) {
		$value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
		setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_safe_redirect( remove_query_arg( array( 'key', 'login' ) ) );
		exit;
	}

	if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
		list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
		$user                      = check_password_reset_key( $rp_key, $rp_login );
		if ( isset( $_POST['pass1'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
			$user = false;
		}
	} else {
		$user = false;
	}

	$errors = new WP_Error();

	if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {
		$errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
	}

	if ( ! $user || is_wp_error( $user ) ) {
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		if ( $user && $user->get_error_code() === 'expired_key' ) {
			wp_redirect( leco_cp_login_url() . '?action=lostpassword&leco-cp-error=expiredkey' );
		} else {
			wp_redirect( leco_cp_login_url() . '?action=lostpassword&leco-cp-error=invalidkey' );
		}
		exit;
	}

	/**
	 * Fires before the password reset procedure is validated.
	 *
	 * @since 4.6
	 *
	 * @param object           $errors WP Error object.
	 * @param WP_User|WP_Error $user   WP_User object if the login and reset key match. WP_Error object otherwise.
	 */
	do_action( 'validate_password_reset', $errors, $user );

	if ( empty( $errors->errors ) && isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
		LECO_Client_Portal()->session->set( 'leco_cp_password_reset_user', $user->ID );

		reset_password( $user, $_POST['pass1'] );
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
	}

	if ( ! empty( $errors->errors ) ) {
		leco_cp_set_error( 'leco-cp-reset-password-error', $errors->get_error_message() );
	}
}
add_action( 'template_redirect', 'leco_cp_password_reset_init' );

/**
 * Display login form messages (determined with URL params).
 *
 * Messages are from from wp-login.php.
 *
 * @since 4.6
 */
function leco_cp_before_login_form_messages() {
	$wp_error = new WP_Error();
	// Some parts of this script use the main login form to display a message.
	if ( isset( $_GET['loggedout'] ) && true == $_GET['loggedout'] ) {
		$wp_error->add( 'loggedout', __( 'You are now logged out.' ), 'message' );
	} elseif ( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] ) {
		$wp_error->add( 'registerdisabled', __( 'User registration is currently not allowed.' ) );
	} elseif ( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] ) {
		$wp_error->add( 'confirm', __( 'Check your email for the confirmation link.' ), 'message' );
	} elseif ( isset( $_GET['checkemail'] ) && 'newpass' == $_GET['checkemail'] ) {
		$wp_error->add( 'newpass', __( 'Check your email for your new password.' ), 'message' );
	} elseif ( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] ) {
		$wp_error->add( 'registered', __( 'Registration complete. Please check your email.' ), 'message' );
	}

	if ( ! empty( $wp_error->errors ) ) {
		$errors   = '';
		$messages = '';
		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data( $code );
			foreach ( $wp_error->get_error_messages( $code ) as $error_message ) {
				if ( 'message' == $severity ) {
					$messages .= '	' . $error_message . "<br />\n";
				} else {
					$errors .= '	' . $error_message . "<br />\n";
				}
			}
		}
		if ( ! empty( $errors ) ) {
			/**
			 * Filters the error messages displayed above the login form.
			 *
			 * @param string $errors Login error message.
			 *
			 * @since 2.1.0
			 *
			 */
			echo '<div id="login_error">' . apply_filters( 'login_errors', $errors ) . "</div>\n";
		}
		if ( ! empty( $messages ) ) {
			/**
			 * Filters instructional messages displayed above the login form.
			 *
			 * @param string $messages Login messages.
			 *
			 * @since 2.5.0
			 *
			 */
			echo '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
		}
	}
}
add_action( 'leco_cp_before_login_form', 'leco_cp_before_login_form_messages', 0 );

/**
 * Add login enqueue scripts from other plugins.
 *
 * @since unknown
 *
 * @param string $content HTML content.
 * @param array  $args wp_login_form() arguments.
 *
 * @return mixed
 */
function leco_cp_login_form_top( $content, $args ) {
	if ( isset( $args['form_class'] ) && 'cp-loginform' === $args['form_class'] ) {
		do_action( 'login_enqueue_scripts' );
	}

	return $content;
}
add_filter( 'login_form_top', 'leco_cp_login_form_top', 10, 2 );

/**
 * Add extra content to the middle part of the form.
 *
 * @since unknown
 *
 * @param string $content HTML content.
 * @param array  $args wp_login_form() arguments.
 *
 * @return string
 */
function leco_cp_login_form_middle( $content, $args ) {
	if ( isset( $args['form_class'] ) && 'cp-loginform' === $args['form_class'] ) {
		ob_start();
		do_action( 'login_form' );
		$content .= ob_get_clean();
		$content .= '<p class="lost-password"><a href="' . esc_url( leco_cp_login_url() . '?action=lostpassword' ) . '">' . __( 'Lost your password?' ) . '</a></p>';
	}

	return $content;
}
add_filter( 'login_form_middle', 'leco_cp_login_form_middle', 10, 2 );

/**
 * Add extra content to the bottom part of the form.
 *
 * @since unknown
 *
 * @param string $content HTML content.
 * @param array  $args wp_login_form() arguments.
 *
 * @return string
 */
function leco_cp_login_form_bottom( $content, $args ) {
	if ( isset( $args['form_class'] ) && 'cp-loginform' === $args['form_class'] ) {
		$content .= '<input type="hidden" name="action" value="lecocplogin" />';
	}

	return $content;
}
add_filter( 'login_form_bottom', 'leco_cp_login_form_bottom', 10, 2 );

/**
 * Add custom action to the wp-login.php when handling login actions.
 *
 * @since 4.6
 */
function leco_cp_login_form_lecocplogin() {
	$redirect_to = ( ! empty( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : leco_cp_login_url();

	add_filter( 'lostpassword_url', 'leco_cp_lost_password_url', 10, 2 );
	$user = wp_signon();
	remove_filter( 'lostpassword_url', 'leco_cp_lost_password_url', 10 );

	if ( is_wp_error( $user ) ) {
		leco_cp_set_error( 'leco-cp-login-error', $user->get_error_message() );
	}

	wp_safe_redirect( $redirect_to );
	exit();
}
add_action( 'login_form_lecocplogin', 'leco_cp_login_form_lecocplogin' );

/**
 * Change the lost password url on CP login page.
 *
 * @since 4.6
 *
 * @param string $lostpassword_url Lost password URL.
 * @param string $redirect Redirect to URL.
 *
 * @return string
 */
function leco_cp_lost_password_url( $lostpassword_url, $redirect ) {
	$login_url        = leco_cp_login_url();
	$lostpassword_url = add_query_arg( 'action', 'lostpassword', $login_url );

	if ( ! empty( $redirect ) ) {
		$lostpassword_url = add_query_arg( 'redirect_to', $redirect, $lostpassword_url );
	}

	return $lostpassword_url;
}

/**
 * The hook action to change the lost password URL.
 *
 * @since 4.6
 */
function leco_cp_change_lostpassword_url_action() {
	add_filter( 'lostpassword_url', 'leco_cp_lost_password_url', 10, 2 );
}
add_action( 'leco_cp_before_login_form', 'leco_cp_change_lostpassword_url_action', 0 );

// leco_cp_print_errors() is in error-tracking.php.
add_action( 'leco_cp_before_login_form', 'leco_cp_print_errors' );
add_action( 'leco_cp_before_lost_password_form', 'leco_cp_print_errors' );
add_action( 'leco_cp_before_reset_password_form', 'leco_cp_print_errors' );

/**
 * Return to CP lost password page if no errors.
 *
 * @since 4.6
 *
 * @param WP_Error $errors WP_Error object.
 */
function leco_cp_lost_password_action( $errors ) {
	$redirect_to = ( ! empty( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : '';

	if ( strstr( $redirect_to, 'client-portal-login' ) ) {
		if ( is_wp_error( $errors ) && ! empty( $errors->errors ) ) {
			leco_cp_set_error( 'leco-cp-lost-password-error', $errors->get_error_message() );
			$redirect_to = add_query_arg( 'action', 'lostpassword', leco_cp_login_url() );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}
}
add_action( 'lost_password', 'leco_cp_lost_password_action' );

/**
 * Filters the subject of the password reset email.
 *
 * @since 4.6
 *
 * @param string $title Default email title.
 *
 * @return string
 */
function leco_cp_retrieve_password_title( $title ) {
	$redirect_to = ( ! empty( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : '';

	if ( strstr( $redirect_to, 'client-portal-login' ) ) {
		if ( is_multisite() ) {
			if ( isset( $_REQUEST['site'] ) ) {
				switch_to_blog( intval( $_REQUEST['site'] ) );

				$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			} else {
				$site_name = get_network()->site_name;
			}
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] Password Reset' ), $site_name );

		if ( is_multisite() && isset( $_REQUEST['site'] ) ) {
			restore_current_blog();
		}
	}

	return $title;
}
add_filter( 'retrieve_password_title', 'leco_cp_retrieve_password_title' );

/**
 * Filters the message body of the password reset mail.
 *
 * If the filtered message is empty, the password reset email will not be sent.
 *
 * @since 4.6
 *
 * @param string $message    Default mail message.
 * @param string $key        The activation key.
 * @param string $user_login The username for the user.
 *
 * @return string $message
 */
function leco_cp_retrieve_password_message( $message, $key, $user_login ) {
	$redirect_to = ( ! empty( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : '';

	if ( strstr( $redirect_to, 'client-portal-login' ) ) {
		if ( is_multisite() ) {
			if ( isset( $_REQUEST['site'] ) ) {
				switch_to_blog( intval( $_REQUEST['site'] ) );

				$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			} else {
				$site_name = get_network()->site_name;
			}
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . leco_cp_login_url() . "?action=rp&key=$key&login=" . rawurlencode( $user_login ) . ">\r\n";

		if ( is_multisite() && isset( $_REQUEST['site'] ) ) {
			restore_current_blog();
		}
	}

	return $message;
}
add_filter( 'retrieve_password_message', 'leco_cp_retrieve_password_message', 10, 3 );
