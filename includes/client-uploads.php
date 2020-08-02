<?php
/**
 * LECO CP Client Upload.
 *
 * Mostly borrowed from Gravity Forms.
 *
 * @package     LECO_Client_Portal
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2019, Laura Elizabeth
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LECO_CP_Client_Upload
 *
 * @since 4.8
 */
class LECO_CP_Client_Upload {

	/**
	 * @var int Post ID.
	 *
	 * @since 4.8
	 */
	public static $post_id;

	/**
	 * @var int The phase Key.
	 *
	 * @since 4.8
	 */
	public static $phase;

	/**
	 * @var int The module key.
	 *
	 * @since 4.8
	 */
	public static $key;

	/**
	 * The main upload method.
	 *
	 * @since 4.8
	 */
	public static function upload() {
		if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			status_header( 404 );
			die();
		}

		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		send_nosniff_header();
		nocache_headers();

		status_header( 200 );

		// If the file is bigger than the server can accept then the form_id might not arrive.
		// This might happen if the file is bigger than the max post size ini setting.
		// Validation in the browser reduces the risk of this happening.
		if ( ! isset( $_REQUEST['post_id'] ) ) {
			self::die_error( 500, esc_html__( 'Failed to upload file.', 'leco-cp' ) );
		}

		// Check module.
		$modules = self::get_modules();
		if ( 'client-uploads' !== $modules[ self::$key ]['type'] ) {
			self::die_error( 500, esc_html__( 'You cannot upload to this module.', 'leco-cp' ) );
		}

		// Check caps.
		if ( ! leco_cp_can_upload( $modules[ self::$key ], self::$post_id ) ) {
			self::die_error( 500, esc_html__( 'You cannot upload files to this module.', 'leco-cp' ) );
		}

		// Change upload folder and then move uploaded file.
		leco_cp_create_protection_files( true );
		add_filter( 'upload_dir', 'leco_cp_set_upload_dir' );
		$file = wp_handle_upload( $_FILES['file'], array( 'test_form' => true, 'action' => 'leco_cp_client_upload' ) );
		remove_filter( 'upload_dir', 'leco_cp_set_upload_dir' );

