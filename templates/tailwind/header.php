<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
	<title><?php echo get_the_title(); ?></title>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
	<?php
	wp_head();

	if ( is_page( 'client-portal-login' ) ) {
		$fallback = 'yes';
    } else {
		$fallback = get_post_meta( get_the_ID(), 'leco_cp_fallback_values', true );
    }
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
        html {
            --primary-color: <?php echo ( empty( $pc ) ) ? 'transparent' : $pc; ?>;
            --primary-text-color: <?php echo ( empty( $ptc ) ) ? 'transparent' : $ptc; ?>;
            --secondary-color: <?php echo ( empty( $sc ) ) ? 'transparent' : $sc; ?>;
            --secondary-text-color: <?php echo ( empty( $stc ) ) ? 'transparent' : $stc; ?>;
            --tertiary-color: <?php echo ( empty( $tc ) ) ? 'transparent' : $tc; ?>;
            --tertiary-text-color: <?php echo ( empty( $ttc ) ) ? 'transparent' : $ttc; ?>;
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
		<?php echo leco_cp_get_option( 'css_tailwind' ); ?>
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
<header class="title-section">
    <nav class="leco-cp-container">
        <?php if ( leco_cp_can_display_project() ) { ?>
        <div class="title-content">
            <?php require_once 'template-parts/title-content.php'; ?>
        </div>
	        <?php require_once 'template-parts/topbar.php'; ?>
        <?php } else { ?>
	        <?php require_once 'template-parts/title-content.php'; ?>
        <?php } ?>
    </nav>
</header>
<?php
if ( get_query_var( 'leco_content_page' ) ) {
	require_once 'template-parts/nav.php';
} ?>
