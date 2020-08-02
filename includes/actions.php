<?php
/**
 * Action Hooks
 *
 * @package     ClientPortal\ACTIONS
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_embed;
add_filter( 'leco_cp_media_filter', array( $wp_embed, 'autoembed' ), 8 );

function leco_cp_wrap_oembed_html( $cached_html, $url = '', $attr = '', $post_id = '' ) {
	$post = get_post( $post_id );

	if ( in_array( $post->post_type, array( 'leco_client', 'leco_template' ) ) && !strstr( $url, 'wistia' ) ) {
		return '<div class="leco-cp-responsive-video">' . $cached_html . '</div>';
	}

	return $cached_html;
}
add_filter( 'embed_oembed_html', 'leco_cp_wrap_oembed_html', 99, 4 );

function leco_cp_add_default_meta_values( $post_id, $post, $update ) {

	if ( ! in_array( $post->post_type, array(
			'leco_client',
			'leco_template'
		) ) || $update || defined( 'WP_LOAD_IMPORTERS' ) ) {
		return;
	}

	$defaults = array(
		'leco_cp_part_0_title'  => 'Discovery',
		'leco_cp_part_0_module' => array(
			array(
				'title'       => 'The brief',
				'description' => 'The outline of our project and all key tasks.',
				'icon'        => 'Finding',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Inspiration',
				'description' => 'A Pinterest board where you can add all your design likes.',
				'icon'        => 'Bespoke',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'About your business',
				'description' => 'A questionnaire for you to fill out about your business.',
				'icon'        => 'Hiring',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Website questionnaire',
				'description' => 'A questionnaire for you to fill out about your website.',
				'icon'        => 'Feedback',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => '20 second gut test',
				'description' => 'Rate these websites so I can get an idea of your likes (and dislikes).',
				'icon'        => 'Quote',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Content sheet',
				'description' => 'Download this worksheet to help you write your website content.',
				'icon'        => 'Content',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
		),
		'leco_cp_part_1_title'  => 'Website',
		'leco_cp_part_1_module' => array(
			array(
				'title'       => 'Prototype',
				'description' => 'View the website prototype for the design.',
				'icon'        => 'Testing',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Style guide',
				'description' => 'Your style guide keeps your brand nice and consistent.',
				'icon'        => 'Blog',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'The final deliverable',
				'description' => 'These are all the files to hand over to the developers to build.',
				'icon'        => 'Promote',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
		),
		'leco_cp_part_2_title'  => 'Assets',
		'leco_cp_part_2_module' => array(
			array(
				'title'       => 'Proposal',
				'description' => 'The initial proposal and quote for your project.',
				'icon'        => 'SEO',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Contract',
				'description' => 'A copy of our signed agreement for your reference.',
				'icon'        => 'Content',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Invoice receipts',
				'description' => 'Keep up to date with all payments made and download receipts.',
				'icon'        => 'Cost',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
			array(
				'title'       => 'Giving effective feedback',
				'description' => 'A cheat sheet on how to give amazing feedback.',
				'icon'        => 'Feedback',
				'url'         => '#',
				'status'      => 'active',
				'new_tab'     => 'default',
			),
		),
	);

	foreach ( $defaults as $key => $value ) {
		add_post_meta( $post_id, $key, $value, true );
	}
}

add_action( 'save_post', 'leco_cp_add_default_meta_values', 10, 3 );

/**
 * Hook function to alter template file.
 *
 * @since 4.7     Added template folder option.
 * @since unknown
 *
 * @param string $template Template file path.
 *
 * @return string
 */
function leco_cp_get_template( $template ) {
	global $wp_query;

	$leco_cp_template = apply_filters( 'leco_cp_get_template', leco_cp_get_option( 'template', 'tailwind' ) );

	if ( 'leco_client' === $wp_query->query_vars['post_type'] ) {
		if ( is_single() ) {
			if ( get_query_var( 'leco_content_page' ) ) {
				$template = LECO_CLIENT_PORTAL_DIR . "templates/$leco_cp_template/content.php";
			} elseif ( get_query_var( 'leco_content_search' ) ) {
				$template = LECO_CLIENT_PORTAL_DIR . "templates/$leco_cp_template/search.php";
			} else {
				$template = LECO_CLIENT_PORTAL_DIR . "templates/$leco_cp_template/index.php";
			}
		} elseif ( is_post_type_archive() ) {
			$template = LECO_CLIENT_PORTAL_DIR . "templates/$leco_cp_template/archive.php";
		}
	}

	return $template;
}

