<?php require_once 'header.php'; ?>
<main>
<?php
// If post password required and it doesn't match the cookie.
if ( post_password_required( get_the_ID() ) ) :
	echo '<div class="content-section"><div class="leco-cp-container"><div class="password-protect">' . get_the_password_form( get_the_ID() ) . '</div></div></div>';
else :
	if ( leco_cp_can_display_project() ) :
		$content_pages = get_posts(
			array(
				'post_type' => 'leco_content_page',
				'name'      => get_query_var( 'leco_content_page' ),
			)
		);
		?>
        <div class="main-content">
            <div class="leco-cp-container">
                <div class="leco-cp-sidebar">
                    <h2 class="leco-cp-back-to-project"><a
                                href="<?php echo trailingslashit( home_url( 'client/' . get_query_var( 'leco_client' ) ) ); ?>"
                                title="<?php echo apply_filters( 'leco_cp_back_to_project_text', esc_html__( 'Back to Project', 'leco-cp' ) ); ?>"><span
                                    class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevon-left.svg' ); ?></span><?php echo apply_filters( 'leco_cp_back_to_project_text', esc_html__( 'Back to Project', 'leco-cp' ) ); ?>
                        </a></h2>
                    <?php
                    // Get phases and modules.
                    $number_of_parts = get_post_meta( get_the_ID(), 'leco_cp_number_of_parts', true );
                    if ( ! $number_of_parts ) {
                        $number_of_parts = 3;
                    } // backward compatibility
                    $can_view = 0;
                    for ( $i = 0; $i < $number_of_parts; $i ++ ) {
                        $number  = $i + 1;
                        $modules = get_post_meta( get_the_ID(), 'leco_cp_part_' . $i . '_module', true );
                        if ( $modules ) {
                            if ( false !== strpos( maybe_serialize( $modules ), '"content_page";s:' . strlen( $content_pages[0]->ID ) . ':"' . $content_pages[0]->ID . '";' ) ) {
                                $collapse = 0;
                                $can_view = 1;
                            } else {
                                $collapse = 1;
                            }
                            ?>
                            <h2>
                                <a href="javascript:void(0)"
                                   title="<?php $section = get_post_meta( get_the_ID(), 'leco_cp_part_' . $i . '_title', true );
                                   echo $section; ?>">
                                    <?php echo $section; ?>
                                    <?php
                                    if ( ! $collapse ) { ?>
                                        <span class="iconset down"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-down.svg' ); ?></span>
                                        <span class="iconset right hidden"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span>
                                    <?php } else { ?>
                                        <span class="iconset down hidden"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-down.svg' ); ?></span>
                                        <span class="iconset right"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span>
                                    <?php }
                                    ?>
                                </a>
                            </h2>
                            <ul class="<?php echo ( $collapse ) ? 'collapse' : ''; ?>">
                                <?php
                                $new_tab    = ( 'current' != leco_cp_get_option( 'new_tab', 'new_tab' ) ) ? true : false;
                                $image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );

                                foreach ( $modules as $key => $module ) {
                                    $key += 1;

                                    $type         = ( isset( $module['type'] ) ) ? $module['type'] : 'url';
                                    $url          = ( isset( $module['url'] ) && ! empty( $module['url'] ) ) ? $module['url'] : '';
                                    $hover_status = $check = '';

                                    if ( 'inactive' === $module['status'] ) {
                                        $url          = 'javascript: void(0);';
                                        $hover_status = ' unclickable';
                                        $files        = array();
                                    } elseif ( 'content_page' === $type ) {
                                        $content_page = get_post_field( 'post_name', $module['content_page'] );
                                        $url          = trailingslashit( get_permalink( get_the_ID() ) . 'module/' . $content_page );
                                    } elseif ( ( 'files' === $type && ! empty( $module['files'] ) ) || ( 'private-files' === $type && ! empty( $module['private_files'] ) ) || 'client-uploads' === $type ) {
	                                    if ( 'client-uploads' === $type ) {
		                                    $files = array();
	                                    } else {
		                                    $files = ( 'files' === $type ) ? array_values( $module['files'] ) : array_values( $module['private_files'] );
                                        }

                                        if ( count( $files ) === 1 ) {
                                            $url = $files[0];

                                            $check = wp_check_filetype( $url );

	                                        if ( 'private-files' === $type ) {
		                                        $filearray = explode( '/', $url );
		                                        $url = str_replace( end( $filearray ), '', $url );
	                                        }
                                        } else {
                                            $url = "#module_{$number}_{$key}";
                                        }
                                    } elseif ( empty( $url ) || '#' == $url ) {
                                        $url = 'javascript: void(0);';
                                        if ( 'inactive' != $module['status'] ) {
                                            $hover_status = ' unclickable';
                                        }
                                    } else {
                                        $files = array();
                                    }

                                    $link_target = '';
                                    if ( 'new_tab' == $module['new_tab'] || ( 'current' != $module['new_tab'] && $new_tab ) ) {
                                        $link_target = ' target="_blank"';
                                    }
                                    ?>
                                    <li>

                                        <a class="<?php if ( 'content_page' === $type && $content_pages[0]->post_name === $content_page ) {
                                            echo 'current';
                                        } else {
                                            echo $module['status'] . $hover_status;
                                        } ?>"
                                           href="<?php echo $url; ?>"<?php echo $link_target; ?><?php if ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 || 'client-uploads' === $type ) {
                                            echo ' data-leco-cp-lity title="' . __( 'Click and show the file list', 'leco-cp' ) . '"';
                                        } ?>
                                            <?php if ( is_array( $check ) && in_array( $check['ext'], $image_exts ) ) {
                                                echo ' data-leco-cp-lity';
                                            } ?>
                                        >
                                            <span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
                                            <?php echo $module['title']; ?>
                                        </a>
                                    </li>
                                    <?php if ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 || 'client-uploads' === $type ) { ?>
		                                <?php require 'template-parts/filelist.php'; ?>
                                    <?php }
                                    $content_page = '';
                                } ?>
                            </ul>
                            <?php
                        }
                    }
                    ?>
                </div>
                <div class="leco-cp-content editor">
                    <?php
                    // checking if this content page is attached under the project, ref: https://trello.com/c/ul2doV5k.
                    if ( $can_view ) {
                        echo apply_filters( 'the_content', $content_pages[0]->post_content );
                    } else {
                        echo esc_html__( 'You don\'t have permissions to view this content.', 'leco-cp' );
                    }
                    ?>
                </div>
            </div>
        </div>
	<?php else : ?>
        <div class="content-section">
            <div class="leco-cp-container">
	            <?php if ( is_user_logged_in() && ! leco_cp_user_has_access( get_the_ID() ) ) { ?>
                    <p class="login-error"><?php echo apply_filters( 'leco_cp_login_error_message', __( 'You don\'t have permission to view this project. If this is your project, you\'ll need to request log in info from the owner.', 'leco-cp' ) ); ?></p>
	            <?php } ?>
	            <?php
	            leco_cp_login_form();
	            ?>
            </div>
        </div>
	<?php endif; ?>
<?php endif; ?>
</main>
<?php require_once 'footer.php'; ?>
