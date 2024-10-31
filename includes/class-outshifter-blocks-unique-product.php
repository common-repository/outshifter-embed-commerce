<?php

class Outshifter_Blocks_Unique_Product
{
  private $editor_script;
  private $editor_style;
  private $frontend_script;
  private $frontend_style;
  private $outshifter_vars;
  private $asset_file;

  public function __construct()
  {
    $sellerId = get_option('outshifter_blocks_seller_id');
    $sellerName = get_option('outshifter_blocks_seller_name');
    $sellerSurname = get_option('outshifter_blocks_seller_surname');
    $sellerEmail = get_option('outshifter_blocks_seller_email');
    $sellerAvatar = get_option('outshifter_blocks_seller_avatar');
    $currency = get_option('outshifter_blocks_currency');
    $fontSelected = get_option('outshifter_blocks_font_selected');
    $layoutSelected = get_option('outshifter_blocks_layout_selected');
    $modalPosition = get_option('outshifter_blocks_modal_position');
    $blocksTitleAlignment = get_option('outshifter_blocks_title_alignment');
    $shopColor = get_option('outshifter_blocks_shop_color');
    $buttonNextType = get_option('outshifter_blocks_button_next_type');
    $buttonPrevType = get_option('outshifter_blocks_button_prev_type');
    $buttonNextColor = get_option('outshifter_blocks_button_next_color');
    $buttonPrevColor = get_option('outshifter_blocks_button_prev_color');
    $buttonNextHoverColor = get_option('outshifter_blocks_button_hover_color');
    $buttonNextTextColor = get_option('outshifter_blocks_button_text_color');
    $buttonPrevTextColor = get_option('outshifter_blocks_button_prev_text_color');
    $buttonNextHoverTextColor = get_option('outshifter_blocks_button_hover_text_color');
    $buttonPrevBorderColor = get_option('outshifter_blocks_button_prev_border_color');
    $buttonBorderRatio = get_option('outshifter_blocks_button_border_ratio');
    $blockTitleSize = get_option('outshifter_blocks_title_size');
    $country = get_option('outshifter_blocks_country');
    $apiKey = get_option('outshifter_blocks_api_key');
    $supplierLogo = wp_get_attachment_url(get_option('outshifter_blocks_supplier_logo'));
    $supplierLogoWhite = wp_get_attachment_url(get_option('outshifter_blocks_supplier_logo_white'));
    $mixPanel = get_option('outshifter_blocks_mixpanel');
    $stripeKey = get_option('outshifter_blocks_stripe_key');
    $stripeId = get_option('outshifter_blocks_stripe_id');
    $createShortcode = get_option('outshifter_blocks_create_shortcode');
    $notGutemberg = get_option('outshifter_blocks_not_gutemberg');
    $allowUploadToMedia = get_option('outshifter_blocks_allow_upload_to_media');
    $savedMediaImages = get_option('outshifter_blocks_saved_media_images');
    //shortcodes
    $shortcodeBuyButton = get_option('outshifter_blocks_shortcode_buy_button');
    $shortcodeSingle = get_option('outshifter_blocks_shortcode_single');
    $shortcodeTwo = get_option('outshifter_blocks_shortcode_two');
    $shortcodeCarousel = get_option('outshifter_blocks_shortcode_carousel');
    $shortcodeShop = get_option('outshifter_blocks_shortcode_shop');
    //custom shop icon
    $shopLogoSelected = get_option('outshifter_blocks_shop_logo_selected');
    $supplierLogoShop = wp_get_attachment_url(get_option('outshifter_blocks_supplier_logo_shop'));
    $shopTextSelected = get_option('outshifter_blocks_shop_text_selected');
    $shopButtonColor = get_option('outshifter_blocks_shop_button_color');
    $textIconColor = get_option('outshifter_blocks_text_icon_color');
    $shopButtonRatio = get_option('outshifter_blocks_shop_button_ratio');
    $showShopIcon = get_option('outshifter_blocks_show_shop_icon');
    $addShopUrl = get_option('outshifter_blocks_add_shop_url');
    $shopCustomUrl = get_option('outshifter_blocks_shop_custom_url');
    $showCardTitle = get_option('outshifter_blocks_show_card_title');
    $showCardPrice = get_option('outshifter_blocks_show_card_price');
    $showCardSupplier = get_option('outshifter_blocks_show_card_supplier');
    $showCardButton = get_option('outshifter_blocks_show_card_button');
             
    $this->editor_script    = '../build/index.js';
    $this->editor_style     = '../build/index.css';
    $this->frontend_script  = '../build/frontend.js';
    $this->frontend_style   = '../build/style-index.css';
    $this->outshifter_vars  = [
      'supplierLogo' => $supplierLogo,
      'supplierLogoWhite' => $supplierLogoWhite,
      'supplierLogoShop' => $supplierLogoShop,
      'currency' => $currency,
      'fontSelected' => $fontSelected,
      'layoutSelected' => $layoutSelected,
      'blocksTitleAlignment' => $blocksTitleAlignment,
      'modalPosition' => $modalPosition,
      'shopColor' => $shopColor,
      'blockTitleSize' => $blockTitleSize,
      'country' => $country,
      'sellerId' => $sellerId,
      'apiKey' => $apiKey,
      'apiUrl' => OUTSHIFTER_API_URL,
      'stripeKey' => $stripeKey !== '' ? $stripeKey : 'pk_test_51I72iQCClYvoTSYsP8Qpv5L95nxI6Z7zgRUFi0fOdVDe6lYKg8pfVD6nxacoOHxIfNve8HHzIAezz8lra1iA96mO00fB3XoxdO',
      'stripeId' => $stripeId != '' ? $stripeId : 'acct_1I72iQCClYvoTSYs',
      'mixPanel' => $mixPanel != '' ? $mixPanel : 'e8e0aea9cc893bdcad98071f279311c2',
      'createShortcode' => $createShortcode,
      'notGutemberg' => $notGutemberg,
      'allowUploadToMedia' => $allowUploadToMedia,
      'savedMediaImages' => $savedMediaImages,
      //button styling
      'buttonNextType' => $buttonNextType,
      'buttonPrevType' => $buttonPrevType,
      'buttonNextColor' => $buttonNextColor,
      'buttonPrevColor' => $buttonPrevColor,
      'buttonNextHoverColor' => $buttonNextHoverColor,
      'buttonNextTextColor' => $buttonNextTextColor,
      'buttonPrevTextColor' => $buttonPrevTextColor,
      'buttonNextHoverTextColor' => $buttonNextHoverTextColor,
      'buttonPrevBorderColor' => $buttonPrevBorderColor,
      'buttonBorderRatio' => $buttonBorderRatio,
      //shortcodes
      'shortcodeBuyButton' => $shortcodeBuyButton,
      'shortcodeSingle' => $shortcodeSingle,
      'shortcodeTwo' => $shortcodeTwo,
      'shortcodeCarousel' => $shortcodeCarousel,
      'shortcodeShop' => $shortcodeShop,
      //custom styles
      'shopLogoSelected' => $shopLogoSelected,
      'supplierLogoShop' => $supplierLogoShop,
      'shopTextSelected' => $shopTextSelected,
      'shopButtonColor' => $shopButtonColor,
      'textIconColor' => $textIconColor,
      'shopButtonRatio' => $shopButtonRatio,
      'addShopUrl' => $addShopUrl,
      'shopCustomUrl' => $shopCustomUrl,
      'showShopIcon' => $showShopIcon,
      'showCardTitle' => $showCardTitle,
      'showCardPrice' => $showCardPrice,
      'showCardSupplier' => $showCardSupplier,
      'showCardButton' => $showCardButton,

    ];
    $this->asset_file = include(plugin_dir_path(__FILE__) . '../build/index.asset.php');
  }

