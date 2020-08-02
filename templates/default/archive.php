<?php require_once 'header.php'; ?>
    <div class="projects">
        <div class="container">
			<?php while ( have_posts() ) : the_post();
				$status = get_post_meta( get_the_ID(), 'leco_cp_completion_date', true );
				$status = ( time() > strtotime( $status ) ) ? __( 'Completed', 'leco-cp' ) : __( 'In Progress', 'leco-cp' );
				?>
                <div class="row project-item">
                    <div class="col col-25">
                        <div class="project-name"><strong><?php the_title(); ?></strong></div>
                    </div>
                    <div class="col col-50">
                        <div class="project-status">
							<?php echo $status; ?>
                        </div>
                    </div>
                    <div class="col col-25">
                        <div class="project-link">
                            <a href="<?php the_permalink(); ?>" class="btn"><?php _e( 'View Portal', 'leco-cp' ); ?></a>
                        </div>
                    </div>
                </div>
			<?php endwhile; ?>
        </div>
    </div>
<?php require_once 'footer.php'; ?>