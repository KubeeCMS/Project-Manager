<?php
/**
 * Check if a content page is shared.
 * Used to disable comments.
 * Content pages attached to "only" public portals can have comments enabled.
 *
 * @param int $post_id Content page ID.
 *
 * @return bool
 */
function leco_cp_is_shared_content_page( $post_id ) {
	global $wpdb;
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta a INNER JOIN (SELECT * FROM {$wpdb->prefix}postmeta WHERE `meta_key` = 'leco_cp_public_portal' AND `meta_value` = 'no') b ON a.`post_id` = b.`post_id` WHERE a.`meta_value` LIKE %s", '%' . $wpdb->esc_like( '"content_page";s:' . strlen( $post_id ) . ':"' . $post_id . '";' ) . '%' ) );

	if ( $count > 1 ) {
		return true;
	}

	return false;
}

/**
 * Get portal by content page id.
 * Content pages attached to "only" public portals can have comments enabled.
 *
 * Note there could be more than 1 portals attached.
 * Use get_post_meta for `leco_client` if you need the portal that comments are attached.
 *
 * @param int $post_id Post ID.
 *
 * @return null|string Project ID.
 */
function leco_cp_get_project_by_content_page( $post_id ) {
	global $wpdb;
	$portal_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta a INNER JOIN (SELECT * FROM {$wpdb->prefix}postmeta WHERE `meta_key` = 'leco_cp_public_portal' AND `meta_value` = 'no') b ON a.`post_id` = b.`post_id` WHERE a.`meta_value` LIKE %s", '%' . $wpdb->esc_like( '"content_page";s:' . strlen( $post_id ) . ':"' . $post_id . '";' ) . '%' ) );

	return $portal_id;
}

/**
 * Get portals by content page id.
 *
 * @param int $post_id Post ID.
 *
 * @return array|null|object
 */
function leco_cp_get_projects_by_content_page( $post_id ) {
	global $wpdb;
	$projects = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT `ID`, `post_title` FROM {$wpdb->prefix}postmeta a LEFT JOIN {$wpdb->prefix}posts b ON a.post_id = b.ID WHERE `meta_value` LIKE %s", '%' . $wpdb->esc_like( '"content_page";s:' . strlen( $post_id ) . ':"' . $post_id . '";' ) . '%' ) );

	return $projects;
}

/**
 * Return content page ids by project id.
 *
 * @since 4.7.0
 *
 * @param int $project_id Project ID.
 *
 * @return array
 */
function leco_cp_get_content_pages_by_project( $project_id ) {
	$number_of_parts = get_post_meta( $project_id, 'leco_cp_number_of_parts', true );
	if ( ! $number_of_parts ) {
		$number_of_parts = 3;
	}
	$content_pages = array();

	for ( $i = 0; $i < $number_of_parts; $i ++ ) {
		$modules = get_post_meta( $project_id, 'leco_cp_part_' . $i . '_module', true );
		foreach ( $modules as $module ) {
			if ( 'content_page' === $module['type'] ) {
				$content_pages[] = $module['content_page'];
			}
		}
	}

	return $content_pages;
}
