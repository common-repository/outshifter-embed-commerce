<?php

class Outshifter_Blocks {

  private $plugin_name;
  private $version;

	public function __construct() {
		if ( defined( 'OUTSHIFTER_WORDPRESS_VERSION' ) ) {
			$this->version = OUTSHIFTER_WORDPRESS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'outshifter-embed-commerce';
		$this->load_dependencies();
    $this->define_admin_hooks();

    add_filter( 'block_categories_all', array( $this, 'block_categories' ) );

	}

  private function load_dependencies() {
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-outshifter-blocks-unique-product.php';
    $unique_product_block = new Outshifter_Blocks_Unique_Product();
    $unique_product_block->register();
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-outshifter-blocks-admin.php';
	}

  public function block_categories($categories) {
    $outshifter_category = array(
      'slug' => 'outshifter',
      'title' => __( 'Reachu', 'outshifter-embed-commerce' ),
      'icon'  => 'wordpress',
    );
    return array_merge( $categories, array($outshifter_category) );
  }

  private function define_admin_hooks() {
		$plugin_admin = new Outsfhiter_Blocks_Admin( $this->plugin_name, $this->version );
		add_action( 'admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles') );
		add_action( 'admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts') );
    add_action( 'admin_enqueue_scripts', array($plugin_admin, 'mw_enqueue_carousel') );
    add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );

    function mw_enqueue_color_picker( $hook_suffix ) {
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'my-script-handle', plugin_dir_path( dirname( __FILE__ ) ) . 'admin/js/index.js', array( 'wp-color-picker' ), false, true );
    }
	}
  public function run() {
  }
}