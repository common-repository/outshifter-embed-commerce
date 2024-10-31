<?php

class Outshifter_Blocks_Activator {

  private static $page_name = 'shop';

  public static function activate() {
    add_option("outshifter_blocks_api_key", '', '', 'yes');
    add_option("outshifter_blocks_seller_id", '', '', 'yes');
    add_option("outshifter_blocks_seller_name", '', '', 'yes');
    add_option("outshifter_blocks_seller_surname", '', '', 'yes');
    add_option("outshifter_blocks_seller_email", '', '', 'yes');
    add_option("outshifter_blocks_seller_avatar", '', '', 'yes');
    add_option("outshifter_blocks_currency", 'NOK', '', 'yes');
    add_option("outshifter_blocks_font_selected", 'Default', '', 'yes');
    add_option("outshifter_blocks_secondary_color", 'white', '', 'yes');
    add_option("outshifter_blocks_country", 'NO', '', 'yes');
    add_option("outshifter_blocks_supplier_logo", '', '', 'yes');
    add_option("outshifter_blocks_supplier_logo_white", '', '', 'yes');
    add_option("outshifter_blocks_shop_color", '', '', 'yes');
    add_option("outshifter_blocks_button_prev_type", 'outlined', '', 'yes');
    add_option("outshifter_blocks_button_next_type", 'filled', '', 'yes');
    add_option("outshifter_blocks_button_next_color", '#000', '', 'yes');
    add_option("outshifter_blocks_button_prev_color", '#000', '', 'yes');
    add_option("outshifter_blocks_button_hover_color", '#2e2e31', '', 'yes');
    add_option("outshifter_blocks_button_text_color", '#fff', '', 'yes');
    add_option("outshifter_blocks_button_prev_text_color", '#fff', '', 'yes');
    add_option("outshifter_blocks_button_hover_text_color", '#fff', '', 'yes');
    add_option("outshifter_blocks_button_prev_border_color", '#000', '', 'yes');
    add_option("outshifter_blocks_button_border_ratio", 0, '', 'yes');
    add_option("outshifter_blocks_title_size", 13, '', 'yes');
    add_option("outshifter_blocks_mixpanel", '', '', 'yes');
    add_option("outshifter_blocks_stripe_key", '', '', 'yes');
    add_option("outshifter_blocks_stripe_id", '', '', 'yes');
    add_option("outshifter_blocks_g_analytics", '', '', 'yes');
    add_option("outshifter_blocks_title_alignment", 'alignLeft', '', 'yes');
    add_option("outshifter_blocks_create_shortcode", '', '', 'yes');
    add_option("outshifter_blocks_not_gutemberg", '', '', 'yes');
    add_option("outshifter_blocks_allow_upload_to_media", '', '', 'yes');
    add_option("outshifter_blocks_saved_media_images", '', '', 'yes');
    add_option("outshifter_blocks_shortcode_buy_button", '', '', 'yes');
    add_option("outshifter_blocks_shortcode_single", '', '', 'yes');
    add_option("outshifter_blocks_shortcode_two", '', '', 'yes');
    add_option("outshifter_blocks_shortcode_carousel", '', '', 'yes');
    add_option("outshifter_blocks_shortcode_shop", '', '', 'yes');
    add_option("outshifter_blocks_supplier_logo_shop", '', '', 'yes');
    add_option("outshifter_blocks_shop_logo_selected", '', '', 'yes');
    add_option("outshifter_blocks_shop_text_selected", '', '', 'yes');
    add_option("outshifter_blocks_shop_button_color", '#000', '', 'yes');
    add_option("outshifter_blocks_text_icon_color", '#fff', '', 'yes');
    add_option("outshifter_blocks_shop_button_ratio", 15, '', 'yes');
    add_option("outshifter_blocks_show_shop_icon", '', '', 'yes');
    add_option("outshifter_blocks_add_shop_url", '', '', 'yes');
    add_option("outshifter_blocks_shop_custom_url", '', '', 'yes');
    add_option("outshifter_blocks_show_card_title", 'showCardTitle', '', 'yes');
    add_option("outshifter_blocks_show_card_price", 'showCardPrice', '', 'yes');
    add_option("outshifter_blocks_show_card_supplier", 'showCardSupplier', '', 'yes');
    add_option("outshifter_blocks_show_card_button", 'showCardButton', '', 'yes');

    $page_shop = array(
      'post_title'    => wp_strip_all_tags( 'Shop' ),
      'post_name'     => self::$page_name,
      'post_content'  => '<!-- wp:outshifter/banner /--><!-- wp:outshifter/masonry /-->',
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type'     => 'page',
    );
    $page_id = wp_insert_post( $page_shop );
    if(!is_wp_error($page_id)){
      add_option( "outshifter_blocks_page_shop_id", $page_id );
      update_post_meta( $page_id, '_wp_page_template', '/includes/template.php' );
    } else {
      echo esc_html($page_id->get_error_message());
    }
  }

