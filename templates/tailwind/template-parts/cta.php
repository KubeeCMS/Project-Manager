<?php if ( $cta = get_post_meta( get_the_ID(), 'leco_cp_cta', true ) ) : ?>
	<?php if ( ! isset( $cta[0]['hidden'] ) && ! empty( $cta[0]['url'] ) ) :
		$link_target = '';
		if ( 'new_tab' == $cta[0]['new_tab'] ) {
			$link_target = ' target="_blank"';
		}
        ?>
		<div class="cta-section">
			<div class="leco-cp-container">
				<?php echo wpautop( $cta[0]['description'] ); ?>
				<a href="<?php echo $cta[0]['url']; ?>" class="btn"<?php echo $link_target; ?>><?php echo $cta[0]['button_text']; ?> <span
						class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span></a>
			</div>
		</div>
	<?php endif; ?>
<?php else: ?>
	<div class="cta-section">
		<div class="leco-cp-container">
			<p>Want to see all your files? </p>
			<a href="<?php echo get_post_meta( get_the_ID(), 'leco_cp_dropbox', true ); ?>"
			   class="btn"><?php _e( 'Go to your Dropbox folder', 'leco-cp' ); ?> <span
					class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevron-right.svg' ); ?></span></a>
		</div>
	</div>
<?php endif; ?>