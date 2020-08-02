<?php
/**
 * Template functions.
 *
 * @package     LECO_Client_Portal
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2019, Laura Elizabeth
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add body class.
 *
 * @since 4.6
 *
 * @param array $classes Classes.
 *
 * @return array
 */
function leco_cp_body_class( $classes ) {
	if ( is_page( 'client-portal-login' ) || ! leco_cp_can_display_project() ) {
		$classes[] = 'leco-cp-login';
	}

	return $classes;
}
add_filter( 'body_class', 'leco_cp_body_class' );

/** Update comment form defaults.
 *
 * @param array $defaults Defaults.
 *
 * @return mixed
 */
function leco_cp_comment_form_defaults( $defaults ) {
	global $post;
	if ( 'leco_content_page' === $post->post_type ) {
		$defaults['comment_notes_before'] = str_replace( 'comment-notes', 'comment-notes callout-yellow', $defaults['comment_notes_before'] );

		$defaults['comment_notes_after'] = '<strong class="">' . esc_html__( 'Please DO NOT share any credentials in the comments section.', 'leco-cp' ) . '</strong>';
	}

	return $defaults;
}
add_filter( 'comment_form_defaults', 'leco_cp_comment_form_defaults' );

/**
 * Get the comments template conditionally.
 *
 * @param int $post_id Content page ID.
 */
function leco_cp_get_comments_template( $post_id ) {
	global $wp_query;

	if ( ! post_type_supports( 'leco_content_page', 'comments' ) ) {
		return;
	}

	$message = esc_html__( 'This content page may contain comments from a private portal, we can\'t display comments for security/privacy reasons.', 'leco-cp' );

	if ( ! leco_cp_is_shared_content_page( $post_id ) ) {
		$portal_id = get_post_meta( $post_id, 'leco_client', true );
		$error     = 0;
		if ( empty( $portal_id ) ) {
			$projects = leco_cp_get_projects_by_content_page( $post_id );
			if ( ! empty( $projects ) ) {
				foreach ( $projects as $project ) {
					if ( leco_cp_is_public_portal( $project->ID ) ) {
						$error++;
					}
				}
			}
		}

		if ( ( $portal_id && ( $wp_query->queried_object && $wp_query->queried_object->ID == $portal_id ) ) || // has comments from a private project.
		     ( count( $projects ) === 1 || // belongs only one project, regardless it's public or private.
		       $error === count( $projects ) ) ) { // belongs to projects are all public.
			require_once trailingslashit( LECO_CLIENT_PORTAL_DIR ) . 'templates/default/comments.php';
		} else {
			echo '<div id="comments" class="comments-area"><p class="no-comments callout-yellow">' . $message . '</p></div>';
		}
	} elseif ( comments_open( $post_id ) ) {
		echo '<div id="comments" class="comments-area"><p class="no-comments callout-yellow">' . $message . '</p></div>';
	}
}

/**
 * Trigger private file downloading.
 *
 * @since 4.6
 * @since 4.8 Added support for client uploads.
 */
