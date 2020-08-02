<?php require_once 'header.php'; ?>
    <div class="content-section">
        <div class="container">
            <div class="entry">
                <?php while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
                <?php endwhile; ?>
            </div>
            <div class="entry">
                <?php leco_cp_login_form(); ?>
            </div>
        </div>
    </div>
<?php require_once 'footer.php'; ?>