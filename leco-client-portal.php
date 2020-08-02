<?php # -*- coding: utf-8 -*-
/**
 * Main plugin file.
 * Project and Client Management ...
 *
 * @package      Project-Manager
 * @author       KubeeCMS
 * @copyright    Copyright (c) 2012-2020, KubeeCMS - KUBEE
 * @license      GPL-2.0-or-later
 * @link         https://github.com/KubeeCMS/Project-Manager/
 * @link         https://github.com/KubeeCMS/
 *
 * @wordpress-plugin
 * Plugin Name:  Project Manager
 * Plugin URI:   https://github.com/KubeeCMS/Project-Manager/
 * Description:  Project and Client Management ...
 * Version:      4.8.8
 * Author:       KubeeCMS - KUBEE
 * Author URI:   https://github.com/KubeeCMS/
 * License:      GPL-2.0-or-later
 * License URI:  https://opensource.org/licenses/GPL-2.0
 * Text Domain:  leco-cp
 * Domain Path:  /languages/
 * Network:      true
 * Requires WP:  5.5
 * Requires PHP: 7.3
 *
 * Copyright (c) 2012-2020 KubeeCMS - KUBEE
 *
 *     This file is part of KCMS Project Manager.
 *
 *     KCMS Project Manager is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 2 of the License, or (at your option)
 *     any later version.
 *
 *     Multisite Toolbar Additions is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */









