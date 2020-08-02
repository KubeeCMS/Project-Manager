<div class="topbar">
    <ul>
        <li><a href="<?php echo site_url(); ?>"><?php esc_html_e( 'Home', 'leco-cp' ); ?></a></li>
        <?php if ( ! is_post_type_archive( 'leco_client' ) ) { ?>
        <li><a href="<?php echo get_permalink(); ?>" class="<?php echo ( is_singular( 'leco_client' ) && get_query_var( 'leco_content_page' ) == '' ) ? 'current' : ''; ?>"><?php esc_html_e( 'Dashboard', 'leco-cp' ); ?></a></li>
        <?php } ?>
	    <?php if ( is_user_logged_in() ) { ?>
		    <?php if ( leco_cp_user_role_allowed() ) {
			    $projects = leco_cp_get_projects_by_client( get_current_user_id() );
			    $count    = count( $projects );
			    if ( $count > 1 ) {
				    ?>
                    <li><a href="<?php echo site_url( 'client' ); ?>"
                           class="<?php echo ( is_post_type_archive( 'leco_client' ) ) ? 'current' : ''; ?>"><?php esc_html_e( 'All Projects', 'leco-cp' ); ?></a>
                    </li>
			    <?php }
		    } ?>
		<?php $text = __( 'Log Out', 'leco-cp' ); ?>
        <li class="leco-cp-logout"><a
                    href="<?php echo wp_logout_url( apply_filters( 'leco_cp_logout_url', leco_cp_login_url() ) ); ?>"
                    class="leco-cp-logout-link"><?php
				echo $text; ?></a></li>
        <?php } ?>
    </ul>
</div>