  public function enqueue_block_editor_assets()
  {
    wp_enqueue_script(
      'unique-product-editor-script',
      plugins_url($this->editor_script, __FILE__),
      array_merge($this->asset_file['dependencies']),
      $this->asset_file['version'],
      true
    );
    wp_localize_script('unique-product-editor-script', 'outshifter_vars', $this->outshifter_vars);

    wp_register_style(
      'unique-product-editor-style',
      plugins_url($this->editor_style, __FILE__),
      ['wp-edit-blocks'],
      filemtime(plugin_dir_path(__FILE__) . $this->editor_style)
    );
  }

  public function enqueue_block_assets()
  {
    wp_enqueue_script(
      'outshifter-unique-product-frontend-script',
      plugins_url($this->frontend_script, __FILE__),
      ['wp-element'],
      $this->asset_file['version'],
      true
    );
    wp_localize_script('outshifter-unique-product-frontend-script', 'outshifter_vars', $this->outshifter_vars);

    wp_register_style(
      'unique-product-style',
      plugins_url($this->frontend_style, __FILE__),
      array('wp-components'),
      filemtime(plugin_dir_path(__FILE__) . $this->frontend_style)
    );
  }

  public function render_block_action_button($attributes)
  {
    $id = array_key_exists('id', $attributes) ? $attributes['id'] : null;
    $title = array_key_exists('title', $attributes) ? $attributes['title'] : null;
    $productTitle = array_key_exists('productTitle', $attributes) ? $attributes['productTitle'] : null;
    $productImage = array_key_exists('productImage', $attributes) ? $attributes['productImage'] : null;
    $productPrice = array_key_exists('productPrice', $attributes) ? $attributes['productPrice'] : null;
    $productSupplier = array_key_exists('productSupplier', $attributes) ? $attributes['productSupplier'] : null;
    $type = array_key_exists('type', $attributes) ? $attributes['type'] : null;
    $marginTop = array_key_exists('marginTop', $attributes) ? $attributes['marginTop'] : null;
    $marginBottom = array_key_exists('marginBottom', $attributes) ? $attributes['marginBottom'] : null;

    return '<div>
      <div
        class="wp-block-outshifter-action-button"
        data-post_id="' . esc_attr(get_the_ID()) . '"
        data-id="' . esc_attr($id) . '"
        data-type="' . esc_attr($type) . '"
        data-margintop="' . esc_attr($marginTop) . '"
        data-marginbottom="' . esc_attr($marginBottom) . '"
        data-title="' . esc_attr($title) . '"
        data-producttitle="' . esc_attr($productTitle) . '"
        data-productimage="' . esc_attr($productImage) . '"
        data-productprice="' . esc_attr($productPrice) . '"
        data-productsupplier="' . esc_attr($productSupplier) . '"
      ></div>
    </div>';
  }

