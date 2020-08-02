<?php if ( ! empty( $logo ) ) : ?>
	<img src="<?php echo $logo; ?>" alt="" class="project-logo<?php if ( 'yes' == $fixed_logo ) {
		echo ' fixed-width';
	} ?>">
<?php endif; ?>
<h1>
	<?php
	if ( is_singular( 'leco_client' ) ) {
		echo get_the_title();
	} elseif ( is_post_type_archive( 'leco_client' ) ) {
		echo apply_filters( 'leco_cp_client_portal_archive', __( 'Client Portal Archive', 'leco-cp' ) );
	} else {
		the_title();
	}
	?>
</h1>