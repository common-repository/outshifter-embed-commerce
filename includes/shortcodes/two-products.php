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
 */

add_shortcode( 'reachu_two_products', 'reachu_two_products' );

function reachu_two_products( $atts = array(), $content = null , $tag = 'reachu_two_products' ){
    $asset_file = include( plugin_dir_path( __FILE__ ) . '../../build/index.asset.php');

    wp_register_style(
        'unique-product-style',
        plugins_url( '../../build/style-index.css', __FILE__ ),
        array( 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . '../../build/style-index.css' )
    );
    wp_enqueue_style( 'unique-product-style' );

    $productIds = array_key_exists('product-ids', $atts) ? $atts['product-ids'] : null;
    $productIdsArray = preg_split ("/\,/", $productIds);
    $id1 = $productIdsArray[0];
    $id2 = $productIdsArray[1];
    $title = array_key_exists('title', $atts) ? $atts['title'] : null;

    $shortcodeId = array_key_exists('shortcode-id', $atts) ? $atts['shortcode-id'] : null;
    $postData = get_post_custom((int)$shortcodeId);
    $product1 = unserialize($postData["product"][0]);
    $product2 = unserialize($postData["product"][1]);
    $productImage1 = isset($product1->images[0]->url) ? $product1->images[0]->url : $product1->images[1]->url;
    $productImage2 = isset($product2->images[0]->url) ? $product2->images[0]->url : $product2->images[1]->url;
    $productSupplier1 = isset($product1->supplier) && isset($product1->supplier->name) ? $product1->supplier->name : '';
    $productSupplier2 = isset($product2->supplier) && isset($product2->supplier->name) ? $product2->supplier->name : '';
    
    return
        '<div>
            <div
                class="wp-block-outshifter-two-products"
                data-post_id="' . esc_attr( get_the_ID() ) . '"
                data-id1="'. esc_attr( $id1 ) .'"
                data-id2="'. esc_attr( $id2 ) .'"
                data-title="'. esc_attr( $title ) .'"
                data-producttitle1="'. esc_attr( $product1->title ) .'"
                data-productimage1="'. esc_attr( $productImage1 ) .'"
                data-productprice1="'. esc_attr( $product1->price->amount ) .'"
                data-productsupplier1="'. esc_attr( $productSupplier1 ) .'"
                data-producttitle2="'. esc_attr( $product2->title ) .'"
                data-productimage2="'. esc_attr( $productImage2 ) .'"
                data-productprice2="'. esc_attr( $product2->price->amount ) .'"
                data-productsupplier2="'. esc_attr( $productSupplier2 ) .'"
            >
            </div>
        </div>';
}
?>