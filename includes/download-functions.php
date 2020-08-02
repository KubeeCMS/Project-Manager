<?php
/**
 * Reads file in chunks so big downloads are possible without changing PHP.INI
 * See http://codeigniter.com/wiki/Download_helper_for_large_files/
 *
 * Borrowed from EDD.
 *
 * @since 4.6
 *
 * @param string  $file The file.
 * @param boolean $retbytes Return the bytes of file.
 *
 * @return   bool|string        If string, $status || $cnt
 */
function lecp_cp_readfile_chunked( $file, $retbytes = true ) {
	while ( ob_get_level() > 0 ) {
		ob_end_clean();
	}

	ob_start();

	// If output buffers exist, make sure they are closed. See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6387.
	if ( ob_get_length() ) {
		ob_clean();
	}

	$chunksize = 1024 * 1024;
	$cnt       = 0;
	$handle    = @fopen( $file, 'rb' );

	if ( $size = @filesize( $file ) ) {
		header( 'Content-Length: ' . $size );
	}

	if ( false === $handle ) {
		return false;
	}

	if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
		list( $size_unit, $range ) = explode( '=', $_SERVER['HTTP_RANGE'], 2 );
		if ( 'bytes' === $size_unit ) {
			if ( strpos( ',', $range ) ) {
				list( $range ) = explode( ',', $range, 1 );
			}
		} else {
			$range = '';
			header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
			exit;
		}
	} else {
		$range = '';
	}

	if ( empty( $range ) ) {
		$seek_start = null;
		$seek_end   = null;
	} else {
		list( $seek_start, $seek_end ) = explode( '-', $range, 2 );
	}

	$seek_end   = ( empty( $seek_end ) ) ? ( $size - 1 ) : min( abs( intval( $seek_end ) ), ( $size - 1 ) );
	$seek_start = ( empty( $seek_start ) || $seek_end < abs( intval( $seek_start ) ) ) ? 0 : max( abs( intval( $seek_start ) ), 0 );

	// Only send partial content header if downloading a piece of the file (IE workaround).
	if ( $seek_start > 0 || $seek_end < ( $size - 1 ) ) {
		header( 'HTTP/1.1 206 Partial Content' );
		header( 'Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $size );
		header( 'Content-Length: ' . ( $seek_end - $seek_start + 1 ) );
	} else {
		header( "Content-Length: $size" );
	}

	header( 'Accept-Ranges: bytes' );

	if ( ! leco_cp_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	fseek( $handle, $seek_start );

	while ( ! @feof( $handle ) ) {
		$buffer = @fread( $handle, $chunksize );
		echo $buffer;
		ob_flush();

		if ( $retbytes ) {
			$cnt += strlen( $buffer );
		}

		if ( connection_status() != 0 ) {
			@fclose( $handle );
			exit;
		}
	}

	ob_flush();

	$status = @fclose( $handle );

	if ( $retbytes && $status ) {
		return $cnt;
	}

	return $status;
}

/**
 * Get portals by private file hash.
 *
 * @since 4.6
 *
 * @param string $hash Hash.
 *
 * @return array|null|object
 */
function leco_cp_get_projects_by_file_hash( $hash ) {
	global $wpdb;
	$projects = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT `ID`, `post_title`, `meta_key` FROM {$wpdb->prefix}postmeta a LEFT JOIN {$wpdb->prefix}posts b ON a.post_id = b.ID WHERE `meta_value` LIKE %s", '%' . $wpdb->esc_like( $hash ) . '%' ) );

	return $projects;
}
