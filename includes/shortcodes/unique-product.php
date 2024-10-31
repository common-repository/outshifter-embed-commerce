<?php

/**
 * The [wporg] shortcode.
 *
 * Accepts a title and will display a box.
 *
 * @param array  $atts    Shortcode attributes. Default empty.
 * @param string $content Shortcode content. Default null.
 * @param string $tag     Shortcode tag (name). Default empty.
 * @return string Shortcode output.
**/

add_shortcode( 'reachu_single_product', 'reachu_single_product' );

function reachu_single_product( $atts = array(), $content = null , $tag = 'reachu_single_product' ){
    $asset_file = include( plugin_dir_path( __FILE__ ) . '../../build/index.asset.php');

    wp_register_style(
        'unique-product-style',
        plugins_url( '../../build/style-index.css', __FILE__ ),
        array( 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . '../../build/style-index.css' )
    );
    wp_enqueue_style( 'unique-product-style' );
    
    $productId = array_key_exists('product-id', $atts) ? $atts['product-id'] : null;
    $title = array_key_exists('title', $atts) ? $atts['title'] : null;
    $shortcodeId = array_key_exists('shortcode-id', $atts) ? $atts['shortcode-id'] : null;

    $postData = get_post_custom((int)$shortcodeId);
    $product = unserialize($postData["product"][0]);
    $productImage = isset($product->images[0]->url) ? $product->images[0]->url : $product->images[1]->url;
    $productSupplier = isset($product->supplier) && isset($product->supplier->name) ? $product->supplier->name : '';

    return
        '<div>
            <div
                class="wp-block-outshifter-unique-product"
                data-post_id="' . esc_attr( get_the_ID() ) . '"
                data-id="'. esc_attr( $productId ) .'"
                data-title="'. esc_attr( $title ) .'"
                data-producttitle="'. esc_attr( $product->title ) .'"
                data-productimage="'. esc_attr( $productImage ) .'"
                data-productprice="'. esc_attr( $product->price->amount ) .'"
                data-productsupplier="'. esc_attr( $productSupplier ) .'"
            >
            </div>
        </div>';
}
?>