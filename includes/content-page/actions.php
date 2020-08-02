<?php

function leco_cp_add_content_page() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	$args = array(
		'post_status'    => 'publish',
		'post_author'    => get_current_user_id(),
		'post_title'     => sanitize_text_field( $_POST['title'] ),
		'post_content'     => '<h1>' . sanitize_text_field( $_POST['title'] ) . '</h1>' . sanitize_textarea_field( $_POST['content'] ),
		'post_type'      => 'leco_content_page',
	);

	$post_id = wp_insert_post( $args );

	if ( ! is_wp_error( $post_id ) ) {
		wp_die( json_encode(
			get_post( $post_id ),
			JSON_FORCE_OBJECT
		) );
	} else {
		wp_die(-1);
	}
}
add_action( 'wp_ajax_leco_cp_add_content_page', 'leco_cp_add_content_page' );

/**
 * AJAX helper function to check if a content page has comments.
 */
function leco_cp_content_page_has_comment() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	$post_id = sanitize_text_field( $_POST['post_id'] );
	$result  = get_post_meta( $post_id, 'leco_client', true );

	wp_die( $result );
}
add_action( 'wp_ajax_leco_cp_content_page_has_comment', 'leco_cp_content_page_has_comment' );

/**
 * AJAX helper function to get comments count for a content page.
 *
 * We return 0 if the comments are from public portals.
 */
function leco_cp_get_comments_count() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	$post_id = sanitize_text_field( $_POST['post_id'] );

	$count = get_comments_number( $post_id );

	wp_die( $count );
}
add_action( 'wp_ajax_leco_cp_get_comments_count', 'leco_cp_get_comments_count' );

function leco_cp_admin_notices() {
	if ( 'leco_content_page' === get_post_type() && isset( $_GET['post'] ) ) {
		global $post;
		if ( isset( $_GET['parent_id'] ) ) {
			$parent = get_post( intval( $_GET['parent_id'] ) );
			?>
			<div class="notice notice-info">
				<p><?php echo sprintf( __( 'Preview this content page in Project %s%s%s.', 'leco-cp' ), '<a href="' . get_permalink( $parent ) . 'module/' . $post->post_name . '/" target="_blank">', $parent->post_title, '</a>' ); ?></p>
			</div>
			<?php
		} else {
			// use sql.
			$projects = leco_cp_get_projects_by_content_page( $post->ID );
			if ( ! empty( $projects ) ) {
			    $links = array();
			    $error = 0;
			    foreach ( $projects as $project ) {
			        $visibility = leco_cp_is_public_portal( $project->ID ) ? 'public' : 'private';
			        if ( 'private' === $visibility ) {
				        $error++;
			        }

			        $links[] = '<a href="' . get_permalink( $project->ID ) . 'module/' . $post->post_name . '/" target="_blank" class="leco-project-link ' . $visibility . '">' . $project->post_title . '</a>';
                }
                if ( 1 < count( $links ) ) {
                    $last_link = $links[ count( $links ) - 1 ];
                    array_pop( $links );
                    $links_text = implode( ', ', $links ) . ' and ' . $last_link;
                } else {
	                $links_text = $links[0];
                }
				?>
				<div class="notice notice-warning">
                    <p><?php echo sprintf( __( 'This content page has been linked with: %s.', 'leco-cp' ), $links_text ); ?></p>
				</div>
                <?php if ( post_type_supports( 'leco_content_page', 'comments' ) && $error > 0 && count( $projects ) > 1 ) { ?>
                <div class="notice notice-error">
                    <p><?php echo sprintf( __( 'You cannot allow comments for this content because it is linked to private portal(s).', 'leco-cp' ) ); ?></p>
                </div>
                    <?php } ?>
				<?php
			} else { ?>
				<div class="notice notice-error">
					<p><?php echo sprintf( __( 'To preview this content page, you must link it with a project.', 'leco-cp' ) ); ?></p>
				</div>
			<?php }
		}
	}
}
add_action( 'admin_notices', 'leco_cp_admin_notices', 11 );