  public function render_block($attributes)
  {
    $id = array_key_exists('id', $attributes) ? $attributes['id'] : null;
    $productTitle = array_key_exists('productTitle', $attributes) ? $attributes['productTitle'] : null;
    $productImage = array_key_exists('productImage', $attributes) ? $attributes['productImage'] : null;
    $productPrice = array_key_exists('productPrice', $attributes) ? $attributes['productPrice'] : null;
    $productSupplier = array_key_exists('productSupplier', $attributes) ? $attributes['productSupplier'] : null;
    $title = array_key_exists('title', $attributes) ? $attributes['title'] : null;
    $fontSize = array_key_exists('fontSize', $attributes) ? $attributes['fontSize'] : null;
    $titleColor = array_key_exists('titleColor', $attributes) ? $attributes['titleColor'] : null;
    $titleAlignment = array_key_exists('titleAlignment', $attributes) ? $attributes['titleAlignment'] : null;
    $showBlockTitle = array_key_exists('showBlockTitle', $attributes) ? $attributes['showBlockTitle'] : null;
    $backgroundColor = array_key_exists('backgroundColor', $attributes) ? $attributes['backgroundColor'] : null;
    $showThumbnail = array_key_exists('showThumbnail', $attributes) ? $attributes['showThumbnail'] : null;
    $thumbnailSize = array_key_exists('thumbnailSize', $attributes) ? $attributes['thumbnailSize'] : null;
    $thumbnailAspectRatio = array_key_exists('thumbnailAspectRatio', $attributes) ? $attributes['thumbnailAspectRatio'] : null;
    $thumbnailBorderRadius = array_key_exists('thumbnailBorderRadius', $attributes) ? $attributes['thumbnailBorderRadius'] : null;
    $productCardTextStyle = array_key_exists('productCardTextStyle', $attributes) ? $attributes['productCardTextStyle'] : null;
    $showTitle = array_key_exists('showTitle', $attributes) ? $attributes['showTitle'] : null;
    $showPrice = array_key_exists('showPrice', $attributes) ? $attributes['showPrice'] : null;
    $showSupplier = array_key_exists('showSupplier', $attributes) ? $attributes['showSupplier'] : null;
    $showBuyButton = array_key_exists('showBuyButton', $attributes) ? $attributes['showBuyButton'] : null;

    return '<div>
      <div
        class="wp-block-outshifter-unique-product"
        data-post_id="' . esc_attr(get_the_ID()) . '"
        data-id="' . esc_attr($id) . '"
        data-producttitle="' . esc_attr($productTitle) . '"
        data-productimage="' . esc_attr($productImage) . '"
        data-productprice="' . esc_attr($productPrice) . '"
        data-productsupplier="' . esc_attr($productSupplier) . '"
        data-title="' . esc_attr($title) . '"
        data-titlecolor="' . esc_attr($titleColor) . '"
        data-fontsize="' . esc_attr($fontSize) . '"
        data-titlealignment="' . esc_attr($titleAlignment) . '"
        data-showblocktitle="' . esc_attr($showBlockTitle) . '"
        data-backgroundcolor="' . esc_attr($backgroundColor) . '"
        data-showthumbnail="' . esc_attr($showThumbnail) . '"
        data-thumbnailsize="' . esc_attr($thumbnailSize) . '"
        data-thumbnailaspectratio="' . esc_attr($thumbnailAspectRatio) . '"
        data-thumbnailborderradius="' . esc_attr($thumbnailBorderRadius) . '"
        data-productcardtextstyle="' . esc_attr($productCardTextStyle) . '"
        data-showtitle="' . esc_attr($showTitle) . '"
        data-showprice="' . esc_attr($showPrice) . '"
        data-showsupplier="' . esc_attr($showSupplier) . '"
        data-showbuybutton="' . esc_attr($showBuyButton) . '"  
      ></div>
    </div>';
  }

