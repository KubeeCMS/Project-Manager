<?php

/**
 * Search and replace src attribute values.
 *
 * @since 4.5
 *
 * @param int   $post_id Post ID.
 * @param int   $original_post_ID Original post ID.
 * @param array $postdata Inserted post data.
 * @param array $post Imported post data.
 */
function leco_cp_wp_import_insert_post( $post_id, $original_post_ID, $postdata, $post ) {
	if ( in_array( $post['post_type'], array( 'leco_content_page' ), true ) ) {
		$content = get_post_field( 'post_content', $post_id );
		preg_match_all( '@src="([^"]+)"@', $content, $matches );
		$matched = false;
		$matches = array_pop( $matches );

		foreach ( $matches as $match ) {
			$base_host    = wp_parse_url( $match );
			$current_host = wp_parse_url( home_url() );
			$test_url     = str_replace( $base_host['scheme'] . '://' . $base_host['host'], $current_host['scheme'] . '://' . $current_host['host'], $match );

			if ( 200 === wp_remote_retrieve_response_code( wp_remote_head( $test_url ) ) ) {
				$content = str_replace( $match, $test_url, $content );
				$matched = true;
			}
		}

		if ( $matched ) {
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $content,
				)
			);
		}
	}
}
//add_action( 'wp_import_insert_post', 'leco_cp_wp_import_insert_post', 10, 4 );

/**
 * Update post meta to be imported.
 *
 * @since 4.5
 *
 * @param array $postmeta Post meta.
 * @param int   $post_id Post ID.
 * @param array $post Post data.
 *
 * @return mixed
 */
function leco_cp_wp_import_post_meta( $postmeta, $post_id, $post ) {
	if ( in_array( $post['post_type'], array( 'leco_client', 'leco_template' ), true ) ) {
		if ( ! empty( $postmeta ) ) {
			$leco_cp_imported_ids = $GLOBALS['wp_import']->processed_posts;

			foreach ( $postmeta as $key => $meta ) {
				if ( isset( $meta['key'] ) ) {
					// rematch content page IDs.
					if ( preg_match( '/(leco_cp_part_)[\d](_module)/', $meta['key'] ) ) {
						$modules = maybe_unserialize( $meta['value'] );

						foreach ( (array) $modules as $k => $module ) {
							if ( isset( $module['type'] ) && 'content_page' === $module['type'] && isset( $leco_cp_imported_ids[ $module['content_page'] ] ) ) {
								$modules[ $k ]['content_page'] = (string) $leco_cp_imported_ids[ $module['content_page'] ];
							} elseif ( isset( $module['type'] ) && 'files' === $module['type'] ) {
								$files = maybe_unserialize( $module['files'] );
								foreach ( (array) $files as $attachment_id => $file ) {
									$new_attachment_id = $leco_cp_imported_ids[ $attachment_id ];
									$attachment_url    = wp_get_attachment_url( $new_attachment_id );
									if ( false !== $attachment_url ) {
										unset( $modules[ $k ]['files'][ $attachment_id ] );
										$modules[ $k ]['files'][ $new_attachment_id ] = $attachment_url;
									}
								}
							}
						}

						$postmeta[ $key ]['value'] = $modules;
					}

					// remove client accounts.
					if ( 'leco_cp_client' === $meta['key'] ) {
						if ( apply_filters( 'leco_cp_import_skip_client', true ) ) {
							$postmeta[ $key ]['value'] = '';
						}
					}

					// update file ID.
					if ( 'leco_cp_logo_id' === $meta['key'] || 'leco_cp_header_background_image_id' === $meta['key'] ) {
						$postmeta[ $key ]['value'] = $leco_cp_imported_ids[ $postmeta[ $key ]['value'] ];
					}
				}
			}
		}
	}

	return $postmeta;
}
add_action( 'wp_import_post_meta', 'leco_cp_wp_import_post_meta', 10, 3 );

/**
 * Display JavaScript on the page.
 *
 * @since 4.5
 */
function leco_cp_export_add_js() {
	?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            var form = $('#export-filters');
            $('.leco-cp-export-filters').hide();
            form.find('input:radio').change(function() {
                switch ( $(this).val() ) {
                    case 'leco-cp':
                        $('#leco-cp-export-client').slideDown();
                        break;
                    default:
                        $('.leco-cp-export-filters').slideUp();
                }
            });
        });
    </script>
	<?php
}
add_action( 'admin_head', 'leco_cp_export_add_js', 100 );

/**
 * Add our own export option.
 *
 * @since 4.5
 */
function leco_cp_export_filters() {
	?>
	<p>
        <label><input type="radio" name="content" value="leco-cp" id="leco-cp-content" /> <?php _e( 'Client Portal data (Projects/Templates/Content Pages)',	'leco-cp' );
	?></label>
    </p>
    <p class="leco-cp-export-filters" id="leco-cp-export-client" style="margin-left: 23px;">
        Client: <select class="smallfat" id="leco_client" name="leco_client">
            <option value="all">All Clients</option>
		    <?php
		    $clients = new WP_User_Query( array( 'role' => 'leco_client' ) );
		    $clients = $clients->get_results();
		    foreach ( $clients as $client ) {
			    ?>
                <option value="<?php echo $client->ID; ?>"><?php echo $client->display_name; ?></option>
		    <?php } ?>
        </select>
    </p>
	<?php
}
add_action( 'export_filters', 'leco_cp_export_filters' );

/**
 * Export action using custom query
 *
 * @since 4.5
 *
 * @param array $args Query arguments.
 */
function leco_cp_export_wp_action( $args ) {
	if ( 'leco-cp' === $args['content'] ) {
		require_once LECO_CLIENT_PORTAL_DIR . 'includes/admin/export-wp.php';

		// Don't use default WP export, instead set parameter for further use.
		global $wp_query;
		$wp_query->wp_exporter = true;
		leco_cp_export_wp( $args );
		die();
	}
}
add_action( 'export_wp', 'leco_cp_export_wp_action' );

/**
 * Store processed_posts to options.
 *
 * @since 4.5
 *
 * @param array $terms Post terms.
 *
 * @return mixed
 */
function leco_cp_wp_import_post_terms( $terms ) {
	update_option( 'leco_cp_processed_posts', $GLOBALS['wp_import']->processed_posts );

	return $terms;
}

add_action( 'wp_import_post_terms', 'leco_cp_wp_import_post_terms' );

/**
 * Remove import_id so the post ID can be reset.
 *
 * @since 4.5
 *
 * @param array $postdata Post data.
 *
 * @return mixed
 */
function leco_cp_wp_import_post_data_processed( $postdata ) {
	unset( $postdata['import_id'] );

	return $postdata;
}

add_filter( 'wp_import_post_data_processed', 'leco_cp_wp_import_post_data_processed' );

/**
 * Update file urls in other post meta.
 *
 * @since 4.5
 */
function leco_cp_import_end() {
	global $wpdb;
	// make sure we do the longest urls first, in case one is a substring of another.
	uksort( $GLOBALS['wp_import']->url_remap, array( &$GLOBALS['wp_import'], 'cmpr_strlen' ) );

	foreach ( $GLOBALS['wp_import']->url_remap as $from_url => $to_url ) {
		// remap file urls.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='leco_cp_logo'", $from_url, $to_url ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='leco_cp_header_background_image'", $from_url, $to_url ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='leco_cp_general_information'", $from_url, $to_url ) );
	}
}
add_action( 'import_end', 'leco_cp_import_end' );