function leco_cp_redirect_post_location( $location, $post_id ) {

	$post = get_post( $post_id );

	if ( 'leco_content_page' === $post->post_type && strstr( $_POST['_wp_http_referer'], 'parent_id' ) ) {
		$location = sanitize_textarea_field( $_POST['_wp_http_referer'] );
	}

	return $location;
}
add_action( 'redirect_post_location', 'leco_cp_redirect_post_location', 10, 2 );

function leco_cp_comment_extra_fields( $post_id ) {
	global $wp_query;

    $post = get_post( $post_id );

	if ( isset( $post->post_type ) && 'leco_content_page' === $post->post_type ) {
		echo '<input type="hidden" name="redirect_to" value="' . esc_attr( get_permalink( $post_id ) ) . '" />';
		if ( $wp_query->queried_object && $wp_query->queried_object->ID != $post->ID ) {
			echo '<input type="hidden" name="cp_project_id" value="' . esc_attr( $wp_query->queried_object->ID ) . '" />';
		}
	}
}
add_action( 'comment_form', 'leco_cp_comment_extra_fields' );

function leco_cp_content_page_link( $link, $post ) {
	if ( 'leco_content_page' === $post->post_type ) {
		if ( comments_open( $post->ID ) ) {
			//
		}

		global $wp_query;
		if ( $wp_query->queried_object && $wp_query->queried_object->ID != $post->ID ) {
			$link = esc_url( get_permalink( $wp_query->queried_object->ID ) . 'module/' . trailingslashit( $post->post_name ) );
		}
	}

	return $link;
}
add_filter( 'post_type_link', 'leco_cp_content_page_link', 10, 2 );

function leco_cp_preprocess_comment( $commentdata ) {
	$post = get_post( (int) $commentdata['comment_post_ID'] );
	if ( isset( $post->post_type ) && 'leco_content_page' === $post->post_type ) {
		$commentdata['comment_type'] = 'leco_cp_comment';
	}

	return $commentdata;
}
add_filter( 'preprocess_comment', 'leco_cp_preprocess_comment' );

/**
 * Exclude notes (comments) on leco_content_page post type from showing in Recent
 * Comments widgets
 *
 * @param object $query WordPress Comment Query Object
 * @return void
 */
function leco_cp_hide_comments( $query ) {
	if ( isset( $query->query_vars['post_id'] ) ) {
		$post = get_post( (int) $query->query_vars['post_id'] );

		if ( isset( $post->post_type ) && 'leco_content_page' !== $post->post_type ) {
			$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
			if( ! is_array( $types ) ) {
				$types = array( $types );
			}
			$types[] = 'leco_cp_comment';

			$query->query_vars['type__not_in'] = $types;
		}
	}
}
add_action( 'pre_get_comments', 'leco_cp_hide_comments', 10 );

/**
 * Exclude notes (comments) on leco_content_page post type from showing in comment feeds
 *
 * @param array $where
 * @param object $wp_comment_query WordPress Comment Query Object
 * @return array $where
 */
function leco_cp_hide_comments_from_feeds( $where, $wp_comment_query ) {
	global $wpdb;

	$where .= $wpdb->prepare( " AND comment_type != %s", 'leco_cp_comment' );

	return $where;
}
add_filter( 'comment_feed_where', 'leco_cp_hide_comments_from_feeds', 10, 2 );

/**
 * Set the default comment status in content pages to closed.
 *
 * @param string $status Comment status.
 * @param string $post_type Post type.
 *
 * @return string
 */
function leco_cp_get_default_comment_status( $status, $post_type ) {
    if ( 'leco_content_page' === $post_type ) {
        $status = 'closed';
    }

    return $status;
}
add_filter( 'get_default_comment_status', 'leco_cp_get_default_comment_status', 10, 2 );

function leco_cp_get_avatar_comment_types( $types ) {
    $types[] = 'leco_cp_comment';

    return $types;
}
add_filter( 'get_avatar_comment_types', 'leco_cp_get_avatar_comment_types' );

