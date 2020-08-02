<nav class="sidebar">
	<h1>
		<a href="<?php the_permalink(); ?>">
            <span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/chevon-left.svg' ); ?></span>
			<?php the_title(); ?>
		</a>
	</h1>
	<button type="button" id="sidebar-open">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"></path></svg>
	</button>
	<button type="button" id="sidebar-close" class="hidden">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"></path></svg>
	</button>
</nav>