/**
 * Redirect page or change the template
 */
function leco_cp_rewrite_templates() {
	global $post;

	if ( empty( $post ) ) {
		global $wp_query;
		if ( $wp_query->is_singular ) {
			$post = get_page_by_path( $wp_query->query['name'], OBJECT, 'leco_template' );
		}

		$post_type = $wp_query->query_vars['post_type'];
	} else {
		$post_type = $post->post_type;
    }

	if ( ! is_search() && ( in_array( $post_type, array( 'leco_client', 'leco_template' ) ) || ( isset( $post->post_name ) && 'client-portal-login' === $post->post_name ) ) ) {
		// Fixed compatible issues with page builders like Layers.
		remove_all_filters( 'single_template' );
		remove_all_filters( 'archive_template' );
		// Fixed specific issue with the Layers theme.
		if ( function_exists( 'layers_body_class' ) ) {
			remove_action( 'body_class', 'layers_body_class' );
		}
		// Fixed specific issue with the Layers Pro plugin.
		if ( class_exists( 'Layers_Pro' ) ) {
			global $layers_pro;
			remove_action( 'wp_enqueue_scripts', array( $layers_pro, 'enqueue_scripts' ), 30 );
		}
		// Fixed compatibility issue with Advanced NoCaptcha ReCaptcha.
		if ( class_exists( 'anr_captcha_class' ) ) {
			remove_filter( 'login_form_middle', array( anr_captcha_class::init(), 'login_form_return' ), 99 );
		}
		// Remove Flatsome Custom CSS from CP.
		if ( function_exists( 'flatsome_custom_css' ) ) {
			remove_action( 'wp_head', 'flatsome_custom_css', 100 );
		}

		add_filter( 'single_template', 'leco_cp_get_template' );
		add_filter( 'archive_template', 'leco_cp_get_template' );

		// Fix themes try to load their own template files. #HS739
		// Remove all other scripts since we don't need them in CP templates.
		// Increase the priority if a theme use a high number like 2000.
		$priority = apply_filters( 'leco_cp_enqueue_scripts_priority', 100 );
		add_action( 'wp_enqueue_scripts', 'leco_cp_enqueue_scripts', $priority );
		// Fix Divi Toolbox.
		if ( function_exists( 'dtb_scripts_styles_enqueue' ) ) {
			remove_action( 'wp_enqueue_scripts', 'dtb_scripts_styles_enqueue', 1 );
		}

		if ( apply_filters( 'leco_cp_remove_template_include', true ) ) {
			remove_all_filters( 'template_include' );
		}

		if ( 'leco_template' === $post->post_type ) {
			add_filter( 'template_include', function() {
				$leco_cp_template = apply_filters( 'leco_cp_get_template', leco_cp_get_option( 'template', 'tailwind' ) );

				if ( get_query_var( 'leco_content_page' ) ) {
					return LECO_CLIENT_PORTAL_DIR . "/templates/$leco_cp_template/content.php";
				} elseif ( get_query_var( 'leco_content_search' ) ) {
					return LECO_CLIENT_PORTAL_DIR . "/templates/$leco_cp_template/search.php";
				} else {
					return LECO_CLIENT_PORTAL_DIR . "/templates/$leco_cp_template/index.php";
				}
			});
		}
	}

	$is_allowed = leco_cp_user_role_allowed();

	if ( is_post_type_archive( 'leco_client' ) ) {
		if ( ! is_user_logged_in() || ! $is_allowed ) {
			wp_redirect( leco_cp_login_url() );
			exit();
		}
	} elseif ( isset( $post->post_name ) && 'client-portal-login' == $post->post_name ) {
		$projects = leco_cp_get_projects_by_client( get_current_user_id() );
		$count    = count( $projects );

		if ( ! is_user_logged_in() || ! $is_allowed || 0 === $count ) {
			add_action( 'wp_head', 'wp_sensitive_page_meta' );
			add_filter( 'template_include', function () {
				$leco_cp_template = apply_filters( 'leco_cp_get_template', leco_cp_get_option( 'template', 'tailwind' ) );
				return LECO_CLIENT_PORTAL_DIR . "/templates/$leco_cp_template/login.php";
			} );
		} else {
			if ( $count > 1 ) {
				wp_redirect( site_url( 'client' ) ); // redirect to projects list
			} else {
				$projects   = array_values( $projects );
				$project_id = is_int( $projects[0] ) ? $projects[0] : $projects[0]->ID;
				wp_redirect( get_permalink( $project_id ) ); // redirect to the single project
			}
			exit();
		}
	}
}

