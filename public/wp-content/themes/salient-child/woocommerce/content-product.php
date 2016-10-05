<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}

$options = get_option('salient'); 
$product_style = (!empty($options['product_style'])) ? $options['product_style'] : 'classic';
$classes[] = $product_style;

?>
<li <?php post_class( $classes ); ?>>

	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>


		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
		
	 <h2 class="course-catalog-h2"> <?php the_title(); ?></h2>
     <?php
     the_field('product_blurb');
	  //the_content();
	  //the_excerpt(); ?>
<br/><br/>
<a class="redButton" href="<?php the_permalink(); ?>"><span class="screen-reader-text"> <?php the_title(); ?></span> Course Info - buy now!</a>
<br/><br/>
		<?php if($product_style == 'classic') { ?>
		<div class="memberPriceWrapper">
		Member Price: <?php the_field('member_price') ?><br/>
 		Non-Member Price:<?php	do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
 		</div>
 		<?php } ?>

	

	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>

</li>