		if ( ! isset( $file['error'] ) ) {
			wp_send_json_success( $file );
		} else {
			wp_send_json_error( $file );
		}
	}

	/**
	 * Save uploaded files to metadata.
	 *
	 * @since 4.8
	 */
	public static function save() {
		$modules = self::get_modules();
		$files = json_decode( wp_unslash( $_POST['files'] ), true );

		foreach ( $files as $k => $file ) {
			if ( ! isset( $file['file'] ) ) {
				unset( $files[ $k ] );
			} else {
				$id = $files[ $k ]['id'];

				// Unset some attributes we don't need.
				unset( $files[ $k ]['id'] );
				unset( $files[ $k ]['origSize'] );
				unset( $files[ $k ]['loaded'] );
				unset( $files[ $k ]['percent'] );
				unset( $files[ $k ]['status'] );
				unset( $files[ $k ]['lastModifiedDate'] );

				// update file path.
				if ( ( $uploads = wp_get_upload_dir() ) && false === $uploads['error'] ) {
					$files[ $k ]['file'] = str_replace( $uploads['basedir'] . '/', '', $files[ $k ]['file'] );
				}

				$files[ $id ] = $files[ $k ];
				unset( $files[ $k ] );
			}
		}

		if ( ! empty( $modules[ self::$key ]['client_uploads'] ) ) {
			$uploads = wp_list_pluck( $files, 'file' );
			foreach ( $modules[ self::$key ]['client_uploads'] as $k => $file ) {
				if ( in_array( $file['file'], $uploads, true ) ) {
					unset( $modules[ self::$key ]['client_uploads'][ $k ] );
				}
			}

			$modules[ self::$key ]['client_uploads'] = array_merge( $modules[ self::$key ]['client_uploads'], $files );
		} else {
			$modules[ self::$key ]['client_uploads'] = $files;
		}

		$r = update_post_meta( self::$post_id, 'leco_cp_part_' . self::$phase . '_module', $modules );

		if ( $r ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'There\'s an error when saving files to this module.', 'leco-cp' ) ) );
		}
	}

	/**
	 * Delete a client upload.
	 *
	 * @since 4.8
	 */
	public static function delete() {
		$modules = self::get_modules();

		// caps check.
		if ( leco_cp_can_upload( $modules[ self::$key ], self::$post_id ) ) {
			$file_id = sanitize_text_field( $_POST['file_id'] );
			$module  = self::get_module( self::$key );
			$files   = $module['client_uploads'];

			// delete file.
			if ( ( $uploads = wp_get_upload_dir() ) && false === $uploads['error'] ) {
				$file_path = trailingslashit( $uploads['basedir'] ) . $files[ $file_id ]['file'];
				wp_delete_file( $file_path );
			}
			// update module.
			unset( $files[ $file_id ] );
			$modules[ self::$key ]['client_uploads'] = $files;
			update_post_meta( self::$post_id, 'leco_cp_part_' . self::$phase . '_module', $modules );

			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'You cannot delete uploaded files in this module.', 'leco-cp' ) ) );
		}
	}

	/**
	 * Clean up client uploads when modules removed or post deleted.
	 *
	 * @since 4.8
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value. This will be a PHP-serialized string representation of the value if
	 *                              the value is an array, an object, or itself a PHP-serialized string.
	 */
	public static function cleanup( $post_id, $meta_key, $meta_value ) {
		// clean up client uploads.
		$current_modules       = get_post_meta( $post_id, $meta_key, true );
		$current_private_files = array();
		foreach ( $current_modules as $module ) {
			if ( 'client-uploads' === $module['type'] && isset( $module['client_uploads'] ) ) {
				if ( ! empty( $module['client_uploads'] ) ) {
					foreach ( $module['client_uploads'] as $file_id => $file ) {
						$current_private_files[ $file_id ] = $file['file'];
					}
				}
			}
		}

		$modules = unserialize( $meta_value );
		if ( is_array( $modules ) ) {
			$private_files = array();
			foreach ( $modules as $module ) {
				if ( 'client-uploads' === $module['type'] && isset( $module['client_uploads'] ) ) {
					if ( ! empty( $module['client_uploads'] ) ) {
						foreach ( $module['client_uploads'] as $file_id => $file ) {
							$private_files[ $file_id ] = $file['file'];
						}
					}
				}
			}

			foreach ( $current_private_files as $file_id => $file ) {
				if ( ! isset( $private_files[ $file_id ] ) ) {
					// delete file.
					if ( ( $uploads = wp_get_upload_dir() ) && false === $uploads['error'] ) {
						$file_path = trailingslashit( $uploads['basedir'] ) . $file;
						wp_delete_file( $file_path );
					}
				}
			}
		}
	}

	/**
	 * Get modules.
	 *
	 * @since 4.8
	 *
	 * @return mixed
	 */
	public static function get_modules() {
		// Check post_id and module.
		$post_id = absint( $_POST['post_id'] );
		$module_id = str_replace( '#module_', '', sanitize_text_field( $_POST['module'] ) );
		list( $phase, $key ) = explode( '_', $module_id );
		// Phase and key is 0 based index.
		$phase --;
		$key --;
		$modules = get_post_meta( $post_id, "leco_cp_part_{$phase}_module", true );

		self::$post_id = $post_id;
		self::$phase = $phase;
		self::$key = $key;

		return $modules;
	}

	/**
	 * Get module.
	 *
	 * @since 4.8
	 *
	 * @param int $key The module key.
	 *
	 * @return mixed
	 */
	public static function get_module( $key ) {
		$modules = self::get_modules();

		return $modules[ $key ];
	}

	/**
	 * Get WP allowed extensions.
	 *
	 * @since 4.8
	 *
	 * @return string
	 */
	public static function get_allowed_extension() {
		$mime_types = get_allowed_mime_types();
		$extensions = array();
		foreach ( $mime_types as $k => $v ) {
			$ext        = explode( '|', $k );
			$extensions = array_merge( $extensions, $ext );
		}

		return implode( ',', $extensions );
	}

	/**
	 * Return errors when uploads failed.
	 *
	 * @since 4.8
	 *
	 * @param int    $status_code The status Code.
	 * @param string $message The message.
	 */
	public static function die_error( $status_code, $message ) {
		$response = array(
			'message' => $message,
		);

		wp_send_json_error( $response, $status_code );
	}
}