add_action( 'template_redirect', 'leco_cp_rewrite_templates' );

/*
 * Function creates post duplicate as a draft and redirects then to the edit post screen
 * @link https://rudrastyh.com/wordpress/duplicate-post.html
 */
function leco_cp_clone_post() {
	global $wpdb;

	// check nonce.
	if ( ! check_admin_referer( $_REQUEST['action'] . '_' . abs( $_REQUEST['post'] ) ) ) {
		wp_die( __( 'We cannot find the post to clone.', 'leco-cp' ) );
	}

	/*
	 * get the original post id
	 */
	$post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );

	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user    = wp_get_current_user();
	$new_post_author = $current_user->ID;

	/*
	 * if post data exists, create the post duplicate
	 */
	if ( isset( $post ) && $post != null ) {

		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => 'closed',
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => ( 'leco_cp_convert_to_template' !== $_REQUEST['action'] ) ? 'Copy of ' . $post->post_title : 'Template copy from ' . $post->post_title,
			'post_type'      => ( 'leco_cp_duplicate_post_as_draft' === $_REQUEST['action'] ) ? $post->post_type : ( ( 'leco_cp_convert_to_template' === $_REQUEST['action'] ) ? 'leco_template' : 'leco_client' ),
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);

		remove_action( 'save_post', 'leco_cp_add_default_meta_values', 10 );

		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );

		/*
		 * duplicate all post meta just in two SQL queries
		 */
		$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id" );
		if ( count( $post_meta_infos ) != 0 ) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ( $post_meta_infos as $meta_info ) {
				$meta_key        = $meta_info->meta_key;
				// don't copy leco_client meta in content pages.
				if ( 'leco_content_page' === $post->post_type && 'leco_client' === $meta_key ) {
				    continue;
                } elseif ( 'leco_content_page' !== $post->post_type && 'leco_cp_client' === $meta_key ) {
					continue;
				}

				$meta_value      = addslashes( $meta_info->meta_value );
				$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query .= implode( " UNION ALL ", $sql_query_sel );
			$wpdb->query( $sql_query );
		}

		add_action( 'save_post', 'leco_cp_add_default_meta_values', 10, 3 );

		/**
		 * Remove files from client uploads module that just being cloned.
		 */
		remove_action( 'update_postmeta', 'leco_cp_update_postmeta' );
		$number_of_parts = get_post_meta( $new_post_id, 'leco_cp_number_of_parts', true );
		if ( ! $number_of_parts ) {
			$number_of_parts = 3;
		}
		for ( $i = 0; $i < $number_of_parts; $i ++ ) {
			$modules = get_post_meta( $new_post_id, 'leco_cp_part_' . $i . '_module', true );
			if ( ! empty( $modules ) ) {
				foreach ( $modules as $k => $module ) {
					if ( 'client-uploads' === $module['type'] && isset( $module['client_uploads'] ) ) {
						unset( $modules[ $k ]['client_uploads'] );

						update_post_meta( $new_post_id, 'leco_cp_part_' . $i . '_module', $modules );
					}
				}
			}
		}
		add_action( 'update_postmeta', 'leco_cp_update_postmeta', 10, 4 );

		/*
		 * finally, redirect to the edit post screen for the new draft
		 */
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die( 'Post creation failed, could not find original post: ' . $post_id );
	}
}

add_action( 'admin_action_leco_cp_duplicate_post_as_draft', 'leco_cp_clone_post' );
add_action( 'admin_action_leco_cp_convert_to_template', 'leco_cp_clone_post' );
add_action( 'admin_action_leco_cp_create_new_project', 'leco_cp_clone_post' );

