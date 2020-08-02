<?php

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 *
 * @param  string $key Options array key.
 * @param  string $default Default value when option is empty.
 *
 * @return mixed        Option value
 */
function leco_cp_get_option( $key = '', $default = '' ) {
	return cmb2_get_option( leco_cp_settings()->key, $key, $default );
}

/**
 * Conditionally displays a metabox when used as a callback in the 'show_on_cb' cmb2_box parameter
 *
 * @return bool             True if metabox should show
 */
function leco_cp_show_if_add_client() {
	global $pagenow;
	// Don't show this metabox if it's updating post.
	if ( 'post-new.php' !== $pagenow ) {
		return false;
	}

	return true;
}

/**
 * Conditionally displays a metabox when used as a callback in the 'show_on_cb' cmb2_box parameter
 *
 * @return bool             True if metabox should show
 */
function leco_cp_show_if_update_client() {
	global $pagenow;
	// Don't show this metabox if it's creating a new post.
	if ( 'post-new.php' === $pagenow || isset( $_GET['cp-action'] ) ) {
		return false;
	}

	return true;
}

/**
 * Conditionally displays a metabox when used as a callback in the 'show_on_cb' cmb2_box parameter
 *
 * @return bool             True if metabox should show
 */
function leco_cp_show_if_manage_phases() {
	global $pagenow;
	// Show this metabox if it's the manage phases screen
	if ( 'post.php' === $pagenow && isset( $_GET['cp-action'] ) ) {
		return true;
	}

	return false;
}

/**
 * Conditionally displays a metabox when used as a callback in the 'show_on_cb' cmb2_box parameter
 *
 * @return bool             True if metabox should show
 */
function leco_cp_show_if_not_manage_phases() {
	global $pagenow;
	// Show this metabox if it's the manage phases screen
	if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && ! isset( $_GET['cp-action'] ) ) {
		return true;
	}

	return false;
}

/**
 * A little helper function to remove the .svg from our icon json
 *
 * @param string $file The file name.
 *
 * @return bool|string
 */
function leco_cp_remove_dotsvg( $file ) {
	return substr( $file, 0, - 4 );
}

function leco_cp_check_post_password( $post = null ) {
	require_once( ABSPATH . WPINC . '/class-phpass.php' );

	$post   = get_post( $post );
	$hasher = new PasswordHash( 8, true );
	$passed = false;

	if ( ! empty( $post->post_password ) ) {
		$hash = wp_unslash( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] );
		if ( 0 !== strpos( $hash, '$P$B' ) ) {
			$passed = false;
		} else {
			$passed = $hasher->CheckPassword( $post->post_password, $hash );
		}
	}

	return $passed;
}

/**
 * If the project content can be displayed.
 *
 * @since 4.7.0
 *
 * @return bool
 */
function leco_cp_can_display_project() {
	if ( current_user_can( 'edit_posts' ) || ( ( is_user_logged_in() && leco_cp_user_has_access( get_the_ID() ) ) ) || leco_cp_check_post_password( get_the_ID() ) || leco_cp_is_public_portal( get_the_ID() ) ) {
		return true;
	}

	return false;
}

/**
 * Get projects by client (user id by default)
 *
 * @since unknown
 * @since 4.4     Added checking new user meta "leco_cp_project".
 *
 * @param int          $user_id User ID.
 * @param string|array $post_type Post types.
 *
 * @return array An array of posts
 */
function leco_cp_get_projects_by_client( $user_id = 0, $post_type = 'leco_client' ) {
	if ( ! $user_id ) {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}
	}

	if ( ! $user_id ) {
		return array();
	}

	$projects = maybe_unserialize( get_user_meta( $user_id, 'leco_cp_project', true ) );

	if ( empty( $projects ) ) {
		$projects = get_posts(
			array(
				'post_type'  => $post_type,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'   => 'leco_cp_client',
						'value' => $user_id,
					),
					array(
						'key'     => 'leco_cp_public_portal',
						'value'   => 'yes',
						'compare' => '!=',
					),
				),
			)
		);
		$projects = wp_list_pluck( $projects, 'ID' );
	}

	return ( empty( $projects ) ) ? array() : $projects;
}

/**
 * Check if current user can access Client Portal
 *
 * @return bool
 */
function leco_cp_user_role_allowed() {
	if ( ! function_exists( 'wp_get_current_user' ) ) {
		require( ABSPATH . WPINC . '/pluggable.php' );
	}

	$roles      = apply_filters( 'leco_cp_client_roles', array( 'leco_client' ) );
	$is_allowed = false;
	foreach ( $roles as $role ) {
		if ( current_user_can( $role ) ) {
			$is_allowed = true;
		}
	}

	return $is_allowed;
}