function leco_cp_download_private_file() {
	global $wp;

	if ( ! empty( $wp->query_vars['leco_private_file'] ) ) {
		$project         = get_page_by_path( $wp->query_vars['leco_client'], OBJECT, 'leco_client' );
		$project_objects = leco_cp_get_projects_by_file_hash( $wp->query_vars['leco_private_file'] );
		$projects        = wp_list_pluck( $project_objects, 'ID' );

		// Check if the file is a private file or client upload.
		$file_id = base64_decode( $wp->query_vars['leco_private_file'] );
		if ( strpos( $file_id, '|' ) !== false ) {
			// is private files.
			$array = explode( '|', $file_id );

			if ( count( $array ) === 2 && is_numeric( $array[0] ) && is_numeric( $array[1] ) ) {
				list( $post_id, $attachment_id ) = $array;
			}
		}

		if ( ! isset( $attachment_id ) ) {
			// is client uploads.
			$file_id       = $wp->query_vars['leco_private_file'];
			$attachment_id = false;
			$post_id       = $projects[0];
		}

		// check permission.
		if ( ( leco_cp_user_has_access( $project->ID ) && $post_id == $project->ID && in_array( $post_id, $projects ) ) || current_user_can( 'edit_posts' ) ) {
			// Prevent caching when endpoint is set
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}

			if ( $attachment_id ) {
				$requested_file = wp_get_attachment_url( $attachment_id );

				$ctype = get_post_mime_type( $attachment_id );
			} else {
				$meta_key = wp_list_pluck( $project_objects, 'meta_key' );
				$modules  = get_post_meta( $post_id, $meta_key[0], true );
				foreach ( $modules as $module ) {
					if ( 'client-uploads' === $module['type'] && isset( $module['client_uploads'][ $file_id ] ) ) {
						$file           = $module['client_uploads'][ $file_id ];
						$requested_file = $file['file'];
						$ctype          = $file['type'];

						// If the file is relative, prepend upload dir.
						// borrowed from get_attached_file() function.
						if ( $requested_file && 0 !== strpos( $requested_file, '/' ) && ! preg_match( '|^.:\\\|', $requested_file ) && ( ( $uploads = wp_get_upload_dir() ) && false === $uploads['error'] ) ) {
							$requested_file = $uploads['basedir'] . "/$requested_file";
						}
					}
				}

				// if no file match, die();
				if ( ! isset( $requested_file ) ) {
					wp_die( esc_html__( 'Error downloading file. Please contact support.', 'leco-cp' ), esc_html__( 'File download error', 'leco-cp' ), 501 );
				}
			}


			/*
			 * If we have an attachment ID stored, use get_attached_file() to retrieve absolute URL
			 * If this fails or returns a relative path, we fall back to our own absolute URL detection
			 */
			if ( $attachment_id && 'attachment' == get_post_type( $attachment_id ) ) {
				$attached_file = get_attached_file( $attachment_id, false );

				// Confirm the file exists.
				if ( ! file_exists( $attached_file ) ) {
					$attached_file = false;
				}

				if ( $attached_file ) {
					$requested_file = $attached_file;
				}
			}

			$file_details = parse_url( $requested_file );
			$schemes      = array( 'http', 'https' );

			$supported_streams = stream_get_wrappers();
			if ( strtoupper( substr( PHP_OS, 0, 3 ) ) !== 'WIN' && isset( $file_details['scheme'] ) && ! in_array( $file_details['scheme'], $supported_streams ) ) {
				wp_die( esc_html__( 'Error downloading file. Please contact support.', 'leco-cp' ), esc_html__( 'File download error', 'leco-cp' ), 501 );
			}

			if ( ! leco_cp_is_func_disabled( 'set_time_limit' ) ) {
				@set_time_limit( 0 );
			}

			@session_write_close();
			if ( function_exists( 'apache_setenv' ) ) {
				@apache_setenv( 'no-gzip', 1 );
			}
			@ini_set( 'zlib.output_compression', 'Off' );

			nocache_headers();
			header( 'Robots: none' );
			header( "Content-Type: {$ctype}" );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename="' . basename( $requested_file ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );

			$direct    = false;
			$file_path = $requested_file;

			if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $requested_file ) ) {

				/** This is an absolute path */
				$direct    = true;
				$file_path = $requested_file;

			} elseif ( defined( 'UPLOADS' ) && strpos( $requested_file, UPLOADS ) !== false ) {

				/**
				 * This is a local file given by URL so we need to figure out the path
				 * UPLOADS is always relative to ABSPATH
				 * site_url() is the URL to where WordPress is installed
				 */
				$file_path = str_replace( site_url(), '', $requested_file );
				$file_path = realpath( ABSPATH . $file_path );
				$direct    = true;

			} elseif ( strpos( $requested_file, content_url() ) !== false ) {

				/** This is a local file given by URL so we need to figure out the path */
				$file_path = str_replace( content_url(), WP_CONTENT_DIR, $requested_file );
				$file_path = realpath( $file_path );
				$direct    = true;

			} elseif ( strpos( $requested_file, set_url_scheme( content_url(), 'https' ) ) !== false ) {

				/** This is a local file given by an HTTPS URL so we need to figure out the path */
				$file_path = str_replace( set_url_scheme( content_url(), 'https' ), WP_CONTENT_DIR, $requested_file );
				$file_path = realpath( $file_path );
				$direct    = true;

			}

			// Set the file size header.
			header( 'Content-Length: ' . @filesize( $file_path ) );

			// Now deliver the file based on the kind of software the server is running / has enabled.
			if ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {

				header( "X-LIGHTTPD-send-file: {$file_path}" );

			} elseif ( $direct && ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) ) {

				$ignore_x_accel_redirect_header = apply_filters( 'leco_cp_ignore_x_accel_redirect', false );

				if ( ! $ignore_x_accel_redirect_header ) {
					// We need a path relative to the domain.
					$file_path = str_ireplace( realpath( $_SERVER['DOCUMENT_ROOT'] ), '', $file_path );
					header( "X-Accel-Redirect: /{$file_path}" );
				}
			}

			if ( $direct ) {
				lecp_cp_readfile_chunked( $file_path );
			} else {
				// The file supplied does not have a discoverable absolute path.
				header( 'Location: ' . $requested_file );
			}
		} else {
			$error_message = esc_html__( 'You do not have permission to download this file.', 'leco-cp' );
			wp_die( apply_filters( 'leco_cp_deny_download_message', $error_message ), esc_html__( 'Error', 'leco-cp' ), array( 'response' => 403 ) );
		}

		exit();
	}
}
