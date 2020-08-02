<?php require_once 'header.php'; ?>
<?php
	global $leco_cp_is_fallback;
	// If post password required and it doesn't match the cookie.
	if ( post_password_required( get_the_ID() ) ) :
		echo '<div class="content-section"><div class="container"><div class="entry password-protect">' . get_the_password_form( get_the_ID() ) . '</div></div></div>';
	else :
		if ( current_user_can( 'edit_posts' ) || ( ( is_user_logged_in() && leco_cp_user_has_access( get_the_ID() ) ) ) || leco_cp_check_post_password( get_the_ID() ) || leco_cp_is_public_portal( get_the_ID() ) ) :
			$welcome = get_post_meta( get_the_ID(), 'leco_cp_welcome', true );
			$gi = get_post_meta( get_the_ID(), 'leco_cp_general_information', true );
			if ( empty( $gi ) && $leco_cp_is_fallback ) {
				$gi = leco_cp_get_option( 'general_information' );
			}
			?>
			<div class="content-section">
				<?php if ( ! empty( $welcome ) || ! empty( $gi ) ) : ?>
				<div class="container">
					<?php if ( ! empty( $gi ) ) :
						$gi = apply_filters( "leco_cp_media_filter", $gi ); ?>
						<div class="entry">
							<?php echo do_shortcode( wpautop( $gi ) ); ?>
						</div>
					<?php else: ?>
						<div class="entry">
							<?php echo wpautop( $welcome ); ?>
						</div>
						<h2>How to contact me</h2>
						<div class="entry">
							<?php echo wpautop( get_post_meta( get_the_ID(), 'leco_cp_contact', true ) ); ?>
						</div>
						<h2>Availability</h2>
						<div class="entry">
							<?php echo wpautop( get_post_meta( get_the_ID(), 'leco_cp_availability', true ) ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<?php
			$phase_x_text = get_post_meta( get_the_ID(), 'leco_cp_phase_x_text', true );
			if ( empty( $phase_x_text ) && $leco_cp_is_fallback ) {
				$phase_x_text = leco_cp_get_option( 'phase_x_text', __( 'Phase', 'leco-cp' ) );
			}

			$show_project_status = get_post_meta( get_the_ID(), 'leco_cp_show_project_status', true );
			if ( empty( $show_project_status ) && $leco_cp_is_fallback ) {
				$show_project_status = leco_cp_get_option( 'show_project_status', 'show' );
			}
			if ( 'hide' != $show_project_status ) {
				?>
				<div class="project-status vertical-padding completed-no">
					<div class="inner-width">
						<div class="column current"><span
									class="value"><?php echo get_post_meta( get_the_ID(), 'leco_cp_current_phase', true ); ?></span><span
									class="label"><?php echo apply_filters( 'leco_cp_current_phase_text', sprintf( esc_html__( 'Current %s', 'leco-cp' ), $phase_x_text ) ); ?></span>
						</div>
						<div class="column next"><span
									class="value"><?php echo get_post_meta( get_the_ID(), 'leco_cp_next_phase', true ); ?></span><span
									class="label"><?php echo apply_filters( 'leco_cp_next_phase_text', sprintf( esc_html__( 'Next %s', 'leco-cp' ), $phase_x_text ) ); ?></span>
						</div>
						<div class="column completion-date">
							<span class="value"><?php $cd = get_post_meta( get_the_ID(), 'leco_cp_completion_date', true ); if ( ! empty( $cd ) ) { echo date_i18n( get_option( 'date_format' ), strtotime( $cd ) ); } else { echo '&nbsp;'; }; ?></span>
							<span class="label"><?php echo apply_filters( 'leco_cp_estimated_completion_date_text', __( 'Estimated Completion Date', 'leco-cp' ) ); ?></span>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="projects">
				<div class="container">
					<?php
					$number_of_parts = get_post_meta( get_the_ID(), 'leco_cp_number_of_parts', true );
					if ( ! $number_of_parts ) {
						$number_of_parts = 3;
					} // backward compatibility
					for ( $i = 0; $i < $number_of_parts; $i ++ ) {
						$number  = $i + 1;
						$modules = get_post_meta( get_the_ID(), 'leco_cp_part_' . $i . '_module', true );
						if ( $modules ) {
							?>
							<h2 class="phase-title leco-cp-phase-<?php echo $number; ?>">
								<span class="leco-cp-phase-text"><?php echo apply_filters( 'leco_cp_phase_x_text', $phase_x_text . sprintf( ' %s%s%s', '<i class="leco-cp-phase-number">', $number, '</i>' ), $number ); ?></span>
								<?php echo get_post_meta( get_the_ID(), 'leco_cp_part_' . $i . '_title', true ); ?>
							</h2>
							<div class="row">
								<?php
								$new_tab = ( 'current' != leco_cp_get_option( 'new_tab', 'new_tab' ) ) ? true : false;
								$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );

								foreach ( $modules as $key => $module ) {
									$key += 1;

									$type = ( isset( $module['type'] ) ) ? $module['type'] : 'url';
									$url = ( isset( $module['url'] ) && ! empty( $module['url'] ) ) ? $module['url'] : '';
									$hover_status = $check = '';

									if ( 'inactive' === $module['status'] ) {
										$url          = 'javascript: void(0);';
										$hover_status = ' unclickable';
										$files        = array();
									} elseif ( 'content_page' === $type ) {
										$content_page = get_post_field( 'post_name', $module['content_page'] );
										$url = trailingslashit( get_permalink( get_the_ID() ) . 'module/' . $content_page );
									} elseif ( ( 'files' === $type && ! empty( $module['files'] ) ) || ( 'private-files' === $type && ! empty( $module['private_files'] ) ) ) {
										$files = ( 'files' === $type ) ? array_values( $module['files'] ) : array_values( $module['private_files'] );
										if ( count( $files ) === 1 ) {
											$url = $files[0];

											$check = wp_check_filetype( $url );
										} else {
											$url = "#module_{$number}_$key";
										}
									} elseif ( empty( $url ) || '#' == $url ) {
										$url = 'javascript: void(0);';
										if ( 'inactive' != $module['status'] ) {
											$hover_status = ' unclickable';
										}
									} else {
										$files = array();
									}

									$hover_status .= ( leco_cp_can_mark_as_complete( $module['status'], get_the_ID() ) ) ? ' mark-as-complete' : '';

									$link_target = '';
									if ( 'new_tab' == $module['new_tab'] || ( 'current' != $module['new_tab'] && $new_tab ) ) {
										$link_target = ' target="_blank"';
									}
									?>
									<div class="col col-3">
										<a id="m_<?php echo $number . '_' . $key; ?>" class="project-item <?php echo $module['status'] . $hover_status; ?>"
										   href="<?php echo $url; ?>"<?php echo $link_target; ?><?php if ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 ) {
											echo ' data-leco-cp-lity title="' . __( 'Click and show the file list', 'leco-cp' ) . '"';
										} ?>
											<?php if ( is_array( $check ) && in_array( $check['ext'], $image_exts ) ) { echo ' data-leco-cp-lity'; } ?>
										>
											<?php if ( 'completed' == $module['status'] ) { ?>
												<span class="checkmark">
													<span class="tooltip"><?php _e( 'Completed', 'leco-cp' ) ?><span class="arrow"></span></span>
												</span>
											<?php } ?>
											<?php if ( leco_cp_can_mark_as_complete( $module['status'], get_the_ID() ) ) { ?>
											<span class="checkmark" data-leco-cp-mark-as-complete data-key="<?php echo $number . '_' . $key; ?>">
													<span class="tooltip"><?php echo apply_filters( 'leco_cp_mark_as_complete_text', esc_html__( 'Mark As Complete?', 'leco-cp' ) ); ?><span class="arrow"></span></span>
												</span>
											<?php } ?>
											<span class="ico-area">
												<span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
											</span>
											<span class="title"><?php echo $module['title']; ?></span>
											<span><?php echo $module['description']; ?></span>
										</a>
									</div>
									<?php if ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 ) { ?>
										<div class="lity-hide" id="module_<?php echo $number . '_' . $key; ?>">
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
													<li><a href="<?php echo $file;  ?>"
														   download><?php echo get_the_title( $ID ); ?>
															<span class="icon-download">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve">
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
									if ( leco_cp_can_mark_as_complete( $module['status'], get_the_ID() ) ) { ?>
									<div class="lity-hide" id="module_mark_as_complete_<?php echo $number . '_' . $key; ?>">
										<div class="project-item <?php echo $module['status']; ?>">
												<span class="ico-area">
												<span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
											</span>
											<span class="title"><?php echo $module['title']; ?></span>
											<span class="desc"><?php echo $module['description']; ?></span>
										</div>

										<p class="module_mark_as_complete"><a href="javascript:void(0);"
											  class="btn"><span><?php echo apply_filters( 'leco_cp_mark_as_complete_long_text', esc_html__( 'Mark this module as complete?', 'leco-cp' ) ); ?></span></a></p>
									</div>
								<?php } } ?>
							</div>
						<?php }
					} ?>
				</div>
			</div>
			<?php if ( $cta = get_post_meta( get_the_ID(), 'leco_cp_cta', true ) ) : ?>
				<?php if ( ! isset( $cta[0]['hidden'] ) && ! empty( $cta[0]['url'] ) ) :
					$link_target = '';
					if ( 'new_tab' == $cta[0]['new_tab'] ) {
						$link_target = ' target="_blank"';
					}
					?>
					<div class="cta-block">
						<div class="container">
							<?php echo wpautop( $cta[0]['description'] ); ?>
							<a href="<?php echo $cta[0]['url']; ?>" class="btn"<?php echo $link_target; ?>><?php echo $cta[0]['button_text']; ?> <span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span></a>
						</div>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<div class="cta-block">
					<div class="container">
						<p>Want to see all your files? </p>
						<a href="<?php echo get_post_meta( get_the_ID(), 'leco_cp_dropbox', true ); ?>"
						   class="btn"><?php _e( 'Go to your Dropbox folder', 'leco-cp' ); ?> <span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span></a>
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