/*
 * Add the duplicate link to action list for post_row_actions
 */
function leco_cp_duplicate_post_link( $actions, $post ) {
	if ( in_array( $post->post_type, array( 'leco_client', 'leco_template', 'leco_content_page' ) ) && current_user_can( 'edit_posts' ) ) {
		$nonce_url = wp_nonce_url( 'edit.php?action=leco_cp_duplicate_post_as_draft&amp;post=' . $post->ID, 'leco_cp_duplicate_post_as_draft_' . $post->ID );
		$actions['duplicate'] = '<a href="' . $nonce_url . '" title="' . __( 'Duplicate this item', 'leco-cp' ) . '" rel="permalink">' . __( 'Duplicate', 'leco-cp' ) . '</a>';

		switch ( $post->post_type ) {
			case 'leco_client':
				$nonce_url = wp_nonce_url( 'edit.php?action=leco_cp_convert_to_template&amp;post=' . $post->ID, 'leco_cp_convert_to_template_' . $post->ID );
				$actions['convert_to_template'] = '<a href="' . $nonce_url . '" title="' . __( 'Convert to template', 'leco-cp' ) . '" rel="permalink">' . __( 'Convert To Template', 'leco-cp' ) . '</a>';
				break;
			case 'leco_template':
				$nonce_url = wp_nonce_url( 'edit.php?action=leco_cp_create_new_project&amp;post=' . $post->ID, 'leco_cp_create_new_project_' . $post->ID );
				$actions['create_new_project'] = '<a href="' . $nonce_url . '" title="' . __( 'Create New Project', 'leco-cp' ) . '" rel="permalink">' . __( 'Create New Project', 'leco-cp' ) . '</a>';
				break;
		}

		if ( 'leco_content_page' !== $post->post_type ) {
			$url = admin_url('post.php?action=edit&amp;post=' . $post->ID . '&amp;cp-action=manage-phases' );
			$actions['manage_phases'] = '<a href="' . $url . '" title="' . __( 'Manage Phases', 'leco-cp' ) . '" rel="permalink">' . __( 'Manage Phases', 'leco-cp' ) . '</a>';
		}
	}

	return $actions;
}

add_filter( 'post_row_actions', 'leco_cp_duplicate_post_link', 10, 2 );

function leco_cp_query_vars( $vars ) {
    if ( is_admin() ) {
        $vars[] = 'leco_client_id';
    }

    return $vars;
}
add_filter( 'query_vars', 'leco_cp_query_vars' );

/**
 * Modify WP Query to suit our needs
 *
 * @since unknown
 * @since 4.4     Getting user projects from the new user meta "leco_cp_project".
 *
 * @param object $query The post query object.
 */
function leco_cp_get_archive_posts( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		$is_allowed = leco_cp_user_role_allowed();

		// Get projects belongs to the current user
		if ( $query->is_post_type_archive( 'leco_client' ) && $is_allowed ) {
		    $user_id = get_current_user_id();
		    if ( $projects = get_user_meta( $user_id, 'leco_cp_project', true ) ) {
		        $projects = maybe_unserialize( $projects );
		        $query->set( 'post__in', $projects );
            } else {
			    $query->set( 'meta_key', 'leco_cp_client' );
			    $query->set( 'meta_value', get_current_user_id() );
            }

			$query->set( 'posts_per_page', -1 );
		}

		// Hide private portals from WP search
		if ( $query->is_search ) {
			$excluded_private_portals = leco_cp_get_private_portals();
			$query->set( 'post__not_in', $excluded_private_portals );
		}
	} elseif ( is_admin() && $query->is_main_query() ) {
		// Get projects belongs to set query var.
		$user_id = $query->get( 'leco_client_id' );
		if ( 'leco_client' === $query->get( 'post_type' ) && ! empty( $user_id ) ) {
			if ( $projects = get_user_meta( $user_id, 'leco_cp_project', true ) ) {
				$projects = maybe_unserialize( $projects );
				$query->set( 'post__in', $projects );
			} else {
				$query->set( 'meta_key', 'leco_cp_client' );
				$query->set( 'meta_value', get_current_user_id() );
			}

			$query->set( 'posts_per_page', -1 );
		}
    }
}