/**
 * Plugin Name:     Project Manager
 * Plugin URI:      https://client-portal.io/
 * Description:     A super simple, lightweight WordPress plugin to keep your client deliverables in one place.
 * Version:         4.8.8
 * Author:          Laura Elizabeth
 * Author URI:      http://lauraelizabeth.co/
 * License:         GPL-3.0+
 * Text Domain:     leco-cp
 * Domain Path:     /languages

------------------------------------------------------------------------
Copyright 2016-2018 Laurium Design Ltd.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses.
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LECO_Client_Portal' ) ) {

	/**
	 * Main LECO_Client_Portal class
	 *
	 * @since       1.0.0
	 */
	class LECO_Client_Portal {

		/**
		 * @var         LECO_Client_Portal $instance The one true LECO_Client_Portal
		 * @since       1.0.0
		 */
		private static $instance;

		/**
		 * Client Portal HTML Session Object.
		 *
		 * This holds everything stored in the session.
		 *
		 * @var object|LECO_CP_Session
		 *
		 * @since 4.6
		 */
		public $session;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true LECO_Client_Portal
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new LECO_Client_Portal();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
				self::$instance->session = new LECO_CP_Session();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version.
			define( 'LECO_CLIENT_PORTAL_VER', '4.8.8' );

			// Plugin path.
			define( 'LECO_CLIENT_PORTAL_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL.
			define( 'LECO_CLIENT_PORTAL_URL', plugin_dir_url( __FILE__ ) );

			// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
			define( 'LECO_CLIENT_PORTAL_STORE_URL', 'https://client-portal.io' );

			// the name of our product.
			define( 'LECO_CLIENT_PORTAL_ITEM_NAME', 'Client Portal' );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/vendor/autoload.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/class-leco-cp-session.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/error-tracking.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/cpt.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/custom-field-types.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/cmb.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/settings.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/actions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/ajax-functions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/download-functions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/content-page/functions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/content-page/actions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/client-uploads.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/scripts.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/helpers.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'includes/EDD_SL_Plugin_Updater.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'templates/includes/functions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'templates/includes/login-actions.php';
			require_once LECO_CLIENT_PORTAL_DIR . 'templates/includes/login-functions.php';

			if ( is_admin() ) {
				require_once LECO_CLIENT_PORTAL_DIR . 'includes/admin/import-export.php';
				require_once LECO_CLIENT_PORTAL_DIR . 'includes/admin/upload-functions.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) ); // Disable admin bar in CP template.
			add_action( 'admin_init', array( $this, 'plugin_updater' ), 0 );
			add_action( 'admin_init', array( $this, 'install_roles' ) );

			add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );

			add_action( 'admin_init', array( $this, 'remove_actions' ) );

			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			add_action( 'init', array( $this, 'endpoint' ), 11 );
			add_action( 'template_redirect', array( $this, 'load_template_functions' ) );
			add_action( 'parse_request', array( $this, 'download_private_file' ), 0 );

			// Fix compatibility issue with Permalink Manager plugin.
			add_filter( 'request', array( $this, 'fix_permalink_manager' ) );
			// Skip the force redirect from the Wishlist member plugin.
			add_filter( 'wishlistmember_login_redirect_override', array( $this, 'wishlistmember_login_redirect_override' ) );
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = LECO_CLIENT_PORTAL_DIR . '/languages/';
			$lang_dir = apply_filters( 'leco_cp_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'leco-cp' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'leco-cp', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/leco-cp/' . $mofile;

			// Look in wp-content/languages/leco-client-portal/
			$mofile_global1 = WP_LANG_DIR . '/leco-client-portal/' . $mofile;

			// Look in wp-content/languages/plugins/leco-client-portal/
			$mofile_global2 = WP_LANG_DIR . '/plugins/leco-client-portal/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/leco-cp/ folder
				load_textdomain( 'leco-cp', $mofile_global );
			} elseif ( file_exists( $mofile_global1 ) ) {
				// Look in wp-content/languages/leco-client-portal/ folder
				load_textdomain( 'leco-cp', $mofile_global1 );
			} elseif ( file_exists( $mofile_global2 ) ) {
				// Look in wp-content/languages/plugins/leco-client-portal/ folder
				load_textdomain( 'leco-cp', $mofile_global2 );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/leco-cp/languages/ folder
				load_textdomain( 'leco-cp', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'leco-cp', false, $lang_dir );
			}
		}


		/**
		 * Add plugin action links
		 *
		 * @access      public
		 * @since       1.0.0
		 *
		 * @param       array $links The existing plugin links array
		 *
		 * @return      array The modified links array
		 */
		function plugin_action_links( $links ) {
			$links[] = '<a href="' . esc_url( get_admin_url( null, 'edit.php?post_type=leco_client&page=leco_cp_options' ) ) . '">' . __( 'Settings', 'leco-cp' ) . '</a>';

			return $links;
		}

		/**
		 * Create login page
		 *
		 * @access public
		 * @since 2.0.0
		 */
		function create_login_page() {
			// Create login page
			$login_page_exists = get_page_by_path( 'client-portal-login' );
			if ( is_null( $login_page_exists ) ) {
				wp_insert_post(
					array(
						'post_title'     => __( 'Client Portal Login', 'leco-cp' ),
						'post_content'   => '',
						'post_status'    => 'publish',
						'post_author'    => 1, // @todo maybe change to current user id
						'post_type'      => 'page',
						'comment_status' => 'closed',
						'post_name'      => 'client-portal-login',
					)
				);
			}
		}

		function show_admin_bar( $bool ) {
			global $post;

			if ( empty( $post ) ) {
				global $wp_query;
				if ( $wp_query->is_singular ) {
					$post = get_page_by_path( $wp_query->query['name'], OBJECT, 'leco_template' );
				}
			}

			if ( ! empty( $post ) && ( in_array( $post->post_type, array( 'leco_client', 'leco_template' ) ) || 'client-portal-login' === $post->post_name ) ) {
				return apply_filters( 'leco_cp_show_admin_bar', false );
			}

			return $bool;
		}

		function plugin_updater() {
			// retrieve our license key from the DB
			$license_key = trim( get_option( 'leco_cp_license_key' ) );

			// setup the updater
			$leco_cp_updater = new LECO_Client_Portal_EDD_SL_Plugin_Updater( LECO_CLIENT_PORTAL_STORE_URL, __FILE__, array(
					'version'   => LECO_CLIENT_PORTAL_VER,                // current version number
					'license'   => $license_key,        // license key (used get_option above to retrieve from DB)
					'item_name' => LECO_CLIENT_PORTAL_ITEM_NAME,    // name of this plugin
					'author'    => 'Laura Elizabeth',  // author of this plugin
				)
			);
		}


		/**
		 * Install user roles on sub-sites of a network
		 *
		 * Roles do not get created when CP is network activation so we need to create them during admin_init
		 *
		 * @since 3.1.9 Added for creating roles on multisite network.
		 * @since 4.3.8 Updated the role name.
		 *
		 * @return void
		 */
		public function install_roles() {
			global $wp_roles;

			if ( ! is_object( $wp_roles ) ) {
				return;
			}

			if ( empty( $wp_roles->roles ) || ! array_key_exists( 'leco_client', $wp_roles->roles ) ) {
				add_role( 'leco_client', esc_html__( 'CP Client', 'leco-cp' ), array(
					'read'    => true,
					'level_0' => true,
				) );
			} elseif ( array_key_exists( 'leco_client', $wp_roles->roles ) && 'Client' === $wp_roles->roles['leco_client']['name'] ) {
				remove_role( 'leco_client' );
				add_role( 'leco_client', esc_html__( 'CP Client', 'leco-cp' ), array(
					'read'    => true,
					'level_0' => true,
				) );
			}

		}

		/**
		 * Add endpoint for content pages.
		 *
		 * @since 4.6   Add new endpoint for private files.
		 * @since 4.3.0
		 */
		public function endpoint() {
			add_rewrite_tag( '%leco_content_page%', '([^/]+)' );
			add_rewrite_rule( 'client/?([^/]*)/module/?([^/]*)', 'index.php?leco_client=$matches[1]&leco_content_page=$matches[2]', 'top' );
			add_rewrite_rule( 'project-template/?([^/]*)/module/?([^/]*)', 'index.php?leco_template=$matches[1]&leco_content_page=$matches[2]', 'top' );

			add_rewrite_tag( '%leco_private_file%', '([^/]+)' );
			add_rewrite_rule( 'client/?([^/]*)/file/?([^/]*)', 'index.php?leco_client=$matches[1]&leco_private_file=$matches[2]', 'top' );
			add_rewrite_rule( 'project-template/?([^/]*)/file/?([^/]*)', 'index.php?leco_template=$matches[1]&leco_private_file=$matches[2]', 'top' );
		}

		/**
		 * Fix compatibility issue with Permalink Manager plugin.
		 *
		 * @param array $query_vars The array of requested query variables.
		 *
		 * @return mixed
		 */
		public function fix_permalink_manager( $query_vars ) {
			if ( class_exists( 'Permalink_Manager_Class' ) ) {
				if ( isset( $query_vars['leco_content_page'] ) && isset( $query_vars['post_type'] ) && ( 'leco_client' === $query_vars['post_type'] || 'leco_template' === $query_vars['post_type'] ) ) {
					$query_vars['do_not_redirect'] = 1;
				}
			}

			return $query_vars;
		}

		/**
		 * Prevent any user who cannot 'edit_posts' (subscribers, customers etc) from accessing admin.
		 * Borrowed from WooCommerce.
		 */
		public function prevent_admin_access() {
			$prevent_access = false;

			if ( 'yes' === get_option( 'leco_cp_lock_down_admin', 'yes' ) && ! defined( 'DOING_AJAX' ) && basename( $_SERVER['SCRIPT_FILENAME'] ) !== 'admin-post.php' ) {
				if ( current_user_can( 'leco_client' ) && ! current_user_can( 'edit_posts' ) ) {
					$prevent_access = true;
				}
			}

			if ( apply_filters( 'leco_cp_prevent_admin_access', $prevent_access ) ) {
				wp_safe_redirect( leco_cp_login_url() );
				exit;
			}
		}

		/**
		 * Remove actions, mostly from other plugins to solve compatibility issues.
		 */
		public function remove_actions() {
			// Fix for Scripts n Styles.
			if ( class_exists( 'SnS_Admin_Meta_Box' ) ) {
				if ( isset( $_GET['page'] ) && 'leco_cp_options' === $_GET['page'] ) {
					remove_all_actions( 'current_screen' );
				}
			}
		}

		/**
		 * Trigger private file downloading
		 *
		 * @since 4.6
		 */
		public function download_private_file() {
			leco_cp_download_private_file();
		}

		/**
		 * Admin notices in Client Portal.
		 *
		 * @since 4.7 Change method name and added leco_cp_tailwind_admin_notices().
		 * @since 4.6
		 */
		public function admin_notices() {
			global $post_type, $typenow;

			$ptype = $post_type;
			if ( $ptype === null && $typenow !== null ) {
				$ptype = $typenow;
			}

			if ( ! isset( $ptype ) || ! in_array( $ptype, array( 'leco_client', 'leco_template', 'leco_content_page' ), true ) ) {
				return;
			}

			leco_cp_admin_notices_for_nginx();

			// Added in 4.7.
			if ( function_exists( 'leco_cp_tailwind_admin_notices' ) ) {
				leco_cp_tailwind_admin_notices();
			}

			// Added in 4.8.
			leco_cp_admin_notices_for_nginx_client_uploads();
		}

		/**
		 * Load template own functions.
		 *
		 * @since 4.7
		 */
		public function load_template_functions() {
			$leco_cp_template = apply_filters( 'leco_cp_get_template', leco_cp_get_option( 'template', 'tailwind' ) );
			$leco_cp_template_file = LECO_CLIENT_PORTAL_DIR . "templates/$leco_cp_template/functions.php";
			if ( file_exists( $leco_cp_template_file ) ) {
				require_once $leco_cp_template_file;
			}
		}

		/**
		 * Skip the force redirect from the Wishlist member plugin.
		 *
		 * @since 4.8.6
		 *
		 * @return bool
		 */
		public function wishlistmember_login_redirect_override() {
			$redirect_to = ( ! empty( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : '';

			if ( strstr( $redirect_to, 'client-portal-login' ) ) {
				return true;
			}

			return false;
		}
	}
} // End if class_exists check


/**
 * The main function responsible for returning the one true LECO_Client_Portal
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      object LECO_Client_Portal The one true LECO_Client_Portal
 */
function LECO_Client_Portal() {
	return LECO_Client_Portal::instance();
}

LECO_Client_Portal();

/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0.0
 * @return      void
 */
function leco_cp_activation() {
	LECO_Client_Portal()->create_login_page();
	leco_cp_register_post_type();
	LECO_Client_Portal()->endpoint();

	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'leco_cp_activation' );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