  public static function deactivate() {
    $page_id = get_option( "outshifter_blocks_page_shop_id" );
    if( $page_id ) {
      wp_delete_post( $page_id );
    }
    delete_option("outshifter_blocks_page_shop_id");
  }

  public static function uninstall() {
    delete_option("outshifter_blocks_api_key");
    delete_option("outshifter_blocks_seller_id");
    delete_option("outshifter_blocks_seller_avatar");
    delete_option("outshifter_blocks_seller_name");
    delete_option("outshifter_blocks_seller_surname");
    delete_option("outshifter_blocks_seller_email");
    delete_option("outshifter_blocks_currency");
    delete_option("outshifter_blocks_font_selected");
    delete_option("outshifter_blocks_secondary_color");
    delete_option("outshifter_blocks_country");
    delete_option("outshifter_blocks_supplier_logo");
    delete_option("outshifter_blocks_supplier_logo_white");
    delete_option("outshifter_blocks_shop_color");
    delete_option("outshifter_blocks_button_next_type");
    delete_option("outshifter_blocks_button_prev_type");
    delete_option("outshifter_blocks_button_next_color");
    delete_option("outshifter_blocks_button_prev_color");
    delete_option("outshifter_blocks_button_hover_color");
    delete_option("outshifter_blocks_button_text_color");
    delete_option("outshifter_blocks_button_prev_text_color");
    delete_option("outshifter_blocks_button_hover_text_color");
    delete_option("outshifter_blocks_button_prev_border_color");
    delete_option("outshifter_blocks_button_border_ratio");
    delete_option("outshifter_blocks_title_size");
    delete_option("outshifter_blocks_mixpanel");
    delete_option("outshifter_blocks_stripe_key");
    delete_option("outshifter_blocks_stripe_id");
    delete_option("outshifter_blocks_g_analytics");
    delete_option("outshifter_blocks_create_shortcode");
    delete_option("outshifter_blocks_not_gutemberg");
    delete_option("outshifter_blocks_allow_upload_to_media");
    delete_option("outshifter_blocks_saved_media_images");
    delete_option("outshifter_blocks_shortcode_buy_button");
    delete_option("outshifter_blocks_shortcode_single");
    delete_option("outshifter_blocks_shortcode_two");
    delete_option("outshifter_blocks_shortcode_carousel");
    delete_option("outshifter_blocks_shortcode_shop");
    delete_option("outshifter_blocks_supplier_logo_shop");
    delete_option("outshifter_blocks_shop_logo_selected");
    delete_option("outshifter_blocks_shop_text_selected");
    delete_option("outshifter_blocks_shop_button_color");
    delete_option("outshifter_blocks_text_icon_color");
    delete_option("outshifter_blocks_shop_button_ratio");
    delete_option("outshifter_blocks_show_shop_icon");
    delete_option("outshifter_blocks_add_shop_url");
    delete_option("outshifter_blocks_show_card_title");
    delete_option("outshifter_blocks_shop_custom_url");
    delete_option("outshifter_blocks_show_card_price");
    delete_option("outshifter_blocks_show_card_supplier");
    delete_option("outshifter_blocks_show_card_button");
  }
}