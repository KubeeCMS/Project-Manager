<?php require_once 'header.php'; ?>
    <main>
		<div class="content-section">
			<div class="leco-cp-container">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
				<?php leco_cp_login_form(); ?>
			</div>
		</div>
	</main>
<?php require_once 'footer.php'; ?>