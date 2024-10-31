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

add_shortcode( 'outshifter_shop_preview', 'outshifter_shop_preview' );

function outshifter_shop_preview( $atts = array(), $content = null , $tag = 'outshifter_shop_preview' ){
    $asset_file = include( plugin_dir_path( __FILE__ ) . '../../build/index.asset.php');

    wp_register_style(
        'unique-product-style',
        plugins_url( '../../build/style-index.css', __FILE__ ),
        array( 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . '../../build/style-index.css' )
    );
    wp_enqueue_style( 'unique-product-style' );

    $backgroundColor = array_key_exists('background-color', $atts) ? $atts['background-color'] : null;
    $buttonText = array_key_exists('button-text', $atts) ? $atts['button-text'] : null;
    $buttonLink = array_key_exists('button-text', $atts) ? $atts['button-link'] : null;
    $productIds = array_key_exists('product-ids', $atts) ? $atts['product-ids'] : null;

    return
        '<div>
            <div
                class="outshifter-shortcode"
                id="outshifter-shortcode"
                data-background_color="' . $backgroundColor . '"
                data-button_text="' . $buttonText . '"
                data-button_link="' . $buttonLink . '"
                data-product_ids="' . $productIds . '"
                style="background-color: '. $backgroundColor .'"
            >
            </div>
        </div>';
}
?>