<?php
/**
 * Settings
 *
 * @package     ClientPortal\Settings
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class LECO_Client_Portal_Settings {
	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $key = 'leco_cp_options';
	/**
	 * License page key, and page slug
	 * @var string
	 */
	private $license_page_key = 'leco_cp_license';
	/**
	 * Options page metabox id
	 * @var string
	 */
	private $metabox_id = 'leco_cp_option_metabox';
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';
	/**
	 * License Page title
	 * @var string
	 */
	protected $license_page_title = '';
	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';
	/**
	 * Holds an instance of the object
	 *
	 * @var LECO_Client_Portal_Settings
	 **/
	private static $instance = null;

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	private function __construct() {
		// Set our title
		$this->title              = __( 'Client Portal Settings', 'leco-cp' );
		$this->license_page_title = __( 'Client Portal License', 'leco-cp' );
	}

	/**
	 * Returns the running object
	 *
	 * @return LECO_Client_Portal_Settings
	 **/
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'activate_license' ) );
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		add_action( 'admin_notices', array( $this, 'license_page_notices' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_menu', array( $this, 'add_license_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
	}

	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
		register_setting( $this->license_page_key, 'leco_cp_license_key', array( $this, 'sanitize_license_key' ) );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page( 'edit.php?post_type=leco_client', $this->title, $this->title, 'manage_options', $this->key, array(
			$this,
			'admin_page_display'
		) );
		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_license_page() {
		$this->options_page = add_submenu_page( 'edit.php?post_type=leco_client', $this->license_page_title, $this->license_page_title, 'manage_options', $this->license_page_key, array(
			$this,
			'license_page_display'
		) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
        <div class="wrap cmb2-options-page <?php echo $this->key; ?>">
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <div class="notice notice-warning">
            	<p>We treat the settings on this page as the default values when you create a new portal. They are also the fallback values when fields are left empty.
</p>
				<p>If you want to leave empty fields blank in your portals, make sure you select “No” under “Use default settings for empty fields“.
</p>
            </div>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, array( 'save_button' => esc_html__( 'Save Changes', 'leco-cp' ) ) ); ?>
        </div>
		<?php
	}

	/**
	 * License page markup.
	 *
	 * @since 3.0.0
	 */
	public function license_page_display() {
		$license = get_option( 'leco_cp_license_key' );
		$status  = get_option( 'leco_cp_license_status' );
		?>
		<div class="wrap">
			<h2><?php _e( 'Client Portal License', 'leco-cp' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( $this->license_page_key ); ?>
				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key' ); ?>
						</th>
						<td>
							<input id="leco_cp_license_key" name="leco_cp_license_key" type="password"
								   class="regular-text"
								   value="<?php esc_attr_e( $license ); ?>"/>
							<label class="description"
								   for="leco_cp_license_key"><?php _e( 'Enter your license key', 'leco-cp' ); ?></label>
						</td>
					</tr>
					<?php if ( ! empty( $license ) ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'License Status', 'leco-cp' ); ?>
							</th>
							<td>
								<?php if ( $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e( 'active', 'leco-cp' ); ?></span>
									<?php wp_nonce_field( 'leco_cp_nonce', 'leco_cp_nonce' ); ?>
									<input type="submit" class="button-secondary" name="leco_cp_license_deactivate"
										   value="<?php _e( 'Deactivate License', 'leco-cp' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<?php submit_button( esc_html__( 'Save Changes', 'leco-cp' ) ); ?>

			</form>
		</div>
		<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	function add_options_page_metabox() {
		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );
		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		// Set our CMB2 fields
		$cmb->add_field( array(
			'name'    => esc_html__( 'Use default settings for empty fields', 'leco-cp' ),
			'desc'    => esc_html__( 'If you select “Yes”, we’ll load the values from Client Portal Settings into the empty fields in your portal. If you select “No”, we will leave all empty fields blank. You can also change this in the Custom Branding section in each portal.', 'leco-cp' ),
			'id'      => 'fallback_values',
			'type'    => 'select',
			'options' => array(
				'yes' => esc_html__( 'Yes', 'leco-cp' ),
				'no'  => esc_html__( 'No', 'leco-cp' ),
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'Your Name', 'leco-cp' ),
			'id'   => 'name',
			'type' => 'text',
		) );

		$cmb->add_field( array(
			'name' => esc_html__( 'Your Logo', 'leco-cp' ),
			'desc' => esc_html__( 'Upload an image or enter a URL.', 'leco-cp' ),
			'id'   => 'logo',
			'type' => 'file',
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Fixed logo width', 'leco-cp' ),
			'desc'    => esc_html__( 'If select "Yes", we\'ll set the logo image width to 270px.', 'leco-cp' ),
			'id'      => 'logo_fixed_width',
			'type'    => 'select',
			'options' => array(
				'yes' => esc_html__( 'Yes', 'leco-cp' ),
				'no'  => esc_html__( 'No', 'leco-cp' ),
			),
		) );

		$cmb->add_field( array(
			'name' => esc_html__( 'Header Image', 'leco-cp' ),
			'desc' => esc_html__( 'Upload an image or enter a URL.', 'leco-cp' ),
			'id'   => 'header_background_image',
			'type' => 'file',
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Primary Background Color', 'leco-cp' ),
			'id'      => 'primary_color',
			'type'    => 'colorpicker',
			'default' => '#52cdf5',
			'classes' => array( 'color', 'leco-cp-background' ),
			'options' => array(
				'alpha' => true,
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Primary Text Color', 'leco-cp' ),
			'id'      => 'primary_text_color',
			'type'    => 'colorpicker',
			'default' => '#ffffff',
			'classes' => array( 'color' ),
			'options' => array(
				'alpha' => true,
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Secondary Background Color', 'leco-cp' ),
			'id'      => 'secondary_color',
			'type'    => 'colorpicker',
			'default' => '#ff5f5f',
			'classes' => array( 'color', 'leco-cp-background' ),
			'options' => array(
				'alpha' => true,
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Secondary Text Color', 'leco-cp' ),
			'id'      => 'secondary_text_color',
			'type'    => 'colorpicker',
			'default' => '#ffffff',
			'classes' => array( 'color' ),
			'options' => array(
				'alpha' => true,
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Tertiary Background Color', 'leco-cp' ),
			'id'      => 'tertiary_color',
			'type'    => 'colorpicker',
			'default' => '#3c5063',
			'classes' => array( 'color', 'leco-cp-background' ),
			'options' => array(
				'alpha' => true,
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Tertiary Text Color', 'leco-cp' ),
			'id'      => 'tertiary_text_color',
			'type'    => 'colorpicker',
			'default' => '#ffffff',
			'classes' => array( 'color' ),
			'options' => array(
				'alpha' => true,
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'Custom Scripts in <code>head</code>', 'leco-cp' ),
			'desc' => __( 'You may need some custom scripts for your portals. For example, the embed code from Typekit or Google Fonts. You can add them here.', 'leco-cp' ),
			'id'   => 'head',
			'type' => 'textarea_code',
		) );

		$cmb->add_field( array(
			'name' => __( 'Theme', 'leco-cp' ),
			'desc' => __( 'You can switch to the Legacy theme if you encounter issues with the latest Default theme.', 'leco-cp' ) . ' <strong>' . __( 'Note that Legacy theme doesn\'t support features released after version 4.7.', 'leco-cp' ) . '</strong>',
			'id'   => 'template',
			'type' => 'select',
			'options' => array(
                'default' => __( 'Legacy', 'leco-cp' ),
                'tailwind' => __( 'Default', 'leco-cp' ),
			),
			'default' => 'tailwind',
		) );

		$cmb->add_field( array(
			'name' => __( 'Custom CSS - Legacy theme', 'leco-cp' ),
			'desc' => __( 'Add your own custom CSS to style the portal. <br /> Each project has a "postid-{ID}" class in the <code>body</code> element. <br/> So you can do things like <code>.postid-212 h2 {color: red}</code> to specify styles for a certain project.', 'leco-cp' ),
			'id'   => 'css',
			'type' => 'textarea_code',
			'classes' => array( 'custom-css' ),
			'sanitization_cb' => false,
		) );

		$cmb->add_field( array(
			'name' => __( 'Custom CSS - Default theme', 'leco-cp' ),
			'desc' => __( 'Add your own custom CSS to style the portal. <br /> Each project has a "postid-{ID}" class in the <code>body</code> element. <br/> So you can do things like <code>.postid-212 h2 {color: red}</code> to specify styles for a certain project.', 'leco-cp' ),
			'id'   => 'css_tailwind',
			'type' => 'textarea_code',
			'classes' => array( 'custom-css' ),
			'sanitization_cb' => false,
		) );

		$cmb->add_field( array(
			'name' => __( 'Your Phone', 'leco-cp' ),
			'id'   => 'phone',
			'type' => 'text_medium',
		) );

		$cmb->add_field( array(
			'name' => __( 'Your Email', 'leco-cp' ),
			'id'   => 'email',
			'type' => 'text_email',
		) );

		$general_information = wpautop( 'Hello and welcome! Here you can find everything you need during our project from all your files to how to contact me. You\'ll have full access to this area throughout the whole project and for 6 months after completion. However all of your files will still live on in <a href="#">your project folder.</a>
<h2>How to contact me</h2>
If you have any small questions or comments throughout the project, you can <a class="email" href="mailto:#">email me.</a> For anything larger it\'s best to wait until our next catch up call so we can go through it properly. Our call is scheduled for every <strong>[date-time]</strong>
<h2>Availability</h2>
My working week is <strong>[your-working-hours]</strong>

Occasionally I take time off for holidays so if this is due to happen during our project I will let you know in good time. Other than that you can expect intermittent availability around all major holidays including Christmas, New Year, Easter and (ok, not a major holiday) my birthday - [your-birthday-if-you-like]' );

		$cmb->add_field( array(
			'name'    => esc_html__( 'General Information', 'leco-cp' ),
			'id'      => 'general_information',
			'desc'    => esc_html__( 'Write some general information about a project, including: welcome messages, contact information and your availability.', 'leco-cp' ),
			'type'    => 'wysiwyg',
			'options' => array( 'textarea_rows' => 12, ),
			'default' => $general_information
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Show project status?', 'leco-cp' ),
			'desc'    => esc_html__( 'This will hide the "Project Status" section (Current/Next Phase & Estimated Completion Date). The default is "Yes".', 'leco-cp' ),
			'id'      => 'show_project_status',
			'type'    => 'select',
			'options' => array(
				'show' => esc_html__( 'Yes', 'leco-cp' ),
				'hide' => esc_html__( 'No', 'leco-cp' ),
			),
		) );

		$cmb->add_field( array(
			'name'    => __( 'Phase X Text', 'leco-cp' ),
			'desc'	  => sprintf( __( 'Fancy to change the "Phase 1, 2, 3" terminology to something else? You can replace it here. Say if you enter "Section", you\'ll get them to be "Section 1, 2, 3" in all your portals, unless you give different values in portal settings. You can also remove the numbers (1, 2, 3...) from it with CSS, %shere\'s how%s.', 'leco-cp' ), '<a href="https://client-portal.io/client/client-portal-support/module/how-do-i-edit-the-phase-1-2-3-etc-text/" target="_blank">', '</a>' ),
			'id'      => 'phase_x_text',
			'type'    => 'text',
			'default' => __( 'Phase', 'leco-cp' ),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Show the mark as complete ticks?', 'leco-cp' ),
			'desc'    => esc_html__( 'This will enable the client side "Mark as Complete" feature for each module. The default is "Yes".', 'leco-cp' ),
			'id'      => 'show_mark_as_complete',
			'type'    => 'select',
			'options' => array(
				'show' => esc_html__( 'Yes', 'leco-cp' ),
				'hide' => esc_html__( 'No', 'leco-cp' ),
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Open Link in New Tab?', 'leco-cp' ),
			'desc'    => esc_html__( 'Set if the module URL should be opened in new tab. The default is "Yes".', 'leco-cp' ),
			'id'      => 'new_tab',
			'type'    => 'select',
			'options' => array(
				'new_tab' => esc_html__( 'Yes', 'leco-cp' ),
				'current' => esc_html__( 'No', 'leco-cp' ),
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Collapse all modules?', 'leco-cp' ),
			'desc'    => esc_html__( 'All modules are collapsed by default. Select "No" if you\'d like them to be expanded.', 'leco-cp' ),
			'id'      => 'module_status',
			'type'    => 'select',
			'options' => array(
				'closed' => esc_html__( 'Yes', 'leco-cp' ),
				'opened' => esc_html__( 'No', 'leco-cp' ),
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Display the "Powered By" link?', 'leco-cp' ),
			'desc'    => esc_html__( 'There will be a "Powered by Client Portal" link in the footer of your projects. The default is "Yes".', 'leco-cp' ),
			'id'      => 'powered_by',
			'type'    => 'select',
			'options' => array(
				'show' => esc_html__( 'Yes', 'leco-cp' ),
				'hide' => esc_html__( 'No', 'leco-cp' ),
			),
		) );
	}

	/**
	 * Register settings notices for display
	 *
	 * @since  0.1.0
	 *
	 * @param  int $object_id Option key
	 * @param  array $updated Array of updated fields
	 *
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}
		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'leco-cp' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}

	/**
     * Catching errors from the activation method above and displaying it to the customer
     *
     * @since 3.0.0
     */
	public function license_page_notices() {
        if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {
            switch( $_GET['sl_activation'] ) {
                case 'false':
                    $message = urldecode( $_GET['message'] );
                    ?>
                    <div class="error">
                        <p><?php echo $message; ?></p>
                    </div>
                    <?php
                    break;
                case 'true':
                default:
                    // Developers can put a custom success message here for when activation is successful if they way.
                    break;
            }
        }
    }

	/**
	 * Delete license status if the old key is not matched with the new one
	 *
	 * @param $new string New license key
	 *
	 * @return string New license key
	 */
	public function sanitize_license_key( $new ) {
		$old = get_option( 'leco_cp_license_key' );
		if ( $old && $old != $new ) {
			delete_option( 'leco_cp_license_status' ); // new license has been entered, so must reactivate
		}

		return $new;
	}

	/**
	 * Activate license
	 */
	public function activate_license() {
	    if ( ! isset( $_POST['option_page'] ) || 'leco_cp_license' != $_POST['option_page'] ) {
	        return;
	    }

	    if ( empty( $_POST['leco_cp_license_key'] ) ) {
	        return;
	    }

	    if ( 'valid' == get_option( 'leco_cp_license_status' ) || isset( $_POST['leco_cp_license_deactivate'] ) ) {
	        return;
	    }

        // retrieve the license from the database
        $license = sanitize_text_field( $_POST['leco_cp_license_key'] );
        $license_data = new stdClass();

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_name'  => urlencode( LECO_CLIENT_PORTAL_ITEM_NAME ), // the name of our product in EDD
            'url'        => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post( LECO_CLIENT_PORTAL_STORE_URL, array(
            'timeout'   => 15,
            'sslverify' => true,
            'body'      => $api_params
        ) );

        // make sure the response came back okay
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            if ( is_wp_error( $response ) ) {
                $message = $response->get_error_message();
            } else {
                $message = __( 'An error occurred, please try again.', 'leco-cp' );
            }
        } else {
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            if ( false === $license_data->success ) {
                switch ( $license_data->error ) {
                    case 'expired' :
                        $message = sprintf(
                            __( 'Your license key expired on %s.', 'leco-cp' ),
                            date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                        );
                        break;
                    case 'revoked' :
                        $message = __( 'Your license key has been disabled.', 'leco-cp' );
                        break;
                    case 'missing' :
                        $message = __( 'Invalid license.', 'leco-cp' );
                        break;
                    case 'invalid' :
                    case 'site_inactive' :
                        $message = __( 'Your license is not active for this URL.', 'leco-cp' );
                        break;
                    case 'item_name_mismatch' :
                        $message = sprintf( __( 'This appears to be an invalid license key for %s.', 'leco-cp' ), LECO_CLIENT_PORTAL_ITEM_NAME );
                        break;
                    case 'no_activations_left':
                        $message = __( 'Your license key has reached its activation limit.', 'leco-cp' );
                        break;
                    default :
                        $message = __( 'An error occurred, please try again.', 'leco-cp' );
                        break;
                }
            }
        }

        // Check if anything passed on a message constituting a failure
        if ( ! empty( $message ) ) {
            $base_url = admin_url( 'edit.php?post_type=leco_client&page=' . $this->license_page_key );
            $redirect = add_query_arg( array(
                'sl_activation' => 'false',
                'message'       => urlencode( $message )
            ), $base_url );

            wp_redirect( $redirect );
            exit();
        }

        // $license_data->license will be either "valid" or "invalid"
        update_option( 'leco_cp_license_status', $license_data->license );
	}

	function deactivate_license() {
        // listen for our activate button to be clicked
        if( isset( $_POST['leco_cp_license_deactivate'] ) ) {
            // run a quick security check
            if( ! check_admin_referer( 'leco_cp_nonce', 'leco_cp_nonce' ) )
                {return;} // get out if we didn't click the Activate button
            // retrieve the license from the database
            $license = trim( get_option( 'leco_cp_license_key' ) );

            // data to send in our API request
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license'    => $license,
                'item_name'  => urlencode( LECO_CLIENT_PORTAL_ITEM_NAME ), // the name of our product in EDD
                'url'        => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post( LECO_CLIENT_PORTAL_STORE_URL, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                if ( is_wp_error( $response ) ) {
                    $message = $response->get_error_message();
                } else {
                    $message = __( 'An error occurred, please try again.' );
                }

                $base_url = admin_url( 'edit.php?post_type=leco_client&page=' . $this->license_page_key );
                $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

                wp_redirect( $redirect );
                exit();
            }

            // decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            // $license_data->license will be either "deactivated" or "failed"
            if( $license_data->license == 'deactivated' ) {
                delete_option( 'leco_cp_license_status' );
                delete_option( 'leco_cp_license_key' );
            }

            wp_redirect( admin_url( 'edit.php?post_type=leco_client&page=' . $this->license_page_key ) );
            exit();
        }
    }

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 *
	 * @param  string $field Field to retrieve
	 *
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		throw new Exception( 'Invalid property: ' . $field );
	}
}

/**
 * Helper function to get/return the LECO_Client_Portal_Settings object
 * @since  0.1.0
 * @return LECO_Client_Portal_Settings object
 */
function leco_cp_settings() {
	return LECO_Client_Portal_Settings::get_instance();
}

// Get it started
leco_cp_settings();
