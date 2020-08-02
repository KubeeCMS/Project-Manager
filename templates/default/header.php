<!DOCTYPE html>
<!--[if lte IE 8]>
<html class="oldie" lang="en"> <![endif]-->
<!--[if IE 9]>
<html class="ie9" lang="en"> <![endif]-->
<!--[if gt IE 9]><!-->
<html lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta name="viewport"
		  content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="format-detection" content="telephone=no">
	<title><?php echo get_the_title(); ?></title>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
	<?php
	wp_head();

	$fallback = get_post_meta( get_the_ID(), 'leco_cp_fallback_values', true );
	if ( empty( $fallback ) ) {
		$fallback = leco_cp_get_option( 'fallback_values', 'yes' );
	}
	global $leco_cp_is_fallback;
	$leco_cp_is_fallback = ( 'yes' === $fallback ) ? true : false;

	// Get colors from project settings.
	$post_pc  = get_post_meta( get_the_ID(), 'leco_cp_primary_color', true );
	$post_ptc = get_post_meta( get_the_ID(), 'leco_cp_primary_text_color', true );
	$post_sc  = get_post_meta( get_the_ID(), 'leco_cp_secondary_color', true );
	$post_stc = get_post_meta( get_the_ID(), 'leco_cp_secondary_text_color', true );
	$post_tc  = get_post_meta( get_the_ID(), 'leco_cp_tertiary_color', true );
	$post_ttc = get_post_meta( get_the_ID(), 'leco_cp_tertiary_text_color', true );

	// Get colors from CP settings.
	$pc  = ( empty( $post_pc ) && $leco_cp_is_fallback ) ? leco_cp_get_option( 'primary_color', '#52cdf5' ) : $post_pc;
	$ptc = ( empty( $post_ptc ) && $leco_cp_is_fallback ) ? leco_cp_get_option( 'primary_text_color', '#ffffff' ) : $post_ptc;
	$sc  = ( empty( $post_sc ) && $leco_cp_is_fallback ) ? leco_cp_get_option( 'secondary_color', '#ff5f5f' ) : $post_sc;
	$stc = ( empty( $post_stc ) && $leco_cp_is_fallback ) ? leco_cp_get_option( 'secondary_text_color', '#ffffff' ) : $post_stc;
	$tc  = ( empty( $post_tc ) && $leco_cp_is_fallback ) ? leco_cp_get_option( 'tertiary_color', '#3c5063' ) : $post_tc;
	$ttc = ( empty( $post_ttc ) && $leco_cp_is_fallback ) ? leco_cp_get_option( 'tertiary_text_color', '#ffffff' ) : $post_ttc;
	?>
	<style>
		.title-section {
			background-color: <?php echo ( empty( $pc ) ) ? 'transparent' : $pc; ?>;
			color: <?php echo ( empty( $ptc ) ) ? 'transparent' : $ptc; ?>;
		}

		a.project-item:hover {
			box-shadow: 0 0 5px <?php echo ( empty( $pc ) ) ? 'transparent' : $pc; ?>;
		}

		.cta-block, .project-status .column .label, .leco-cp-content dt {
			background: <?php echo ( empty( $pc ) ) ? 'transparent' : $pc; ?>;
			color: <?php echo ( empty( $ptc ) ) ? 'transparent' : $ptc; ?>;
		}

		.btn, button, input[type="button"], input[type="reset"], input[type="submit"], .project-item.completed .checkmark, .project-status .column.current .label {
			background-color: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
			color: <?php echo ( empty( $stc ) ) ? 'transparent' : $stc; ?>;
		}

		.project-status .column.completion-date .label, #footer {
			background-color: <?php echo ( empty( $tc ) ) ? 'transparent' : $tc; ?>;
			color: <?php echo ( empty( $ttc ) ) ? 'transparent' : $ttc; ?>;
		}

		#footer a {
			color: <?php echo ( empty( $ttc ) ) ? 'transparent' : $ttc; ?>;
		}

		#footer a:hover {
			opacity: 0.8;
		}

		.project-link a {
			background: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
			color: <?php echo ( empty( $stc ) ) ? 'transparent' : $stc; ?>;
		}

		.btn:hover, input[type="button"]:hover, input[type="submit"]:hover {
			background: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
			color: <?php echo ( empty( $stc ) ) ? 'transparent' : $stc; ?>;
			filter: brightness(95%);
		}

		a {
			border-bottom-color: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
		}

		a:hover, .lity li a:hover, .leco-cp-sidebar a:hover, .leco-cp-sidebar a.current:hover {
			color: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
		}
		.leco-cp-sidebar a:hover .iconset .st0 {
			stroke: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
		}
        .lity .icon-download .st0 {
            stroke: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
        }

		.leco-cp-sidebar a.current {
			color: <?php echo ( empty( $pc ) ) ? 'transparent' : $pc; ?>
		}
		<?php
		$header_bg = get_post_meta( get_the_ID(), 'leco_cp_header_background_image', true );
		if ( empty( $header_bg ) && $leco_cp_is_fallback ) {
			$header_bg = leco_cp_get_option( 'header_background_image' );
		}
		if ( ! empty( $header_bg ) ) { ?>
		.title-section {
			background-image: url("<?php echo $header_bg; ?>");
			background-position: center;
			background-size: cover;
			background-repeat: no-repeat;
		}
		<?php } ?>
		<?php echo leco_cp_get_option( 'css' ); ?>
	</style>
	<?php echo leco_cp_get_option( 'head' ); ?>
</head>
<body <?php body_class(); ?>>
<?php
$logo = get_post_meta( get_the_ID(), 'leco_cp_logo', true );
$fixed_logo = get_post_meta( get_the_ID(), 'leco_cp_logo_fixed_width', true );
if ( empty( $logo ) && $leco_cp_is_fallback ) {
	$logo       = leco_cp_get_option( 'logo' );
	$fixed_logo = leco_cp_get_option( 'logo_fixed_width', 'yes' );
}
?>
<div class="title-section add2">
	<div class="container">
		<?php if ( ! empty( $logo ) ) : ?>
			<img src="<?php echo $logo; ?>" alt="Logo" class="logo<?php if ( 'yes' == $fixed_logo ) echo ' fixed-width'; ?>">
		<?php endif; ?>
		<p>
			<?php
			if ( is_singular( 'leco_client' ) ) {
				echo get_the_title();
			} elseif ( is_post_type_archive( 'leco_client' ) ) {
				echo apply_filters( 'leco_cp_client_portal_archive', __( 'Client Portal Archive', 'leco-cp' ) );
			} else {
				the_title();
			}
			?>
		</p>
		<?php if ( is_user_logged_in() ) :
			$text = __( 'Log Out', 'leco-cp' );
			if ( ! current_user_can( 'edit_posts' ) && ! leco_cp_user_role_allowed() && ! leco_cp_is_public_portal( get_the_ID() ) ) {
				$text = __( "You don't have permission to view this project. Log Out?", 'leco-cp' );
			}
			?>
		<div class="leco-cp-logout"><a href="<?php echo wp_logout_url( apply_filters( 'leco_cp_logout_url', leco_cp_login_url() ) ); ?>"
                                       class="leco-cp-logout-link"><?php
                echo $text; ?></a></div>
		<?php endif; ?>
	</div>
</div>