add_action( 'pre_get_posts', 'leco_cp_get_archive_posts' );

/**
 * Get an array of private portals' ID
 *
 * @return array Private portal IDs
 */
function leco_cp_get_private_portals() {
	remove_action( 'pre_get_posts', 'leco_cp_get_archive_posts' );

	// Password protected portal
	$password_protected_portals = get_posts( array(
		'post_type'    => 'leco_client',
		'has_password' => true
	) );
	$post_ids                   = wp_list_pluck( $password_protected_portals, 'ID' );

	$client_only_portals = get_posts( array(
		'post_type'  => 'leco_client',
		'meta_key'   => 'leco_cp_public_portal',
		'meta_value' => 'no'
	) );
	$post_ids            = array_merge( $post_ids, wp_list_pluck( $client_only_portals, 'ID' ) );

	add_action( 'pre_get_posts', 'leco_cp_get_archive_posts' );

	return $post_ids;
}

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function leco_cp_login_redirect( $redirect_to, $request, $user ) {
	if ( site_url( 'client' ) != $request )
		return $redirect_to;

	if ( isset( $user->roles ) && is_array( $user->roles ) && in_array( 'leco_client', $user->roles ) ) {
		$projects = leco_cp_get_projects_by_client( $user->ID );
		$count = count( $projects );

		if ( $count > 1 ) {
			$redirect_to = site_url( 'client' );
		} else {
			$project_id = is_int( $projects[0] ) ? $projects[0] : $projects[0]->ID;
			$redirect_to = get_permalink( $project_id );
		}
	}

	return $redirect_to;
}

add_filter( 'login_redirect', 'leco_cp_login_redirect', 10, 3 );

/**
 * Add the Filter by Client dropdown.
 *
 * @since 4.4.2
 *
* @param string $post_type Post type.
 */
function leco_cp_restrict_manage_posts( $post_type ) {
    if ( 'leco_client' === $post_type || 'leco_template' === $post_type ) {
        $client_id = get_query_var( 'leco_client_id' );
        $clients = new WP_User_Query( array( 'role' => 'leco_client' ) );
        $clients = $clients->get_results();
        ?>
     <label for="filter-by-client" class="screen-reader-text"><?php esc_html_e( 'Filter by Client' ); ?></label>
		<select name="leco_client_id" id="filter-by-client">
			<option<?php selected( $client_id, 0 ); ?> value="0"><?php esc_html_e( 'All Clients' ); ?></option>
			<?php foreach ( $clients as $client ) { ?>
            <option<?php selected( $client_id, $client->ID ); ?> value="<?php echo $client->ID; ?>"><?php echo $client->display_name; ?></option>
			<?php } ?>
        </select>
<?php
    }
}
add_action( 'restrict_manage_posts', 'leco_cp_restrict_manage_posts' );

/**
 * Customize admin columns.
 *
 * @since unknown
 * @since 4.4 Added more columns to show project and client relationships.
 *
 * @param $columns
 *
 * @return array
 */
function set_custom_edit_leco_client_columns( $columns ) {
	$screen    = get_current_screen();
	$post_type = $screen->post_type;

	if ( 'leco_content_page' === $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			$_columns = array(
				'cb'       => '<input type="checkbox" />',
				'ID'       => __( 'ID', 'leco-cp' ),
				'title'    => $columns['title'],
				'projects' => __( 'CP Projects', 'leco-cp' ),
				'author'   => $columns['author'],
				'comments' => $columns['comments'],
				'date'     => $columns['date']
			);
		} else {
			$_columns = array(
				'cb'       => '<input type="checkbox" />',
				'ID'       => __( 'ID', 'leco-cp' ),
				'title'    => $columns['title'],
				'projects' => __( 'CP Projects', 'leco-cp' ),
				'author'   => $columns['author'],
				'date'     => $columns['date']
			);
		}
	} else {
		$_columns = array(
			'cb'             => '<input type="checkbox" />',
			'ID'             => __( 'ID', 'leco-cp' ),
			'title'          => $columns['title'],
			'leco_cp_client' => __( 'Client', 'leco-cp' ),
			'author'         => $columns['author'],
			'date'           => $columns['date']
		);
	}

	return $_columns;
}
add_filter( 'manage_leco_client_posts_columns', 'set_custom_edit_leco_client_columns' );
add_filter( 'manage_leco_template_posts_columns', 'set_custom_edit_leco_client_columns' );
add_filter( 'manage_leco_content_page_posts_columns', 'set_custom_edit_leco_client_columns' );

