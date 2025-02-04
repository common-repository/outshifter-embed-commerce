<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Outsfhiter_Blocks
 * @subpackage Outsfhiter_Blocks/admin
 */

require_once "partials/custom-shortcodes.php";

class Outsfhiter_Blocks_Admin {

  const ADMIN_SLUG = 'reachu-embed-commerce';
	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
    if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'build_menu' ) );
      remove_all_actions( 'admin_notices' );
      add_action('wp_ajax_outshifter_blocks_connect', array( $this, 'outshifter_blocks_connect' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_connect', array( $this, 'outshifter_blocks_connect' ));
      add_action('wp_ajax_outshifter_blocks_disconnect', array( $this, 'outshifter_blocks_disconnect' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_disconnect', array( $this, 'outshifter_blocks_disconnect' ));
      add_action('wp_ajax_save_reset_media_uploads', array( $this, 'save_reset_media_uploads' ));
      add_action('wp_ajax_nopriv_save_reset_media_uploads', array( $this, 'save_reset_media_uploads' ));
      add_action('wp_ajax_outshifter_blocks_save_settings', array( $this, 'outshifter_blocks_save_settings' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_settings', array( $this, 'outshifter_blocks_save_settings' ));
      add_action('wp_ajax_store_shortcode', array( $this, 'store_shortcode' ));
      add_action('wp_ajax_nopriv_store_shortcode', array( $this, 'store_shortcode' ));
      add_action('wp_ajax_delete_shortcode', array( $this, 'delete_shortcode' ));
      add_action('wp_ajax_nopriv_delete_shortcode', array( $this, 'delete_shortcode' ));
      add_action('wp_ajax_selected-data', array( $this, 'selected-data' ));
      add_action('wp_ajax_outshifter_blocks_save_selected_products', array( $this, 'outshifter_blocks_save_selected_products' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_selected_products', array( $this, 'outshifter_blocks_save_selected_products' ));
      add_action('wp_ajax_outshifter_blocks_save_selected_buy_button', array( $this, 'outshifter_blocks_save_selected_buy_button' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_selected_buy_button', array( $this, 'outshifter_blocks_save_selected_buy_button' ));
      add_action('wp_ajax_outshifter_blocks_save_selected_products_single', array( $this, 'outshifter_blocks_save_selected_products_single' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_selected_products_single', array( $this, 'outshifter_blocks_save_selected_products_single' ));
      add_action('wp_ajax_outshifter_blocks_save_selected_products_two', array( $this, 'outshifter_blocks_save_selected_products_two' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_selected_products_two', array( $this, 'outshifter_blocks_save_selected_products_two' ));
      add_action('wp_ajax_outshifter_blocks_save_selected_products_carousel', array( $this, 'outshifter_blocks_save_selected_products_carousel' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_selected_products_carousel', array( $this, 'outshifter_blocks_save_selected_products_carousel' ));
      add_action('wp_ajax_outshifter_blocks_save_selected_products_shop', array( $this, 'outshifter_blocks_save_selected_products_shop' ));
      add_action('wp_ajax_nopriv_outshifter_blocks_save_selected_products_shop', array( $this, 'outshifter_blocks_save_selected_products_shop' ));
    }
	}
  
  public function build_menu() {
    add_menu_page( 'Reachu blocks page', 'Reachu', 'manage_options', self::ADMIN_SLUG, array( $this, 'admin_page' ), OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/reachu-logo-only-white.svg' );
    add_action('admin_init', array($this, 'remove_admin_notices_on_custom_page'));
  }

  public function remove_admin_notices_on_custom_page() {
    global $pagenow;
    if (is_admin() && $pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === self::ADMIN_SLUG) {
        remove_all_actions('admin_notices');
    }
}

  public function outshifter_blocks_connect() {
    if (isset($_POST['token'])) {
      $token = sanitize_text_field($_POST['token']);
      $headers = array(
        'Accept' => 'application/json',
        'Content-Type' => 'application/json;charset=UTF-8',
        'authorization' => esc_html($token)
      );
      $args = array(
        'headers' =>  $headers,
        'method' => 'GET',
        'timeout' => '10',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'cookies' => array()
      );
      $response = wp_remote_request(OUTSHIFTER_API_URL . '/api/channel/validate-api-token', $args);
      $response_code = wp_remote_retrieve_response_code($response);
      if (is_wp_error($response) || !in_array($response_code, array(200, 201))) {
        update_option('outshifter_blocks_connect_error', 'The API Key provided could not be validated. Please check the value is correct and try again.');
        wp_send_json_error(array('response'=>$response));
      } else {
        $responseBody = wp_remote_retrieve_body( $response );
        $outshifterUser = json_decode( $responseBody );
        $id = $outshifterUser && $outshifterUser->id !== null ? $outshifterUser->id : 1;
        update_option('outshifter_blocks_api_key', esc_html($token));
        update_option('outshifter_blocks_seller_id', $id);
        update_option('outshifter_blocks_seller_name', $outshifterUser->userName);
        update_option('outshifter_blocks_seller_surname', $outshifterUser->userSurname);
        update_option('outshifter_blocks_seller_email', $outshifterUser->email);
        update_option('outshifter_blocks_seller_avatar', $outshifterUser->avatar);
        update_option('outshifter_blocks_connect_error', '');        
        wp_send_json_success();
      }
    } else {
      update_option('outshifter_blocks_connect_error', 'Please provide a valid API Key.');
    }
  }

  public function outshifter_blocks_disconnect() {
    update_option('outshifter_blocks_api_key', '');
    update_option('outshifter_blocks_seller_id', '');
    update_option('outshifter_blocks_seller_name', '');
    update_option('outshifter_blocks_seller_surname', '');
    update_option('outshifter_blocks_seller_email', '');
    update_option('outshifter_blocks_seller_avatar', '');

  }

  public function save_reset_media_uploads() {
    if (isset($_POST['allowUploadToMedia'])) {
      $allowUploadToMedia = sanitize_text_field($_POST['allowUploadToMedia']);
    }
    update_option( 'outshifter_blocks_allow_upload_to_media', esc_html($allowUploadToMedia) );
    update_option( 'outshifter_blocks_saved_media_images', '' );
  }

  public function store_shortcode() {
    if (isset($_POST['shortcode'])) {
      $shortcode = sanitize_text_field($_POST['shortcode']);
    }
    if (isset($_POST['shortcodeName'])) {
      $shortcodeName = sanitize_text_field($_POST['shortcodeName']);
    }
    if (isset($_POST['shortcodeType'])) {
      $shortcodeType = sanitize_text_field($_POST['shortcodeType']);
    }
    if (isset($_POST['selectedProducts'])) {
      $selectedProducts = sanitize_text_field($_POST['selectedProducts']);
    }
    $my_cptpost_args = array(
      'post_title'    =>  $shortcodeName,
      'post_content'  => $shortcode,
      'post_excerpt' => $shortcodeType,
      'post_status'   => 'publish',
      'post_date' => date( 'Y-m-d H:i:s', time() ),
      'post_type' => 'shortcodes'
    );
    $cpt_id = wp_insert_post( $my_cptpost_args, $wp_error);
    $shortcodes_args = get_posts([
      'post_type'   => 'shortcodes',
      'post_status' => 'publish',
      'numberposts' => -1,
      'order'       => 'DESC',
    ]);
    $theSelected = array();
    $updateProducts = json_decode(stripslashes($selectedProducts));
    $allowUploadToMedia = get_option('outshifter_blocks_allow_upload_to_media', '');
    $savedMediaImages = get_option('outshifter_blocks_saved_media_images', '');
    function uploadImageToMediaLibrary($updateProduct) {
      $variable_url = isset($updateProduct->images[0]->url) ? $updateProduct->images[0]->url : $updateProduct->images[1]->url;
      if ( strpos($variable_url, "?v") ) {
        $url = substr($variable_url, 0, strpos($variable_url, "?v"));
      } else {
        $url = $variable_url;
      }
      $allow = ['gif', 'jpg', 'png', 'jpeg'];
      $img = file_get_contents($url);
      $url_info = pathinfo($url);
      if (in_array($url_info['extension'], $allow)) {
        $title = str_replace(" ", "_", $updateProduct->title);
        $base64_img = 'data:image/'. $url_info['extension'] .';base64,'. base64_encode($img);
        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
        $img             = str_replace( 'data:image/'. $url_info['extension'] .';base64,', '', $base64_img );
        $img             = str_replace( ' ', '+', $img );
        $decoded         = base64_decode( $img );
        $filename        = $title . '.' . $url_info['extension'];
        $file_type       = 'image/' . $url_info['extension'];
        $hashed_filename = md5( $filename . microtime() ) . '_' . $filename;
        // resize base64 image
        $filenameoutput = $upload_path . $hashed_filename; // output file name
        $im = imagecreatefromstring($decoded);
        $source_width = imagesx($im);
        $source_height = imagesy($im);
        $ratio =  $source_height / $source_width;
        $new_width = 700; // assign new width to new resized image
        $new_height = $ratio * 700;
        $thumb = imagecreatetruecolor($new_width, $new_height);
        $transparency = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $transparency);
        imagecopyresampled($thumb, $im, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);
        imagejpeg($thumb, $filenameoutput, 90);
        imagedestroy($im);
        // Save the image in the uploads directory.
        $upload_file = file_put_contents( $upload_path . $hashed_filename, $thumb );
        $attachment = array(
          'post_mime_type' => $file_type,
          'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
          'post_content'   => '',
          'post_status'    => 'inherit',
          'guid'           => $upload_dir['url'] . '/' . basename( $hashed_filename )
        );
        $attach_id = wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $hashed_filename );
        $media_url = wp_get_attachment_url( $attach_id );
        wp_generate_attachment_metadata( $attach_id, $media_url );
        $updateProduct->mediaId = $attach_id;
        if (isset($updateProduct->images[0]->url)) {
          $updateProduct->images[0]->url = $media_url;
        } else {
          $updateProduct->images[1]->url = $media_url;
        }
      } else $re = 'Invalid extension: '. $url_info['extension'];
    }
    foreach ($updateProducts as $updateProduct) {
      $productId = strval($updateProduct->id);
      if ($savedMediaImages !== '') {
        $isAlreadyInMedia = strpos( $savedMediaImages, $productId );
        if ($isAlreadyInMedia === false) {
          $savedMediaImages .= ','. $productId .'';
          update_option( 'outshifter_blocks_saved_media_images', esc_html($savedMediaImages) );
        }
      } else {
        $savedMediaImages = $productId;
        update_option( 'outshifter_blocks_saved_media_images', esc_html($savedMediaImages) );
        $isAlreadyInMedia = false;
      }
      if ( $allowUploadToMedia && $isAlreadyInMedia === false) {
        uploadImageToMediaLibrary($updateProduct);
      }
      if ( $allowUploadToMedia && $isAlreadyInMedia !== false ) {
        foreach ($shortcodes_args as $shortcode_arg) {
          $getTheProducts = get_post_meta( $shortcode_arg->ID, 'product' );
          foreach ( $getTheProducts as $getTheProduct ) {
            if ( $productId === strval($getTheProduct->id) ) {
              $updateProduct->mediaId = $getTheProduct->mediaId;
              $imageExist = wp_get_attachment_url( $getTheProduct->mediaId );
              if ($imageExist) {
                if ( isset($updateProduct->images[0]->url) ) {
                  $updateProduct->images[0]->url = $getTheProduct->images[0]->url;
                } else {
                  $updateProduct->images[1]->url = $getTheProduct->images[1]->url;
                }
              } else {
                uploadImageToMediaLibrary($updateProduct);
              }
              break 2;
            }
          }
        }
      }
      add_post_meta( $shortcodes_args[0]->ID, "product", $updateProduct );
    }
    // add id to shortcode
    $shortcodeLength = strlen($shortcode);
    $updatedShortcode = substr($shortcode, 0, $shortcodeLength - 1);
    $updatedShortcode .= ' shortcode-id="'. $shortcodes_args[0]->ID . '"]';
    // Update post
    $my_post = array(
      'ID'           => $shortcodes_args[0]->ID ,
      'post_content' => $updatedShortcode,
    );
    wp_update_post( $my_post );
    // update shortcodes array
    $shortcodes_updated = get_posts([
      'post_type'   => 'shortcodes',
      'post_status' => 'publish',
      'numberposts' => -1,
      'order'       => 'DESC',
    ]);
    // put the posts into an array
    $arr = array();
    foreach ($shortcodes_updated as $shortcode_updated) {
      $entry = array();
      $entry['id'] = $shortcode_updated->ID;
      $entry['title'] = $shortcode_updated->post_title;
      $entry['excerpt'] = $shortcode_updated->post_excerpt;
      $entry['content'] = $shortcode_updated->post_content;
      $arr[] = $entry;
    }
    // output in json format
    header("Content-Type: application/json");
    echo json_encode($arr);
    exit;
  }

  public function delete_shortcode() {
    if (isset($_POST['shortcodeId'])) {
      $shortcodeId = sanitize_text_field($_POST['shortcodeId']);
    }
    wp_delete_post( $shortcodeId, false );
    // update shortcodes array
    $shortcodes_args = get_posts([
      'post_type'   => 'shortcodes',
      'post_status' => 'publish',
      'numberposts' => -1,
      'order'       => 'DESC',
    ]);
    // put the posts into an array
    $arr = array();
    foreach ($shortcodes_args as $shortcode_arg) {
      $entry = array();
      $entry['id'] = $shortcode_arg->ID;
      $entry['title'] = $shortcode_arg->post_title;
      $entry['excerpt'] = $shortcode_arg->post_excerpt;
      $entry['content'] = $shortcode_arg->post_content;
      $arr[] = $entry;
    }
    // output in json format
    header("Content-Type: application/json");
    echo json_encode($arr);
    exit;
  }

  public function outshifter_blocks_save_settings() {
    if (isset($_POST['currency'])) {
      $currency = sanitize_text_field($_POST['currency']);
    }
    if (isset($_POST['createShortcode'])) {
      $createShortcode = sanitize_text_field($_POST['createShortcode']);
    }
    if (isset($_POST['shortcodeShopLayout'])) {
      $shortcodeShopLayout = sanitize_text_field($_POST['shortcodeShopLayout']);
    }
    if (isset($_POST['supplierLogo'])) {
      $supplierLogo = sanitize_text_field($_POST['supplierLogo']);
    }
    if (isset($_POST['supplierLogoWhite'])) {
      $supplierLogoWhite = sanitize_text_field($_POST['supplierLogoWhite']);
    }
    if (isset($_POST['mixpanel'])) {
      $mixPanel = sanitize_text_field($_POST['mixpanel']);
    }
    if (isset($_POST['layoutSelected'])) {
      $layoutSelected = sanitize_text_field($_POST['layoutSelected']);
    }
    if (isset($_POST['blocksTitleAlignment'])) {
      $blocksTitleAlignment = sanitize_text_field($_POST['blocksTitleAlignment']);
    }
    if (isset($_POST['modalPosition'])) {
      $modalPosition = sanitize_text_field($_POST['modalPosition']);    
    }
    if (isset($_POST['shopColor'])) {
      $shopColor = sanitize_text_field($_POST['shopColor']);
    }
    if (isset($_POST['buttonPrevType'])) {
      $buttonPrevType = sanitize_text_field($_POST['buttonPrevType']);
    }
    if (isset($_POST['buttonNextType'])) {
      $buttonNextType = sanitize_text_field($_POST['buttonNextType']);
    }
    if (isset($_POST['buttonNextColor'])) {
      $buttonNextColor = sanitize_text_field($_POST['buttonNextColor']);
    }
    if (isset($_POST['buttonPrevColor'])) {
      $buttonPrevColor = sanitize_text_field($_POST['buttonPrevColor']);
    }
    if (isset($_POST['buttonNextHoverColor'])) {
      $buttonNextHoverColor = sanitize_text_field($_POST['buttonNextHoverColor']);
    }
    if (isset($_POST['buttonNextTextColor'])) {
      $buttonNextTextColor = sanitize_text_field($_POST['buttonNextTextColor']);
    }
    if (isset($_POST['buttonPrevTextColor'])) {
      $buttonPrevTextColor = sanitize_text_field($_POST['buttonPrevTextColor']);
    }
    if (isset($_POST['buttonNextHoverTextColor'])) {
      $buttonNextHoverTextColor = sanitize_text_field($_POST['buttonNextHoverTextColor']);
    }
    if (isset($_POST['buttonPrevBorderColor'])) {
      $buttonPrevBorderColor = sanitize_text_field($_POST['buttonPrevBorderColor']);
    }
    if (isset($_POST['buttonBorderRatio'])) {
      $buttonBorderRatio = sanitize_text_field($_POST['buttonBorderRatio']);
    }    
    if (isset($_POST['blockTitleSize'])) {
      $blockTitleSize = sanitize_text_field($_POST['blockTitleSize']);
    }
    if (isset($_POST['stripeKey'])) {
      $stripeKey = sanitize_text_field($_POST['stripeKey']);
    }
    if (isset($_POST['stripeId'])) {
      $stripeId = sanitize_text_field($_POST['stripeId']);
    }
    if (isset($_POST['gAnalytics'])) {
      $gAnalytics = sanitize_text_field($_POST['gAnalytics']);
    }
    if (isset($_POST['fontSelected'])) {
      $fontSelected = sanitize_text_field($_POST['fontSelected']);
    }
    if (isset($_POST['notGutemberg'])) {
      $notGutemberg = sanitize_text_field($_POST['notGutemberg']);
    }
    if (isset($_POST['allowUploadToMedia'])) {
      $allowUploadToMedia = sanitize_text_field($_POST['allowUploadToMedia']);
    }
    if (isset($_POST['shopLogoSelected'])) {
      $shopLogoSelected = sanitize_text_field($_POST['shopLogoSelected']);
    }
    if (isset($_POST['supplierLogoShop'])) {
      $supplierLogoShop = sanitize_text_field($_POST['supplierLogoShop']);
    }
    if (isset($_POST['shopTextSelected'])) {
      $shopTextSelected = sanitize_text_field($_POST['shopTextSelected']);
    }
    if (isset($_POST['shopButtonRatio'])) {
      $shopButtonRatio = sanitize_text_field($_POST['shopButtonRatio']);
    }
    if (isset($_POST['showShopIcon'])) {
      $showShopIcon = sanitize_text_field($_POST['showShopIcon']);
    }
    if (isset($_POST['showCardTitle'])) {
      $showCardTitle = sanitize_text_field($_POST['showCardTitle']);
    }
    if (isset($_POST['showCardPrice'])) {
      $showCardPrice = sanitize_text_field($_POST['showCardPrice']);
    }
    if (isset($_POST['showCardSupplier'])) {
      $showCardSupplier = sanitize_text_field($_POST['showCardSupplier']);
    }
    if (isset($_POST['showCardButton'])) {
      $showCardButton = sanitize_text_field($_POST['showCardButton']);
    }
    if (isset($_POST['shopButtonColor'])) {
      $shopButtonColor = sanitize_text_field($_POST['shopButtonColor']);
    }
    if (isset($_POST['textIconColor'])) {
      $textIconColor = sanitize_text_field($_POST['textIconColor']);
    }
    if (isset($_POST['addShopUrl'])) {
      $addShopUrl = sanitize_text_field($_POST['addShopUrl']);
    }
    if (isset($_POST['shopCustomUrl'])) {
      $shopCustomUrl = sanitize_text_field($_POST['shopCustomUrl']);
    }

    update_option( 'outshifter_blocks_currency', esc_html($currency));
    update_option( 'outshifter_blocks_supplier_logo', esc_html($supplierLogo) );
    update_option( 'outshifter_blocks_supplier_logo_white', esc_html($supplierLogoWhite) );
    update_option( 'outshifter_blocks_mixpanel', esc_html($mixPanel) );
    update_option( 'outshifter_blocks_layout_selected', esc_html($layoutSelected) );
    update_option( 'outshifter_blocks_modal_position', esc_html($modalPosition) );
    update_option( 'outshifter_blocks_title_alignment', esc_html($blocksTitleAlignment) );
    update_option( 'outshifter_blocks_shop_color', esc_html($shopColor) );
    update_option( 'outshifter_blocks_button_prev_type', esc_html($buttonPrevType) );
    update_option( 'outshifter_blocks_button_next_type', esc_html($buttonNextType) );
    update_option( 'outshifter_blocks_button_next_color', esc_html($buttonNextColor) );
    update_option( 'outshifter_blocks_button_prev_color', esc_html($buttonPrevColor) );
    update_option( 'outshifter_blocks_button_hover_color', esc_html($buttonNextHoverColor) );
    update_option( 'outshifter_blocks_button_text_color', esc_html($buttonNextTextColor) );
    update_option( 'outshifter_blocks_button_prev_text_color', esc_html($buttonPrevTextColor) );
    update_option( 'outshifter_blocks_button_hover_text_color', esc_html($buttonNextHoverTextColor) );
    update_option( 'outshifter_blocks_button_prev_border_color', esc_html($buttonPrevBorderColor) );
    update_option( 'outshifter_blocks_button_border_ratio', esc_html($buttonBorderRatio) );
    update_option( 'outshifter_blocks_title_size', esc_html($blockTitleSize) );
    update_option( 'outshifter_blocks_stripe_key', esc_html($stripeKey) );
    update_option( 'outshifter_blocks_stripe_id', esc_html($stripeId) );
    update_option( 'outshifter_blocks_g_analytics', esc_html($gAnalytics) );
    update_option( 'outshifter_blocks_font_selected', esc_html($fontSelected));
    update_option( 'outshifter_blocks_create_shortcode', esc_html($createShortcode));
    update_option( 'outshifter_blocks_shortcode_shop_layout', esc_html($shortcodeShopLayout));
    update_option( 'outshifter_blocks_not_gutemberg', esc_html($notGutemberg) );
    update_option( 'outshifter_blocks_allow_upload_to_media', esc_html($allowUploadToMedia) );
    update_option( 'outshifter_blocks_shop_logo_selected', esc_html($shopLogoSelected) );
    update_option( 'outshifter_blocks_supplier_logo_shop', esc_html($supplierLogoShop) );
    update_option( 'outshifter_blocks_shop_text_selected', esc_html($shopTextSelected) );
    update_option( 'outshifter_blocks_shop_button_ratio', esc_html($shopButtonRatio) );
    update_option( 'outshifter_blocks_show_shop_icon', esc_html($showShopIcon) );
    update_option( 'outshifter_blocks_shop_button_color', esc_html($shopButtonColor) );
    update_option( 'outshifter_blocks_text_icon_color', esc_html($textIconColor) );
    update_option( 'outshifter_blocks_add_shop_url', esc_html($addShopUrl) );
    update_option( 'outshifter_blocks_shop_custom_url', esc_html($shopCustomUrl) );
    update_option( 'outshifter_blocks_show_card_title', esc_html($showCardTitle) );
    update_option( 'outshifter_blocks_show_card_price', esc_html($showCardPrice) );
    update_option( 'outshifter_blocks_show_card_supplier', esc_html($showCardSupplier) );
    update_option( 'outshifter_blocks_show_card_button', esc_html($showCardButton) );

    update_option( 'outshifter_blocks_saved', 'Settings saved.' );

    wp_send_json_success();

  }

  public function outshifter_blocks_save_selected_buy_button() {
    if (isset($_POST['shortcodeBuyButton'])) {
      $shortcodeBuyButton = sanitize_text_field($_POST['shortcodeBuyButton']);
    }
    update_option( 'outshifter_blocks_shortcode_buy_button', esc_html($shortcodeBuyButton));
    wp_send_json_success();
  }
  
  public function outshifter_blocks_save_selected_products_single() {
    if (isset($_POST['shortcodeSingle'])) {
      $shortcodeSingle = sanitize_text_field($_POST['shortcodeSingle']);
    }
    update_option( 'outshifter_blocks_shortcode_single', esc_html($shortcodeSingle));
    wp_send_json_success();
  }

  public function outshifter_blocks_save_selected_products_two() {
    if (isset($_POST['shortcodeTwo'])) {
      $shortcodeTwo = sanitize_text_field($_POST['shortcodeTwo']);
    }
    update_option( 'outshifter_blocks_shortcode_two', esc_html($shortcodeTwo));
    wp_send_json_success();
  }

  public function outshifter_blocks_save_selected_products_carousel() {
    if (isset($_POST['shortcodeCarousel'])) {
      $shortcodeCarousel = sanitize_text_field($_POST['shortcodeCarousel']);
    }
    update_option( 'outshifter_blocks_shortcode_carousel', esc_html($shortcodeCarousel));
    wp_send_json_success();
  }

  public function outshifter_blocks_save_selected_products_shop() {
    if (isset($_POST['shortcodeShop'])) {
      $shortcodeShop = sanitize_text_field($_POST['shortcodeShop']);
    }
    update_option( 'outshifter_blocks_shortcode_shop', esc_html($shortcodeShop));
    wp_send_json_success();
  }

  public function admin_page() {
    $connectError = get_option('outshifter_blocks_connect_error');
    $isSaved = get_option('outshifter_blocks_saved');
    $sellerId = get_option('outshifter_blocks_seller_id');
    $sellerName = get_option('outshifter_blocks_seller_name');    
    $sellerSurname = get_option('outshifter_blocks_seller_surname');    
    $sellerEmail = get_option('outshifter_blocks_seller_email');    
    $sellerAvatar = get_option('outshifter_blocks_seller_avatar');    
    $currency = get_option('outshifter_blocks_currency', '');
    $supplierLogo = get_option('outshifter_blocks_supplier_logo', '');
    $supplierLogoWhite = get_option('outshifter_blocks_supplier_logo_white', '');
    $mixPanel = get_option('outshifter_blocks_mixpanel', '');
    $layoutSelected = get_option('outshifter_blocks_layout_selected', '') ?: "alignTwoCols";
    $modalPosition = get_option('outshifter_blocks_modal_position', '') ?: "modalRight";
    $blocksTitleAlignment = get_option('outshifter_blocks_title_alignment', '') ?: "alignLeft";
    $shopColor = get_option('outshifter_blocks_shop_color', '') ?: "#fff";
    $buttonNextType = get_option('outshifter_blocks_button_next_type', '') ?: "filled";
    $buttonPrevType = get_option('outshifter_blocks_button_prev_type', '') ?: "outlined";
    $buttonNextColor = get_option('outshifter_blocks_button_next_color', '') ?: "#000";
    $buttonPrevColor = get_option('outshifter_blocks_button_prev_color', '') ?: "#000";
    $buttonNextHoverColor = get_option('outshifter_blocks_button_hover_color', '') ?: "#2e2e31";
    $buttonNextTextColor = get_option('outshifter_blocks_button_text_color', '') ?: "#fff";
    $buttonPrevTextColor = get_option('outshifter_blocks_button_prev_text_color', '') ?: "#fff";
    $buttonNextHoverTextColor = get_option('outshifter_blocks_button_hover_text_color', '') ?: "#fff";
    $buttonPrevBorderColor = get_option('outshifter_blocks_button_prev_border_color', '') ?: "#000";
    $buttonBorderRatio = get_option('outshifter_blocks_button_border_ratio', '');
    $blockTitleSize = get_option('outshifter_blocks_title_size', '') ?: 13;
    $stripeKey = get_option('outshifter_blocks_stripe_key', '');
    $stripeId = get_option('outshifter_blocks_stripe_id', '');
    $gAnalytics = get_option('outshifter_blocks_g_analytics', '');
    $fontSelected = get_option('outshifter_blocks_font_selected', '');
    $createShortcode = null;
    $shortcodeShopLayout = null;
    $apiUrl = OUTSHIFTER_API_URL;
    $notGutemberg = get_option('outshifter_blocks_not_gutemberg', '');
    $allowUploadToMedia = get_option('outshifter_blocks_allow_upload_to_media', '');
    $savedMediaImages = get_option('outshifter_blocks_saved_media_images', '');
    $userApiKey = get_option('outshifter_blocks_api_key', '');
    $shortcodeBuyButton = null;
    $shortcodeSingle = null;
    $shortcodeTwo = null;
    $shortcodeCarousel = null;
    $shortcodeShop = null;
    $shopLogoSelected = get_option('outshifter_blocks_shop_logo_selected', '') ?: "shopImgSelected";
    $supplierLogoShop = get_option('outshifter_blocks_supplier_logo_shop', '');
    $shopTextSelected = get_option('outshifter_blocks_shop_text_selected', '');
    $shopButtonColor = get_option('outshifter_blocks_shop_button_color', '') ?: "#000";
    $textIconColor = get_option('outshifter_blocks_text_icon_color', '') ?: "#fff";
    $shopButtonRatio = get_option('outshifter_blocks_shop_button_ratio', '');
    $showShopIcon = get_option('outshifter_blocks_show_shop_icon', '');
    $addShopUrl = get_option('outshifter_blocks_add_shop_url', '');
    $shopCustomUrl = get_option('outshifter_blocks_shop_custom_url', '');
    $showCardTitle = get_option('outshifter_blocks_show_card_title');
    $showCardPrice = get_option('outshifter_blocks_show_card_price');
    $showCardSupplier = get_option('outshifter_blocks_show_card_supplier');
    $showCardButton = get_option('outshifter_blocks_show_card_button');
    $showType = gettype($showCardTitle);

    update_option('outshifter_blocks_connect_error', '');
    update_option('outshifter_blocks_saved', '');

    wp_enqueue_media();

    $content = null;
    $contentError = null;
    $contentIsSaved = null;
    $mixPanelContent = null;
    
    $default_tab = null;
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $default_tab;

    if ($sellerId) {
      $headers = array(
        'Accept' => 'application/json',
        'Content-Type' => 'application/json;charset=UTF-8',
        'authorization' => esc_html($userApiKey)
      );
      $args = array(
        'headers' =>  $headers,
        'method' => 'GET',
        'timeout' => '10',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'cookies' => array()
      );
      $response_currencies = wp_remote_request(OUTSHIFTER_API_URL . '/api/currencies', $args);
      $body = wp_remote_retrieve_body($response_currencies);
      $currencies = json_decode($body, true);
    }

    echo "<script>
      const sellerId = '$sellerId';
      const apiUrl = '$apiUrl';
      const userApiKey = '$userApiKey';
      const notGutemberg = '$notGutemberg';
      const allowUploadToMedia = '$allowUploadToMedia';
      const currency = '$currency';
      const prevSelectedBlock = '$createShortcode' || null;
      const prevSelectedShop = '$shortcodeShopLayout' || null;
      const layoutSelected = '$layoutSelected' || 'alignTwoCols';
      const blockTitleSize = '$blockTitleSize';
      let shortcodeBuyButtonIds = '$shortcodeBuyButton'.split(',') == '' ? [] : '$shortcodeBuyButton'.split(',');
      let shortcodeSingleIds = '$shortcodeSingle'.split(',') == '' ? [] : '$shortcodeSingle'.split(',');
      let shortcodeTwoIds = '$shortcodeTwo'.split(',') == '' ? [] : '$shortcodeTwo'.split(',');
      let shortcodeCarouselIds = '$shortcodeCarousel'.split(',') == '' ? [] : '$shortcodeCarousel'.split(',');
      let shortcodeShopIds = '$shortcodeShop'.split(',') == '' ? [] : '$shortcodeShop'.split(',');
      let selectedIds = [];
      if (prevSelectedBlock === 'previewBuyButton') {selectedIds = shortcodeBuyButtonIds}
      if (prevSelectedBlock === 'previewSingleProduct') {selectedIds = shortcodeSingleIds}
      if (prevSelectedBlock === 'previewTwoProducts') {selectedIds = shortcodeTwoIds}
      if (prevSelectedBlock === 'previewCarousel') {selectedIds = shortcodeCarouselIds}
      if (prevSelectedBlock === 'previewShop') {selectedIds = shortcodeShopIds}
    </script>";

    $helpCenterBox = '
      <div class="box" style="color:#6603E5;background-color:#4EF1C9">
        <div class="box-header">
          <svg width="16" height="22" viewBox="0 0 16 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.3589 18.7369C10.4424 19.3223 10.0385 19.8655 9.45393 19.9539L7.77793 20.3169C7.40867 20.4516 6.99481 20.3593 6.71771 20.0806C6.44061 19.8018 6.35081 19.3874 6.48766 19.019C6.62451 18.6505 6.96305 18.3952 7.35493 18.3649L9.02993 18.0029C9.59925 17.8419 10.1921 18.1693 10.3589 18.7369V18.7369Z" stroke="#6603E5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.77783 15.3252C1.67326 15.3252 0.777832 14.4298 0.777832 13.3252V10.3252C0.777832 9.22063 1.67326 8.3252 2.77783 8.3252C3.8824 8.3252 4.77783 9.22063 4.77783 10.3252V13.3252C4.77783 14.4298 3.8824 15.3252 2.77783 15.3252Z" stroke="#6603E5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.7778 15.3252C11.6733 15.3252 10.7778 14.4298 10.7778 13.3252V10.3252C10.7778 9.22063 11.6733 8.3252 12.7778 8.3252C13.8824 8.3252 14.7778 9.22063 14.7778 10.3252V13.3252C14.7778 14.4298 13.8824 15.3252 12.7778 15.3252Z" stroke="#6603E5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14.0278 10.333C14.0278 10.7472 14.3636 11.083 14.7778 11.083C15.192 11.083 15.5278 10.7472 15.5278 10.333H14.0278ZM0.027832 10.333C0.027832 10.7472 0.363618 11.083 0.777832 11.083C1.19205 11.083 1.52783 10.7472 1.52783 10.333H0.027832ZM15.5278 13.3353C15.5291 12.9211 15.1943 12.5843 14.7801 12.583C14.3659 12.5818 14.0291 12.9165 14.0278 13.3307L15.5278 13.3353ZM9.28912 19.2281C8.88489 19.3185 8.6305 19.7195 8.72092 20.1237C8.81134 20.5279 9.21232 20.7823 9.61655 20.6919L9.28912 19.2281ZM15.5278 10.333V8.33301H14.0278V10.333H15.5278ZM15.5278 8.33301C15.5278 4.0528 12.058 0.583008 7.77783 0.583008V2.08301C11.2296 2.08301 14.0278 4.88123 14.0278 8.33301H15.5278ZM7.77783 0.583008C3.49763 0.583008 0.027832 4.0528 0.027832 8.33301H1.52783C1.52783 4.88123 4.32605 2.08301 7.77783 2.08301V0.583008ZM0.027832 8.33301V10.333H1.52783V8.33301H0.027832ZM14.0278 13.3307C14.0192 16.1619 12.052 18.6101 9.28912 19.2281L9.61655 20.6919C13.0631 19.921 15.5171 16.867 15.5278 13.3353L14.0278 13.3307Z" fill="#6603E5"/>
          </svg>
          <div style="font-weight:bold;margin-left:15px;">Help Center</div>
        </div>
        <p>
          Check out our step-by-step help center to get started
        </p>
        <div>
          <a class="box-button" href="https://support.reachu.io/" target="_blank" rel="noopener noreferrer">
            <span style="margin-right:8px;">Help Center</span>
            <svg width="9" height="12" viewBox="0 0 16 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M10.3589 18.7369C10.4424 19.3223 10.0385 19.8655 9.45393 19.9539L7.77793 20.3169C7.40867 20.4516 6.99481 20.3593 6.71771 20.0806C6.44061 19.8018 6.35081 19.3874 6.48766 19.019C6.62451 18.6505 6.96305 18.3952 7.35493 18.3649L9.02993 18.0029C9.59925 17.8419 10.1921 18.1693 10.3589 18.7369V18.7369Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M2.77783 15.3252C1.67326 15.3252 0.777832 14.4298 0.777832 13.3252V10.3252C0.777832 9.22063 1.67326 8.3252 2.77783 8.3252C3.8824 8.3252 4.77783 9.22063 4.77783 10.3252V13.3252C4.77783 14.4298 3.8824 15.3252 2.77783 15.3252Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M12.7778 15.3252C11.6733 15.3252 10.7778 14.4298 10.7778 13.3252V10.3252C10.7778 9.22063 11.6733 8.3252 12.7778 8.3252C13.8824 8.3252 14.7778 9.22063 14.7778 10.3252V13.3252C14.7778 14.4298 13.8824 15.3252 12.7778 15.3252Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M14.0278 10.333C14.0278 10.7472 14.3636 11.083 14.7778 11.083C15.192 11.083 15.5278 10.7472 15.5278 10.333H14.0278ZM0.027832 10.333C0.027832 10.7472 0.363618 11.083 0.777832 11.083C1.19205 11.083 1.52783 10.7472 1.52783 10.333H0.027832ZM15.5278 13.3353C15.5291 12.9211 15.1943 12.5843 14.7801 12.583C14.3659 12.5818 14.0291 12.9165 14.0278 13.3307L15.5278 13.3353ZM9.28912 19.2281C8.88489 19.3185 8.6305 19.7195 8.72092 20.1237C8.81134 20.5279 9.21232 20.7823 9.61655 20.6919L9.28912 19.2281ZM15.5278 10.333V8.33301H14.0278V10.333H15.5278ZM15.5278 8.33301C15.5278 4.0528 12.058 0.583008 7.77783 0.583008V2.08301C11.2296 2.08301 14.0278 4.88123 14.0278 8.33301H15.5278ZM7.77783 0.583008C3.49763 0.583008 0.027832 4.0528 0.027832 8.33301H1.52783C1.52783 4.88123 4.32605 2.08301 7.77783 2.08301V0.583008ZM0.027832 8.33301V10.333H1.52783V8.33301H0.027832ZM14.0278 13.3307C14.0192 16.1619 12.052 18.6101 9.28912 19.2281L9.61655 20.6919C13.0631 19.921 15.5171 16.867 15.5278 13.3353L14.0278 13.3307Z" fill="white"/>
            </svg>
          </a>
        </div>
      </div>    
    ';

    if ($shopTextSelected) { 
      $logoContent = '<span id="saved-text-shop">'.$shopTextSelected.'</span>';
    } else {
      $logoContent = '<img id="image-preview_shop" src="' . wp_get_attachment_url( get_option( 'outshifter_blocks_supplier_logo_shop' ) ) . '" style="max-height: 30px; max-width: 70px;">';
    }

    if ($mixPanel) {
      $mixPanelContent = '<div class="reset-container">
        <div>
          <input disabled id="outshifter-mixpanel" value="'. $mixPanel . '">
        </div>
        <div>
          <button id="outshifter-mixpanel-disconnect" class="upload-logo-button mixpanel-remove-button">Reset Mixpanel</button>
        </div>
      </div>';
    } else {
      $mixPanelContent = '<input id="outshifter-mixpanel" type="text" name="mixpanel" placeholder="Enter your mixpanel Api key here">';
    }

    if ($stripeKey) {
      $stripeKeyContent = '<div class="reset-container">
        <div>
          <input disabled id="outshifter-stripe-key" value="'. $stripeKey . '">
        </div>
        <div>
          <button id="outshifter-stripe-key-disconnect" class="upload-logo-button mixpanel-remove-button">Reset Stripe Key</button>
        </div>
      </div>';
    } else {
      $stripeKeyContent = '<input id="outshifter-stripe-key" type="text" name="stripe-key" placeholder="Enter your Stripe Key here">';
    }

    if ($stripeId) {
      $stripeIdContent = '<div class="reset-container">      
        <div>
          <input disabled id="outshifter-stripe-id" value="'. $stripeId . '">
        </div>
        <div>
          <button id="outshifter-stripe-id-disconnect" class="upload-logo-button mixpanel-remove-button">Reset Stripe Id</button>
        </div>
      </div>'
      ;
    } else {
      $stripeIdContent = '<input id="outshifter-stripe-id" type="text" name="stripe-id" placeholder="Enter your Stripe Id here">';
    }

    if ($gAnalytics) {
      $gAnalyticsContent = '<div class="reset-container">
        <div>
          <input disabled id="outshifter-g-analytics" value="'. $gAnalytics . '">
        </div>
        <div>
          <button id="outshifter-g-analytics-disconnect" class="upload-logo-button mixpanel-remove-button">Reset Google Tracker Id</button>
        </div>
      </div>';
    } else {
      $gAnalyticsContent = '<input id="outshifter-g-analytics" type="text" name="g-analytics" placeholder="G-XXXXXXX">';
    }

    if ($connectError) {
      $contentError = '<div class="notice notice-error is-dismissible">'.
        '<p><strong>' . $connectError . '</strong></p>'.
      '</div>';
    }

    if ($isSaved) {
      $contentIsSaved = '<div class="notice notice-success is-dismissible" >'.
        '<p><strong>' . $isSaved . '</strong></p>'.
      '</div>';
    }

    if ($sellerId) {

      $options = '';
      foreach ($currencies as $curr) {
        $options .= '<option value="' . $curr['currency_code'] . '" ' . (($curr['currency_code'] === $currency) ? 'selected' : '') . '>' . $curr['currency_code'] . ' -</option>';
      }

      $content = '<div id="outshifter-admin-content" class="animate-bottom">

        <div class="outshifter-admin-header">
          <div>
            <img
              src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/reachu-logo-full.svg' . '"
              alt="Logo"
              style="width: 125px;"
            />
          </div>
          <div class="reachu-admin-header-user">
            <div style="display:flex;">
              <img
                src="' . $sellerAvatar . '"
                alt="Avatar"
                style="width: 45px;border-radius:50%"
              />
            </div>
            <div>
              <div style="font-weight:600;">'. $sellerName .' '. $sellerName . '</div>
              <div>'. $sellerEmail .'</div>
            </div>
          </div>
        </div>

        <div class="outshifter-admin-content-container">
          <div>

            <div class="reachu-admin-content-header">
              <div>
                <div id="notice-is-saved" class="animate-is-saved is-saved-container">'
                  . $contentIsSaved .
                '</div>
                <div class="connection-checked">
                  <svg width="15" height="15" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4.4318 6.36358L6.02271 7.95449L9.20453 4.77267M12.1212 6.36358C12.1212 9.29236 9.74695 11.6666 6.81817 11.6666C3.88938 11.6666 1.51514 9.29236 1.51514 6.36358C1.51514 3.43479 3.88938 1.06055 6.81817 1.06055C9.74695 1.06055 12.1212 3.43479 12.1212 6.36358Z" stroke="#067647" stroke-width="1.06061" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span class="connection-text">Connected</span>
                </div>
                <div class="disconnect-button" style="margin-top:10px;">
                  <button id="outshifter-btn-disconnect">Disconnect</button>
                </div>
              </div>
              <div>
                <button id="outshifter-btn-save-settings">Save</button>
              </div>
            </div>'.

            /* Tabs menú */
            '<div id="tabs-system">'.
              '<ul class="tabs-menu" id="tabs-header">'.
                  /*class "is-active marks initially opened tab-content". data-target holds the id of the target tab-content */
                  '<li class="is-active" data-target="first-tab"><a>General</a></li>'.
                  '<li data-target="second-tab"><a>Logo</a></li>'.
                  '<li data-target="third-tab"><a>Styles</a></li>'.
                  '<li style="display:none;" data-target="fourth-tab"><a>Layout</a></li>'.
                  '<li data-target="fifth-tab" class="have-gutemberg '. $notGutemberg .'"><a>Shortcodes</a></li>'.
              '</ul>'.
            '</div>'.
            /* Select currency & add keys */
            '<div style="margin-top:52px;">'.
              '<div class="tab-content container-tabs" id="first-tab">'.

                '<div class="select-switch">'.
                  '<label class="switch reachu-switch" style="margin-left:0px;">'.
                    '<input type="checkbox" name="not-gutemberg" value="notGutemberg" id="not-gutemberg" '. (("notGutemberg" === $notGutemberg) ? 'checked' : '') .' />'.
                    '<span class="slider round"></span>'.
                  '</label>'.
                  '<div>
                    Gutemberg is not implemented on this WordPress
                  </div>'.
                '</div>'.
                '<div class="select-container">'.
                  '<label for="outshifter-admin-select">Default currency</label>'.
                  '<span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>'.
                  '<select id="outshifter-select-currency" class="outshifter-admin-select" required>'.
                    $options.
                  '</select>'.
                '</div>'.
                '<div class="select-container">'.
                  '<div id="mixpanel-container">'.
                    '<label for="outshifter-mixpanel">Mixpanel Api Key</label>'.
                    $mixPanelContent.
                  '</div>'.
                '</div>'.
                '<div class="select-container">'.
                  '<div id="mixpanel-container">'.
                    '<label for="outshifter-stripe-id">Google Analytics Tracker Id</label>'.
                    $gAnalyticsContent.
                  '</div>'.
                '</div>'.
              '</div>'.
              /* Upload logo */
              '<div class="tab-content container-tabs" id="second-tab">'.
                '<div class="tab-select-logo">'. 
                  '<div class="upload-logo">'.
                    '<label for="image_attachment_id">Brand Logo</label>'.
                    '<div class="image-preview-wrapper">'.
                      '<img id="image-preview" src="' . wp_get_attachment_url( get_option( 'outshifter_blocks_supplier_logo' ) ) . '" style="max-height: 30px; max-width: 130px;">'.
                    '</div>'.
                    '<input id="upload_image_button" type="button" class="upload-logo-button" value="Upload Logo" />'.
                    '<input type="hidden" name="image_attachment_id" id="image_attachment_id" value="' . get_option( 'outshifter_blocks_supplier_logo' ) . '">'.
                  '</div>'.
                  '<div class="upload-logo">'.
                    '<label for="image_attachment_id">Brand Logo White</label>'.
                    '<div class="image-preview-wrapper-white">'.
                      '<img id="image-preview_white" src="' . wp_get_attachment_url( get_option( 'outshifter_blocks_supplier_logo_white' ) ) . '" style="max-height: 30px; max-width: 130px;">'.
                    '</div>'.
                    '<input id="upload_image_button_white" type="button" class="upload-logo-button" value="Upload White Logo" />'.
                    '<input type="hidden" name="image_attachment_id_white" id="image_attachment_id_white" value="' . get_option( 'outshifter_blocks_supplier_logo_white' ) . '">'.
                  '</div>'.
                '</div>'.
              '</div>'.
              /* Shop page background color & custom font family */
              '<div class="tab-content container-tabs" id="third-tab">
                <div class="select-container select-color">
                  <label for="my-color-field" class="select-color">
                    Select Shop Page background color:
                  </label>
                  <input type="text" value="' . $shopColor . '" class="my-color-field" id="my-color-field" data-default-color="#fff" />
                </div>
                <div class="select-container">
                  <label for="outshifter-admin-select" id="select-bg-color">Select font family</label>
                  <span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>
                  <select id="outshifter-select-font" class="outshifter-admin-select" required>
                    <option value="Default" '. (('Default' === $fontSelected) ? 'selected' : '') .'>Default site font -</option>
                    <option value="Playfair" '. (('Playfair' === $fontSelected) ? 'selected' : '') .'>Playfair -</option>
                    <option value="Poppins" '. (('Poppins' === $fontSelected) ? 'selected' : '') .'>Poppins -</option>
                    <option value="Roboto" '. (('Roboto' === $fontSelected) ? 'selected' : '') .'>Roboto -</option>
                    <option value="Open Sans" '. (('Open Sans' === $fontSelected) ? 'selected' : '') .'>Open Sans -</option>
                  </select>
                </div>
                <div class="select-container blocks-title-styles">
                  <div class="admin-section-title">
                    Product Card Content
                  </div>
                  <div class="select-switch">
                    <label class="switch reachu-switch">
                      <input type="checkbox" name="show-card-title" value="showCardTitle" id="show-card-title" '. (("showCardTitle" === $showCardTitle || false === $showCardTitle) ? 'checked' : '') .' />
                      <span class="slider round"></span>
                    </label>
                    <div>
                      Product Name
                    </div>
                  </div>
                  <div class="select-switch">
                    <label class="switch reachu-switch">
                      <input type="checkbox" name="show-card-price" value="showCardPrice" id="show-card-price" '. (("showCardPrice" === $showCardPrice || false === $showCardPrice) ? 'checked' : '') .' />
                      <span class="slider round"></span>
                    </label>
                    <div>
                      Product price
                    </div>
                  </div>
                  <div class="select-switch">
                    <label class="switch reachu-switch">
                      <input type="checkbox" name="show-card-supplier" value="showCardSupplier" id="show-card-supplier" '. (("showCardSupplier" === $showCardSupplier || false === $showCardSupplier) ? 'checked' : '') .' />
                      <span class="slider round"></span>
                    </label>
                    <div>
                      Product supplier
                    </div>
                  </div>
                  <div class="select-switch">
                    <label class="switch reachu-switch">
                      <input type="checkbox" name="show-card-button" value="showCardButton" id="show-card-button" '. (("showCardButton" === $showCardButton || false === $showCardButton) ? 'checked' : '') .' />
                      <span class="slider round"></span>
                    </label>
                    <div>
                      Buy button
                    </div>
                  </div>
                </div>
                <div class="select-container blocks-title-styles" style="margin-top:15px;">
                  <div class="admin-section-title">Blocks title styles</div>
                  <div>
                    <div class="select-button-ratio" style="margin-bottom:15px;">
                      <label id="label-select-title-size" for="select-title-size">
                        Title font size:
                      </label>
                      <input type="range" id="select-title-size" min="0" max="100" step="1" value="'. $blockTitleSize .'" style="margin: 0 5px 0 30px"/>
                      <span class="demo" style="margin-bottom: 0"></span>
                    </div>
                  </div>
                  <form class="radio-group">
                    <span class="section-subitem">Title alignment: </span>
                    <div class="radio-controls">
                      <label style="margin-bottom:0px;">
                        <input type="radio" name="blocksTitleAlignment" value="alignLeft" '. (('alignLeft' === $blocksTitleAlignment) ? 'checked' : '') .'/> 
                        <svg class="icon-title-left" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/></svg>
                      </label>
                      <label style="margin-bottom:0px;">
                        <input type="radio" name="blocksTitleAlignment" value="alignCenter" '. (('alignCenter' === $blocksTitleAlignment) ? 'checked' : '') .'/> 
                        <svg class="icon-title-center" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/></svg>                        
                      </label>
                      <label style="margin-bottom:0px;">
                        <input type="radio" name="blocksTitleAlignment" value="alignRight" '. (('alignRight' === $blocksTitleAlignment) ? 'checked' : '') .'/> 
                        <svg class="icon-title-right" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z"/></svg>                        
                      </label>
                    </div>
                  </form>
                </div>'.
                /* Checkout buttons style */
                '<hr style="margin-top:50px;margin-button:25px;" />'.
                '<div class="custom-checkout-buttons-container">'.
                  '<div class="preview-checkout-buttons">
                    <span class="admin-section-title">Action buttons</span>
                    <div class="preview-buttons-container">
                      <div class="preview-button-white">
                        <svg class="prev-button-icon" height="48" viewBox="0 0 48 48" width="48" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h48v48h-48z" fill="none"/><path d="M38 12h-4c0-5.52-4.48-10-10-10s-10 4.48-10 10h-4c-2.21 0-3.98 1.79-3.98 4l-.02 24c0 2.21 1.79 4 4 4h28c2.21 0 4-1.79 4-4v-24c0-2.21-1.79-4-4-4zm-14-6c3.31 0 6 2.69 6 6h-12c0-3.31 2.69-6 6-6zm0 20c-5.52 0-10-4.48-10-10h4c0 3.31 2.69 6 6 6s6-2.69 6-6h4c0 5.52-4.48 10-10 10z"/></svg>
                        <span>Add to cart</span>
                      </div>
                      <div class="preview-button-black" id="preview-button-black">
                        <span>Buy Now</span>
                      </div>
                    </div>
                    <div class="reset-preview-container">
                      <input id="reset-checkout-buttons" type="button" class="reset-checkout-buttons" value="Reset buttons styles" />
                    </div>
                 </div>
                <div class="custom-checkout-buttons">
                  <div class="buttons-title">
                    <div>
                      "Add to cart" button
                    </div>
                    <div>
                      "Buy" button
                    </div>
                  </div>
                  <div>
                  <div class="buttons-title">
                    <div class="select-container">
                      <label for="outshifter-admin-select" id="select-bg-color">Style type</label>
                      <span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>
                      <select id="outshifter-select-button-prev-type" class="outshifter-admin-select" required>
                        <option value="filled" '. (('filled' === $buttonPrevType) ? 'selected' : '') .'>Filled -</option>
                        <option value="outlined" '. (('outlined' === $buttonPrevType) ? 'selected' : '') .'>Outlined -</option>
                      </select>
                    </div>
                    <div class="select-container">
                      <label for="outshifter-admin-select" id="select-bg-color">Style type</label>
                      <span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>
                      <select id="outshifter-select-button-next-type" class="outshifter-admin-select" required>
                        <option value="filled" '. (('filled' === $buttonNextType) ? 'selected' : '') .'>Filled -</option>
                        <option value="outlined" '. (('outlined' === $buttonNextType) ? 'selected' : '') .'>Outlined -</option>
                      </select>
                    </div>
                  </div>
                  </div>
                  <div class="buttons-background-color">
                    <div class="select-container select-color custom-bg-btn-prev">
                      <label for="button-prev-color-field" class="select-color">
                        Primary color:
                      </label>
                      <input type="text" value="' . $buttonPrevColor . '" class="button-prev-color-field" id="button-prev-color-field" data-default-color="#fff" />
                    </div>
                    <div class="select-container select-color custom-bg-btn-next">
                      <label for="button-color-field" class="select-color">
                        Primary color:
                      </label>
                      <input type="text" value="' . $buttonNextColor . '" class="button-color-field" id="button-color-field" data-default-color="#000" />
                    </div>
                  </div>
                  <div class="buttons-text-color">'.
                    '<div class="select-container select-color custom-text-color-prev">'.
                      '<label for="button-prev-text-color-field" class="select-color">'.
                        'Secondary Color:'.
                      '</label>'.
                      '<input type="text" value="' . $buttonPrevTextColor . '" class="button-prev-text-color-field" id="button-prev-text-color-field" data-default-color="#000" />'.
                    '</div>'.
                    '<div class="select-container select-color custom-text-color-next">'.
                      '<label for="button-text-color-field" class="select-color">'.
                        'Secondary color:'.
                      '</label>'.
                      '<input type="text" value="' . $buttonNextTextColor . '" class="button-text-color-field" id="button-text-color-field" data-default-color="#fff" />'.
                    '</div>'.
                  '</div>'.
                  '<div>'.
                    '<div class="select-container select-color" style="padding-top:25px">'.
                      '<div class="select-button-ratio">'.
                        '<label id="label-select-border-ratio" for="select-border-ratio">Buttons border radius:</label>'.
                        '<input type="range" id="select-border-ratio" min="0" max="20" step="1" value="'. $buttonBorderRatio .'" />'.
                        '<span class="demo"></span>'.
                      '</div>'.
                    '</div>'.
                  '</div>'.
                '</div>'.
                '</div>'.
                /* Custom shop icon & shop btn */
                '<hr style="margin-top:50px;margin-bottom:50px;" />'.
                '<div class="custom-shop-icon">'.
                  '<div>
                    <div class="admin-section-title">Shop Icon</div>
                    <span class="section-subitem">Brandname:</span>
                    <div class="text-logo-selectors">
                      <div class="text-logo-group">
                        <div class="shop-brand-logo-selected">
                          <label for="shop-img-selected">Show logo</label>
                          <input type="radio" name="shopLogoSelected" value="shopImgSelected" '. (('shopImgSelected' === $shopLogoSelected) ? 'checked' : '') .' id="shop-img-selected">
                        </div>
                        <div class="shop-brand-text-selected">
                          <label for="shop-text-selected">Show text</input>
                          <input type="radio" name="shopLogoSelected" value="shopTextSelected" '. (('shopTextSelected' === $shopLogoSelected) ? 'checked' : '') .' id="shop-text-selected">    
                          </label>
                        </div>
                      </div>
                      <div class="upload-logo upload-logo-btn-shop">
                        <input id="upload_image_button_shop" type="button" class="upload_image_button_shop" value="Select Logo" />
                        <input type="hidden" name="image_attachment_id_shop" id="image_attachment_id_shop" value="' . get_option( 'outshifter_blocks_supplier_logo_shop' ) . '">
                      </div>
                      <div>
                        <input value="' .$shopTextSelected. '" id="input-text-shop" class="input-text-shop" type="text" name="input-text-shop" placeholder="Add brandmane">
                      </div>
                    </div>
                    <div class="select-container select-color select-button-shop-color">
                      <label for="button-shop-color" class="select-color">
                        Select button Shop Color:
                      </label>
                      <input type="text" value="'. $shopButtonColor .'" class="button-shop-color" id="button-shop-color" data-default-color="#000" />
                    </div>
                    <div class="select-container select-color select-button-shop-color">
                      <label for="text-icon-color" class="select-color">
                        Select text and shop icon color:
                      </label>
                      <input type="text" value="'. $textIconColor .'" class="text-icon-color" id="text-icon-color" data-default-color="#fff" />
                    </div>
                    <div class="select-button-ratio">
                      <span class="section-subitem">Button border radius:</span>
                      <input type="range" id="shop-button-ratio" min="0" max="15" step="1" value="'. $shopButtonRatio .'" />
                      <span class="demo"></span>
                    </div>
                    <div class="select-switch">
                      <label class="switch reachu-switch" style="margin-left:0px;">
                        <input type="checkbox" name="show-shop-icon" value="hideIcon" id="show-shop-icon" '. (("hideIcon" === $showShopIcon) ? 'checked' : '') .' />
                        <span class="slider round"></span>
                      </label>
                      <div>
                        Hide shop icon
                      </div>
                    </div>
                    <div class="select-switch">
                      <label class="switch reachu-switch">
                        <input type="checkbox" value="addShopUrl" name="shop-custom-url" id="shop-custom-url" '. (("addShopUrl" === $addShopUrl) ? 'checked' : '') .'  />
                        <span class="slider round"></span>
                      </label>
                      <div>
                        Add shop url
                      </div>
                      <div>
                        <input value="' .$shopCustomUrl. '" id="input-url-shop" class="input-url-shop" type="text" name="input-url-shop" placeholder="www.mystore.com" />
                      </div>
                    </div>
                  </div>'.
                  '<div class="shop-button-preview select-container">
                    <div class="admin-section-title">Preview</div>
                    <div class="btn-shop-preview-container">
                      <div class="image-preview-wrapper-shop">
                        '. $logoContent .'
                        <span class="shop-default-text">Shop</span>
                        <svg class="image-shop-cart-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#FFFFFF"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                      </div>
                      <div class="reset-preview-shop">
                        <input id="reset-shop-button" type="button" class="reset-shop-button" value="Reset button styles" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>'.
              /* Layout & modal alignment */
              '<div class="tab-content container-tabs" id="fourth-tab">
                <div class="container-radio">
                  <form id="radio-group">
                    <div id="container-cols">
                      <span>Select Product Card Text Alignment</span>
                      <hr />
                      <div id="radio-cols">
                        <label>
                          <input type="radio" name="layoutSelected" value="alignTwoCols" '. (('alignTwoCols' === $layoutSelected) ? 'checked' : '') .'/>
                          Default
                          <img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/two-cols.png' . '" alt="align two columns" />
                        </label>
                        <label>
                          <input type="radio" name="layoutSelected" value="alignCenter" '. (('alignCenter' === $layoutSelected) ? 'checked' : '') .'/>
                          Align center
                          <img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/center.png' . '" alt="align center" />
                        </label>
                        <label>
                          <input type="radio" name="layoutSelected" value="alignLeft" '. (('alignLeft' === $layoutSelected) ? 'checked' : '') .'/>
                          Align left
                          <img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/left-column.png' . '" alt="align left column" />
                        </label>
                      </div>
                    </div>
                    <div id="container-cols">
                      <span>Select Modal Placement</span>
                      <hr />
                      <div id="radio-cols">
                        <label>
                            <input type="radio" name="modalPosition" value="modalRight" '. (('modalRight' === $modalPosition) ? 'checked' : '') .'/>
                            Default
                            <img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/modal-right.png' . '" alt="modal align right" />
                        </label>
                        <label>
                            <input type="radio" name="modalPosition" value="modalCenter" '. (('modalCenter' === $modalPosition) ? 'checked' : '') .'/>
                            Align center
                            <img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/modal-center.png' . '" alt="modal align center" />
                        </label>
                      </div>
                    </div>
                  </form>
                </div>
              </div>'.
              // Shortcodes Tab
              '<div class="tab-content container-tabs" id="fifth-tab">
                <div class="tab-shortcode-container"><div>
                <div class="select-switch" style="align-items:start;">
                  <label class="switch reachu-switch" style="margin-left:0px;">
                    <input type="checkbox" name="allow-media" value="allowUploadToMedia" id="allow-media" '. (("allowUploadToMedia" === $allowUploadToMedia) ? 'checked' : '') .' />
                    <span class="slider round"></span>
                  </label>
                  <div>
                    <div style="display:flex;">
                      <div style="font-size: 18px;font-weight: 500;color: #1d2327;">
                        Upload product images to the media library
                      </div>
                      <button id="btn-save-reset-media-upload">
                        Save & Reset
                      </button>
                    </div>
                    <p style="margin-left:0px;line-height:1.5;">
                      Activating this feature will trigger that the cover product image is uploaded to the media library each time a new shortcode is saved.
                      </br>
                      This provides a faster loading of the product images on the blocks (better page speed and user experience).
                    </p>
                  </div>'.
                '</div>'.
                '<div class="shortcode-editor-container">'.
                  '<div class="creating-shortcode creating-shortcode-single">
                    <div class="loader"></div>
                    <p>Creating shortcode...</p>
                  </div>'.
                  '<div class="select-container">'.
                    '<div class="select-shortcode-container">'.
                      '<label for="outshifter-admin-select">Shortcode editor</label>
                      <span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>
                      <select style="margin-top:0px;" id="outshifter-create-shortcode" class="outshifter-admin-select" required>
                        <option value=null '. (('null' === $createShortcode) ? 'selected' : '') .' disabled selected>Select block to create</option>
                        <option value="previewSingleProduct" '. (('previewSingleProduct' === $createShortcode) ? 'selected' : '') .'>Single product -</option>
                        <option value="previewTwoProducts" '. (('previewTwoProducts' === $createShortcode) ? 'selected' : '') .'>Two products -</option>
                        <option value="previewCarousel" '. (('previewCarousel' === $createShortcode) ? 'selected' : '') .'>Carousel -</option>
                        <option value="previewShop" '. (('previewShop' === $createShortcode) ? 'selected' : '') .'>Shop -</option>
                        <option value="previewBuyButton" '. (('previewBuyButton' === $createShortcode) ? 'selected' : '') .'>Action button -</option>
                      </select>
                      <div id="select-action-button-container">
                        <label for="outshifter-admin-select">Action Button Type</label>
                        <span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>
                        <select id="select-action-button" class="outshifter-admin-select select-shop-layout" required>
                          <option value=null '. (('null' === $shortcodeShopLayout) ? 'selected' : '') .' disabled selected>Select Action Button Type</option>
                          <option value="previewBuyButton" '. (('previewBuyButton' === $shortcodeShopLayout) ? 'selected' : '') .'>Buy Button -</option>
                          <option value="previewAddToCartButton" '. (('previewAddToCartButton' === $shortcodeShopLayout) ? 'selected' : '') .'>Add To cart Button -</option>
                        </select>
                      </div>
                      <div id="select-shop-layout-container">
                        <label for="outshifter-admin-select">Shop Layout</label>
                        <span class="select-icon"><img src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/arrow_drop_down.svg' . '" alt="select icon"></span>
                        <select id="select-shop-layout" class="outshifter-admin-select select-shop-layout" required>
                          <option value=null '. (('null' === $shortcodeShopLayout) ? 'selected' : '') .' disabled selected>Select Shop Layout</option>
                          <option value="previewMasonryLayout" '. (('previewMasonry' === $shortcodeShopLayout) ? 'selected' : '') .'>Masonry -</option>
                          <option value="previewShopLayout" '. (('previewShop' === $shortcodeShopLayout) ? 'selected' : '') .'>Shop -</option>
                        </select>
                      </div>
                      <div>
                        <button type="button" data-open="modal1" id="modal-select-product" class="upload-logo-button mixpanel-remove-button open-modal">
                          Select product
                        </button>
                      </div>
                      <div class="modal" id="modal1">
                        <div class="modal-dialog">
                          <header class="modal-header">
                            <span>Choose products</span>
                            <button class="close-modal" aria-label="close modal" data-close>
                              ✕  
                            </button>
                          </header>
                          <div class="search-select-products">
                            <div>
                              <div class="modal-search">
                                <input
                                  type="text"
                                  placeholder="Search product"
                                  name="searchProduct"
                                  id="searchProduct"
                                  oninput="searchProducts()"
                                />
                                <div class="search-icon-container">
                                  <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M500.3 443.7l-119.7-119.7c27.22-40.41 40.65-90.9 33.46-144.7C401.8 87.79 326.8 13.32 235.2 1.723C99.01-15.51-15.51 99.01 1.724 235.2c11.6 91.64 86.08 166.7 177.6 178.9c53.8 7.189 104.3-6.236 144.7-33.46l119.7 119.7c15.62 15.62 40.95 15.62 56.57 0C515.9 484.7 515.9 459.3 500.3 443.7zM79.1 208c0-70.58 57.42-128 128-128s128 57.42 128 128c0 70.58-57.42 128-128 128S79.1 278.6 79.1 208z"/></svg>
                                </div>
                              </div>
                            </div>
                            <div>
                              <button id="btn-select-product">SAVE PRODUCTS</button>
                            </div>
                          </div>
                          <section class="modal-content">
                            <div id="product-info">
                              <div class="table-row">
                                <div class="table-col">
                                  Photo
                                </div>
                                <div class="table-col">
                                  Name
                                </div>
                                <div class="table-col">
                                  Brand
                                </div>
                                <div class="table-col">
                                  Commission
                                </div>
                                <div class="table-col">
                                  Category
                                </div>
                                <div class="table-col">
                                  Price
                                </div>
                                <div class="table-col">
                                  Select
                                </div>
                              </div>
                              <div id="table-products">
                              </div>
                            </div>
                          </section>
                        </div>
                      </div>'.
                      // Action Button
                      '<div class="shortcodes-container" id="shortcodes-container">
                        <div class="copy-shortcode shortcode-action-button" id="shortcode-action-button">
                          <h3>Action button shortcode</h3>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="update-title">Block title (optional)</label>
                              <input class="shortcode-name update-block-title" name="update-title" type="text" value="" placeholder="">
                            </div>
                          </div>
                        <div class="save-shortcode-container">
                          <div class="shortcode-input-container">
                            <label class="shortcode-label" for="shortcode-name">Save the shortcode</label>
                            <input class="shortcode-name shortcode-name-action-button" type="text" name="shortcode-name" placeholder="Enter the shortcode name reference here">
                          </div>
                          <div style="position:relative;">
                            <span class="text-shortcode-saved">Shortcode saved!</span>
                            <button data-ref="action-button" class="shortcode-btn save-shortcode-btn">
                              Save
                            </button>
                          </div>
                        </div>
                        <p class="shortcode-name-missing">Shortcode name is empty. Please provide one.</p>
                        <span class="shortcode-text-action-button" style="display:none;">
                          [reachu_action_button product-id="<span id="action-button-ids">'. $shortcodeBuyButton .'</span>" title="<span class="block-title-replace">Title Example</span>" type="<span id="action-button-replace">buy_button</span>"]
                        </span>
                        <div class="creating-shortcode creating-shortcode-action-button">
                          <div class="loader"></div>
                          <p>Creating shortcode...</p>
                        </div>
                        <div class="shortcode-box-container" style="display:none;">
                          <div class="shortcode-container" id="shortcode-action-button-container">
                            <div class="saved-shortcode-container"></div>
                          </div>
                          <div style="position:relative;">
                            <button onclick=copyToClipboard("shortcode-action-button-container") class="shortcode-btn copy-btn">
                              Copy
                            </button>
                            <span id="action-button-copied" class="text-copy-confirm">¡Copied!</span>
                          </div>
                        </div>
                      </div>'.
                      // Single
                      '<div class="shortcodes-container" id="shortcodes-container">
                        <div class="copy-shortcode shortcode-single" id="shortcode-single">
                          <h3>Single product shortcode</h3>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="update-title">Block title (optional)</label>
                              <input class="shortcode-name update-block-title" name="update-title" type="text" value="" placeholder="">
                            </div>
                          </div>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="shortcode-name">Save the shortcode</label>
                              <input class="shortcode-name shortcode-name-single" type="text" name="shortcode-name" placeholder="Enter the shortcode name reference here">
                            </div>
                            <div style="position:relative;">
                              <span class="text-shortcode-saved">Shortcode saved!</span>
                              <button data-ref="single" class="shortcode-btn save-shortcode-btn">
                                Save
                              </button>
                            </div>
                          </div>
                          <p class="shortcode-name-missing">Shortcode name is empty. Please provide one.</p>
                          <span class="shortcode-text-single" style="display:none;">
                            [reachu_single_product product-id="<span id="single-product-ids">'. $shortcodeSingle .'</span>" title="<span class="block-title-replace">Title Example</span>"]
                          </span>
                          <div class="creating-shortcode creating-shortcode-single">
                            <div class="loader"></div>
                            <p>Creating shortcode...</p>
                          </div>
                          <div class="shortcode-box-container" style="display:none;">
                            <div class="shortcode-container" id="shortcode-single-container">
                              <div class="saved-shortcode-container"></div>
                            </div>
                            <div style="position:relative;">
                              <button onclick=copyToClipboard("shortcode-single-container") class="shortcode-btn copy-btn">
                                Copy
                              </button>
                              <span id="single-copied" class="text-copy-confirm">¡Copied!</span>
                            </div>
                          </div>
                        </div>'.
                        // Two products 
                        '<div class="copy-shortcode shortcode-two" id="shortcode-two">
                          <h3>Two products shortcode</h3>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="update-title">Block title (optional)</label>
                              <input class="shortcode-name update-block-title" name="update-title" type="text" value="" placeholder="">
                            </div>
                          </div>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="shortcode-name">Save the shortcode</label>
                              <input class="shortcode-name shortcode-name-two" type="text" name="shortcode-name" placeholder="Enter the shortcode name here">
                            </div>
                            <div style="position:relative;">
                              <span class="text-shortcode-saved">Shortcode saved!</span>
                              <button data-ref="two" id="store-shortcode" class="shortcode-btn save-shortcode-btn">Save</button>
                            </div>
                          </div>
                          <p class="shortcode-name-missing" style="color: #E81D1D;">Shortcode name is empty. Please provide one.</p>
                          <span class="shortcode-text-two" style="display:none;">
                            [reachu_two_products product-ids="<span id="two-products-ids">'. $shortcodeTwo .'</span>" title="<span class="block-title-replace">Title Example</span>"]
                          </span>
                          <div class="creating-shortcode creating-shortcode-two">
                            <div class="loader"></div>
                            <p>Creating shortcode...</p>
                          </div>
                          <div class="shortcode-box-container" style="display:none;">
                            <div class="shortcode-container two-products-ids" id="two-products-ids-container">
                              <div class="saved-shortcode-container"></div>
                            </div>
                            <div style="position:relative;">
                              <button onclick=copyToClipboard("two-products-ids-container") class="shortcode-btn copy-btn">Copy</button>
                              <span id="two-copied" class="text-copy-confirm">¡Copied!</span>
                            </div>
                          </div>
                        </div>'.
                        // Carousel
                        '<div class="copy-shortcode shortcode-carousel" id="shortcode-carousel">
                          <h3>Carousel product shortcode</h3>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="update-title">Block title (optional)</label>
                              <input class="shortcode-name update-block-title" name="update-title" type="text" value="" placeholder="">
                            </div>
                          </div>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="shortcode-name">Save the shortcode</label>
                              <input class="shortcode-name shortcode-name-carousel" type="text" name="shortcode-name" placeholder="Enter the shortcode name here">
                            </div>
                            <div style="position:relative;">
                              <span class="text-shortcode-saved">Shortcode saved!</span>
                              <button data-ref="carousel" id="store-shortcode" class="shortcode-btn save-shortcode-btn">Save</button>
                            </div>
                          </div>
                          <p class="shortcode-name-missing" style="color: #E81D1D;">Shortcode name is empty. Please provide one.</p>
                          <span class="shortcode-text-carousel" style="display:none;">
                            [reachu_carousel product-ids="<span id="carousel-ids">'. $shortcodeCarousel .'</span>" title="<span class="block-title-replace">Title Example</span>"]
                          </span>
                          <div class="creating-shortcode creating-shortcode-carousel">
                            <div class="loader"></div>
                            <p>Creating shortcode...</p>
                          </div>
                          <div class="shortcode-box-container" style="display:none;">
                            <div class="shortcode-container shortcode-carousel-container" id="carousel-ids-container">
                              <div class="saved-shortcode-container"></div>
                            </div>
                            <div style="position:relative;">
                              <button onclick=copyToClipboard("carousel-ids-container") class="shortcode-btn copy-btn">Copy</button>
                              <span id="carousel-copied" class="text-copy-confirm">¡Copied!</span>
                            </div>
                          </div>
                        </div>'.
                        // Shop
                        '<div class="copy-shortcode shortcode-shop" id="shortcode-shop">
                          <h3>Shop shortcode</h3>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="update-title">Block title (optional)</label>
                              <input class="shortcode-name update-block-title" name="update-title" type="text" value="" placeholder="">
                            </div>
                          </div>
                          <div class="save-shortcode-container">
                            <div class="shortcode-input-container">
                              <label class="shortcode-label" for="shortcode-name">Save the shortcode</label>
                              <input class="shortcode-name shortcode-name-shop" type="text" name="shortcode-name" placeholder="Enter the shortcode name here">
                            </div>
                            <div style="position:relative;">
                              <span class="text-shortcode-saved">Shortcode saved!</span>
                              <button data-ref="shop" class="shortcode-btn save-shortcode-btn">Save</button>
                            </div>
                          </div>
                          <p class="shortcode-name-missing" style="color: #E81D1D;">Shortcode name is empty. Please provide one.</p>
                          <span class="shortcode-text-shop" style="display:none;">
                            [reachu_shop product-ids="<span id="shop-ids">'. $shortcodeShop .'</span>" title="<span class="block-title-replace">Title Example</span>" layout="<span id="shop-layout-replace">masonry</span>"]
                          </span>
                          <div class="creating-shortcode creating-shortcode-shop">
                            <div class="loader"></div>
                            <p>Creating shortcode...</p>
                          </div>
                          <div class="shortcode-box-container" style="display:none;">
                            <div class="shortcode-container shortcode-shop-container" id="shop-ids-container">
                              <div class="saved-shortcode-container"></div>
                            </div>
                            <div style="position:relative;">
                              <button onclick=copyToClipboard("shop-ids-container") class="shortcode-btn copy-btn">Copy</button>
                              <span id="shop-copied" class="text-copy-confirm">¡Copied!</span>
                            </div>
                          </div>
                          <textarea style="display: none;" id="copyTextarea"></textarea>
                        </div>
                      </div>
                    </div>             
                    <div class="blocks-preview">
                      <div style="display:none">
                        <pre id="obj-selected-products"></pre>
                      </div>'.

                      // Single Product Preview
                      '<div id="preview-single-product">
                        <h3 class="block-name">Single Product Preview</h3>
                        <div class="single-product-container">
                          <div class="header-preview">
                            <div
                              class="block-title-container ' . $blocksTitleAlignment . '"
                              style="font-size:' . $blockTitleSize . 'px"
                            >
                              <div class="block-title-replace"></div>
                            </div>
                            <div class="shop-btn-preview image-preview-wrapper-shop">
                              '. $logoContent .'
                              <span class="shop-default-text">Shop</span>
                              <svg
                                class="image-shop-cart-icon"
                                xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px"
                                fill="#FFFFFF"
                              >
                                <path
                                  d="M0 0h24v24H0V0z"
                                  fill="none"
                                />
                                <path
                                  d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"
                                />
                              </svg> 
                            </div>
                          </div>
                          <div id="single-product-product"></div>
                        </div>
                      </div>'.

                      // Action Button Preview
                      '<div id="preview-action-button">
                        <h3 class="block-name">Action Button Preview</h3>
                        <div class="action-button-container">
                          <div id="action-button-container"></div>
                        </div>
                      </div>'.

                      // Two Products Preview
                      '<div id="preview-two-products" class="d-none">
                        <h3 class="block-name">Two Products Preview</h3>
                        <div class="two-products-container">
                          <div class="single-product-container">
                            <div class="header-preview">
                              <div
                                class="block-title-container ' . $blocksTitleAlignment . '"
                                style="font-size:' . $blockTitleSize . 'px"
                              >
                                <div class="block-title-replace"></div>
                              </div>
                              <div class="shop-btn-preview image-preview-wrapper-shop">
                                '. $logoContent .'
                                <span class="shop-default-text">Shop</span>
                                <svg class="image-shop-cart-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#FFFFFF"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg> 
                              </div>
                            </div>
                            <div class="two-products-product-container"></div>
                          </div>
                          <div class="single-product-container">
                            <div class="header-preview">
                            </div>
                            <div class="two-products-product-container"></div>
                          </div>
                        </div>
                      </div>'.

                      // Carousel Products Preview
                      '<div id="preview-carousel" class="d-none">
                        <h3 class="block-name">Carousel Preview</h3>
                        <div class="carousel-preview-container">
                          <div class="header-preview header-preview-carousel">
                            <div
                              class="block-title-container ' . $blocksTitleAlignment . '"
                              style="font-size:' . $blockTitleSize . 'px"
                            >
                              <div class="block-title-replace"></div>
                            </div>
                            <div class="shop-btn-preview image-preview-wrapper-shop">
                              '. $logoContent .'
                              <span class="shop-default-text">Shop</span>
                              <svg class="image-shop-cart-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#FFFFFF"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg> 
                            </div>
                          </div>
                          <!-- Slider main container -->
                          <div class="swiper">
                            <!-- Additional required wrapper -->
                            <div class="swiper-wrapper" id="swiper-wrapper">
                              <!-- Slides -->
                            </div>
                            <!-- If we need navigation buttons -->
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                          </div>
                        </div>
                      </div>'.
                      
                      // Carousel Products Preview
                      '.<div id="preview-shop" class="d-none">
                        <h3 class="block-name">Shop Preview</h3>
                        <div class="preview-shop-container">
                          <div class="header-preview">
                            <div
                              class="block-title-container ' . $blocksTitleAlignment . '"
                              style="font-size:' . $blockTitleSize . 'px"
                            >
                              <span class="block-title-replace"></span>
                            </div>
                          </div>
                          <div class="top-products-container">
                            <div class="first-product product-shop shop-1"></div>
                            <div class="sidebar-container">
                              <div class="sidebar-item product-shop shop-2"></div>
                              <div class="sidebar-item product-shop shop-3"></div>
                            </div>
                          </div>
                          <div class="bottom-product-container">
                            <div class="bottom-item product-shop shop-4"></div>
                            <div class="bottom-item product-shop shop-5"></div>
                            <div class="bottom-item product-shop shop-6"></div>
                            <div class="bottom-item product-shop shop-7"></div>
                          </div>
                        </div>
                      </div>
                      <div id="preview-shop-layout" class="d-none">
                        <h3 class="block-name">Shop Preview</h3>
                        <div class="preview-shop-container">
                          <div class="header-preview">
                            <div class="block-title-preview ' . $blocksTitleAlignment . '" style="font-size:' . $blockTitleSize . 'px">
                              <span class="block-title-replace">Title Example</span>
                            </div>
                          </div>
                          <div class="bottom-product-container">
                            <div class="bottom-item product-shop-layout shop-1"></div>
                            <div class="bottom-item product-shop-layout shop-2"></div>
                            <div class="bottom-item product-shop-layout shop-3"></div>
                            <div class="bottom-item product-shop-layout shop-4"></div>
                          </div>
                          <div class="bottom-product-container">
                            <div class="bottom-item product-shop-layout shop-5"></div>
                            <div class="bottom-item product-shop-layout shop-6"></div>
                            <div class="bottom-item product-shop-layout shop-7"></div>
                            <div class="bottom-item product-shop-layout shop-8"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                </div>';
                
                // Shorcode List
                $content .= '<div><div class="shortcode-list-container">';
                  $content .= '<h3 style="margin-top:0px;margin-bottom:0px;">Saved Shortcodes</h3>';
                  $getShortcodes = get_posts([
                    'post_type'   => 'shortcodes',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'order'       => 'DESC',
                  ]);
                  $index = 1;                
                  $content .= '<div class="shortcode-list">';
                    $content .= '<div class="shortcode-list-item shortcode-list-item-header">';
                      $content .= '<h4>Shortcode Name</h4><h4>Block Type</h4><h4>Shortcode</h4><h4>Action</h4>';
                    $content .= '</div>';
                    $content .= '<div id="saved-shortcodes-container">';
                      if (count($getShortcodes) === 0) {
                        $content .= '<div class="shortcode-list-item">You have not saved any shortcodes yet.</div>';
                      }
                      foreach($getShortcodes as $getShortcode) {
                        $content .= '<div class="shortcode-list-item">';
                          $content .= '<span>'. get_the_title($getShortcode->ID) . '</span>';
                          $content .= '<span>'. get_the_excerpt($getShortcode->ID) . '</span>';
                          $content .= '<span>'. get_the_content(null, null, $getShortcode->ID) . '</span>';
                          $content .= '<button data-id="'. $getShortcode->ID .'" class="shortcode-delete-btn">
                            <svg width="10" height="13" viewBox="0 0 10 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M7.16111 1.05739L7.30103 1.19885H7.5H9.28571C9.41038 1.19885 9.52337 1.30548 9.52337 1.44444C9.52337 1.58341 9.41038 1.69003 9.28571 1.69003H0.714286C0.589617 1.69003 0.476632 1.58341 0.476632 1.44444C0.476632 1.30548 0.589617 1.19885 0.714286 1.19885H2.5H2.69897L2.83889 1.05739L3.34603 0.544607C3.38666 0.503528 3.45099 0.476632 3.50714 0.476632H6.49286C6.54901 0.476632 6.61334 0.503528 6.65397 0.544607L7.16111 1.05739ZM2.14286 12.5234C1.62533 12.5234 1.19092 12.0917 1.19092 11.5556V4.33333C1.19092 3.79715 1.62533 3.36552 2.14286 3.36552H7.85714C8.37467 3.36552 8.80908 3.79715 8.80908 4.33333V11.5556C8.80908 12.0917 8.37467 12.5234 7.85714 12.5234H2.14286Z" stroke="#E81D1D" stroke-width="0.953265"/>
                            </svg>          
                          </button>';
                        $content .= '</div>';
                      }
                    $content .= '</div>';
                  $content .= '</div>';
                $content .= '</div></div>

              </div>
              </div>
              </div>
            </div></div>'.

            // Box Content
            '<div>
              <div class="box-sidebar-container">'
                . $helpCenterBox .
                '<div class="box" style="color:white;background-color:#6603E5;margin-top:30px;">
                  <div class="box-header">
                    <svg width="18" height="22" viewBox="0 0 18 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M16.6121 18.6696C16.5676 17.3489 15.4686 16.3098 14.1473 16.3395C12.8261 16.3692 11.7748 17.4565 11.7897 18.778C11.8045 20.0994 12.88 21.1629 14.2016 21.1628C15.5554 21.1394 16.6343 20.0235 16.6121 18.6696Z" stroke="#41BE9F" stroke-width="1.56716" stroke-linecap="round" stroke-linejoin="round"/>
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M8.17388 11.19C8.20721 13.2213 6.588 14.8954 4.55664 14.9297C2.52583 14.8946 0.907484 13.2208 0.940813 11.19C0.907484 9.15913 2.52583 7.48531 4.55664 7.4502C6.588 7.48453 8.20721 9.15858 8.17388 11.19Z" stroke="#41BE9F" stroke-width="1.56716" stroke-linecap="round" stroke-linejoin="round"/>
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M15.4069 3.70985C15.4282 4.58528 14.9733 5.40348 14.2185 5.84745C13.4637 6.29143 12.5275 6.29143 11.7727 5.84745C11.0179 5.40348 10.5631 4.58528 10.5843 3.70985C10.5631 2.83441 11.0179 2.01622 11.7727 1.57224C12.5275 1.12826 13.4637 1.12826 14.2185 1.57224C14.9733 2.01622 15.4282 2.83441 15.4069 3.70985Z" stroke="#41BE9F" stroke-width="1.56716" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M11.9392 17.6586C12.2923 17.9089 12.7814 17.8256 13.0316 17.4725C13.2819 17.1194 13.1986 16.6304 12.8455 16.3801L11.9392 17.6586ZM7.85915 12.8455C7.5061 12.5952 7.01701 12.6785 6.76674 13.0316C6.51648 13.3846 6.5998 13.8737 6.95286 14.124L7.85915 12.8455ZM11.7168 5.9363C12.0327 5.64055 12.0491 5.14468 11.7533 4.82875C11.4575 4.51282 10.9617 4.49646 10.6458 4.79221L11.7168 5.9363ZM6.87606 8.32113C6.56013 8.61689 6.54377 9.11275 6.83953 9.42868C7.13528 9.74461 7.63115 9.76097 7.94708 9.46522L6.87606 8.32113ZM12.8455 16.3801L7.85915 12.8455L6.95286 14.124L11.9392 17.6586L12.8455 16.3801ZM10.6458 4.79221L6.87606 8.32113L7.94708 9.46522L11.7168 5.9363L10.6458 4.79221Z" fill="#41BE9F"/>
                    </svg>
                    <div style="font-weight:bold;margin-left:15px;">
                      How to add products to wordpress
                    </div>
                  </div>
                  <p>
                    Get up to speed faster with our Reachu introduction resources.
                  </p>
                  <div>
                    <a
                      style="background:#4EF1C9"
                      class="box-button"
                      href="https://support.reachu.io/en/articles/7923795-adding-products-to-wordpress"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <span>See more</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>'.


            '</div>
          </div>
      </div>
      </div>';

    } else {

      $content = '
        <div class="connect-container">
          <div class="connect-content">

            <div>
              <div class="connect-header">
                <img
                  src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/reachu-logo-full-white.svg' . '"
                  alt="Logo"
                  style="width: 140px;"
                >              
                <h2>
                  Sell Products
                  <br/>
                  Natively On Your
                  <br/>
                  WordPress Site.
                </h2>
                <p>
                  Reachu allows you to sell products directly in your content
                  <br/>
                  without any ecommerce infrastructure. 
                </p>
              </div>
              <form id="outshifter-blocks-form">
                <div style="font-size:14px;font-weight:bold;color:white;">
                  Connect your account
                </div>
                <div style="margin-top:10px">
                  <input id="outshifter-blocks-form-token" type="text" name="token" placeholder="Your Reachu API key" required>
                  <button type="submit" class="outshifter-btn-blocks-form-token">Connect</button>
                </div>
              </form>
              <div class="connect-box-container">
                <div class="box" style="background-color:white;margin-left:inherit;margin-right:inherit;">
                  <div class="box-header">
                    <svg width="25" height="22" viewBox="0 0 32 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M27.1423 20.9561L27.1528 10.8184L19.9375 3.6259C17.7601 1.45803 14.24 1.45803 12.0625 3.6259L4.86304 10.8009V20.9509C4.86883 24.0208 7.36165 26.505 10.4315 26.5001H21.5738C24.6416 26.505 27.1336 24.024 27.1423 20.9561Z" stroke="#4EF1C9" stroke-width="2.625" stroke-linecap="round" stroke-linejoin="round"/>
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M18.625 13.1388C18.5981 14.5819 17.4112 15.7325 15.968 15.7146C14.5248 15.6968 13.3667 14.5171 13.3755 13.0738C13.3844 11.6305 14.5567 10.4651 16 10.4648C16.7027 10.4713 17.3741 10.7567 17.8664 11.2582C18.3587 11.7596 18.6316 12.4361 18.625 13.1388V13.1388Z" stroke="#4EF1C9" stroke-width="2.625" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M28.4775 3.3358C28.4786 2.61092 27.8918 2.02247 27.1669 2.02146C26.442 2.02044 25.8536 2.60724 25.8525 3.33211L28.4775 3.3358ZM27.1545 10.8187L25.842 10.8169C25.8416 11.1663 25.9804 11.5015 26.2279 11.7482L27.1545 10.8187ZM29.0734 14.585C29.5867 15.0967 30.4178 15.0955 30.9295 14.5821C31.4413 14.0687 31.44 13.2377 30.9267 12.7259L29.0734 14.585ZM5.78966 11.7308C6.303 11.219 6.30428 10.3879 5.7925 9.8746C5.28072 9.36125 4.44969 9.35998 3.93634 9.87176L5.78966 11.7308ZM1.07334 12.726C0.559997 13.2378 0.558725 14.0688 1.0705 14.5822C1.58228 15.0955 2.41331 15.0968 2.92666 14.585L1.07334 12.726ZM12.5 19.8452C11.7751 19.8452 11.1875 20.4328 11.1875 21.1577C11.1875 21.8826 11.7751 22.4702 12.5 22.4702V19.8452ZM19.5 22.4702C20.2249 22.4702 20.8125 21.8826 20.8125 21.1577C20.8125 20.4328 20.2249 19.8452 19.5 19.8452V22.4702ZM25.8525 3.33211L25.842 10.8169L28.467 10.8205L28.4775 3.3358L25.8525 3.33211ZM26.2279 11.7482L29.0734 14.585L30.9267 12.7259L28.0812 9.8892L26.2279 11.7482ZM3.93634 9.87176L1.07334 12.726L2.92666 14.585L5.78966 11.7308L3.93634 9.87176ZM12.5 22.4702H19.5V19.8452H12.5V22.4702Z" fill="#4EF1C9"/>
                    </svg>
                    <div style="font-weight:bold;margin-left:15px;">Sign up here!</div>
                  </div>
                  <p>
                    Don&#039;t have an API-key yet?
                    <br/>
                    Create an account fro free...
                  </p>
                  <div>
                    <a class="box-button" href="https://dashboard.reachu.io/signup" target="_blank" rel="noopener noreferrer">
                      <span style="margin-right:8px;">Sign up here</span>
                      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.33329 2.66634C5.33329 3.40272 4.73634 3.99967 3.99996 3.99967C3.26358 3.99967 2.66663 3.40272 2.66663 2.66634C2.66663 1.92996 3.26358 1.33301 3.99996 1.33301C4.73634 1.33301 5.33329 1.92996 5.33329 2.66634Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.33329 8.33333C7.33329 9.622 5.84063 10.6667 3.99996 10.6667C2.15929 10.6667 0.666626 9.622 0.666626 8.33333C0.666626 7.04467 2.15929 6 3.99996 6C5.84063 6 7.33329 7.04467 7.33329 8.33333Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.5 1.33301C10.5 1.05687 10.2761 0.833008 9.99996 0.833008C9.72382 0.833008 9.49996 1.05687 9.49996 1.33301H10.5ZM9.49996 2.66634C9.49996 2.94248 9.72382 3.16634 9.99996 3.16634C10.2761 3.16634 10.5 2.94248 10.5 2.66634H9.49996ZM10.5 2.66634C10.5 2.3902 10.2761 2.16634 9.99996 2.16634C9.72382 2.16634 9.49996 2.3902 9.49996 2.66634H10.5ZM9.49996 3.99967C9.49996 4.27582 9.72382 4.49967 9.99996 4.49967C10.2761 4.49967 10.5 4.27582 10.5 3.99967H9.49996ZM9.99996 3.16634C10.2761 3.16634 10.5 2.94248 10.5 2.66634C10.5 2.3902 10.2761 2.16634 9.99996 2.16634V3.16634ZM8.66663 2.16634C8.39048 2.16634 8.16663 2.3902 8.16663 2.66634C8.16663 2.94248 8.39048 3.16634 8.66663 3.16634V2.16634ZM9.99996 2.16634C9.72382 2.16634 9.49996 2.3902 9.49996 2.66634C9.49996 2.94248 9.72382 3.16634 9.99996 3.16634V2.16634ZM11.3333 3.16634C11.6094 3.16634 11.8333 2.94248 11.8333 2.66634C11.8333 2.3902 11.6094 2.16634 11.3333 2.16634V3.16634ZM9.49996 1.33301V2.66634H10.5V1.33301H9.49996ZM9.49996 2.66634V3.99967H10.5V2.66634H9.49996ZM9.99996 2.16634H8.66663V3.16634H9.99996V2.16634ZM9.99996 3.16634H11.3333V2.16634H9.99996V3.16634Z" fill="white"/>
                      </svg>
                    </a>
                  </div>
                </div>'.

                $helpCenterBox.
 
              '</div>
            </div>

            <div class="connect-image-container">
              <img
                src="' . OUTSHIFTER_PLUGIN_FOLDER . OUTSHIFTER_NAME_FOLDER . '/admin/assets/images/connect_mockup.png' . '"
                alt="Logo"
                style="max-width:450px;"
              >
            </div>

          </div>
        </div>
      ';
    }

    echo
      '<div class="outshifter-admin-page">'.
        '<div class="reachu-admin-container">'.
            '<div style="position:absolute;z-index:99;top:30px;left:30px;width:700px;transition:all 5s ease-in-out;">'.
              $contentError.
            '</div>'.
          '<div style="height:100%;width:100%;">'.
            '<div id="loader"></div>'.
            $content.
          '</div>'.
        '</div>'.
      '</.>';
  }

	public function enqueue_styles() {
    if (isset($_GET['page']) && $_GET['page'] === 'reachu-embed-commerce') {
      wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/index.css', array(), $this->version, 'all' );
    }
    wp_enqueue_style('reachu-menu', plugin_dir_url( __FILE__ ) . 'css/reachu-menu.css');
	}

  public function mw_enqueue_carousel( $hook_suffix ) {
    if (isset($_GET['page']) && $_GET['page'] === 'reachu-embed-commerce') {
      wp_enqueue_style( 'my-styles-carousel', 'https://unpkg.com/swiper@8/swiper-bundle.min.css' );
      wp_enqueue_script( 'my-script-carousel', 'https://unpkg.com/swiper@8/swiper-bundle.min.js' );
    }
  }

	public function enqueue_scripts() {
    if (isset($_GET['page']) && $_GET['page'] === self::ADMIN_SLUG) {
      wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/index.js', array( 'jquery' ), $this->version, false );
      wp_localize_script( $this->plugin_name, 'outshifter_blocks_admin_vars', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'connectionError' => get_option('outshifter_blocks_connect_error'),
        'isSaved' => get_option('outshifter_blocks_saved'),
        'sellerId' => get_option('outshifter_blocks_seller_id'),
        'sellerName' => get_option('outshifter_blocks_seller_name'),
        'sellerSurname' => get_option('outshifter_blocks_seller_surname'),
        'sellerEmail' => get_option('outshifter_blocks_seller_email'),
        'sellerAvatar' => get_option('outshifter_blocks_seller_avatar'),
        'currency' => get_option('outshifter_blocks_currency'),
        'fontSelected' => get_option('outshifter_blocks_font_selected'),
        'supplierLogo' => get_option('outshifter_blocks_supplier_logo'),
        'supplierLogoWhite' => get_option('outshifter_blocks_supplier_logo_white'),
        'mixpanel' => get_option('outshifter_blocks_mixpanel'),
        'stripekey' => get_option('outshifter_blocks_stripe_key'),
        'stripeid' => get_option('outshifter_blocks_stripe_id'),
        'ganalytics' => get_option('outshifter_blocks_g_analytics'),
        'layoutSelected' => get_option ('outshifter_blocks_layout_selected'),
        'modalPosition' => get_option ('outshifter_blocks_modal_position'),
        'blocksTitleAlignment' => get_option ('outshifter_blocks_title_alignment'),
        'shopColor' => get_option ('outshifter_blocks_shop_color'),
        'buttonNextType' => get_option ('outshifter_blocks_button_next_type'),
        'buttonPrevType' => get_option ('outshifter_blocks_button_prev_type'),
        'buttonNextColor' => get_option ('outshifter_blocks_button_next_color'),
        'buttonPrevColor' => get_option ('outshifter_blocks_button_prev_color'),
        'buttonNextHoverColor' => get_option ('outshifter_blocks_button_hover_color'),
        'buttonNextTextColor' => get_option ('outshifter_blocks_button_text_color'),
        'buttonPrevTextColor' => get_option ('outshifter_blocks_button_prev_text_color'),
        'buttonNextHoverTextColor' => get_option ('outshifter_blocks_button_hover_text_color'),
        'buttonPrevBorderColor' => get_option ('outshifter_blocks_button_prev_border_color'),
        'buttonBorderRatio' => get_option ('outshifter_blocks_button_border_ratio'),
        'blockTitleSize' => get_option ('outshifter_blocks_title_size'),
        'createShortcode' => get_option ('outshifter_blocks_create_shortcode'),
        'shortcodeShopLayout' => get_option ('outshifter_blocks_shortcode_shop_layout'),     
        'notGutemberg' => get_option ('outshifter_blocks_not_gutemberg'),
        'allowUploadToMedia' => get_option ('outshifter_blocks_allow_upload_to_media'),
        'savedMediaImages' => get_option ('outshifter_blocks_saved_media_images'),
        'shortcodeBuyButton' => get_option ('outshifter_blocks_shortcode_buy_button'),
        'shortcodeSingle' => get_option ('outshifter_blocks_shortcode_single'),
        'shortcodeTwo' => get_option ('outshifter_blocks_shortcode_two'),
        'shortcodeCarousel' => get_option ('outshifter_blocks_shortcode_carousel'),
        'shortcodeShop' => get_option ('outshifter_blocks_shortcode_shop'),
        'shopLogoSelected' => get_option ('outshifter_blocks_shop_logo_selected'),
        'supplierLogoShop' => get_option('outshifter_blocks_supplier_logo_shop'),
        'shopTextSelected' => get_option ('outshifter_blocks_shop_text_selected'),
        'shopButtonColor' => get_option ('outshifter_blocks_shop_button_color'),
        'textIconColor' => get_option ('outshifter_blocks_text_icon_color'),
        'shopButtonRatio' => get_option ('outshifter_blocks_shop_button_ratio'),
        'showShopIcon' => get_option ('outshifter_blocks_show_shop_icon'),
        'addShopUrl' => get_option ('outshifter_blocks_add_shop_url'),
        'shopCustomUrl' => get_option ('outshifter_blocks_shop_custom_url'),
        'showCardTitle' => get_option ('outshifter_blocks_show_card_title'),
        'showCardPrice' => get_option ('outshifter_blocks_show_card_price'),
        'showCardSupplier' => get_option ('outshifter_blocks_show_card_supplier'),
        'showCardButton' => get_option ('outshifter_blocks_show_card_button'),
      ]);
    }
	}
}

add_action( 'admin_footer', 'media_selector_print_scripts' );

function media_selector_print_scripts() {
  global $pagenow;

  if ( $pagenow == 'admin.php' && $_GET['page'] == 'reachu-embed-commerce' ) {

  $my_saved_attachment_post_id = 0;
  $my_saved_attachment_post_id_white = 1;
  $my_saved_attachment_post_id_shop = 2;

	?><script type='text/javascript'>

    //modal select products
    //common constants
    let allProducts = []
    const checkboxes = document.getElementsByClassName('selectedProducts'); 
    const table = document.getElementById('table-products');
    const tableRow = table?.getElementsByClassName("table-row-products") || '';
    //get products
    async function getProducts () {
      const res = await fetch(`${apiUrl}/api/channel/all-items/user?channelId=7&currency=${currency}`,
        {
          headers: new Headers({
            Authorization: userApiKey,
            "Content-Type": "application/json",
          }),
        }
      );
      const products = await res.json();
      allProducts = products;
      showData(allProducts);
      handleSelectedProducts();
    }
    getProducts();

    //show products and get selected
    const showData = (allProducts) => {
      let body = ""
      for (let product of allProducts ) {
        const img = product.images[0] || product.images[1]
        const {url} = img
        body += `
          <label class="table-row-products">
            <div class="table-col">
              <img src=${url} alt=${product.title} />
            </div>
            <div class="table-col col-title">
              ${product.title}
            </div>
            <div class="table-col col-brand">
              ${product.supplier?.name || 'Brand Name'}
            </div>
            <div class="table-col">
              ${product.referralFee}%
            </div>
            <div class="table-col">
              ${product.category || 'category'}
            </div>
            <div class="table-col">
              ${product.price?.amount} ${currency}
            </div>
            <div class="table-col col-checkbox">
              <div class="checkbox-container">
                <input class="selectedProducts" type="checkbox" value=${product.id} name="selectedProducts" />
                <div class="checked-icon"></div>
              </div>
            </div>
          </label>
        `
      }
      document.getElementById('table-products').innerHTML = body
      productSelectedStyles();
      //add selected products
      for (let checkbox of checkboxes) {
        if (!selectedIds.length) {
          document.getElementById('btn-select-product').style.display = 'none';
        } 
        if (selectedIds.length && selectedIds.includes(checkbox.value)) {
          checkbox.click();
          productSelectedStyles();
        }
        checkbox.addEventListener('click', () =>{
          if(checkbox.checked == true) {
            selectedIds.push(checkbox.value)
            document.getElementById('btn-select-product').style.display = 'block'
            handleDisabled(selectedIds)
          } else {
            selectedIds = selectedIds.filter(p => p !== checkbox.value);
            handleDisabled(selectedIds)
          }
          if (!selectedIds.length) {
            document.getElementById('btn-select-product').style.display = 'none';
          } else {
            document.getElementById('btn-select-product').style.display = 'block';
          }
          productSelectedStyles();
        })
      }
      if(selectedIds.length) {
        handleDisabled(selectedIds)
      }
    }    

    //product selected styles
    const checkboxIcons = document.getElementsByClassName('checked-icon');

    const productSelectedStyles = () => {
      for (let i = 0; i < tableRow.length; i++) {
        if (checkboxes[i].checked == true) {
          tableRow[i].style.backgroundColor = "#77db1436";
          checkboxIcons[i].style.display = "block";
        } else {
          tableRow[i].style.backgroundColor = "";
          checkboxIcons[i].style.display = "none"
        }
      }
    }

    const handleDisabled = (selectedIds) => {
      let maxProductAllowed;
      const handler = (maxProductAllowed) => {
        if(selectedIds.length > maxProductAllowed) {
          for (let checkbox of checkboxes) {
            if(checkbox.checked == false) {
              checkbox.setAttribute("disabled", true)
            }
            if(checkbox.checked == true) {
              checkbox.removeAttribute("disabled", true)
            }
          }
          for (let i = 0; i < tableRow.length; i++) {
            if (checkboxes[i].checked == false) {
              tableRow[i].style.opacity = 0.5;
            } 
            if (checkboxes[i].checked == true) {
              tableRow[i].style.opacity = 1;
            } 
          }
        }
        if(selectedIds.length <= maxProductAllowed){
          for (let checkbox of checkboxes) {
              checkbox.removeAttribute("disabled")
          }
          for (let i = 0; i < tableRow.length; i++) {
            if (checkboxes[i].checked == false) {
              tableRow[i].style.opacity = 1;
            } 
          }
        }
      }

      switch (blockSelected) {
        case 'previewBuyButton':
          maxProductAllowed = 0;
          handler(maxProductAllowed)
          break;
        
        case 'previewSingleProduct':
          maxProductAllowed = 0;
          handler(maxProductAllowed)
          break;
        
        case 'previewTwoProducts':
          maxProductAllowed = 1;
          handler(maxProductAllowed)
          break;
        
        case 'previewCarousel':
          maxProductAllowed = 4;
          handler(maxProductAllowed)
          break;

        case 'previewShop':
          maxProductAllowed = 6;
          handler(maxProductAllowed)
          break;

        default:
          return;
          break;
      }
    }

    //filter products
    const searchProducts = () => {
      const searchInput = document.getElementById('searchProduct');
      const filter = searchInput.value.toUpperCase();

      for (let i = 0; i < tableRow.length; i++) {
        let tableColTitle = tableRow[i].getElementsByClassName("col-title");
        let tableColBrand = tableRow[i].getElementsByClassName("col-brand");
        if (tableColTitle || tableColBrand) {
          let titleValue = tableColTitle[0].textContent || tableColTitle[0].innerText;
          let brandValue = tableColBrand[0].textContent || tableColBrand[0].innerText;
          if (titleValue.toUpperCase().indexOf(filter) > -1 || brandValue.toUpperCase().indexOf(filter) > -1) {
            tableRow[i].style.display = "";
          } else {
            tableRow[i].style.display = "none";
          }
        }
      }
    }

    // Modal select product
    const openEls = document.querySelectorAll("[data-open]");
    const closeEls = document.querySelectorAll("[data-close]");
    const isVisible = "is-visible";

    for (const el of openEls) {
      el.addEventListener("click", function() {
        const modalId = this.dataset.open;
        document.getElementById(modalId).classList.add(isVisible);
      });
    }

    for (const el of closeEls) {
      el.addEventListener("click", function() {
        if (this.parentElement.parentElement.parentElement.classList.contains(isVisible)) {
          this.parentElement.parentElement.parentElement.classList.remove(isVisible);
        }
        if (!selectedIds.length) {
          document.getElementById('btn-select-product').click();
        }
      });
    }

    document.addEventListener("click", e => {
      if (e.target == document.querySelector(".modal.is-visible")) {
        if (document.querySelector(".modal.is-visible").classList.contains(isVisible)) {
          document.querySelector(".modal.is-visible").classList.remove(isVisible);
        }
        if (!selectedIds.length) {
          document.getElementById('btn-select-product').click();
        }      
      }
    });

    document.addEventListener("keyup", e => {
      // if we press the ESC
      if (e.key == "Escape" && document.querySelector(".modal.is-visible")) {
        if (document.querySelector(".modal.is-visible").classList.contains(isVisible)) {
          document.querySelector(".modal.is-visible").classList.remove(isVisible);
        }
        if (!selectedIds.length) {
          document.getElementById('btn-select-product').click();
        }      
      }
    });

    //show/hide previews
    const previewBuyButton = document.getElementById('preview-action-button');
    const previewSingleProduct = document.getElementById('preview-single-product');
    const previewTwoProducts = document.getElementById('preview-two-products');
    const previewCarousel = document.getElementById('preview-carousel');
    const previewShop = document.getElementById('preview-shop');
    const previewShopLayout = document.getElementById('preview-shop-layout');
    const blockSelect = document.getElementById('outshifter-create-shortcode');
    const shopLayoutSelect = document.getElementById('select-shop-layout');
    const shopLayoutSelectContainer = document.getElementById('select-shop-layout-container');
    const btnSelectProducts = document.getElementById('modal-select-product');
    const shopLayoutReplace = document.getElementById('shop-layout-replace');
    const selectActionButton = document.getElementById('select-action-button');
    const selectContainerActionButton = document.getElementById('select-action-button-container');
    const actionButtonReplace = document.getElementById('action-button-replace');
    let isShop = prevSelectedBlock === 'previewShop';
    let blockSelected = isShop ? prevSelectedShop : prevSelectedBlock;
    let actionButtonTypeSelected;

    if (sellerId != '') {
      blockSelect.addEventListener('change', (e) => {
        blockSelected = e.target.value;
        handleCurrentBlock();
        handleChangePreview(blockSelected);
        handleShowShortcodes();
      });
    }

    if (sellerId != '') {
      shopLayoutSelect.addEventListener('change', (e) => {
        shopLayoutSelected = e.target.value;
        shopLayoutReplace.innerHTML = shopLayoutSelected === 'previewShopLayout' ? 'shop' : 'masonry';
        handleChangePreview(shopLayoutSelected);
      });
    }

    if (sellerId != '') {
      selectActionButton.addEventListener('change', (e) => {
        selectActionButtonSelected = e.target.value;
        actionButtonTypeSelected = selectActionButtonSelected;
        actionButtonReplace.innerHTML = selectActionButtonSelected === 'previewBuyButton' ? 'buy' : 'add_to_cart';
        handleChangePreview(selectActionButtonSelected);
      });
    }

    const handleCurrentBlock = () => {
      selectedIds = [];
      selectedProducts = [];
      const productsSelectedCurrentBlock = () => {
        if (!selectedIds.length) {
          document.getElementById('btn-select-product').style.display = 'none';
        }
        handleSelectedProducts();
        for (checkbox of checkboxes) {
          if (selectedIds.includes(checkbox.value)) {
            checkbox.checked = true;
            productSelectedStyles();
            handleDisabled(selectedIds)

          } else {
            checkbox.checked = false;
            productSelectedStyles();
            handleDisabled(selectedIds)
          }
        }
      }

      const setSelectedIds = (ids) => {
        if (Array.isArray(ids)) { 
          selectedIds = ids 
        } else if (ids?.split(',') == '') {
          selectedIds = []
        } else {
          selectedIds = ids?.split(',')
        }
        productsSelectedCurrentBlock();
      }

      if (blockSelected === "previewSingleProduct") {
        setSelectedIds(shortcodeSingleIds);
      } else if (blockSelected === "previewBuyButton") {
        setSelectedIds(shortcodeBuyButtonIds);
      } else if (blockSelected === "previewTwoProducts") {
        setSelectedIds(shortcodeTwoIds);
      } else if (blockSelected === "previewCarousel") {
        setSelectedIds(shortcodeCarouselIds);
      } else if (blockSelected === "previewShop") {
        setSelectedIds(shortcodeShopIds);
      } else {
        for (checkbox of checkboxes) {
          if (checkbox.checked == true) {
            checkbox.click();
            productSelectedStyles();
          }
        } 
      }
    }

    const handleChangePreview = (blockSelected) => {
      switch (blockSelected) {
        case 'previewBuyButton':
          previewBuyButton.style.display = 'block';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          shopLayoutSelectContainer.style.display = 'none';
          selectContainerActionButton.style.display = 'block';
          createPreview();
          break;
        
        case 'previewAddToCartButton':
          previewBuyButton.style.display = 'block';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          shopLayoutSelectContainer.style.display = 'none';
          selectContainerActionButton.style.display = 'block';
          createPreview("add-to-cart");
          break;
        
        case 'previewSingleProduct':
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'block';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          shopLayoutSelectContainer.style.display = 'none';
          selectContainerActionButton.style.display = 'none';
          createPreview();
          break;
        
        case 'previewTwoProducts':
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'block';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          shopLayoutSelectContainer.style.display = 'none';
          selectContainerActionButton.style.display = 'none';
          createPreview();
          break;
          
        case 'previewCarousel':
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'block';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          shopLayoutSelectContainer.style.display = 'none';
          selectContainerActionButton.style.display = 'none';
          createPreview();
          break;

        case 'previewShop':
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'block';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          shopLayoutSelectContainer.style.display = 'block';
          selectContainerActionButton.style.display = 'none';
          createPreview();
          break;
        
        case 'previewMasonryLayout':
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'block';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'block';
          selectContainerActionButton.style.display = 'none';
          createPreview();
          break;

        case 'previewShopLayout':
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'block';
          btnSelectProducts.style.display = 'block';
          selectContainerActionButton.style.display = 'none';
          createPreview();
          break;

        default:
          previewBuyButton.style.display = 'none';
          previewSingleProduct.style.display = 'none';
          previewTwoProducts.style.display = 'none';
          previewCarousel.style.display = 'none';
          previewShop.style.display = 'none';
          previewShopLayout.style.display = 'none';
          btnSelectProducts.style.display = 'none';
          selectContainerActionButton.style.display = 'none';
          break;
      }
    }
    
    let selectedProducts = [];
    const handleSelectedProducts = () => {
      if (selectedIds.length) {
        for (let i = 0; i < selectedIds.length; i++) {
          const isSelected = () => {
            allProducts.filter((product) => {
              if (product.id == selectedIds[i] && !selectedProducts.includes(product)) {
                selectedProducts.push({ ...product })
              }
            })
          }
          isSelected();
        }
      }
      createPreview()
    }
    
    const createPreview = (block) => {
      const previewImgContainer = document.getElementsByClassName("img-preview");
      const styles = `
        background-size: cover; 
        height: 400px`;
      for (let i = 0; i < previewImgContainer.length; i++) {
        previewImgContainer[i].style.cssText += styles;
      }
      
      const nullProductContent = `
        <div class="img-preview" id="img-preview-two-1">
          <div class="container-null-product">
            <svg width="364" height="397" viewBox="0 0 364 397" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M139.336 5.39454C137.72 5.38955 130.98 8.94266 127.802 11.4777C126.001 12.915 107.174 20.096 94.2337 24.2829C79.6021 29.0137 75.4066 30.1715 67.5645 31.6586C55.2825 33.9891 48.283 39.3786 42.6194 50.8564C41.0156 54.1051 39.1109 59.31 38.3945 62.4289C37.6787 65.5429 36.7508 68.4472 36.3367 68.8664C35.9232 69.2906 35.5884 71.137 35.5884 72.9835C35.5884 76.2122 35.2457 76.9009 30.3349 83.5579C29.6435 84.4961 28.7884 87.67 28.4333 90.6242C27.9638 94.5217 27.2844 96.5478 25.923 97.9999C24.3486 99.6767 23.925 101.333 23.3039 108.325C22.5028 117.342 22.1241 118.894 16.6007 136.47C12.3489 149.999 8.86687 163.338 6.54566 174.896C4.7721 183.729 3.3024 196.758 3.84856 198.899C4.03773 199.638 5.70957 200.796 7.55885 201.469C9.40813 202.143 12.96 203.435 15.4469 204.339C29.3776 209.409 33.8384 210.267 47.9512 210.592L61.5283 210.906L64.3668 213.74C66.8412 216.221 67.6992 216.58 71.2112 216.58C73.4212 216.58 75.232 216.839 75.232 217.174C75.232 218.811 70.9069 234.984 69.994 236.776C68.3477 240.01 67.7591 250.275 68.8416 256.627C70.2933 265.146 70.1087 268.05 67.5795 276.434C64.8956 285.317 65.16 288.251 69.5101 298.172C72.4135 304.784 72.5881 308.043 70.3681 313.677C68.5223 318.363 68.2979 322.694 69.7595 325.259C70.448 326.467 70.9219 333.329 71.2561 346.842C71.755 366.878 73.2067 378.246 75.5613 380.667C76.3445 381.475 77.6864 381.55 81.3581 380.966C86.7558 380.103 104.84 381.036 111.215 382.508C113.44 383.022 119.641 384.664 124.979 386.141C134.706 388.841 148.829 391.82 154.895 392.459C156.676 392.643 165.97 392.703 175.533 392.584C189.626 392.409 194.535 392.025 201.414 390.602C215.797 387.628 223 385.343 238.674 378.735C255.915 371.47 268.546 367.617 278.489 366.604C282.554 366.19 284.181 365.666 284.754 364.593C285.787 362.667 285.747 345.35 284.694 337.396C284.23 333.912 283.088 328.832 282.15 326.103L280.454 321.162L283.427 314.206C289.179 300.807 290.022 290.232 285.817 284.029C284.245 281.719 283.891 280.451 284.3 278.54C289.069 256.328 289.828 237.689 286.64 220.712L286.126 217.982L291.693 218.611C301.746 219.744 306.031 218.606 310.9 213.476C312.646 211.64 314.232 210.891 317.37 210.467C327.257 209.129 340.258 205.751 350.649 201.814C360.307 198.151 360.137 198.455 357.543 189.897C356.66 186.992 354.815 180.605 353.443 175.709C350.13 163.902 345.356 148.502 340.407 133.696C336.786 122.857 336.426 121.135 336.9 117.023C337.579 111.189 336.342 106.568 333.393 103.938C331.268 102.042 331.039 101.304 330.54 94.3021C330.031 87.131 326.898 72.5144 325.162 69.2707C324.728 68.4572 324.369 66.8803 324.369 65.7625C324.369 64.6496 323.386 61.1115 322.199 57.9027C318.926 49.0698 312.75 42.8569 302.17 37.7418C297.69 35.576 289.109 32.1277 283.103 30.0667C250.482 18.8784 243.957 16.2086 235.771 10.7791C231.95 8.23902 230.782 7.81983 229.396 8.48355C222.107 11.9868 201.175 15.0957 184.887 15.0957C168.579 15.0957 157.14 12.8451 145.198 7.28587C142.973 6.24789 140.329 5.39953 139.336 5.39454ZM138.243 8.62328C138.712 8.62328 139.825 10.0405 140.708 11.7772C143.626 17.4911 152.236 28.1354 157.375 32.3772C167.437 40.6811 179.19 44.6185 188.224 42.7022C197.987 40.6262 206.473 36.2996 215.023 29.0387C219.254 25.4506 227.24 16.5928 229.8 12.6654L231.112 10.6843L234.464 12.9C236.305 14.1176 237.816 15.49 237.816 15.9391C237.816 17.8654 232.738 26.5585 228.552 31.8133C219.982 42.5774 208.793 49.1197 194.131 51.9492C176.356 55.3726 159.839 50.1427 145.711 36.584C138.139 29.3181 130.232 17.7805 130.232 13.9879C130.232 12.8501 136.547 8.62328 138.243 8.62328ZM243.019 18.3245C243.144 18.3245 245.758 19.3724 248.821 20.635C251.884 21.8975 256.584 23.6791 259.252 24.6073C261.921 25.5405 270.976 28.6794 279.377 31.5937C297.356 37.8366 306.415 42.0484 311.488 46.5198C315.414 49.9781 319.575 56.9645 321.076 62.6136C322.104 66.4711 321.795 67.3194 315.978 76.5416L312.706 81.7315L311.583 78.9569C309.837 74.6303 308.286 73.3577 304.819 73.3577C302.334 73.3577 301.072 73.9666 298.488 76.4018C294.766 79.905 294.008 82.2854 291.21 99.2475C290.217 105.256 289.264 110.321 289.089 110.506C288.915 110.69 288.775 105.855 288.775 99.7466C288.775 80.6286 285.473 62.8331 281.931 62.8331C279.91 62.8331 278.938 79.1814 280.08 93.9927C280.554 100.186 281.073 106.578 281.886 116.245C283.816 139.245 283.078 162.59 279.811 181.772C277.291 196.589 276.653 198.839 262.869 240.364C256.648 259.097 251.196 275.551 250.752 276.918C249.784 279.917 249.689 283.73 250.597 283.735C252.263 283.735 256.733 277.572 267.623 260.26L269.399 257.441L268.856 259.856C267.963 263.753 264.216 274.238 258.594 288.58C252.568 303.97 249.121 311.511 245.344 317.524C242.391 322.23 242.037 323.997 243.912 324.715C246.107 325.559 248.397 322.415 254.029 310.837C260.41 297.713 264.655 286.993 273.764 261.074C275.251 256.847 276.723 252.76 277.037 251.981C277.725 250.285 279.067 250.888 279.062 252.9C279.062 253.693 280.22 256.642 281.621 259.452C284.395 265.001 284.395 265.151 281.527 279.288C280.105 286.32 263.458 317.419 250.208 337.83C245.708 344.766 245.105 347.251 247.933 347.251C249.051 347.251 250.931 345.166 254.104 340.375C260.664 330.479 271.874 311.057 278.474 298.157C281.601 292.039 284.45 287.203 284.784 287.413C286.046 288.191 286.445 294.339 285.533 298.875C283.627 308.362 278.663 320.244 274.293 325.808C268.337 333.398 261.727 340.44 257.486 343.708C254.294 346.174 253.166 347.571 253.341 348.794C253.476 349.722 254.139 350.585 254.808 350.71C256.658 351.064 265.763 343.045 272.986 334.711L279.436 327.255L280.374 329.566C281.866 333.299 283.273 347.446 282.978 355.75L282.714 363.435L280.279 363.655C269.619 364.608 254.149 369.254 236.599 376.784C224.896 381.81 212.115 385.777 200.167 388.092C190.2 390.024 165.84 390.922 155.299 389.759C146.809 388.826 133.639 386.046 124.575 383.272C114.443 380.173 104.7 378.406 97.5861 378.391C94.4133 378.386 91.0709 378.182 90.1829 377.942C88.1326 377.388 77.0579 355.74 78.0207 354.173C78.9735 352.636 84.1717 353.484 99.4917 357.696C110.432 360.71 128.042 365.127 134.677 366.524C140.139 367.672 144.245 368.48 153.693 370.252C164.553 372.288 196.032 375.182 207.071 375.162C214.963 375.152 232.419 373.091 234.634 371.904C235.941 371.205 236.14 370.741 235.432 370.032C234.339 368.944 224.916 369.134 220.745 370.332C217.034 371.395 208.758 371.325 204.048 370.187C191.342 367.123 167.058 358.046 149.358 349.762C144.983 347.711 141.252 346.034 141.067 346.034C140.324 346.034 124.844 336.832 119.426 333.169C110.552 327.16 100.819 316.436 94.7326 305.972C91.8941 301.081 87.6338 293.79 85.2741 289.768C74.9976 272.257 72.0293 259.467 78.2402 259.467C79.9962 259.467 81.9717 260.999 87.3594 266.563C93.0664 272.452 95.3911 275.73 101.018 285.776C109.644 301.176 116.827 312 123.687 319.944C129.438 326.607 132.666 328.348 132.666 324.775C132.666 323.702 131.878 322.13 130.935 321.282C128.84 319.406 122.14 310.787 122.14 309.979C122.14 309.659 124.046 311.201 126.38 313.407C145.676 331.637 177.639 346.837 207.476 351.972C227.395 355.406 228.912 355.52 228.912 353.719C228.912 353.055 228.722 352.506 228.493 352.506C226.996 352.506 215.063 348.908 212 347.531C202.602 343.309 195.208 339.257 193.727 337.52C192.839 336.472 189.561 333.912 186.448 331.826C156.931 312.035 121.931 271.778 103.498 236.402C100.714 231.062 97.6409 225.907 96.6532 224.939C92.9666 221.336 85.4837 193.894 81.7971 170.454C76.6588 137.793 75.7259 97.1665 79.1282 54.7688C79.632 48.5209 79.8565 47.8272 81.7023 46.944C82.8147 46.405 92.9915 42.9567 104.336 39.2838C115.685 35.6109 126.48 31.9431 128.331 31.1296C130.177 30.3162 132.192 29.8471 132.791 30.0966C133.389 30.3511 135.44 32.5269 137.355 34.9173C141.601 40.212 150.012 47.1336 156.392 50.5919C166.878 56.2709 180.048 58.7511 191.467 57.1891C212.494 54.3097 228.089 43.6404 239.153 24.5923C241.159 21.144 242.9 18.3245 243.019 18.3245ZM62.526 56.8897C63.6584 56.69 63.7133 58.1173 62.5559 61.0566C61.1292 64.6895 59.2535 65.4381 57.3528 63.1425C56.415 62.0147 56.6943 61.4758 59.3632 59.0255C60.785 57.723 61.8476 57.0094 62.526 56.8897ZM54.2649 66.8703C54.619 66.8703 55.8712 67.7037 57.0385 68.7117L59.1587 70.5382L57.9265 75.793C57.253 78.6824 56.4948 82.4052 56.2304 84.0719C55.966 85.7437 55.4621 87.1161 55.1229 87.1161C54.3596 87.1161 49.3386 81.981 49.3386 81.2025C49.3386 80.8881 49.8414 80.6436 50.4585 80.6436C52 80.6436 52.6136 78.7672 53.1574 72.3447C53.4118 69.3405 53.9107 66.8703 54.2649 66.8703ZM323.541 74.1562C324.603 74.1562 326.803 84.5011 327.891 94.5816C328.824 103.25 329.088 104.173 331.054 105.72C333.842 107.911 334.845 111.698 334.017 116.928C333.438 120.611 333.703 122.103 336.217 129.389C343.565 150.688 353.612 184.407 352.834 185.141C352.006 185.924 345.112 187.935 330.899 191.553C324.04 193.295 317.944 194.732 317.35 194.732C316.033 194.732 315.968 190.675 317.041 173.963C317.465 167.36 317.998 143.831 318.223 121.689L318.647 81.4221L320.767 77.7891C321.94 75.788 323.187 74.1562 323.541 74.1562ZM304.709 78.363C306.784 78.378 307.363 79.6555 307.268 83.0589C307.198 85.4543 306.38 86.637 301.98 90.7141L296.792 95.5347L297.306 91.9317C297.585 89.9456 298.109 87.2308 298.473 85.8984C298.837 84.561 299.361 82.485 299.625 81.2824C300.01 79.5457 300.718 78.9718 303.028 78.5377C303.671 78.4129 304.23 78.363 304.709 78.363ZM46.7171 87.1909C47.2823 87.1859 48.4576 88.0792 50.7703 90.1552L55.0406 93.9927L54.1077 97.2165C53.1449 100.575 53.4243 117.522 54.9458 149.011C55.3599 157.465 56.0782 173.593 56.5372 184.846C56.9961 196.1 57.5798 205.631 57.8342 206.04C58.0836 206.445 57.5648 207.163 56.6918 207.632C52.8206 209.703 29.6001 207.368 27.3707 204.683C26.8464 204.049 26.4358 201.829 26.4508 199.738C26.4672 197.457 25.9749 195.366 25.2345 194.513C24.1849 193.305 24.154 192.591 25.0165 189.667C27.1307 182.491 33.1216 175.375 41.4945 170.075C44.0781 168.443 46.3943 166.582 46.6392 165.943C47.7088 163.159 45.6984 160.015 38.0179 152.534L30.0052 144.734L30.8467 141.615C31.3117 139.899 32.7504 134.699 34.043 130.063C35.3355 125.422 36.3966 120.676 36.3966 119.518C36.3966 116.439 38.5327 113.884 42.5546 112.157C46.508 110.466 46.9715 108.954 44.8309 104.812C43.6875 102.601 43.695 101.733 44.8149 95.7543C45.4968 92.1163 46.1553 88.5083 46.2806 87.7348C46.3369 87.3905 46.4601 87.1959 46.7171 87.1909ZM313.339 89.6561C313.394 89.6561 313.454 89.6761 313.499 89.716C314.187 90.4047 312.122 195.231 311.359 198.101C310.496 201.369 307.123 204.653 302.723 206.49C301.167 207.143 299.191 207.677 298.328 207.677C296.792 207.677 296.767 207.063 297.301 181.977C297.765 160.109 298.084 155.389 299.451 150.224C301.91 140.922 302.998 136.495 304.11 131.201C304.674 128.531 305.412 125.252 305.766 123.915C306.116 122.582 307.009 118.041 307.742 113.814C308.48 109.582 309.433 104.592 309.847 102.726C310.266 100.859 310.61 98.2095 310.61 96.8272C310.61 94.5616 312.526 89.6811 313.339 89.6561ZM306.744 92.3709L306.295 94.8011C306.046 96.1385 305.607 98.8633 305.327 100.869C304.409 107.432 298.737 134.848 298.189 135.392C298.014 135.567 297.655 134.594 297.376 133.212C297.101 131.829 296.512 130.926 296.069 131.201C295.625 131.475 294.921 132.862 294.507 134.29C293.519 137.713 293.41 120.701 294.367 112.437C294.956 107.357 295.505 105.95 298.688 101.508C300.693 98.7036 303.337 95.5098 304.549 94.3969L306.744 92.3709ZM353.907 190.146C354.62 190.091 355.109 190.635 355.403 191.803C355.673 192.886 356.107 194.318 356.366 194.997C356.68 195.815 356.047 196.644 354.495 197.447C350.624 199.448 337.434 203.615 328.4 205.696C318.657 207.937 315.459 208.156 315.459 206.569C315.459 205.966 315.689 203.84 315.958 201.844C316.407 198.58 316.637 198.255 318.343 198.585C320.114 198.924 329.742 197.158 335.274 195.481C336.611 195.077 340.986 193.819 344.987 192.676C348.993 191.529 352.864 190.406 353.592 190.196C353.702 190.161 353.802 190.156 353.907 190.146ZM65.0004 190.785C65.0153 190.78 65.0303 190.785 65.0453 190.805C65.3596 191.089 66.8911 195.815 68.4625 201.315C70.0289 206.814 71.5155 211.949 71.75 212.727C72.0543 213.735 71.72 214.15 70.6126 214.15C67.7391 214.15 66.1777 212.233 65.9682 208.441C65.8584 206.465 65.4793 201.559 65.1251 197.557C64.7908 193.799 64.7509 190.9 65.0004 190.785ZM310.236 208.331C310.481 208.296 310.61 208.326 310.61 208.426C310.61 209.723 306.445 214.175 304.08 215.397C301.341 216.809 297.181 216.944 296.378 215.647C295.744 214.614 298.129 212.528 299.95 212.528C300.588 212.528 303.247 211.44 305.856 210.108C307.817 209.114 309.513 208.436 310.236 208.331ZM75.9753 227.559C76.1699 227.579 76.3694 228.008 76.6638 228.711C77.9658 231.86 80.7195 239.436 81.2932 241.457C81.5726 242.455 81.6823 243.268 81.5426 243.248C81.398 243.228 79.5272 241.596 77.3771 239.615L73.4661 236.012L74.6484 231.351C75.3418 228.627 75.6511 227.534 75.9753 227.559ZM89.4147 247.864C89.719 247.969 91.1557 250.13 93.0464 253.384C104.126 272.477 114.961 285.227 130.227 297.144C138.952 303.955 145.597 310.448 145.597 312.18C145.597 315.323 138.278 312.828 128.027 306.191C119.87 300.906 115.914 296.45 108.681 284.448C105.089 278.49 100.619 271.139 98.7534 268.105C95.4709 262.775 90.5621 252.281 89.3997 248.129C89.3399 247.919 89.3449 247.839 89.4147 247.864ZM68.682 283.326L70.2085 286.978C79.173 308.392 90.3426 326.397 98.9081 333.229C102.435 336.043 124.869 347.646 126.799 347.656C127.603 347.661 138.268 352.037 141.281 353.599C142.603 354.283 144.165 355.411 144.739 356.109C146.26 357.936 145.088 357.676 132.661 353.534C123.078 350.341 100.774 340.929 85.9376 333.808C80.4901 331.193 75.5463 329.052 74.9477 329.052C74.3491 329.052 73.1817 327.031 72.3436 324.561C70.8221 320.079 70.8121 320.054 72.608 315.044C75.1023 308.087 74.9377 303.666 71.9545 297.283C70.6076 294.399 69.3305 290.087 69.101 287.692L68.682 283.326ZM73.8851 333.089C74.0847 333.089 78.9286 335.38 84.6456 338.174C90.3625 340.969 97.7706 344.592 101.108 346.219C108.117 349.647 108.002 349.687 98.2695 347.281C87.3095 344.572 80.5998 343.439 77.1277 343.738L74.0098 344.023L73.7604 338.548C73.6307 335.544 73.6906 333.089 73.8851 333.089Z"
                fill="black"
                fill-opacity="0.071066"
              />
              <path
                d="M140.299 0.688588C140.129 0.678607 139.954 0.688587 139.77 0.703558H139.74C139.595 0.703558 139.431 0.733498 139.256 0.76344C139.236 0.76843 139.211 0.758449 139.191 0.76344C136.971 1.11775 133.659 2.71466 128.062 6.00328C117.541 12.1913 84.5358 23.8686 68.4774 27.0874C59.1986 28.9488 53.2522 31.384 48.3523 35.3214C44.6139 38.3306 38.9667 46.5397 36.4734 52.6029C33.9153 58.8208 30.5963 70.3585 30.5963 73.0133C30.5963 74.4855 29.5372 76.7511 27.8366 78.9568C24.9592 82.6895 23.3234 87.2457 23.3159 91.5424C23.3134 92.9347 22.8041 94.5016 22.178 95.0206C19.7077 97.0717 18.3667 101.663 18.031 109.228C17.7616 115.306 17.0238 118.884 14.4611 126.804C7.25904 149.046 2.26592 168.313 1.06965 178.419C0.899538 179.861 0.631898 183.629 0.399428 187.811C0.389201 187.99 0.378356 188.155 0.368249 188.34C0.247135 190.171 0.132197 191.988 0.0720446 193.654C0.00259294 195.581 -0.0311751 197.088 -0.0526661 198.365C-0.101585 201.275 -0.00974393 202.762 0.321481 203.62C0.326908 203.64 0.331508 203.665 0.33707 203.685C0.868558 205.486 2.00986 205.921 4.15646 206.365C5.57138 206.659 8.7247 207.742 11.1715 208.77C15.7161 210.666 24.0795 213.271 30.5962 214.833C32.5981 215.312 39.8665 215.921 46.7623 216.176C58.4851 216.61 59.4429 216.764 61.3535 218.546C62.481 219.594 64.6111 220.882 66.0927 221.4C66.8909 221.675 67.4446 221.87 67.8238 222.119C67.8288 222.119 67.8338 222.129 67.8388 222.134C68.0682 222.309 68.2428 222.528 68.3825 222.773C68.597 223.312 68.4873 224.115 68.2578 225.547C67.9784 227.314 66.9857 230.817 66.0628 233.327C65.1349 235.842 64.0424 240.079 63.6134 242.749C62.7603 248.074 63.3191 258.868 64.6909 263.474C65.4093 265.894 65.2846 267.167 63.9576 270.755C59.7173 282.252 59.9068 290.072 64.641 300.093C67.4247 305.966 67.5394 308.092 65.3744 313.143C63.5535 317.399 63.2542 324.096 64.7658 326.756C65.4542 327.964 65.9281 334.945 66.2624 348.749C66.6814 365.82 67.0306 370.162 68.5721 376.55C70.0288 382.603 70.5725 385.198 73.2963 386.221C73.3113 386.226 73.3262 386.231 73.3412 386.236C74.1144 386.62 75.0373 386.77 76.1797 386.75C78.3099 386.895 81.2033 386.765 85.3289 386.61C99.9954 386.056 107.773 387.014 122.589 391.196C133.788 394.355 138.997 395.458 148.684 396.76C149.602 396.885 151.283 397.01 153.379 397.12C153.888 397.234 154.252 397.339 154.312 397.404C154.576 397.664 159.999 397.679 167.003 397.559H167.192C168.439 397.579 169.692 397.579 170.934 397.589C199.828 397.788 212.294 395.363 239.682 384.18C261.003 375.472 268.362 373.266 280.668 371.889C284.24 371.489 287.528 370.811 287.962 370.377C291.534 366.803 290.611 334.766 286.67 325.324C285.542 322.624 285.602 322.15 287.777 316.885C291.833 307.044 292.975 302.219 292.965 294.943C292.96 289.369 292.616 287.388 291.11 284.478C289.069 280.531 289.089 279.613 291.549 267.032C292.626 261.518 292.99 255.469 293.03 242.185L293.075 224.689L293.853 224.644H298.034C303.935 224.644 308.226 223.022 312.077 219.325C314.591 216.914 315.923 216.41 323.945 214.898C339.923 211.879 359.199 205.337 362.482 201.813C362.881 201.384 363.17 201.03 363.37 200.611L363.385 200.581C363.699 200.102 363.854 199.623 363.854 199.144C363.854 198.759 363.679 197.821 363.4 196.574C363.185 195.211 362.836 193.45 362.337 190.96C360.861 183.554 349.766 146.93 343.959 130.277C341.495 123.206 341.205 121.564 341.654 117.287C342.273 111.379 341.19 106.943 338.098 102.676C336.317 100.216 335.688 98.2444 335.274 93.8479C334.296 83.4081 328.075 59.0055 324.708 52.3684C318.547 40.2369 309.343 34.6527 277.456 23.7189C254.598 15.8841 245.394 12.0915 239.103 7.87465C235.866 5.70885 232.563 3.68278 231.76 3.38336C230.927 3.07895 228.333 3.6728 225.699 4.77067C216.615 8.56331 201.334 10.8639 185.086 10.9038C167.726 10.9387 158.238 8.7779 141.655 1.01296C141.251 0.823324 140.808 0.71354 140.299 0.688588ZM138.742 7.70498C138.747 7.70498 138.747 7.69999 138.757 7.70498C138.762 7.70498 138.767 7.71995 138.772 7.71995L142.638 14.3621C147.522 22.7757 159.076 34.498 166.1 38.1908C168.779 39.5981 173.688 41.3797 177.01 42.1382C182.243 43.3259 183.76 43.3708 188.314 42.4825C194.73 41.23 201.928 38.091 208.254 33.7644C212.938 30.5606 224.821 18.8035 228.847 13.3989L230.937 10.6044L234.693 12.9897C237.023 14.4669 238.465 15.9689 238.465 16.9221C238.47 19.1228 233.586 27.4966 229.001 33.1556C220.401 43.775 209.222 50.512 195.393 53.3964C170.64 58.5613 144.359 45.1324 131.943 20.9742C128.381 14.0377 128.486 13.5936 134.627 10.0604L138.742 7.70498ZM145.163 11.1034C145.861 11.1483 148.315 11.8769 151.104 12.8799C161.161 16.5129 165.741 17.506 177.713 17.7156C182.363 18.1996 185.341 18.1996 191.043 17.7156C204.183 17.481 212.739 16.4331 220.66 14.1625C222.167 13.7283 223.544 13.5187 223.718 13.6934C223.893 13.868 221.733 16.438 218.914 19.4023C213.731 24.8567 213.402 25.0164 204.093 27.1373C199.169 28.2551 178.971 28.7342 172.211 27.8858C168.913 27.4716 163.256 26.2141 159.63 25.1062C153.055 23.1001 153.015 23.0901 148.824 17.2964C146.515 14.0976 144.788 11.323 144.978 11.1333C144.998 11.1134 145.063 11.0984 145.163 11.1034ZM125.163 15.8292C125.168 15.8292 125.173 15.8442 125.178 15.8442L128.62 22.4564C131.379 27.7511 133.868 30.91 140.987 38.0661C148.251 45.3769 151.099 47.6475 156.372 50.3273C172.396 58.4615 187.376 59.6842 203.844 54.1948C218.805 49.2045 231.775 37.7118 239.712 22.3965C241.313 19.3025 242.306 18.1896 243.234 18.4791C243.952 18.7036 248.003 20.2407 252.233 21.8825C256.459 23.5193 267.693 27.5365 277.207 30.8152C308.864 41.739 316.342 46.9539 320.997 61.3659L322.573 66.2165C322.583 66.3862 322.588 66.5608 322.588 66.7155C322.588 66.8303 322.558 66.97 322.523 67.1347L318.956 73.5323C316.871 77.2451 314.302 81.3372 313.249 82.6197C311.389 84.9003 311.329 84.9152 311.299 83.1037C311.269 81.0178 309.104 76.3718 307.732 75.4636C306.31 74.5254 301.985 74.7549 300.917 75.8229C298.882 77.8539 299.81 78.9418 303.117 78.4128C305.766 77.9887 306.41 78.1733 307.153 79.5656C308.485 82.0508 308.296 87.7996 306.889 87.7996C306.255 87.7996 303.566 90.195 300.917 93.1343C298.268 96.0686 295.904 98.1645 295.664 97.7803C295.24 97.0916 295.864 93.4187 297.909 84.5559C298.473 82.1057 298.757 79.8201 298.548 79.4558C297.635 77.8839 295.36 81.6665 294.402 86.3475C291.958 98.3043 291.589 100.64 290.566 110.336C290.112 114.623 288.76 147.359 287.822 176.797C287.373 190.815 286.735 204.658 286.386 207.552C286.026 210.562 282.774 221.63 278.778 233.437C274.942 244.785 270.627 257.715 269.195 262.166C262.904 281.704 251.919 308.641 246.526 317.744C244.765 320.713 243.329 323.368 243.329 323.652C243.329 323.942 244.107 324.061 245.06 323.922C248.901 323.348 260.036 298.396 272.417 262.571C273.42 259.676 275.456 254.222 276.942 250.439C278.429 246.657 280.798 240.199 282.21 236.092C283.622 231.98 285.094 228.816 285.483 229.056C286.48 229.674 286.371 251.767 285.328 260.105C283.003 278.689 281.472 284.064 274.757 297.189C268.232 309.944 260.689 323.148 251.61 337.69C248.038 343.404 246.646 346.323 247.21 346.892C247.669 347.346 248.417 347.486 248.881 347.202C250.991 345.899 270.382 312.773 278.733 296.225C283.507 286.759 284.116 285.915 285.109 287.273C286.934 289.768 286.825 297.588 284.874 304.644C282.824 312.075 278.06 322.599 274.617 327.305C272.827 329.75 267.399 335.669 262.55 340.45C254.109 348.773 251.869 352.511 257.187 349.372C259.791 347.83 271.814 335.689 276.628 329.735L279.576 326.087L280.479 328.393C282.1 332.585 283.357 343.713 283.677 352.521V352.741C283.667 353.409 283.647 354.078 283.627 354.752L283.427 362.502V362.517C283.273 363.46 283.068 364.109 282.804 364.373C282.27 364.907 280.609 365.341 279.122 365.341C271.544 365.341 256.524 369.878 236.859 378.097C218.81 385.642 204.482 389.494 188.189 391.211C181.136 391.955 164.813 392.119 158.397 391.506C148.076 390.527 134.691 387.753 121.871 383.945C111.544 380.876 97.4614 378.99 86.1422 379.155C81.3232 379.229 77.0778 378.965 76.7087 378.581C75.1522 376.939 73.486 361.569 73.1717 345.879C72.9024 332.465 72.5532 328.453 71.3609 325.104C70.6674 323.143 70.2983 322.085 70.3033 320.972C70.3083 320.813 70.3232 320.648 70.3332 320.488C70.3382 320.453 70.3482 320.413 70.3482 320.379C70.4729 319.311 70.9119 318.038 71.6901 315.827C74.1994 308.676 74.0098 304.429 70.877 297.688C67.35 290.087 67.2652 283.595 70.5677 273.719L72.9223 266.658L71.5804 261.059C69.5949 252.835 69.8244 244.66 72.2488 237.01C73.3613 233.507 74.4887 229.45 74.7631 227.998C75.3368 224.929 76.3246 224.554 76.9431 227.169C78.1454 232.244 87.4642 257.625 91.0061 265.47C98.0251 281.025 110.736 302.947 119.361 314.36C122.958 319.116 130.012 326.492 130.96 326.492C132.706 326.492 131.434 323.847 127.578 319.46C125.308 316.875 121.891 312.584 120 309.914C111.569 298.007 94.8473 266.039 88.2474 249.222C85.5385 242.32 80.3454 226.461 75.5264 210.392C74.3241 206.385 73.0221 202.193 72.643 201.08C69.8244 192.876 65.6689 167.789 66.482 163.902C66.7215 162.759 66.4122 158.732 65.7986 154.949C62.8403 136.725 62.2965 117.053 64.2521 99.9311C66.4271 80.9479 66.492 77.3449 64.8158 72.8436C63.6634 69.7596 62.4612 68.2575 59.6875 66.4211C57.692 65.0987 55.7864 63.5367 55.4322 62.9578C54.4794 61.4008 50.9923 61.6803 50.0844 63.377C49.6509 64.1854 49.1745 67.1896 49.0228 70.0541C48.8712 72.9185 48.3957 75.234 47.9627 75.214C46.7415 75.1641 40.9008 65.0438 40.9008 62.9728C40.9008 60.1383 44.2906 52.3035 47.7288 47.1734C52.6336 39.8576 57.1632 37.0081 67.4049 34.7924C82.1363 31.6036 108.706 23.155 120.549 17.8902L125.163 15.8292ZM161.361 29.7422C161.69 29.7223 162.323 29.8271 163.326 30.0067C164.843 30.2712 167.347 30.9 168.903 31.394C173.029 32.6965 198.461 32.5867 202.327 31.2543C204.028 30.6654 205.61 30.371 205.839 30.5956C206.433 31.1894 199.569 35.0769 194.58 36.9782C189.681 38.8396 180.432 39.5382 175.873 38.3955C171.971 37.4124 163.81 33.1207 161.954 31.0647C161.111 30.1365 160.812 29.7672 161.361 29.7422ZM54.1501 66.9351C56.1705 67.01 60.346 71.7208 61.3238 75.214C62.2616 78.5725 61.9324 87.7098 60.76 90.7639C60.5356 91.3527 60.1115 91.8318 59.8272 91.8218C58.3456 91.7769 51.5411 86.7367 49.3191 84.0419C46.1843 80.2343 46.0067 78.6624 48.7888 79.361C51.2068 79.9698 51.4862 79.4159 52.2644 72.4245C52.5787 69.6049 53.2621 67.1497 53.776 66.98C53.8907 66.9401 54.0154 66.9301 54.1501 66.9351ZM323.661 72.9235C323.955 72.9385 324.139 73.3776 324.409 74.0912C325.92 78.0885 328.185 90.0103 328.729 96.8621C329.238 103.235 329.497 104.098 331.583 105.949C334.446 108.504 335.065 110.875 334.496 117.148C334.092 121.609 334.476 123.301 338.472 135.068C342.303 146.351 353.947 184.163 354.126 185.874C354.116 185.924 354.121 185.969 354.106 186.014C353.448 186.743 342.752 190.206 336.352 191.738C333.458 192.432 329.817 193.335 328.26 193.749C326.704 194.163 323.701 194.937 321.59 195.466C317.639 196.459 316.223 198.645 319.53 198.645C321.865 198.645 336.252 195.102 346.359 192.032C350.754 190.705 354.605 189.917 354.934 190.286C355.264 190.655 355.972 192.237 356.496 193.799C357.344 196.324 357.259 196.768 355.777 197.976C353.518 199.817 338.716 204.668 327.856 207.128C322.962 208.241 317.939 209.154 316.691 209.159L314.417 209.174L314.856 202.497C315.878 186.668 316.387 167.201 316.851 125.821L317.35 81.936C317.43 81.7414 317.525 81.5368 317.629 81.3122L320.498 76.9157C322.403 74.0164 323.177 72.8935 323.661 72.9235ZM37.6727 74.0463C38.0074 74.0463 39.1802 75.768 40.2917 77.8789C42.3126 81.7214 51.0063 90.6391 57.0176 95.0655L60.2003 97.421L59.4969 104.143C58.7137 111.564 59.3722 138.157 60.5745 147.669C60.9935 151.007 61.662 158.473 62.0711 164.261C62.4801 170.045 63.1935 176.967 63.6425 179.637C64.6053 185.316 68.7209 201.414 71.1104 208.815C72.0284 211.649 72.5472 214.344 72.2678 214.803C71.1803 216.56 68.252 215.522 65.2987 212.323C62.0162 208.77 61.5872 208.66 53.6353 209.593C53.2162 209.643 52.7772 209.683 52.3432 209.718L48.7415 209.688C46.8987 209.668 45.3477 209.638 43.9239 209.608C34.3572 208.775 22.6285 205.716 10.7655 200.815C6.77913 199.169 5.87819 198.43 5.66767 196.698C5.51701 195.456 5.8712 194.423 6.52521 194.173C7.13682 193.939 11.3921 195.052 15.9876 196.649C21.9396 198.724 24.5985 199.313 25.2166 198.695C26.7915 197.118 23.1548 194.702 14.9121 191.833C10.8489 190.416 7.41119 189.153 6.837 188.869C6.77664 188.774 6.72426 188.674 6.68085 188.574C6.6594 186.528 9.46501 171.258 11.2484 163.887C13.4759 154.685 19.8917 132.817 21.7405 128.126C23.5264 123.595 25.7274 111.529 25.7314 106.264C25.7334 103.424 26.2413 101.887 27.7423 100.106C30.4088 96.9319 31.406 94.0974 31.406 89.686C31.406 86.6718 31.8964 85.4392 34.2275 82.7145C36.2305 80.374 37.0646 78.6274 37.0646 76.7261C37.0646 75.249 37.3384 74.0463 37.6727 74.0463ZM313.918 88.628C313.973 88.633 314.018 88.648 314.057 88.6879C314.656 89.2868 313.08 192.317 312.406 196.619C311.663 201.404 309.932 203.8 305.113 206.724C301.775 208.75 299.546 209.449 295.385 209.783L289.987 210.217L290.536 207.068C291.644 200.676 292.641 170.819 292.591 145.248C292.547 120.222 293.27 109.063 295.181 105.45C296.657 102.666 305.672 91.8368 306.515 91.8368C307.098 91.8368 307.208 93.264 306.859 96.0935C305.312 108.564 299.341 137.119 297.176 142.414C296.632 143.746 295.51 147.479 294.682 150.708C293.469 155.433 293.36 156.776 294.183 157.584C294.996 158.388 295.41 158.318 296.238 157.195C297.096 156.037 300.409 143.492 304.829 124.618C306.096 119.214 307.822 109.627 308.834 102.366C309.368 98.5837 309.952 94.7561 310.131 93.8679C310.516 91.9865 313.08 88.5282 313.918 88.628ZM310.506 207.552C313.459 207.552 307.737 215.173 303.427 216.969C300.878 218.037 297.964 218.237 294.697 217.548C293.809 217.358 292.252 217.029 291.25 216.814C288.107 216.146 289.045 214.025 292.482 214.025C295.859 214.025 302.564 211.789 306.919 209.219C308.475 208.301 310.087 207.552 310.506 207.552ZM95.5258 227.075C93.7847 226.985 94.3884 229.23 97.8156 236.092C116.513 273.504 151.393 313.976 184.682 336.877C190.315 340.754 194.924 341.528 192.555 338.204C192.23 337.745 188.219 334.721 183.655 331.482C172.7 323.717 167.073 319.001 156.482 308.731C136.003 288.865 115.356 261.503 102.45 237.105C99.3171 231.182 96.7779 227.289 95.9149 227.124C95.7802 227.094 95.6405 227.085 95.5258 227.075ZM75.0874 331.203C74.4837 331.252 74.2792 331.602 74.2792 332.201C74.2792 333.114 87.0202 339.576 100.42 345.455C105.837 347.83 112.816 350.989 115.934 352.456C119.047 353.928 125.418 356.479 130.087 358.15C138.722 361.234 143.432 361.813 143.432 359.772C143.432 359.558 143.037 359.248 142.374 358.869C142.364 358.859 142.354 358.844 142.339 358.834C142.05 358.594 141.656 358.39 141.252 358.275C141.232 358.27 141.222 358.265 141.202 358.26C139.476 357.452 136.812 356.439 133.315 355.266C121.786 351.393 102.784 343.464 86.7009 335.804C79.6619 332.45 76.4243 331.093 75.0874 331.203Z"
                fill="#CDCDCD"
              />
            </svg>
          </div>
        </div>
        <div class="${(layoutSelected === "alignTwoCols" && blockSelected !== "previewShop") ? "layout-two-cols" : (layoutSelected === "alignLeft" ? "align-left" : "align-center")}">
          <div class="card-info-preview">
            <span class="product-title-preview">Product Name</span>
            <span class="product-brandname-preview">Brandname</span>
            <span class="product-price-preview">100 NOK</span>
          </div>
          <div class="card-btn-preview ${layoutSelected === "alignLeft" && "align-left"}">
            <span>Buy Now</span>
          </div>
        </div>
      `;

      const handlePreviewProductContent = (i, imgContainer, isShop) => {
        const { title, price } = selectedProducts[i];
        const brandName = selectedProducts[i].supplier?.name || 'Brand Name';
        const shortTitle = title.length < 16 ? title : `${title.substring(0,14)}...`;
        const displaySelectedProducts = document.getElementById("obj-selected-products");
        displaySelectedProducts.innerHTML = `${JSON.stringify(selectedProducts, null, 2)}`;
        const content = `
          <div class="img-preview" id="${imgContainer}">
            <div class="container-null-product">
            </div>
          </div>
          <div class="${(layoutSelected === "alignTwoCols" && blockSelected !== "previewShop") ? "layout-two-cols" : (layoutSelected === "alignLeft" ? "align-left" : "align-center")}">
            <div class="card-info-preview">
              <span class="product-title-preview">${isShop ? shortTitle : title}</span>
              <span class="product-brandname-preview">${brandName}</span>
              <span class="product-price-preview">${price.amount} ${price.currency_code}</span>
            </div>
            <div class="card-btn-preview ${layoutSelected === "alignLeft" && "align-left"}">
              <span>Buy Now</span>
            </div>
          </div>
        `;
        return content;
      }

      const nullBuyButtonContent = `
        <div class="card-btn-preview">
          <span>Buy Now</span>
        </div>
      `;

      const nullAddToCartButtonContent = `
        <div class="card-btn-preview-outlined">
          <span>Add to cart</span>
        </div>
      `;

      const handlePreviewBuyButtonContent = () => {
        const { title, price } = selectedProducts[0];
        const brandName = selectedProducts[0].supplier?.name || 'Brand Name';
        const displaySelectedProducts = document.getElementById("obj-selected-products");
        displaySelectedProducts.innerHTML = `${JSON.stringify(selectedProducts, null, 2)}`;
        const content = `
          <div>
            <div class="card-btn-preview">
              <span>Buy Now</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:center;margin-top:50px;">
              <span style="padding-right:15px;font-weight:600;">Selected Product:</span>
              <span>${title}</span>
              <span style="padding-left:5px;padding-right:5px;">-</span>
              <span>${brandName}</span>
              <span style="padding-left:5px;padding-right:5px;">-</span>
              <span>${price.amount} ${price.currency_code}</span>
            </div>
          </div>
        `;
        return content;
      }
      
      const handlePreviewAddToCartButtonContent = () => {
        const { title, price } = selectedProducts[0];
        const brandName = selectedProducts[0].supplier?.name || 'Brand Name';
        const displaySelectedProducts = document.getElementById("obj-selected-products");
        displaySelectedProducts.innerHTML = `${JSON.stringify(selectedProducts, null, 2)}`;
        const content = `
          <div>
            <div class="card-btn-preview-outlined">
              <span>Add to cart</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:center;margin-top:50px;">
              <span style="padding-right:15px;font-weight:600;">Selected Product:</span>
              <span>${title}</span>
              <span style="padding-left:5px;padding-right:5px;">-</span>
              <span>${brandName}</span>
              <span style="padding-left:5px;padding-right:5px;">-</span>
              <span>${price.amount} ${price.currency_code}</span>
            </div>
          </div>
        `;
        return content;
      }
      
      switch (blockSelected) {
        case 'previewSingleProduct':
          const singleProductContainer = document.getElementById("single-product-product");
          if(selectedProducts[0]) {
            const i = 0;
            const img = selectedProducts[i].images[0] || selectedProducts[i].images[1];
            const { url } = img;
            const imgContainer = 'img-preview-single-product'
            singleProductContainer.innerHTML = handlePreviewProductContent(0, imgContainer);
            document.getElementById(imgContainer).style.backgroundImage = `url(${url})`;
          }   
          if(!selectedProducts[0]) {
            singleProductContainer.innerHTML = nullProductContent;
          }
          break;
        
        case 'previewBuyButton':
          if (actionButtonTypeSelected === "previewAddToCartButton") {
            const previewButtonContainer = document.getElementById("action-button-container");
            if(selectedProducts[0]) {
              previewButtonContainer.innerHTML = handlePreviewAddToCartButtonContent();
            }   
            if(!selectedProducts[0]) {
              previewButtonContainer.innerHTML = nullAddToCartButtonContent;
            }
          } else {
            const buyButtonContainer = document.getElementById("action-button-container");
            if(selectedProducts[0]) {
              buyButtonContainer.innerHTML = handlePreviewBuyButtonContent();
            }   
            if(!selectedProducts[0]) {
              buyButtonContainer.innerHTML = nullBuyButtonContent;
            }
          }
          break;
        
        case 'previewTwoProducts':
          const twoProductsContainer = document.getElementsByClassName("two-products-product-container");
          for(i = 0; i < twoProductsContainer.length; i++) {
            if(selectedProducts[i]) {
              const img = selectedProducts[i].images[0] || selectedProducts[i].images[1];
              const { url } = img;
              const imgContainer = `{img-preview-two-${i}}`
              twoProductsContainer[i].innerHTML = handlePreviewProductContent(i, imgContainer);
              document.getElementById(imgContainer).style.backgroundImage = `url(${url})`;
            }
            if(!selectedProducts[i]) {
              twoProductsContainer[i].innerHTML = nullProductContent;
            }
          }
          break;
          
        case 'previewCarousel':
          const carouselLength = 5;
          jQuery( document ).ready( function( $ ) {
            for (let i = 0; i < carouselLength; i++) {
              //create slides
              $('.swiper-wrapper').append(`<div class="swiper-slide" id="slide${i}"></div>`);
              //create preview of selected products
              if(selectedProducts[i]) {
                const img = selectedProducts[i].images[0] || selectedProducts[i].images[1];
                const { url } = img;
                const imgContainer = `{img-preview-slide-${i}}`
                $(`#slide${i}`).html(handlePreviewProductContent(i, imgContainer))
                document.getElementById(imgContainer).style.backgroundImage = `url(${url})`;
              }
              if(!selectedProducts[i]) {
                $(`#slide${i}`).html(nullProductContent)
              }
            }
            const swiper = new Swiper('.swiper', {
              direction: 'horizontal',
              slidesPerView: 2,
              spaceBetween: 20,
              navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
              },
            });
            //hide empty slides
            const slides = document.getElementsByClassName("swiper-slide");
            for (let i = 0; i < slides.length; i++) {
              if(slides[i].innerHTML == "") {
                slides[i].style.display = "none";
              }
            }
          })
          break;

        case 'previewShop':
          const shopItems = document.getElementsByClassName("product-shop");
          const shopItemsLayout = document.getElementsByClassName("product-shop-layout");
          const shopLength = 7;
          const shopLengthLayout = 8;
          for (let i = 0; i < shopLength; i++) {
            if(selectedProducts[i]) {
              const img = selectedProducts[i].images[0] || selectedProducts[i].images[1];
              const { url } = img;
              const imgContainer = `{img-preview-shop-${i}}`
              const isShop = true;
              shopItems[i].innerHTML = handlePreviewProductContent(i, imgContainer, isShop);
              document.getElementById(imgContainer).style.backgroundImage = `url(${url})`;
            }
            if(!selectedProducts[i]) {
              shopItems[i].innerHTML = nullProductContent;
            }
          }
          for (let i = 0; i < shopLengthLayout; i++) {
            if(selectedProducts[i]) {
              const img = selectedProducts[i].images[0] || selectedProducts[i].images[1];
              const { url } = img;
              const imgContainer = `{img-preview-shop-layout-${i}}`
              const isShop = true;
              shopItemsLayout[i].innerHTML = handlePreviewProductContent(i, imgContainer, isShop);
              document.getElementById(imgContainer).style.backgroundImage = `url(${url})`;
            }
            if(!selectedProducts[i]) {
              shopItemsLayout[i].innerHTML = nullProductContent;
            }
          }
          break;
          
        default:
          return;
          break;
      }
    }

    if (sellerId != '') {
      handleChangePreview(blockSelected);
    }

    //show/hide shortcodes
    const handleShowShortcodes = () => {
      if (blockSelected) {
        const shortcodesContainer = document.getElementById("shortcodes-container");
        shortcodesContainer.style.display = "block";

        if(!shortcodeBuyButtonIds.length || blockSelected !== "previewBuyButton") {
          document.getElementById('shortcode-action-button').style.display = 'none';
        } else {
          document.getElementById('shortcode-action-button').style.display = 'block';
        }

        if(!shortcodeSingleIds.length || blockSelected !== "previewSingleProduct") {
          document.getElementById('shortcode-single').style.display = 'none';
        } else {
          document.getElementById('shortcode-single').style.display = 'block';
        }

        if(!shortcodeTwoIds.length || blockSelected !== "previewTwoProducts") {
          document.getElementById('shortcode-two').style.display = 'none';
        } else {
          document.getElementById('shortcode-two').style.display = 'block';
        }

        if(!shortcodeCarouselIds.length || blockSelected !== "previewCarousel") {
          document.getElementById('shortcode-carousel').style.display = 'none';
        } else {
          document.getElementById('shortcode-carousel').style.display = 'block';
        }

        if(!shortcodeShopIds.length || blockSelected !== "previewShop") {
          document.getElementById('shortcode-shop').style.display = 'none';
        } else {
          document.getElementById('shortcode-shop').style.display = 'block';
        }

      } else {
        const shortcodesContainer = document.getElementById("shortcodes-container");
        shortcodesContainer.style.display = "none"
      }
    }

    if(sellerId) {
      handleShowShortcodes();
    }

    //copy shortcode to clipboard
    const copyToClipboard = (id) => {
      const textCopy = document.getElementById(id);
      if (!navigator.clipboard) {
        return
      }
      const text = textCopy.innerText
      try {
        navigator.clipboard.writeText(text)
      } catch (err) {
        console.error('Failed to copy!', err)
      }

      //copy confirm
      let confirmId;
      if (id === 'shortcode-action-button-container') {confirmId = 'action-button-copied'}
      if (id === 'shortcode-single-container') {confirmId = 'single-copied'}
      if (id === 'two-products-ids-container') {confirmId = 'two-copied'}
      if (id === 'carousel-ids-container') {confirmId = 'carousel-copied'}
      if (id === 'shop-ids-container') {confirmId = 'shop-copied'}

      const textConfirm = document.getElementById(confirmId);

      if (textConfirm.classList.contains("copy-hide")) {
        textConfirm.classList.remove("copy-hide");
      }
      textConfirm.classList.add("copy-confirm");

      setTimeout(() => {
        textConfirm.classList.remove("copy-confirm");
        textConfirm.classList.add("copy-hide");
      }, 5000);
    }
    
    //tab system handler
    const tabSystem = {
      init(){
        document.querySelectorAll('.tabs-menu').forEach(tabMenu => {
          Array.from(tabMenu.children).forEach((child, ind) => {
            child.addEventListener('click', () => {
              tabSystem.toggle(child.dataset.target);
            });
            if(child.className.includes('is-active')){
              tabSystem.toggle(child.dataset.target);
            }
          });
        });
      },
      toggle(targetId){
        document.querySelectorAll('.tab-content').forEach(contentElement=>{
          contentElement.style.display = contentElement.id === targetId ? 'block' : 'none';
          document.querySelector(`[data-target="${contentElement.id}"]`).classList[contentElement.id === targetId ? 'add' : 'remove']('is-active');
          document.querySelector(`[data-target="${contentElement.id}"]`).classList[contentElement.id !== targetId ? 'add' : 'remove']('inactive');
        })
      },
    };
    tabSystem.init();

    //Custom shop button, border ratio selector handler //
    let input1 = document.querySelector("#shop-button-ratio");
    let input2 = document.querySelector("#select-border-ratio");
    let input3 = document.querySelector("#select-title-size");

    if (input1) {
      updateInput(input1) 
      input1.addEventListener("input", function(evt) {
        updateInput(input1)
      });
    }
    
    if(input2) {
      updateInput(input2);
      input2.addEventListener("input", function(evt) {
        updateInput(input2)
      });
    }

    if(input3) {
      updateInput(input3);
      input3.addEventListener("input", function(evt) {
        updateInput(input3)
      });
    }
    
    function updateInput(input){
      var label = input.parentElement.querySelector(".demo");
      label.innerHTML = `${input.value}px`;
      var inputMin = input.getAttribute("min");
      var inputMax = input.getAttribute("max");
      var unidad = (inputMax - inputMin) / 100;
      input.style.setProperty("--value", (input.value - inputMin)/unidad);  
    }


		jQuery( document ).ready( function( $ ) {
      // update block title live
      $(".update-block-title").keyup(function(){
        var output = $(this).val(); 
        $('.block-title-replace').html(output);
        if (output !== "") {
          $('.block-title-container').css("display","block");
        } else {
          $('.block-title-container').css("display","none");
        }
      });

      $(".copy-btn").click(function(){
        $(".text-copy-confirm").show();
        setTimeout(() => {
          $(".text-copy-confirm").hide();
        }, 1000);
      });

      //selected products update on server
      function updateSelectedProducts(e) {
        e.preventDefault();
        selectedProducts = [];
        
        //pass ids array to string
        const selectedsString = selectedIds.toString();
        const updateShortcode = (data) => {
          $.ajax({
            url: outshifter_blocks_admin_vars.ajaxurl,
            type: 'post',
            data: data
          })
        }

        if(blockSelected === 'previewBuyButton') {
          const data = {
            action: 'outshifter_blocks_save_selected_buy_button',
            shortcodeBuyButton: selectedsString,
          };
          updateShortcode(data);
          shortcodeBuyButtonIds = selectedsString;
          $("#action-button-ids").html(shortcodeBuyButtonIds);
        }

        if(blockSelected === 'previewSingleProduct') {
          const data = {
            action: 'outshifter_blocks_save_selected_products_single',
            shortcodeSingle: selectedsString,
          };
          updateShortcode(data);
          shortcodeSingleIds = selectedsString;
          $("#single-product-ids").html(shortcodeSingleIds);
        }

        if(blockSelected === 'previewTwoProducts') {
          const data = {
            action: 'outshifter_blocks_save_selected_products_two',
            shortcodeTwo: selectedsString,
          };
          updateShortcode(data);
          shortcodeTwoIds = selectedsString;
          $("#two-products-ids").html(shortcodeTwoIds);
        }

        if(blockSelected === 'previewCarousel') {
          const data = {
            action: 'outshifter_blocks_save_selected_products_carousel',
            shortcodeCarousel: selectedsString,
          };
          updateShortcode(data)
          shortcodeCarouselIds = selectedsString;
          $("#carousel-ids").html(shortcodeCarouselIds);
        }

        if(blockSelected === 'previewShop') {
          const data = {
            action: 'outshifter_blocks_save_selected_products_shop',
            shortcodeShop: selectedsString,
          };
          updateShortcode(data)
          shortcodeShopIds = selectedsString;
          $("#shop-ids").html(shortcodeShopIds);
        }

        document.querySelectorAll("[data-close]").forEach((el) => {
          el.click();
        });
        handleSelectedProducts();
        createPreview();
        handleShowShortcodes();
      }

      $('#btn-select-product').on("click", (e) => {
        updateSelectedProducts(e);
        var containers = document.getElementsByClassName("shortcode-box-container");
        for (var i = 0, len = containers.length; i < len; i++) {
          containers[i].style.display = "none";
        }
      });

      //loader
      $(document).ready(function() {  
        setTimeout(() => {
          document.getElementById("loader").style.display = "none";
          let adminContent = document.getElementById("outshifter-admin-content");
          if(adminContent) {
            adminContent.style.display = "grid";
          }
        }, 500);
      });

      //isSaved
      $(document).ready(function() {  
        let messageIsSaved = document.getElementById("notice-is-saved");
        setTimeout(() => {
          if (messageIsSaved) {
            messageIsSaved.style.display = "block";
          }
        }, 800);
        setTimeout(() => {
          if (messageIsSaved) {
            messageIsSaved.style.display = "none";
          }
        }, 4000);
      });

      // Shop button, live changes //
      $("#input-text-shop").on("keyup", function() {
        let shopTextButton = $(this).val();
        $("#text-shop").html(shopTextButton);
        $("#input-text-shop").css("display", "block");
        $("#text-shop").css("display", "block");
        $("#saved-text-shop").css("display", "none");
      });

      $("#button-shop-color").wpColorPicker({
        width: 150,
        change: function(event, ui) {
          $(".image-preview-wrapper-shop").css("background-color", ui.color.toString());
        }
      });
      let shopButtonColorSaved = $("#button-shop-color").val();
      if (shopButtonColorSaved) {
        $(".image-preview-wrapper-shop").css("background-color", shopButtonColorSaved);
      };

      $("#text-icon-color").wpColorPicker({
        width: 150,
        change: function(event, ui) {
          $("#text-shop").css("color", ui.color.toString());
          $("#saved-text-shop").css("color", ui.color.toString());
          $(".shop-default-text").css("color", ui.color.toString());
          $(".image-shop-cart-icon").css("fill", ui.color.toString());
        }
      });

      let textIconColorSaved = $("#text-icon-color").val();
      if (textIconColorSaved) {
        $("#text-shop").css("color", textIconColorSaved);
        $("#saved-text-shop").css("color", textIconColorSaved);
        $(".shop-default-text").css("color", textIconColorSaved);
        $(".image-shop-cart-icon").css("fill", textIconColorSaved);
      };

      $("#shop-button-ratio").on("input", function() {
        let shopButtonRatio = $(this).val();
        $(".image-preview-wrapper-shop").css("border-radius", `${shopButtonRatio}px`)
      });

      let buttonRatioSaved = $("#shop-button-ratio").val();
      if (buttonRatioSaved) {
        $(".image-preview-wrapper-shop").css("border-radius", `${buttonRatioSaved}px`);
      }

      if ($("#shop-img-selected").is(':checked')) {
        $("#image-preview_shop").css("display", "block");
        $(".upload-logo-btn-shop").css("display", "block");
        $(".shop-brand-logo-selected").css("border", "2px solid #485bff");
        $(".shop-brand-logo-selected").css("height", "35px");
        $(".shop-brand-text-selected").css("height", "35x");
        $(".shop-brand-text-selected").css("border", "1px solid #2d3b52");
        $("#input-text-shop").css("display", "none");
        $("#text-shop").css("display", "none");
        $("#saved-text-shop").css("display", "none");
        $("#input-text-shop").val('');
        $("#text-shop").val("");
        $("#saved-text-shop").val("");
      }

      $("#shop-img-selected").on('change', function() {
        if ($(this).is(":checked")) {
          $("#image-preview_shop").css("display", "block");
          $(".upload-logo-btn-shop").css("display", "block");
          $(".shop-brand-logo-selected").css("border", "2px solid #485bff");
          $(".shop-brand-logo-selected").css("height", "35px");
          $(".shop-brand-text-selected").css("height", "35px");
          $(".shop-brand-text-selected").css("border", "1px solid #2d3b52");
          $("#input-text-shop").css("display", "none");
          $("#text-shop").css("display", "none");
          $("#saved-text-shop").css("display", "none");
          $("#input-text-shop").val('');
          $("#text-shop").val('');
          $("#saved-text-shop").val('');
        };
      });

      if ($("#shop-text-selected").is(':checked')) {
        $("#image-preview_shop").css("display", "none");
          $(".upload-logo-btn-shop").css("display", "none");
          $(".shop-brand-text-selected").css("border", "2px solid #485bff");
          $(".shop-brand-text-selected").css("height", "35px");
          $(".shop-brand-logo-selected").css("height", "35px");
          $(".shop-brand-logo-selected").css("border", "1px solid #2d3b52");
          $("#text-shop").css("display", "none");
          $("#input-text-shop").css("display", "block");
          $("#saved-text-shop").css("display", "block");
      };

      $("#shop-text-selected").on('change', function() {
        if ($(this).is(":checked")) {
          $("#image-preview_shop").css("display", "none");
          $(".upload-logo-btn-shop").css("display", "none");
          $(".shop-brand-text-selected").css("border", "2px solid #485bff");
          $(".shop-brand-text-selected").css("height", "35px");
          $(".shop-brand-logo-selected").css("height", "35px");
          $(".shop-brand-logo-selected").css("border", "1px solid #2d3b52");
          $("#input-text-shop").css("display", "block");
          $("#text-shop").css("display", "block");
          $("#saved-text-shop").css("display", "none");
          let shopTextButton = $("#input-text-shop").val()
          $("#text-shop").html(shopTextButton)
        };
      });

      if ($("#show-shop-icon").is(':checked')) {
        $(".image-shop-cart-icon").css("display", "none")
      } else {
        $(".image-shop-cart-icon").css("display", "block")
      };

      $("#show-shop-icon").on('change', function() {
        if ($(this).is(":checked")) {
          $(".image-shop-cart-icon").css("display", "none")
        } else {
          $(".image-shop-cart-icon").css("display", "block")
        }
      });

      if ($("#shop-custom-url").is(":checked")) {
        $("#input-url-shop").prop('disabled', false)
      } else {
        $("#input-url-shop").prop('disabled', true);
        $("#input-url-shop").val('');
      };

      $("#shop-custom-url").on('change', function() {
        if ($(this).is(":checked")) {
          $("#input-url-shop").prop('disabled', false)
        } else {
          $("#input-url-shop").prop('disabled', true);
          $("#input-url-shop").val('');
        }
      });
      
      //select shop bg color
      $('.my-color-field').wpColorPicker({width: 150});

      // checkout buttons, preview changes 
      let nextButtonBackgroundPreview;
      let prevButtonBackgroundPreview;
      let nextButtonTextColorPreview;
      let prevButtonTextColorPreview;

      let nextButtonMainColor = $("#button-color-field").val();
      let nextButtonSecondaryColor = $("#button-text-color-field").val();
      let nextButtonType = $("#outshifter-select-button-next-type").val();
      let prevButtonMainColor = $("#button-prev-color-field").val();
      let prevButtonSecondaryColor = $("#button-prev-text-color-field").val();
      let prevButtonType = $("#outshifter-select-button-prev-type").val();
      let buttonBorderRatio = $("#select-border-ratio").val();

      //prev button type
      function handlePrevTypeChange() {
        var selectedOption = $(this).val();
        if (selectedOption === "filled") {
          $(".preview-button-white").css( 'border-color', prevButtonMainColor);
          $(".preview-button-white").css( 'background-color', prevButtonMainColor);
          $(".preview-button-white").css( 'color', prevButtonSecondaryColor);
          $(".prev-button-icon").css('fill', prevButtonSecondaryColor);
        } else if (selectedOption === "outlined") {
          $(".preview-button-white").css( 'background-color', "transparent");
          $(".preview-button-white").css( 'border-color', prevButtonMainColor);
          $(".preview-button-white").css( 'color', prevButtonMainColor);
          $(".prev-button-icon").css('fill', prevButtonMainColor);
        }
      }
      $("#outshifter-select-button-prev-type").on("change", handlePrevTypeChange);
      //next button type
      function handleNextTypeChange() {
        var selectedOption = $(this).val();
        if (selectedOption === "filled") {
          $(".preview-button-black").css( 'background-color', nextButtonMainColor);
          $(".preview-button-black").css( 'border-color', nextButtonMainColor);
          $(".preview-button-black").css( 'color', nextButtonSecondaryColor);
        } else if (selectedOption === "outlined") {
          $(".preview-button-black").css( 'background-color', "transparent");
          $(".preview-button-black").css( 'border-color', nextButtonMainColor);
          $(".preview-button-black").css( 'color', nextButtonMainColor);
        }
      }
      $("#outshifter-select-button-next-type").on("change", handleNextTypeChange);

      //default button styles
      $(".preview-button-black").css( 'border-color', nextButtonMainColor);
      $(".preview-button-white").css( 'border-color', prevButtonMainColor);
      $(".preview-button-white").css("border-radius", `${buttonBorderRatio}px`);
      $(".preview-button-black").css("border-radius", `${buttonBorderRatio}px`);
      if (nextButtonType === "filled") {
        $(".preview-button-black").css( 'background-color', nextButtonMainColor);
        $(".preview-button-black").css( 'border-color', nextButtonMainColor);
        $(".preview-button-black").css( 'color', nextButtonSecondaryColor);
      } else {
        $(".preview-button-black").css( 'background-color', "transparent");
        $(".preview-button-black").css( 'border-color', nextButtonMainColor);
        $(".preview-button-black").css( 'color', nextButtonMainColor);
      }
      if (prevButtonType === "filled") {
        $(".preview-button-white").css( 'border-color', prevButtonMainColor);
        $(".preview-button-white").css( 'background-color', prevButtonMainColor);
        $(".preview-button-white").css( 'color', prevButtonSecondaryColor);
        $(".prev-button-icon").css('fill', prevButtonSecondaryColor);
      } else {
        $(".preview-button-white").css( 'background-color', "transparent");
        $(".preview-button-white").css( 'border-color', prevButtonMainColor);
        $(".preview-button-white").css( 'color', prevButtonMainColor);
        $(".prev-button-icon").css('fill', prevButtonMainColor);
      }
      
      //next button bg color
      $('.button-color-field').wpColorPicker({
        width: 150,
        change: function(event, ui) {
          nextButtonBackgroundPreview = ui.color.toString()
          $(".preview-button-black").css( 'background-color', nextButtonBackgroundPreview);
        }
      });
      //prev button bg color
      $('.button-prev-color-field').wpColorPicker({
        width: 150,
        change: function(event, ui) {
          prevButtonBackgroundPreview = ui.color.toString()
          $(".preview-button-white").css( 'background-color', prevButtonBackgroundPreview);
        }
      });
      //next button text color      
      $('.button-text-color-field').wpColorPicker({
        width: 150,
        change: function(event, ui) {
          nextButtonTextColorPreview = ui.color.toString()
          $(".preview-button-black").css( 'color', nextButtonTextColorPreview);
        }
      });
      //next button hover text color
      $('.button-text-hover-color-field').wpColorPicker({
        width: 150,
        change: function(event, ui) {
          $(".preview-button-black").hover(function() {
            $(this).css('color', ui.color.toString())
          }, function() {
            $(this).css('color', nextButtonTextColorPreview || checkoutNextButtonTextSaved || "#fff")
          });
        }
      });
      //prev button text color
      $('.button-prev-text-color-field').wpColorPicker({
        width: 150,
        change: function(event, ui) {
          prevButtonTextColorPreview = ui.color.toString()
          $(".preview-button-white").css('color', prevButtonTextColorPreview);
          $(".prev-button-icon").css('fill', prevButtonTextColorPreview);
        }
      });
      //checkout buttons border ratio
      $("#select-border-ratio").on("input", function() {
        let checkoutButtonRatio = $(this).val();
        $(".preview-button-white").css("border-radius", `${checkoutButtonRatio}px`)
        $(".preview-button-black").css("border-radius", `${checkoutButtonRatio}px`)
      });
      //handle reset checkout btns styles
      $('#reset-checkout-buttons').click( function() {
        $('.custom-checkout-buttons .wp-picker-input-wrap .button.wp-picker-default').trigger("click");
        $('#select-border-ratio').val(0);
        updateInput(input2);
        let checkoutButtonRatio = 0;
        $(".preview-button-white").css("border-radius", `${checkoutButtonRatio}px`);
        $(".preview-button-black").css("border-radius", `${checkoutButtonRatio}px`);
      });

      //handle reset shop btns style
      $('#reset-shop-button').click( function() {
        $('.custom-shop-icon .wp-picker-input-wrap .button.wp-picker-default').trigger("click");
        $('#shop-button-ratio').val(15);
        updateInput(input1);
        let shopButtonRatio = 15;
        $(".image-preview-wrapper-shop").css("border-radius", `${shopButtonRatio}px`);  
        $("#show-shop-icon").removeAttr('checked');
        $(".image-shop-cart-icon").css("display", "block");
        $("#shop-custom-url").removeAttr('checked');
        $("#input-url-shop").prop('disabled', true);
        $("#input-url-shop").val('');
      });

      // Handle reset Keys
      jQuery('#outshifter-mixpanel-disconnect').on('click', function( e ){
        e.preventDefault();
        $('#outshifter-mixpanel').val('');
        $('#outshifter-mixpanel').prop('disabled', function(i, v) { return !v; });
        $('#outshifter-mixpanel').attr("placeholder", "Enter your mixpanel Api key here");
        $('#outshifter-mixpanel-disconnect').css("display", "none");
      });
      jQuery('#outshifter-stripe-key-disconnect').on('click', function( e ){
        e.preventDefault();
        $('#outshifter-stripe-key').val('');
        $('#outshifter-stripe-key').prop('disabled', function(i, v) { return !v; });
        $('#outshifter-stripe-key').attr("placeholder", "Enter your Stripe Key here");
        $('#outshifter-stripe-key-disconnect').css("display", "none");
      });
      jQuery('#outshifter-stripe-id-disconnect').on('click', function( e ){
        e.preventDefault();
        $('#outshifter-stripe-id').val('');
        $('#outshifter-stripe-id').prop('disabled', function(i, v) { return !v; });
        $('#outshifter-stripe-id').attr("placeholder", "Enter your Stripe Id here");
        $('#outshifter-stripe-id-disconnect').css("display", "none");
      });
      jQuery('#outshifter-g-analytics-disconnect').on('click', function( e ){
        e.preventDefault();
        $('#outshifter-g-analytics').val('');
        $('#outshifter-g-analytics').prop('disabled', function(i, v) { return !v; });
        $('#outshifter-g-analytics').attr("placeholder", "G-XXXXXXX");
        $('#outshifter-g-analytics-disconnect').css("display", "none");
      });

			// Uploading files brand logo
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo esc_html($my_saved_attachment_post_id); ?>; // Set this

      // Uploading files brand logo white
			var file_frame_white;
			var wp_media_post_id_white = wp.media.model.settings.post.id2; // Store the old id
			var set_to_post_id_white = <?php echo esc_html($my_saved_attachment_post_id_white); ?>; // Set this

      // Uploading files shop button logo
			var file_frame_shop;
			var wp_media_post_id_shop = wp.media.model.settings.post.id3; // Store the old id
			var set_to_post_id_shop = <?php echo esc_html($my_saved_attachment_post_id_shop); ?>; // Set this

      //upload logo handler
			jQuery('#upload_image_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
					$( '#image-preview' ).attr( 'src', attachment.url ).css( 'max-width', '130px' );
					$( '#image_attachment_id' ).val( attachment.id );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});
        // Finally, open the modal
        file_frame.open();
			});

      //upload logo white handler
      jQuery('#upload_image_button_white').on('click', function( event ){
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame_white ) {
          // Set the post ID to what we want
          file_frame_white.uploader.uploader.param( 'post_id_white', set_to_post_id_white );
          // Open frame
          file_frame_white.open();
          return;
        } else {
          // Set the wp.media post id so the uploader grabs the ID we want when initialised
          wp.media.model.settings.post.id2 = set_to_post_id_white;
        }
        // Create the media frame.
        file_frame_white = wp.media.frames.file_frame = wp.media({
          title: 'Select a image to upload',
          button: {
            text: 'Use this image',
          },
          multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame_white.on( 'select', function() {
          // We set multiple to false so only get one image from the uploader
          attachment = file_frame_white.state().get('selection').first().toJSON();
          // Do something with attachment.id and/or attachment.url here
          $( '#image-preview_white' ).attr( 'src', attachment.url ).css( 'max-width', '130px' );
          $( '#image_attachment_id_white' ).val( attachment.id );
          // Restore the main post ID
          wp.media.model.settings.post.id2 = wp_media_post_id_white;
        });
        // Finally, open the modal
        file_frame_white.open();
      });

      // Upload shop logo handler
      jQuery('#upload_image_button_shop').on('click', function( event ){
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame_shop ) {
          // Set the post ID to what we want
          file_frame_shop.uploader.uploader.param( 'post_id_shop', set_to_post_id_shop );
          // Open frame
          file_frame_shop.open();
          return;
        } else {
          // Set the wp.media post id so the uploader grabs the ID we want when initialised
          wp.media.model.settings.post.id3 = set_to_post_id_shop;
        }
        // Create the media frame.
        file_frame_shop = wp.media.frames.file_frame = wp.media({
          title: 'Select a image to upload',
          button: {
            text: 'Use this image',
          },
          multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame_shop.on( 'select', function() {
          // We set multiple to false so only get one image from the uploader
          attachment = file_frame_shop.state().get('selection').first().toJSON();
          // Do something with attachment.id and/or attachment.url here
          $( '#image-preview_shop' ).attr( 'src', attachment.url ).css( 'max-width', '70px' );
          $( '#image_attachment_id_shop' ).val( attachment.id );
          // Restore the main post ID
          wp.media.model.settings.post.id3 = wp_media_post_id_shop;
        });
        // Finally, open the modal
        file_frame_shop.open();
      });

			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
        wp.media.model.settings.post.id2 = wp_media_post_id_white;
        wp.media.model.settings.post.id3 = wp_media_post_id_shop;
			});
		});

	</script>
  <?php
  }
}
