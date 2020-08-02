<?php
$key += 1;

$type = ( isset( $module['type'] ) ) ? $module['type'] : 'url';
$url = ( isset( $module['url'] ) && ! empty( $module['url'] ) ) ? $module['url'] : '';
$hover_status = $check = '';
$files        = array();

if ( 'inactive' === $module['status'] ) {
	$url          = 'javascript: void(0);';
	$hover_status = ' unclickable';
} elseif ( 'content_page' === $type ) {
	$content_page = get_post_field( 'post_name', $module['content_page'] );
	$url = trailingslashit( get_permalink( get_the_ID() ) . 'module/' . $content_page );
} elseif ( ( 'files' === $type && ! empty( $module['files'] ) ) || ( 'private-files' === $type && ! empty( $module['private_files'] ) ) || 'client-uploads' === $type ) {
	if ( 'client-uploads' === $type ) {
	    $files = array();
    } else {
		$files = ( 'files' === $type ) ? array_values( $module['files'] ) : array_values( $module['private_files'] );
    }
	if ( count( $files ) === 1 ) {
		$url = $files[0];

		$check = wp_check_filetype( $url );

		if ( 'private-files' === $type ) {
			$filearray = explode( '/', $url );
		    $url = str_replace( end( $filearray ), '', $url );
        }
	} else {
		$url = "#module_{$number}_$key";
	}
} elseif ( empty( $url ) || '#' == $url ) {
	$url = 'javascript: void(0);';
	if ( 'inactive' != $module['status'] ) {
		$hover_status = ' unclickable';
	}
}

$hover_status .= ( leco_cp_can_mark_as_complete( $module['status'], get_the_ID() ) ) ? ' mark-as-complete' : '';

$link_target = '';
if ( 'new_tab' == $module['new_tab'] || ( 'current' != $module['new_tab'] && $new_tab ) ) {
	$link_target = ' target="_blank"';
}
?>
	<div class="module-wrap <?php echo $type; ?> m_<?php echo $number . '_' . $key; ?>" <?php if ( 'content_page' === $type ) echo "id='content_page_{$module['content_page']}'"; ?>>
		<a id="m_<?php echo $number . '_' . $key; ?>" class="module <?php echo $module['status'] . $hover_status; ?>"
		   href="<?php echo $url; ?>"<?php echo $link_target; ?><?php if ( ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 ) || 'client-uploads' === $type ) {
			echo ' data-leco-cp-lity title="' . __( 'Click and show the file list', 'leco-cp' ) . '"';
		} ?>
			<?php if ( is_array( $check ) && in_array( $check['ext'], $image_exts ) ) { echo ' data-leco-cp-lity'; } ?>
		>
			<?php if ( 'completed' == $module['status'] ) { ?>
				<span class="action checkmark">
													<span class="tooltip"><?php _e( 'Completed', 'leco-cp' ) ?><span class="arrow"></span></span>
												</span>
			<?php } ?>
			<?php if ( leco_cp_can_mark_as_complete( $module['status'], get_the_ID() ) ) { ?>
				<span class="action checkmark" data-leco-cp-mark-as-complete data-key="<?php echo $number . '_' . $key; ?>">
													<span class="tooltip"><?php echo apply_filters( 'leco_cp_mark_as_complete_text', esc_html__( 'Mark As Complete?', 'leco-cp' ) ); ?><span class="arrow"></span></span>
												</span>
			<?php } ?>
			<span class="ico-area">
												<span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
											</span>
			<span class="title"><?php echo $module['title']; ?></span>
			<span><?php echo ( isset( $module['description'] ) ) ? $module['description'] : ''; ?></span>

			<?php if ( leco_cp_can_upload( $module, get_the_ID() ) ) { ?>
                <p class="upload">
	                <?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'templates/tailwind/assets/paperclip.svg' ); ?>

                    <span class="upload"><?php esc_html_e( 'add files', 'leco-cp' ); ?></span>
                </p>
			<?php } ?>
		</a>
	</div>
<?php if ( ( ( 'files' === $type || 'private-files' === $type ) && count( $files ) > 1 ) || 'client-uploads' === $type ) { ?>
	<?php require 'filelist.php'; ?>
<?php }
if ( leco_cp_can_mark_as_complete( $module['status'], get_the_ID() ) ) { ?>
	<div class="lity-hide" id="module_mark_as_complete_<?php echo $number . '_' . $key; ?>">
		<div class="module <?php echo $module['status']; ?>">
            <span class="ico-area">
                <span class="iconset"><?php echo file_get_contents( LECO_CLIENT_PORTAL_DIR . 'assets/icon/caviar/' . strtolower( $module['icon'] ) . '.svg' ); ?></span>
            </span>
			<span class="title"><?php echo $module['title']; ?></span>
			<span class="desc"><?php echo ( isset( $module['description'] ) ) ? $module['description'] : ''; ?></span>
		</div>

		<p class="module_mark_as_complete"><a href="javascript:void(0);"
		                                      class="btn"><span><?php echo apply_filters( 'leco_cp_mark_as_complete_long_text', esc_html__( 'Mark this module as complete?', 'leco-cp' ) ); ?></span></a></p>
	</div>
<?php } ?>