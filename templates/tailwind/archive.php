<?php require_once 'header.php'; ?>
    <main>
        <div class="main-content projects">
	        <?php while ( have_posts() ) : the_post();
		        $status = get_post_meta( get_the_ID(), 'leco_cp_completion_date', true );
		        $status = ( time() > strtotime( $status ) ) ? __( 'Completed', 'leco-cp' ) : __( 'In Progress', 'leco-cp' );
		        ?>
                <div class="project">
                    <div class="project-name"><strong><?php the_title(); ?></strong></div>
                    <div class="project-status">
				        <?php echo $status; ?>
                    </div>
                    <div class="project-link">
                        <a href="<?php the_permalink(); ?>" class="btn"><?php echo apply_filters( 'leco_cp_view_portal_text', esc_html__( 'View Portal', 'leco-cp' ) ); ?></a>
                    </div>
                </div>
	        <?php endwhile; ?>
        </div>
    </main>
<?php require_once 'footer.php'; ?>