  public function render_block_two($attributes)
  {
    $id1 = array_key_exists('id1', $attributes) ? $attributes['id1'] : null;
    $id2 = array_key_exists('id2', $attributes) ? $attributes['id2'] : null;
    $productTitle1 = array_key_exists('productTitle1', $attributes) ? $attributes['productTitle1'] : null;
    $productImage1 = array_key_exists('productImage1', $attributes) ? $attributes['productImage1'] : null;
    $productPrice1 = array_key_exists('productPrice1', $attributes) ? $attributes['productPrice1'] : null;
    $productSupplier1 = array_key_exists('productSupplier1', $attributes) ? $attributes['productSupplier1'] : null;
    $productTitle2 = array_key_exists('productTitle2', $attributes) ? $attributes['productTitle2'] : null;
    $productImage2 = array_key_exists('productImage2', $attributes) ? $attributes['productImage2'] : null;
    $productPrice2 = array_key_exists('productPrice2', $attributes) ? $attributes['productPrice2'] : null;
    $productSupplier2 = array_key_exists('productSupplier2', $attributes) ? $attributes['productSupplier2'] : null;
    $title = array_key_exists('title', $attributes) ? $attributes['title'] : null;
    $fontSize = array_key_exists('fontSize', $attributes) ? $attributes['fontSize'] : null;
    $titleColor = array_key_exists('titleColor', $attributes) ? $attributes['titleColor'] : null;
    $titleAlignment = array_key_exists('titleAlignment', $attributes) ? $attributes['titleAlignment'] : null;
    $showBlockTitle = array_key_exists('showBlockTitle', $attributes) ? $attributes['showBlockTitle'] : null;
    $backgroundColor = array_key_exists('backgroundColor', $attributes) ? $attributes['backgroundColor'] : null;
    $showThumbnail = array_key_exists('showThumbnail', $attributes) ? $attributes['showThumbnail'] : null;
    $thumbnailSize = array_key_exists('thumbnailSize', $attributes) ? $attributes['thumbnailSize'] : null;
    $thumbnailAspectRatio = array_key_exists('thumbnailAspectRatio', $attributes) ? $attributes['thumbnailAspectRatio'] : null;
    $thumbnailBorderRadius = array_key_exists('thumbnailBorderRadius', $attributes) ? $attributes['thumbnailBorderRadius'] : null;
    $productCardTextStyle = array_key_exists('productCardTextStyle', $attributes) ? $attributes['productCardTextStyle'] : null;
    $showTitle = array_key_exists('showTitle', $attributes) ? $attributes['showTitle'] : null;
    $showPrice = array_key_exists('showPrice', $attributes) ? $attributes['showPrice'] : null;
    $showSupplier = array_key_exists('showSupplier', $attributes) ? $attributes['showSupplier'] : null;
    $showBuyButton = array_key_exists('showBuyButton', $attributes) ? $attributes['showBuyButton'] : null;

    return '<div>
      <div
        class="wp-block-outshifter-two-products"
        data-post_id="' . esc_attr(get_the_ID()) . '"
        data-id1="' . esc_attr($id1) . '"
        data-id2="' . esc_attr($id2) . '"
        data-producttitle1="' . esc_attr($productTitle1) . '"
        data-productimage1="' . esc_attr($productImage1) . '"
        data-productprice1="' . esc_attr($productPrice1) . '"
        data-productsupplier1="' . esc_attr($productSupplier1) . '"
        data-producttitle2="' . esc_attr($productTitle2) . '"
        data-productimage2="' . esc_attr($productImage2) . '"
        data-productprice2="' . esc_attr($productPrice2) . '"
        data-productsupplier2="' . esc_attr($productSupplier2) . '"
        data-title="' . esc_attr($title) . '"
        data-titlecolor="' . esc_attr($titleColor) . '"
        data-fontsize="' . esc_attr($fontSize) . '"
        data-titlealignment="' . esc_attr($titleAlignment) . '"
        data-showblocktitle="' . esc_attr($showBlockTitle) . '"
        data-backgroundcolor="' . esc_attr($backgroundColor) . '"
        data-showthumbnail="' . esc_attr($showThumbnail) . '"
        data-thumbnailsize="' . esc_attr($thumbnailSize) . '"
        data-thumbnailaspectratio="' . esc_attr($thumbnailAspectRatio) . '"
        data-thumbnailborderradius="' . esc_attr($thumbnailBorderRadius) . '"
        data-productcardtextstyle="' . esc_attr($productCardTextStyle) . '"
        data-showtitle="' . esc_attr($showTitle) . '"
        data-showprice="' . esc_attr($showPrice) . '"
        data-showsupplier="' . esc_attr($showSupplier) . '"
        data-showbuybutton="' . esc_attr($showBuyButton) . '"
      ></div>
    </div>';
  }

