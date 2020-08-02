<?php
/**
 * Register Custom Post Type
 *
 * @package     ClientPortal\CPT
 * @since       1.0.0
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a client post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function leco_cp_register_post_type() {
	$labels = array(
		'name'               => _x( 'CP Projects', 'post type general name', 'leco-cp' ),
		'singular_name'      => _x( 'CP Project', 'post type singular name', 'leco-cp' ),
		'menu_name'          => _x( 'Client Portal', 'admin menu', 'leco-cp' ),
		'name_admin_bar'     => _x( 'CP Project', 'add new on admin bar', 'leco-cp' ),
		'add_new'            => _x( 'Add New', 'project', 'leco-cp' ),
		'add_new_item'       => __( 'Add New CP Project', 'leco-cp' ),
		'new_item'           => __( 'New CP Project', 'leco-cp' ),
		'edit_item'          => __( 'Edit CP Project', 'leco-cp' ),
		'view_item'          => __( 'View CP Project', 'leco-cp' ),
		'all_items'          => __( 'All CP Projects', 'leco-cp' ),
		'search_items'       => __( 'Search CP Projects', 'leco-cp' ),
		'not_found'          => __( 'No CP projects found.', 'leco-cp' ),
		'not_found_in_trash' => __( 'No CP projects found in Trash.', 'leco-cp' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => array(
			'slug'       => 'client',
			'with_front' => false,
		),
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title', 'author' ),
	);

	register_post_type( 'leco_client', apply_filters( 'leco_cpt_args', $args, 'leco_client' ) );

	$labels = array(
		'name'               => _x( 'CP Project Templates', 'post type general name', 'leco-cp' ),
		'singular_name'      => _x( 'CP Project Template', 'post type singular name', 'leco-cp' ),
		'menu_name'          => _x( 'CP Project Templates', 'admin menu', 'leco-cp' ),
		'name_admin_bar'     => _x( 'CP Project Template', 'add new on admin bar', 'leco-cp' ),
		'add_new'            => _x( 'Add New', 'project template', 'leco-cp' ),
		'add_new_item'       => __( 'Add New CP Project Template', 'leco-cp' ),
		'new_item'           => __( 'New CP Project Template', 'leco-cp' ),
		'edit_item'          => __( 'Edit CP Project Template', 'leco-cp' ),
		'view_item'          => __( 'View CP Project Template', 'leco-cp' ),
		'all_items'          => __( 'All CP Project Templates', 'leco-cp' ),
		'search_items'       => __( 'Search CP Project Templates', 'leco-cp' ),
		'not_found'          => __( 'No CP project templates found.', 'leco-cp' ),
		'not_found_in_trash' => __( 'No CP project templates found in Trash.', 'leco-cp' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=leco_client',
		'show_in_nav_menus'   => false,
		'query_var'           => true,
		'rewrite'             => array(
			'slug'       => 'project-template',
			'with_front' => false,
		),
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title', 'author' ),
	);

	register_post_type( 'leco_template', apply_filters( 'leco_cpt_args', $args, 'leco_template' ) );

	$labels = array(
		'name'               => _x( 'Content Pages', 'post type general name', 'leco-cp' ),
		'singular_name'      => _x( 'Content Page', 'post type singular name', 'leco-cp' ),
		'menu_name'          => _x( 'Content Pages', 'admin menu', 'leco-cp' ),
		'name_admin_bar'     => _x( 'Content Page', 'add new on admin bar', 'leco-cp' ),
		'add_new'            => _x( 'Add New', 'content page', 'leco-cp' ),
		'add_new_item'       => __( 'Add New Content Page', 'leco-cp' ),
		'new_item'           => __( 'New Content Page', 'leco-cp' ),
		'edit_item'          => __( 'Edit Content Page', 'leco-cp' ),
		'view_item'          => __( 'View Content Page', 'leco-cp' ),
		'all_items'          => __( 'All Content Pages', 'leco-cp' ),
		'search_items'       => __( 'Search Content Pages', 'leco-cp' ),
		'parent_item_colon'  => __( 'Parent Content Page:', 'leco-cp' ),
		'not_found'          => __( 'No content pages found.', 'leco-cp' ),
		'not_found_in_trash' => __( 'No content pages found in Trash.', 'leco-cp' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=leco_client',
		'show_in_nav_menus'   => false,
		'query_var'           => true,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title', 'editor', 'author', 'revisions' ),
	);

	register_post_type( 'leco_content_page', apply_filters( 'leco_cpt_args', $args, 'leco_content_page' ) );

	do_action( 'leco_cp_after_register_post_type' );
}
add_action( 'init', 'leco_cp_register_post_type' );

/**
 * Flush rewrite rules when needed
 */
function leco_cp_flush_rewrite_rules() {
	if ( '4.6.0' !== get_option( 'leco_cp_flush_rewrite_rules' ) ) {
		update_option( 'leco_cp_flush_rewrite_rules', '4.6.0' ); // Use version number so later on we can do this by comparing version.
		flush_rewrite_rules();
	}

	if ( ! get_option( 'leco_cp_set_closedpostboxes' ) ) {
		update_option( 'leco_cp_set_closedpostboxes', '4.2.0' ); // Use version number so later on we can do this by comparing version.

		// update closed metabox option.
		if ( is_user_logged_in() ) {
			$metaboxes = array(
				'leco_cp_custom_branding',
				'leco_cp_info',
				'leco_cp_part_0',
				'leco_cp_part_1',
				'leco_cp_part_2',
				'leco_cp_part_3',
				'leco_cp_part_4',
				'leco_cp_part_5',
			);
			update_user_meta( get_current_user_id(), 'closedpostboxes_leco_client', $metaboxes );
			update_user_meta( get_current_user_id(), 'closedpostboxes_leco_template', $metaboxes );
		}
	}
}
add_action( 'leco_cp_after_register_post_type', 'leco_cp_flush_rewrite_rules' );
