<footer id="footer">
	<ul class="contacts">
		<?php
		global $leco_cp_is_fallback;
		$phone = get_post_meta( get_the_ID(), 'phone', true );
		if ( empty( $phone ) && $leco_cp_is_fallback ) {
			$phone = leco_cp_get_option( 'phone' );
		}
		if ( $phone ) : ?>
			<li><a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></li>
		<?php endif;
		$email = get_post_meta( get_the_ID(), 'email', true );
		if ( empty( $email ) && $leco_cp_is_fallback ) {
			$email = leco_cp_get_option( 'email' );
		}
		if ( $email ) : ?>
			<li><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></li>
		<?php endif;
		$show = leco_cp_get_option( 'powered_by', 'show' );
		if ( 'show' == $show ) : ?>
            <li>Powered by <a href="http://www.client-portal.io">Client Portal</a></li>
		<?php endif; ?>
	</ul>
</footer>
<?php wp_footer(); ?>
</body>
</html>