  public function render_block_carousel($attributes)
  {
    $id = array_key_exists('id', $attributes) ? $attributes['id'] : null;
    $title = array_key_exists('title', $attributes) ? $attributes['title'] : null;
    $fontSize = array_key_exists('fontSize', $attributes) ? $attributes['fontSize'] : null;
    $titleColor = array_key_exists('titleColor', $attributes) ? $attributes['titleColor'] : null;
    $titleAlignment = array_key_exists('titleAlignment', $attributes) ? $attributes['titleAlignment'] : null;
    $showBlockTitle = array_key_exists('showBlockTitle', $attributes) ? $attributes['showBlockTitle'] : null;
    $backgroundColor = array_key_exists('backgroundColor', $attributes) ? $attributes['backgroundColor'] : null;
    $showThumbnail = array_key_exists('showThumbnail', $attributes) ? $attributes['showThumbnail'] : null;
    $thumbnailSize = array_key_exists('thumbnailSize', $attributes) ? $attributes['thumbnailSize'] : null;
    $thumbnailAspectRatio = array_key_exists('thumbnailAspectRatio', $attributes) ? $attributes['thumbnailAspectRatio'] : null;
    $thumbnailBorderRadius = array_key_exists('thumbnailBorderRadius', $attributes) ? $attributes['thumbnailBorderRadius'] : null;
    $productCardTextStyle = array_key_exists('productCardTextStyle', $attributes) ? $attributes['productCardTextStyle'] : null;
    $showTitle = array_key_exists('showTitle', $attributes) ? $attributes['showTitle'] : null;
    $showPrice = array_key_exists('showPrice', $attributes) ? $attributes['showPrice'] : null;
    $showSupplier = array_key_exists('showSupplier', $attributes) ? $attributes['showSupplier'] : null;
    $showBuyButton = array_key_exists('showBuyButton', $attributes) ? $attributes['showBuyButton'] : null;
    return '<div>
      <div
        class="wp-block-outshifter-carousel"
        data-post_id="' . esc_attr(get_the_ID()) . '"
        data-id="' . esc_attr($id) . '"
        data-title="' . esc_attr($title) . '"
        data-titlecolor="' . esc_attr($titleColor) . '"
        data-fontsize="' . esc_attr($fontSize) . '"
        data-titlealignment="' . esc_attr($titleAlignment) . '"
        data-showblocktitle="' . esc_attr($showBlockTitle) . '"
        data-backgroundcolor="' . esc_attr($backgroundColor) . '"
        data-showthumbnail="' . esc_attr($showThumbnail) . '"
        data-thumbnailsize="' . esc_attr($thumbnailSize) . '"
        data-thumbnailaspectratio="' . esc_attr($thumbnailAspectRatio) . '"
        data-thumbnailborderradius="' . esc_attr($thumbnailBorderRadius) . '"
        data-productcardtextstyle="' . esc_attr($productCardTextStyle) . '"
        data-showtitle="' . esc_attr($showTitle) . '"
        data-showprice="' . esc_attr($showPrice) . '"
        data-showsupplier="' . esc_attr($showSupplier) . '"
        data-showbuybutton="' . esc_attr($showBuyButton) . '"
        ></div>
    </div>';
  }