/**
 * Add custom column values.
 *
 * @since unknown.
 * @since 4.4 Added more columns to show project and client relationships.
 *
 * @param $column
 * @param $post_id
 */
function custom_leco_client_column( $column, $post_id ) {
	switch ( $column ) {
		case 'ID':
			echo $post_id;
			break;
        case 'projects':
            $projects = leco_cp_get_projects_by_content_page( $post_id );
	        if ( ! empty( $projects ) ) {
		        $links = array();
		        foreach ( $projects as $project ) {
			        $links[] = '<a href="' . admin_url( 'post.php?post=' . $project->ID . '&action=edit' ) . '" target="_blank">' . $project->post_title . '</a>';
		        }

		        echo implode( ', ', $links );
            } else {
	            echo '-';
            }
            break;
		case 'leco_cp_client' :
			$client_id = get_post_meta( $post_id, 'leco_cp_client', true );
			$clients = array();
			if ( leco_cp_is_public_portal( $post_id ) ) {
				echo esc_html__( 'Public', 'leco-cp' );
			} elseif ( $client_id ) {
			    if ( is_string( $client_id ) ) {
			        $client_id = array( $client_id );
                }

				foreach ( $client_id as $user_id ) {
					$client = get_user_by( 'id', $user_id );
					$clients[] = ( isset( $client->display_name ) ) ? $client->display_name : '-';
                }
				echo implode( ', ', $clients );
			} else {
				echo '-';
			}
			break;
	}
}
add_action( 'manage_leco_client_posts_custom_column' , 'custom_leco_client_column', 10, 2 );
add_action( 'manage_leco_template_posts_custom_column' , 'custom_leco_client_column', 10, 2 );
add_action( 'manage_leco_content_page_posts_custom_column' , 'custom_leco_client_column', 10, 2 );

/**
 * Manage user columns.
 *
 * @since 4.4
 *
 * @param $columns
 *
 * @return mixed
 */
function leco_cp_manage_users_columns( $columns ) {
	$columns['leco_cp_project'] = esc_html__( 'CP Projects', 'leco-cp' );

	return $columns;
}
add_action( 'manage_users_columns', 'leco_cp_manage_users_columns' );

/**
 * Display custom user column values.
 *
 * @since 4.4
 * @scine 4.8.3 Fix an issue where usermeta still contains deleted projects.
 *
 * @param string $value   The column value.
 * @param string $column  The column name.
 * @param int    $user_id The user ID.
 *
 * @return array|string
 */
function leco_cp_manage_users_custom_column( $value, $column, $user_id ) {
	if ( 'leco_cp_project' === $column ) {
		$projects  = leco_cp_get_projects_by_client( $user_id );
		$_projects = array();

		$value = array();
		foreach ( $projects as $key => $project ) {
			$title = get_the_title( $project );

			if ( ! empty( $title ) ) {
				$_projects[ $key ] = $project;

				$value[ $key ] = '<a href="' . admin_url( 'post.php?post=' . $project . '&action=edit' ) . '" target="_blank">';
				$value[ $key ] .= $title;
				$value[ $key ] .= '</a>';
			}
		}

		if ( $projects !== $_projects ) {
			update_user_meta( $user_id, 'leco_cp_project', $_projects );
		}

		$value = implode( ', ', $value );
	}

	return $value;
}
add_action( 'manage_users_custom_column', 'leco_cp_manage_users_custom_column', 10, 3 );

