<div class="lity-hide" id="module_<?php echo $number . '_' . $key; ?>">
    <div class="module <?php echo $module['status']; ?>">
												<span class="ico-area">
												<span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
											</span>
        <span class="title"><?php echo $module['title']; ?></span>
        <span class="desc"><?php echo ( isset( $module['description'] ) ) ? $module['description'] : ''; ?></span>
    </div>

    <?php if ( 'client-uploads' === $type ) { ?>
    <div class="client-uploads-container">
        <div class="choose-files" href="javascript:void(0);">
            <span><?php esc_html_e( 'Click or drop files here to add more', 'leco-cp' ); ?></span>
            <p class="max-upload-size"><?php
		        $max_upload_size = wp_max_upload_size();
		        if ( ! $max_upload_size ) {
			        $max_upload_size = 0;
		        }
		        printf( __( 'Maximum upload file size: %s.' ), esc_html( size_format( $max_upload_size ) ) );
		        ?></p>
        </div>
    </div>
    <?php } ?>

    <ul class="files">
		<?php
		$files = array();
		if ( 'files' === $type ) {
			$files = $module['files'];
		} elseif ( 'private-files' === $type ) {
			foreach ( (array) $module['private_files'] as $ID => $file ) {
				$filearray    = explode( '/', $file );
				$files[ $ID ] = str_replace( end( $filearray ), '', $file );
			}
		} elseif ( 'client-uploads' === $type ) {
			if ( ! empty( $module['client_uploads'] ) ) {
				$files = $module['client_uploads'];
			}
		}

		foreach ( $files as $ID => $file ) {
		    $file_url = ( isset( $file['name'] ) ) ? trailingslashit( get_permalink() . 'file/' . $ID ) : $file;
		    $file_name = ( isset( $file['name'] ) ) ? $file['name'] : get_the_title( $ID );
		    ?>
            <li>
                <a href="<?php echo $file_url; ?>" download>
                    <span class="progress"></span>
                    <?php echo $file_name; ?>
                    <span class="icon-download">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px"
                             y="0px" viewBox="0 0 48 48" style="enable-background:new 0 0 48 48;" xml:space="preserve">
                            <title>Download</title>
                            <g>
                                <path class="st0" d="M43,31v6c0,3.3-3,6-6.6,6H11.6C8,43,5,40.3,5,37v-6"></path>
                                <line class="st0" x1="24" y1="4" x2="24" y2="31"></line>
                                <polyline class="st0" points="14,21 24,31 34,21  "></polyline>
                            </g>
                        </svg>
                     </span>
                </a>
	            <?php if ( leco_cp_can_upload( $module, get_the_ID() ) ) { ?>
                    <a href="javascript:void(0);" class="file-delete" data-file-id="<?php echo $ID; ?>" title="Delete"><span class="icon-file-delete">
                        <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><defs></defs><g id="Trash"><path class="cls-1" d="M40,7L38.38,39.4a8,8,0,0,1-8,7.6H17.61a8,8,0,0,1-8-7.6L8,7"/><line class="cls-1" x1="4" y1="7" x2="44" y2="7"/><line class="cls-1" x1="18.5" y1="15" x2="19.5" y2="37"/><line class="cls-1" x1="29.5" y1="15" x2="28.5" y2="37"/><path class="cls-1" d="M16,7l1.09-3.26A4,4,0,0,1,20.88,1h6.23a4,4,0,0,1,3.79,2.74L32,7"/></g></svg>
                    </span></a>
	            <?php } ?>
            </li>
		<?php } ?>
    </ul>

</div>