/**
 * Save a post meta in the Content page to bind to a portal when comments added.
 *
 * @param int    $comment_ID Comment ID.
 * @param object $comment Comment object.
 */
function leco_cp_wp_insert_comment( $comment_ID, $comment ) {
    if ( 'leco_content_page' === get_post_type( $comment->comment_post_ID ) && ! leco_cp_is_shared_content_page( $comment->comment_post_ID ) ) {
        $portal_id = ( isset( $_POST['cp_project_id'] ) && ! empty( $_POST['cp_project_id'] ) ) ? intval( $_POST['cp_project_id'] ) : 0;
        if ( $portal_id && ! leco_cp_is_public_portal( $portal_id ) ) {
	        update_post_meta( $comment->comment_post_ID, 'leco_client', $portal_id );
        }
    }
}
add_action( 'wp_insert_comment', 'leco_cp_wp_insert_comment', 10, 2 );

/**
 * Delete the portal connection with the content page when all comments are deleted.
 *
 * @param int    $comment_ID Comment ID.
 * @param object $comment Comment object.
 */
function leco_cp_deleted_comment( $comment_ID, $comment ) {
	if ( 'leco_content_page' === get_post_type( $comment->comment_post_ID ) ) {
	    $comment_count = get_comment_count( $comment->comment_post_ID );
		if ( 0 === $comment_count['trash'] ) {
			delete_post_meta( $comment->comment_post_ID, 'leco_client' );
        }
	}
}
add_action( 'deleted_comment', 'leco_cp_deleted_comment', 10, 2 );

/**
 * Added justposted URL param.
 *
 * @since 4.4
 *
 * @param string     $location The 'redirect_to' URI sent via $_POST.
 * @param WP_Comment $comment  Comment object.
 *
 * @return string
 */
function leco_cp_comment_post_redirect( $location, $comment ) {
	if ( 'leco_content_page' === get_post_type( $comment->comment_post_ID ) ) {
		$url = str_replace( '#comment-' . $comment->comment_ID, '', $location );

		$location = $url . '?justposted=' . $comment->comment_ID . '#comment-' . $comment->comment_ID;;
	}

    return $location;
}
add_filter( 'comment_post_redirect', 'leco_cp_comment_post_redirect', 10, 2 );

/**
 * Live search in portal
 *
 * @since 4.7.0
 */
function leco_cp_live_portal_search() {
	check_ajax_referer( 'leco_cp_ajax_nonce' );

	extract( $_POST );
	$s       = sanitize_text_field( $s );
	$post_id = intval( $post_id );
	$results = array();

	$number_of_parts = get_post_meta( $post_id, 'leco_cp_number_of_parts', true );
	if ( ! $number_of_parts ) {
		$number_of_parts = 3;
	}

	$_s = strtolower( $s );
	for ( $i = 0; $i < $number_of_parts; $i ++ ) {
	    $number = $i + 1;
		$modules = get_post_meta( $post_id, 'leco_cp_part_' . $i . '_module', true );
		foreach ( $modules as $key => $module ) {
			if ( preg_match("/$_s/", strtolower( $module['title'] ) ) || preg_match( "/$_s/", strtolower( $module['description'] ) ) ) {
			    $_key = $key + 1;
                $results[] = "m_{$number}_{$_key}";
            }
		}
	}

	$content_pages = new WP_Query(
		array(
			'post_type' => 'leco_content_page',
			's'         => $s,
			'post__in'  => leco_cp_get_content_pages_by_project( $post_id )
		)
	);

	if ( $content_pages->have_posts() ) {
		$results = array_merge( $results, wp_list_pluck( $content_pages->posts, 'ID' ) );
	}

	if ( ! empty( $results ) ) {
		wp_send_json_success( $results );
    } else {
		wp_die( -1 );
    }
}

add_action( 'wp_ajax_leco_cp_live_portal_search', 'leco_cp_live_portal_search' );
add_action( 'wp_ajax_nopriv_leco_cp_live_portal_search', 'leco_cp_live_portal_search' );