/**
 * Check if the user can access a project (is the client or can edit the project).
 *
 * @since 4.6.1 $project_id can be null.
 * @since 4.4
 *
 * @param int $project_id Project ID.
 *
 * @return bool
 */
function leco_cp_user_has_access( $project_id = null ) {
	if ( null === $project_id ) {
		$project    = get_post();
		$project_id = $project->ID;
	}

	$projects = leco_cp_get_projects_by_client( get_current_user_id() );

	if ( in_array( $project_id, $projects, true ) || current_user_can( 'edit_posts' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if it's a public portal.
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function leco_cp_is_public_portal( $post_id ) {
	$public_portal = get_post_meta( $post_id, 'leco_cp_public_portal', true );

	if ( 'yes' === $public_portal ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if a module can be mark as complete by the client.
 *
 * @param string $module_status Module status.
 * @param int    $post_id Project ID.
 *
 * @return bool
 */
function leco_cp_can_mark_as_complete( $module_status, $post_id ) {
	if ( ( 'hide' !== leco_cp_get_option( 'show_mark_as_complete' ) && 'hide' !== get_post_meta( $post_id, 'leco_cp_show_mark_as_complete', true ) ) && 'active' === $module_status && ( leco_cp_user_role_allowed() || current_user_can( 'administrator' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Check if showing the upload icon.
 *
 * @since 4.8
 *
 * @param array $module  The module data.
 * @param int   $post_id The post ID.
 *
 * @return bool
 */
function leco_cp_can_upload( $module, $post_id ) {
	if ( 'client-uploads' === $module['type'] && leco_cp_user_has_access( $post_id ) && ( leco_cp_user_role_allowed() || current_user_can( 'administrator' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Notify the site administrator via email when a module marked as completed.
 *
 * Without this, the admin would have to manually check the site to see if any
 * action was needed on their part yet.
 *
 * @since 4.3.0
 *
 * @param int $post_id Project ID.
 * @param int $phase Phase.
 * @param int $module Module.
 *
 * @return bool True if email sent successfully, false otherwise.
 */
function leco_cp_send_admin_module_completed_notification( $post_id, $phase, $module ) {
	// Don't send if the module is not completed yet.
	$modules = get_post_meta( $post_id, "leco_cp_part_{$phase}_module", true );
	if ( 'completed' !== $modules[ $module ]['status'] ) {
		return;
	}

//	$already_notified = (bool) get_post_meta( $request_id, '_wp_admin_notified', true );
//
//	if ( $already_notified ) {
//		return;
//	}

	$subject = apply_filters( 'leco_cp_send_admin_module_completed_subject', 'Client Portal - Module Marked As Completed', $post_id, $phase, $module );

	$manage_url = add_query_arg( array(
		'action' => 'edit',
		'post'   => $post_id,
	), admin_url( 'post.php' ) );

	/**
	 * Filters the recipient of Client Portal admin notifications.
	 *
	 * In a Multisite environment, this will default to the email address of the
	 * network admin because, by default, single site admins do not have the
	 * capabilities required to process requests. Some networks may wish to
	 * delegate those capabilities to a single-site admin, or a dedicated person
	 * responsible for managing privacy requests.
	 *
	 * @since 4.3
	 *
	 * @param string $admin_email The email address of the notification recipient.
	 * @param int $post_id Post ID.
	 * @param int $phase Phase.
	 * @param int $module Module.
	 */
	$admin_email = apply_filters( 'leco_cp_admin_email_to', get_site_option( 'admin_email' ), $post_id, $phase, $module );

	$email_data = array(
		'post_id'     => $post_id,
		'phase'       => $phase,
		'module'      => $module,
		'manage_url'  => $manage_url,
		'sitename'    => get_option( 'blogname' ),
		'siteurl'     => home_url(),
		'admin_email' => $admin_email,
	);

	/* translators: Do not translate SITENAME, USER_EMAIL, DESCRIPTION, MANAGE_URL, SITEURL; those are placeholders. */
	$email_text = __(
		'Howdy,

A module has been marked as completed in your Client Portal on ###SITENAME###:

Project: ###PROJECT###
Phase: ###PHASE###
Module: ###MODULE###

You can view and manage this project here:

###MANAGE_URL###

Regards,
All at ###SITENAME###
###SITEURL###'
	);

	/**
	 * Filters the body of the module marked as completed email.
	 *
	 * The email is sent to an administrator when a module marked as completed.
	 * The following strings have a special meaning and will get replaced dynamically:
	 *
	 * ###SITENAME###    The name of the site.
	 * ###PROJECT###  The project title.
	 * ###PHASE###  The phase the module belongs to.
	 * ###MODULE###  The module.
	 * ###MANAGE_URL###  The URL to manage requests.
	 * ###SITEURL###     The URL to the site.
	 *
	 * @since 4.3
	 *
	 * @param string $email_text Text in the email.
	 * @param array $email_data .
	 */
	$content = apply_filters( 'leco_cp_send_admin_module_completed_content', $email_text, $email_data );

	$content = str_replace( '###SITENAME###', wp_specialchars_decode( $email_data['sitename'], ENT_QUOTES ), $content );
	$project = get_post( $email_data['post_id'] );
	$content = str_replace( '###PROJECT###', $project->post_title, $content );
	$phase   = get_post_meta( $post_id, "leco_cp_part_{$email_data['phase']}_title", true );
	$content = str_replace( '###PHASE###', $phase, $content );
	$content = str_replace( '###MODULE###', $modules[ $email_data['module'] ]['title'], $content );
	$content = str_replace( '###MANAGE_URL###', esc_url_raw( $email_data['manage_url'] ), $content );
	$content = str_replace( '###SITEURL###', esc_url_raw( $email_data['siteurl'] ), $content );

	$email_sent = wp_mail( $email_data['admin_email'], $subject, $content );

	/**
	 * @todo Maybe record email sent later
	 */
//	if ( $email_sent ) {
//		update_post_meta( $request_id, '_wp_admin_notified', true );
//	}

	return $email_sent;
}

/**
 * Set user projects by posted meta value of leco_cp_client.
 *
 * @since 4.4
 *
 * @param string $value Value.
 * @param int $project_id Project ID.
 * @param bool $remove Remove user projects or not.
 */
function leco_cp_set_user_projects( $value, $project_id, $remove = false ) {
	$value = maybe_unserialize( $value );

	if ( is_string( $value ) ) {
		$value = array( $value );
	}

	if ( ! $remove ) {
		// Attach project to user.
		foreach ( $value as $user_id ) {
			$projects = leco_cp_get_projects_by_client( $user_id );

			if ( ! in_array( $project_id, $projects, true ) ) {
				$projects[] = $project_id;
				update_user_meta( $user_id, 'leco_cp_project', $projects );
			}
		}
	} else {
		// Check current meta value.
		$_value = maybe_unserialize( get_post_meta( $project_id, 'leco_cp_client', true ) );
		if ( is_string( $_value ) ) {
			$_value = array( $_value );
		}

		foreach ( $_value as $old_client ) {
			if ( ! in_array( $old_client, $value, true ) || empty( $value ) || doing_action( 'delete_post_meta' ) ) {
				$projects = leco_cp_get_projects_by_client( $old_client );

				if ( ! empty( $projects ) ) {
					$key = array_search( $project_id, $projects, true );
					if ( false !== $key ) {
						unset( $projects[ $key ] );
						update_user_meta( $old_client, 'leco_cp_project', $projects );
					}
				}
			}
		}
	}
}

/**
 * Checks whether function is disabled.
 *
 * Borrowed from EDD.
 *
 * @since 4.6
 *
 * @param string $function Name of the function.
 *
 * @return bool Whether or not function is disabled.
 */
function leco_cp_is_func_disabled( $function ) {
	$disabled = explode( ',', ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled, true );
}

/**
 * Retrieves the login URL.
 *
 * @since 4.6
 *
 * @param string $redirect     Path to redirect to on log in.
 * @param bool   $force_reauth Whether to force reauthorization, even if a cookie is present.
 *                             Default false.
 * @return string The login URL. Not HTML-encoded.
 */
function leco_cp_login_url( $redirect = '', $force_reauth = false ) {
	$login_url = trailingslashit( home_url( 'client-portal-login' ) );

	if ( ! empty( $redirect ) ) {
		$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
	}

	if ( $force_reauth ) {
		$login_url = add_query_arg( 'reauth', '1', $login_url );
	}

	/**
	 * Filters the login URL.
	 *
	 * @param string $login_url The login URL. Not HTML-encoded.
	 * @param string $redirect The path to redirect to on login, if supplied.
	 * @param bool $force_reauth Whether to force reauthorization, even if a cookie is present.
	 *
	 * @since 4.6
	 *
	 */
	return apply_filters( 'leco_cp_login_url', $login_url, $redirect, $force_reauth );
}

/**
 * Deregister and dequeue styles from themes.
 *
 * @since 4.7
 */
function leco_cp_remove_theme_css_js() {
	global $wp_styles, $wp_scripts;
	$theme_enqueued = array(
		'style'  => $wp_styles,
		'script' => $wp_scripts,
	);
	foreach ( $theme_enqueued as $type => $enqueued ) {
		foreach ( $enqueued->registered as $handle => $data ) {
			// CP works as template so we don't need anything from the "theme".
			// But we want to load styles and scripts from the "plugins".
			if ( strpos( $data->src, 'themes' ) ) {
				// otherwise remove it.
				if ( 'style' === $type ) {
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				} else {
					wp_dequeue_script( $handle );
					wp_deregister_script( $handle );
				}
			}
		}
	}
}