  public function render_block_masonry($attributes)
  {
    $id = array_key_exists('id', $attributes) ? $attributes['id'] : null;
    $id2 = array_key_exists('id2', $attributes) ? $attributes['id2'] : null;
    $title = array_key_exists('title', $attributes) ? $attributes['title'] : null;
    $fontSize = array_key_exists('fontSize', $attributes) ? $attributes['fontSize'] : null;
    $titleColor = array_key_exists('titleColor', $attributes) ? $attributes['titleColor'] : null;
    $titleAlignment = array_key_exists('titleAlignment', $attributes) ? $attributes['titleAlignment'] : null;
    $showBlockTitle = array_key_exists('showBlockTitle', $attributes) ? $attributes['showBlockTitle'] : null;
    $maxProducts = array_key_exists('maxProducts', $attributes) ? $attributes['maxProducts'] : null;
    $backgroundColor = array_key_exists('backgroundColor', $attributes) ? $attributes['backgroundColor'] : null;
    $showThumbnail = array_key_exists('showThumbnail', $attributes) ? $attributes['showThumbnail'] : null;
    $thumbnailSize = array_key_exists('thumbnailSize', $attributes) ? $attributes['thumbnailSize'] : null;
    $thumbnailAspectRatio = array_key_exists('thumbnailAspectRatio', $attributes) ? $attributes['thumbnailAspectRatio'] : null;
    $thumbnailBorderRadius = array_key_exists('thumbnailBorderRadius', $attributes) ? $attributes['thumbnailBorderRadius'] : null;
    $productCardTextStyle = array_key_exists('productCardTextStyle', $attributes) ? $attributes['productCardTextStyle'] : null;
    $showTitle = array_key_exists('showTitle', $attributes) ? $attributes['showTitle'] : null;
    $showPrice = array_key_exists('showPrice', $attributes) ? $attributes['showPrice'] : null;
    $showSupplier = array_key_exists('showSupplier', $attributes) ? $attributes['showSupplier'] : null;
    $showBuyButton = array_key_exists('showBuyButton', $attributes) ? $attributes['showBuyButton'] : null;
    $blockVariation = array_key_exists('blockVariation', $attributes) ? $attributes['blockVariation'] : null;

    return '<div>
      <div
        class="wp-block-outshifter-masonry"
        data-post_id="' . esc_attr(get_the_ID()) . '"
        data-id="' . esc_attr($id) . '"
        data-id2="' . esc_attr($id2) . '"
        data-title="' . esc_attr($title) . '"
        data-maxproducts="' . esc_attr($maxProducts) . '"
        data-fontsize="' . esc_attr($fontSize) . '"
        data-titlecolor="' . esc_attr($titleColor) . '"
        data-titlealignment="' . esc_attr($titleAlignment) . '"
        data-showblocktitle="' . esc_attr($showBlockTitle) . '"
        data-backgroundcolor="' . esc_attr($backgroundColor) . '"
        data-showthumbnail="' . esc_attr($showThumbnail) . '"
        data-thumbnailsize="' . esc_attr($thumbnailSize) . '"
        data-thumbnailaspectratio="' . esc_attr($thumbnailAspectRatio) . '"
        data-thumbnailborderradius="' . esc_attr($thumbnailBorderRadius) . '"
        data-productcardtextstyle="' . esc_attr($productCardTextStyle) . '"
        data-showtitle="' . esc_attr($showTitle) . '"
        data-showprice="' . esc_attr($showPrice) . '"
        data-showsupplier="' . esc_attr($showSupplier) . '"
        data-showbuybutton="' . esc_attr($showBuyButton) . '"
        data-blockvariation="' . esc_attr($blockVariation) . '"
      ></div>
    </div>';
  }

