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

add_shortcode( 'reachu_shop', 'reachu_shop' );

function reachu_shop( $atts = array(), $content = null , $tag = 'reachu_shop' ){
    $asset_file = include( plugin_dir_path( __FILE__ ) . '../../build/index.asset.php');

    wp_register_style(
        'unique-product-style',
        plugins_url( '../../build/style-index.css', __FILE__ ),
        array( 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . '../../build/style-index.css' )
    );
    wp_enqueue_style( 'unique-product-style' );

    $productIds = array_key_exists('product-ids', $atts) ? $atts['product-ids'] : null;
    $title = array_key_exists('title', $atts) ? $atts['title'] : null;
    $layout = array_key_exists('layout', $atts) ? $atts['layout'] : null;
    $shortcodeId = array_key_exists('shortcode-id', $atts) ? $atts['shortcode-id'] : null;

    return
        '<div>
            <div
                class="wp-block-outshifter-masonry"
                data-post_id="' . esc_attr( get_the_ID() ) . '"
                data-id="'. esc_attr( $productIds ) .'"
                data-title="'. esc_attr( $title ) .'"
                data-blockvariation="'. esc_attr( $layout ) .'"
                data-shortcodeid="'. esc_attr( $shortcodeId ) .'"
            >
            </div>
        </div>';
}
?>