<?php require_once 'header.php'; ?>
<main>
<?php
global $leco_cp_is_fallback;
// If post password required and it doesn't match the cookie.
if ( post_password_required( get_the_ID() ) ) :
	echo '<div class="content-section"><div class="leco-cp-container"><div class="password-protect">' . get_the_password_form( get_the_ID() ) . '</div></div></div>';
else :
	if ( leco_cp_can_display_project() ) :
		$welcome = get_post_meta( get_the_ID(), 'leco_cp_welcome', true );
		$gi = get_post_meta( get_the_ID(), 'leco_cp_general_information', true );
		if ( empty( $gi ) && $leco_cp_is_fallback ) {
			$gi = leco_cp_get_option( 'general_information' );
		}
		?>
        <div class="content-section">
			<?php if ( ! empty( $welcome ) || ! empty( $gi ) ) : ?>
                <div class="leco-cp-container">
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
            <div class="project-status completed-no">
                <div class="leco-cp-container">
                    <div class="status current"><span
                                class="value"><?php echo get_post_meta( get_the_ID(), 'leco_cp_current_phase', true ); ?></span><span
                                class="label"><?php echo apply_filters( 'leco_cp_current_phase_text', sprintf( esc_html__( 'Current %s', 'leco-cp' ), $phase_x_text ) ); ?></span>
                    </div>
                    <div class="status next"><span
                                class="value"><?php echo get_post_meta( get_the_ID(), 'leco_cp_next_phase', true ); ?></span><span
                                class="label"><?php echo apply_filters( 'leco_cp_next_phase_text', sprintf( esc_html__( 'Next %s', 'leco-cp' ), $phase_x_text ) ); ?></span>
                    </div>
                    <div class="status completion-date">
                        <span class="value"><?php $cd = get_post_meta( get_the_ID(), 'leco_cp_completion_date', true ); if ( ! empty( $cd ) ) { echo date_i18n( get_option( 'date_format' ), strtotime( $cd ) ); } else { echo '&nbsp;'; }; ?></span>
                        <span class="label"><?php echo apply_filters( 'leco_cp_estimated_completion_date_text', __( 'Estimated Completion Date', 'leco-cp' ) ); ?></span>
                    </div>
                </div>
            </div>
		<?php } ?>
        <div class="main-content phases">
            <div class="leco-cp-container">
                <div class="docsearch-wrapper">
                    <div class="docsearch-container">
                        <input id="docsearch" type="text" placeholder="<?php echo apply_filters( 'leco_cp_portal_search_placeholder', esc_html__( 'Search the portal', 'leco-cp' ) ); ?>" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="false" aria-label="search input">
                        <div class="search-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.9 14.32a8 8 0 1 1 1.41-1.41l5.35 5.33-1.42 1.42-5.33-5.34zM8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"></path></svg>
                        </div>
                    </div>
                    <div class="search-feedback hidden">
                        <p class="results-default hidden"><?php esc_html_e( 'Type more than three characters to get results.', 'leco-cp' ); ?></p>
                        <p class="has-results hidden"><?php esc_html_e( 'See search results below.', 'leco-cp' ); ?></p>
                        <p class="no-result"><?php esc_html_e( 'No search results. All modules are shown below.', 'leco-cp' ); ?></p>
                    </div>
                </div>
            </div>
			<?php
			$number_of_parts = get_post_meta( get_the_ID(), 'leco_cp_number_of_parts', true );
			if ( ! $number_of_parts ) {
				$number_of_parts = 3;
			} // backward compatibility
			for ( $i = 0; $i < $number_of_parts; $i ++ ) {
				$number       = $i + 1;
				$modules      = get_post_meta( get_the_ID(), 'leco_cp_part_' . $i . '_module', true );
				$module_count = count( $modules );
				if ( $modules ) { ?>
                    <h2 class="phase-title leco-cp-phase-<?php echo $number; ?>">
                        <span class="leco-cp-phase-text"><?php echo apply_filters( 'leco_cp_phase_x_text', $phase_x_text . sprintf( ' %s%s%s', '<i class="leco-cp-phase-number">', $number, '</i>' ), $number ); ?></span>
						<?php echo get_post_meta( get_the_ID(), 'leco_cp_part_' . $i . '_title', true ); ?>
                    </h2>
                    <div class="leco-cp-container">
					<?php
					$new_tab    = ( 'current' != leco_cp_get_option( 'new_tab', 'new_tab' ) ) ? true : false;
					$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
					foreach ( $modules as $key => $module ) { ?>
						<?php require "template-parts/module.php"; ?>
					<?php } // end module. ?>
                    </div>
				<?php }
			} ?>
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