  public function render_block_banner($attributes)
  {
    $title = array_key_exists('title', $attributes) ? $attributes['title'] : null;
    $image = array_key_exists('mediaUrl', $attributes) ? $attributes['mediaUrl'] : null;
    return '<div>
      <div class="wp-block-outshifter-banner" data-post_id="' . esc_attr(get_the_ID()) . '" data-title="' . esc_attr($title) . '" data-image="' . esc_attr($image) . '"></div>
    </div>';
  }

  public function register()
  {
    add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    add_action('enqueue_block_assets', array($this, 'enqueue_block_assets'));

    function shapeSpace_script_loader_tag($tag, $handle, $src)
    {
      if ($handle === 'outshifter-unique-product-frontend-script') {
        if (false === stripos($tag, 'defer')) {
          $tag = str_replace('<script ', '<script defer ', $tag);
        }
      }
      return $tag;
    }
    add_filter('script_loader_tag', 'shapeSpace_script_loader_tag', 10, 3);

    register_block_type('outshifter/action-button', array(
      'editor_script' => 'unique-product-editor-script',
      'editor_style' => 'unique-product-editor-style',
      'style' => 'unique-product-style',
      'render_callback' => array($this, 'render_block_action_button'),
    ));

    register_block_type('outshifter/unique-product', array(
      'editor_script' => 'unique-product-editor-script',
      'editor_style' => 'unique-product-editor-style',
      'style' => 'unique-product-style',
      'render_callback' => array($this, 'render_block'),
    ));

    register_block_type('outshifter/two-products', array(
      'editor_script' => 'unique-product-editor-script',
      'editor_style' => 'unique-product-editor-style',
      'style' => 'unique-product-style',
      'render_callback' => array($this, 'render_block_two'),
    ));

    register_block_type('outshifter/carousel', array(
      'editor_script' => 'unique-product-editor-script',
      'editor_style' => 'unique-product-editor-style',
      'style' => 'unique-product-style',
      'render_callback' => array($this, 'render_block_carousel'),
    ));

    register_block_type('outshifter/masonry', array(
      'editor_script' => 'unique-product-editor-script',
      'editor_style' => 'unique-product-editor-style',
      'style' => 'unique-product-style',
      'render_callback' => array($this, 'render_block_masonry'),
    ));

    register_block_type('outshifter/banner', array(
      'editor_script' => 'unique-product-editor-script',
      'editor_style' => 'unique-product-editor-style',
      'style' => 'unique-product-style',
      'render_callback' => array($this, 'render_block_banner'),
    ));
  }
}
