<?php
/**
 * Most functions here are borrowed from EDD.
 *
 * @package LECO-CP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since  4.6
 *
 * @return string $path Absolute path to the CP upload directory
 */
function leco_cp_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/leco-cp' );
	$path = $wp_upload_dir['basedir'] . '/leco-cp';

	return apply_filters( 'leco_cp_get_upload_dir', $path );
}


/**
 * Change Downloads Upload Directory
 *
 * Hooks the leco_cp_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for CP to an leco-cp directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/leco-cp/{year}/{month}. This directory is
 * provides protection to anything uploaded to it.
 *
 * @since  4.6
 *
 * @return void
 */
function leco_cp_change_downloads_upload_dir() {
	global $pagenow;

	// We used to also check $_REQUEST['post_id'] as EDD does,
	// But somehow on some server the post_id isn't available.
	// So removed it.
	if ( ! empty( $_REQUEST['type'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
		if ( 'leco_private_file' === $_REQUEST['type'] ) {
			leco_cp_create_protection_files( true );
			add_filter( 'upload_dir', 'leco_cp_set_upload_dir' );
			add_filter( 'wp_handle_upload_prefilter', 'leco_cp_upload_file_check' );
		}
	}
}
add_action( 'admin_init', 'leco_cp_change_downloads_upload_dir', 999 );

/**
 * Get the file Download method
 *
 * @since 4.6
 *
 * @return string The method to use for file downloads
 */
function leco_cp_get_file_download_method() {
	$method = leco_cp_get_option( 'download_method', 'direct' );

	return apply_filters( 'leco_cp_file_download_method', $method );
}

/**
 * Set Upload Directory
 *
 * Sets the upload dir to edd. This function is called from
 * edd_change_downloads_upload_dir()
 *
 * @since 4.6
 *
 * @param array $upload Upload dir info.
 *
 * @return array Upload directory information
 */
function leco_cp_set_upload_dir( $upload ) {
	// Override the year / month being based on the post publication date, if year/month organization is enabled.
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs.
		$time             = current_time( 'mysql' );
		$y                = substr( $time, 0, 4 );
		$m                = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/leco-cp' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];

	return $upload;
}

/**
 * Check files before they are uploaded.
 *
 * @since 4.6
 *
 * @param array $file Uploaded file array.
 *
 * @return mixed
 */
function leco_cp_upload_file_check( $file ) {
	$file_type = wp_check_filetype( $file['name'] );
	$file_type = $file_type['ext'];

	$not_allowed_filetypes = apply_filters( 'leco_cp_protected_directory_allowed_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ) );

	if ( in_array( $file_type, $not_allowed_filetypes ) ) {
		$not_allowed_types = implode( ', ', array_values( $not_allowed_filetypes ) );
		$file['error']     = sprintf( esc_html__( 'These file types are now allowed to be uploaded as private files: %s.', 'leco-cp' ), $not_allowed_types );
	}

	return $file;
}

/**
 * Creates blank index.php and .htaccess files
 *
 * This function runs approximately once per day in order to ensure all folders
 * have their necessary protection files
 *
 * @since 4.6
 *
 * @param bool        $force Force create or not.
 * @param bool|string $method Download file method.
 */

function leco_cp_create_protection_files( $force = false, $method = false ) {
	if ( false === get_transient( 'leco_cp_check_protection_files' ) || $force ) {

		$upload_path = leco_cp_get_upload_dir();

		// Make sure the /leco-cp folder is created.
		wp_mkdir_p( $upload_path );

		// Top level .htaccess file.
		$rules = leco_cp_get_htaccess_rules( $method );
		if ( leco_cp_htaccess_exists() ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match.
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif ( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist.
			@file_put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php.
		if ( ! file_exists( $upload_path . '/index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Now place index.php files in all sub folders.
		$folders = leco_cp_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist.
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}
		// Check for the files once per day.
		set_transient( 'leco_cp_check_protection_files', true, 3600 * 24 );
	}
}
add_action( 'admin_init', 'leco_cp_create_protection_files' );

/**
 * Checks if the .htaccess file exists in wp-content/uploads/leco-cp
 *
 * @since 4.6
 *
 * @return bool
 */
function leco_cp_htaccess_exists() {
	$upload_path = leco_cp_get_upload_dir();

	return file_exists( $upload_path . '/.htaccess' );
}

/**
 * Scans all folders inside of /uploads/leco-cp.
 *
 * @since 4.6
 *
 * @param string $path Folder path.
 * @param array  $return Sub-folders.
 *
 * @return array $return List of files inside directory
 */
function leco_cp_scan_folders( $path = '', $return = array() ) {
	$path  = $path == '' ? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) ) {
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );
				}

				leco_cp_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return );
			}
		}
	}

	return $return;
}

