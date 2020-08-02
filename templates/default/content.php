<?php require_once 'header.php'; ?>
<?php
// If post password required and it doesn't match the cookie.
if ( post_password_required( get_the_ID() ) ) :
	echo '<div class="content-section"><div class="container"><div class="entry password-protect">' . get_the_password_form( get_the_ID() ) . '</div></div></div>';
else :
	if ( current_user_can( 'edit_posts' ) || ( ( is_user_logged_in() && leco_cp_user_has_access( get_the_ID() ) ) ) || leco_cp_check_post_password( get_the_ID() ) || leco_cp_is_public_portal( get_the_ID() ) ) :
		$content_pages = get_posts(
			array(
				'post_type' => 'leco_content_page',
				'name'      => get_query_var( 'leco_content_page' ),
			)
		);
		?>
        <div class="projects content-page">
            <div class="container">
                <div class="row">
                    <div class="col leco-cp-sidebar">
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
										} elseif ( ( 'files' === $type && ! empty( $module['files'] ) ) || ( 'private-files' === $type && ! empty( $module['private_files'] ) ) ) {
											$files = ( 'files' === $type ) ? array_values( $module['files'] ) : array_values( $module['private_files'] );
											if ( count( $files ) === 1 ) {
												$url = $files[0];

												$check = wp_check_filetype( $url );
											} else {
												$url = "#module_$key";
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
                                               href="<?php echo $url; ?>"<?php echo $link_target; ?><?php if ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 ) {
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
										<?php if ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 ) { ?>
                                            <div class="lity-hide" id="module_<?php echo $key; ?>">
                                                <div class="project-item <?php echo $module['status']; ?>">
												<span class="ico-area">
												<span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
											</span>
                                                    <span class="title"><?php echo $module['title']; ?></span>
                                                    <span class="desc"><?php echo $module['description']; ?></span>
                                                </div>
                                                <ul class="files">
                                                    <?php
                                                    if ( 'files' === $type ) {
                                                        $files = $module['files'];
                                                    } else {
                                                        $files = array();
                                                        foreach ( $module['private_files'] as $ID => $file ) {
                                                            $filearray = explode( '/', $file );
                                                            $files[ $ID ] = str_replace( end( $filearray ), '', $file );
                                                        }
                                                    }
													foreach ( $files as $ID => $file ) { ?>
                                                        <li><a href="<?php echo $file; ?>"
                                                               download><?php echo get_the_title( $ID ); ?>
                                                                <span class="icon-download">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px"
     y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve">
    <title>Download</title>
    <g id="Save">
        <path class="st0" d="M43,31v6c0,3.3-3,6-6.6,6H11.6C8,43,5,40.3,5,37v-6"></path>
        <line class="st0" x1="24" y1="4" x2="24" y2="31"></line>
        <polyline class="st0" points="14,21 24,31 34,21  "></polyline>
    </g>
</svg>
                                                            </span>
                                                            </a></li>
													<?php } ?>
                                                </ul>
                                            </div>
										<?php }
										$content_page = '';
									} ?>
                                </ul>
								<?php
							}
						}
						?>
                    </div>
                    <div class="col leco-cp-content">
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
        </div>
		<?php if ( $cta = get_post_meta( get_the_ID(), 'leco_cp_cta', true ) ) : ?>
		<?php if ( ! isset( $cta[0]['hidden'] ) && ! empty( $cta[0]['url'] ) ) : ?>
            <div class="cta-block">
                <div class="container">
					<?php echo wpautop( $cta[0]['description'] ); ?>
                    <a href="<?php echo $cta[0]['url']; ?>" class="btn"><?php echo $cta[0]['button_text']; ?> <span
                                class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span></a>
                </div>
            </div>
		<?php endif; ?>
	<?php else: ?>
        <div class="cta-block">
            <div class="container">
                <p>Want to see all your files? </p>
                <a href="<?php echo get_post_meta( get_the_ID(), 'leco_cp_dropbox', true ); ?>"
                   class="btn"><?php _e( 'Go to your Dropbox folder', 'leco-cp' ); ?> <span
                            class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span></a>
            </div>
        </div>
	<?php endif; ?>
	<?php else : ?>
        <div class="content-section">
            <div class="container">
                <div class="entry">
					<?php if ( is_user_logged_in() && ! leco_cp_user_has_access( get_the_ID() ) ) { ?>
                        <p class="login-error"><?php echo apply_filters( 'leco_cp_login_error_message', __( 'You don\'t have permission to view this project. If this is your project, you\'ll need to request log in info from the owner.', 'leco-cp' ) ); ?></p>
					<?php } ?>
					<?php
					leco_cp_login_form();
					?>
                </div>
            </div>
        </div>
	<?php endif; ?>
<?php endif; ?>
<?php require_once 'footer.php'; ?>
