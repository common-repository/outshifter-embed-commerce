<?php
function add_custom_html() {
    echo '<div id="reachu-cart"></div>';
    echo '<div class="wp-block-outshifter-checkout-confirmation"></div>';
    echo '<div class="reachu-product-id"></div>';
}
add_action('wp_footer', 'add_custom_html');

function enqueue_custom_styles() {
    wp_enqueue_style('custom-styles', plugins_url( '../build/style-index.css', __FILE__ ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');