/**
 * Retrieve the .htaccess rules to wp-content/uploads/leco-cp/
 *
 * @since 4.6
 *
 * @param bool|string $method Download method.
 *
 * @return mixed|void The htaccess rules
 */
function leco_cp_get_htaccess_rules( $method = false ) {

	if( empty( $method ) )
		$method = leco_cp_get_file_download_method();

	switch( $method ) :

		case 'redirect' :
			// Prevent directory browsing.
			$rules = "Options -Indexes";
			break;

		case 'direct' :
		default :
			// Prevent directory browsing and direct access to all files, except images (they must be allowed for featured images / thumbnails).
			$allowed_filetypes = apply_filters( 'leco_cp_protected_directory_allowed_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ) );
			$rules = "Options -Indexes\n";
			$rules .= "deny from all\n";
			$rules .= "<FilesMatch '\.(" . implode( '|', $allowed_filetypes ) . ")$'>\n";
			$rules .= "Order Allow,Deny\n";
			$rules .= "Allow from all\n";
			$rules .= "</FilesMatch>\n";
			break;

	endswitch;
	$rules = apply_filters( 'leco_cp_protected_directory_htaccess_rules', $rules, $method );
	return $rules;
}

/**
 * Admin notices for Nginx servers.
 *
 * Borrowed from Download Monitor.
 *
 * @since 4.6
 *
 */
function leco_cp_admin_notices_for_nginx() {
	// check for Nginx.
	if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false && 1 != get_option( 'leco_cp_hide_notice-nginx_rules', 0 ) ) {

		// get upload dir.
		$upload_dir = wp_upload_dir();

		// replace document root because Nginx uses path from document root.
		$upload_path = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $upload_dir['basedir'] );

		// form Nginx rules.
		$nginx_rules = "location ~ ^" . $upload_path . "/leco-cp/(.*)$ { rewrite / permanent; }";
		echo '<div class="error notice is-dismissible leco-cp-notice" id="nginx_rules" data-nonce="' . wp_create_nonce( 'leco_cp_hide_notice-nginx_rules' ) . '">';
		echo '<p>' . esc_html__( "Because your server is running on Nginx, we cannot use the .htaccess file to protect your private files.", 'leco-cp' );
		echo '<br/>' . sprintf( esc_html__( "Please add the following rules to your Nginx config to disable direct file access: %s", 'leco-cp' ), '<br/><br/><code class="leco-cp-code-nginx-rules">' . $nginx_rules . '</code><br/>' ) . '</p>';
		echo '<p>' . esc_html__( 'You can usually ask your hosting service to help you with it.', 'leco-cp' ) . ' <strong>' . esc_html__( 'If you\'re pretty sure the rules have been added, you can dismiss this message.', 'leco-cp' ) . '</strong>' . '</p>';
		echo '</div>';
	}
}

/**
 * Admin notices about client uploads for Nginx servers.
 *
 * This notice displays if the private uploads notice is closed.
 *
 * @since 4.8
 *
 */
function leco_cp_admin_notices_for_nginx_client_uploads() {
	// check for Nginx.
	if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false && 1 == get_option( 'leco_cp_hide_notice-nginx_rules', 0 ) && 1 != get_option( 'leco_cp_hide_notice-nginx_rules_client_uploads', 0 ) ) {

		// get upload dir.
		$upload_dir = wp_upload_dir();

		// replace document root because Nginx uses path from document root.
		$upload_path = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $upload_dir['basedir'] );

		// form Nginx rules.
		$nginx_rules = "location ~ ^" . $upload_path . "/leco-cp/(.*)$ { rewrite / permanent; }";
		echo '<div class="error notice is-dismissible leco-cp-notice" id="nginx_rules_client_uploads" data-nonce="' . wp_create_nonce( 'leco_cp_hide_notice-nginx_rules_client_uploads' ) . '">';
		echo '<p>' . esc_html__( "Because your server is running on Nginx, we cannot use the .htaccess file to protect your client uploads.", 'leco-cp' );
		echo '<br/>' . sprintf( esc_html__( "Please add the following rules to your Nginx config to disable direct file access: %s", 'leco-cp' ), '<br/><br/><code class="leco-cp-code-nginx-rules">' . $nginx_rules . '</code><br/>' ) . '</p>';
		echo '<p>' . esc_html__( 'You can usually ask your hosting service to help you with it.', 'leco-cp' ) . ' <strong>' . esc_html__( 'If you\'re pretty sure the rules have been added, you can dismiss this message.', 'leco-cp' ) . '</strong>' . '</p>';
		echo '</div>';
	}
}