function leco_cp_password_form( $output ) {
	global $post;

	if ( in_array( $post->post_type, array( 'leco_client', 'leco_template' ) ) ) {
	$label = 'pwbox-' . ( empty($post->ID) ? rand() : $post->ID );
	ob_start();
	?>
	<form name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>" method="post" class="post-password-form">
		<p class="login-password">
			<label for="<?php echo $label; ?>"><?php _e( 'Password' ) ?></label>
			<input type="password" name="post_password" id="<?php echo $label; ?>" class="input" value="" size="20">
		</p>
		<p class="login-submit">
			<input type="submit" name="Submit" class="button button-primary" value="<?php echo esc_attr_x( 'Enter', 'post password form' ); ?>">
		</p>

	</form>
<?php
	$output = ob_get_clean();
	}

	return $output;
}
add_filter( 'the_password_form', 'leco_cp_password_form' );

function leco_cp_protected_title_format( $title, $post ) {

	if ( in_array( $post->post_type, array( 'leco_client', 'leco_template' ) ) ) {
		return '%s';
	} else {
		return $title;
	}
}
add_filter( 'protected_title_format', 'leco_cp_protected_title_format', 10, 2 );

function leco_cp_get_canonical_url( $canonical ) {
	if ( is_single() && get_query_var( 'leco_content_page' ) ) {
		$canonical = trailingslashit( get_permalink() . 'module/' . get_query_var( 'leco_content_page' ) );
	}

	return $canonical;
}
add_filter( 'get_canonical_url', 'leco_cp_get_canonical_url' );
add_filter( 'wpseo_canonical', 'leco_cp_get_canonical_url' );

/**
 * Enqueue scripts from 3rd party plugin.
 *
 * @since 4.6
 */
function leco_cp_enqueue_scripts_third_party() {
	// Fixed compatibility issue with Login No Captcha reCAPTCHA.
	if ( is_page( 'client-portal-login' ) ) {
		if ( class_exists( 'LoginNocaptcha' ) ) {
			if(!wp_script_is('login_nocaptcha_google_api','registered')) {
				LoginNocaptcha::register_scripts_css();
			}

			wp_enqueue_script('login_nocaptcha_google_api');
			wp_enqueue_style('login_nocaptcha_css');
		}
    }
}
add_action( 'leco_cp_enqueue_scripts', 'leco_cp_enqueue_scripts_third_party' );

/**
 * Admin notices about switch to legacy theme.
 *
 * @since 4.7
 */
function leco_cp_tailwind_admin_notices() {
	if ( 1 != get_option( 'leco_cp_hide_notice-tailwind_release', 0 ) ) {
		echo '<div class="notice notice-warning is-dismissible leco-cp-notice" id="tailwind_release" data-nonce="' . wp_create_nonce( 'leco_cp_hide_notice-tailwind_release' ) . '">';
		echo '<p>' . esc_html__( "Client Portal 4.7 has an upgraded Default theme to improve mobile experiences. We also added a new search feature in your portals. ", 'leco-cp' );
		echo sprintf( esc_html__( "%sRead more about this release.%s", 'leco-cp' ), '<a href="https://client-portal.io/client/client-portal-support/module/what-is-the-legacy-theme/" target="_blank">', '</a>' ) . '</p>';
		echo '</div>';
	}
}

/**
 * Force trailingslash for leco_client post type. Fix a compatible issue with the Semplice theme.
 *
 * Some users need to have the permalink end with no "/".
 *
 * @param string  $post_link The post's permalink.
 * @param WP_Post $post      The post in question.
 *
 * @return string
 */
function leco_cp_post_link_trailingslashit( $post_link, $post ) {
	if ( 'leco_client' === get_post_type( $post ) ) {
		global $wp_rewrite;
		if ( ! $wp_rewrite->use_trailing_slashes || substr( $wp_rewrite->permalink_structure, -1, 1 ) !== '/' ) {
			$post_link = trailingslashit( $post_link );
		}
	}

	return $post_link;
}

add_action( 'post_type_link', 'leco_cp_post_link_trailingslashit', 10, 2 );

/**
 * Remove related posts from CP posts.
 *
 * @since 4.8.2
 *
 * @param array $options Array of basic Related Posts options.
 *
 * @return array
 */
function leco_cp_remove_relatedposts( $options ) {
	if ( is_singular( array(
			'leco_client',
			'leco_template',
			'leco_content_page',
		) ) || is_page( 'client-portal-login' ) ) {
		$options['enabled'] = false;
	}

	return $options;
}

add_filter( 'jetpack_relatedposts_filter_options', 'leco_cp_remove_relatedposts' );
