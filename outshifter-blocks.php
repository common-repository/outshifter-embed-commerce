<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.reachu.io
 * @since             1.0.0
 * @package           Outshifter_Blocks
 *
 * @wordpress-plugin
 * Plugin Name:       Reachu Embed Commerce
 * Description:       Sell any product directly on your existing surface
 * Version:           1.1.4
 * Author:            Reachu team
 * Author URI:        www.reachu.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       outshifter-embed-commerce 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once "vars.php";

define( 'OUTSHIFTER_WORDPRESS_VERSION', '1.1.4' );
define( 'OUTSHIFTER_PLUGIN_FOLDER', plugin_dir_url(dirname(__FILE__)) );
define( 'OUTSHIFTER_NAME_FOLDER', 'outshifter-embed-commerce');

require_once plugin_dir_path( __FILE__ ) . 'includes/cart.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/preview-shop.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/action-button.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/unique-product.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/two-products.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/carousel.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/shop.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-outshifter-blocks-activator.php';

function activate_outshifter_blocks() {
	Outshifter_Blocks_Activator::activate();
}

function deactivate_outshifter_blocks() {
	Outshifter_Blocks_Activator::deactivate();
}

function uninstall_outshifter_blocks() {
  Outshifter_Blocks_Activator::uninstall();
}

register_activation_hook( __FILE__, 'activate_outshifter_blocks' );
register_deactivation_hook( __FILE__, 'deactivate_outshifter_blocks' );
register_uninstall_hook( __FILE__, 'uninstall_outshifter_blocks' );

require plugin_dir_path( __FILE__ ) . 'includes/class-outshifter-blocks.php';

function run_outshifter_blocks() {
	$plugin = new Outshifter_Blocks();
	$plugin->run();
}

class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		} 

		return self::$instance;

	} 

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);


		// Add your templates to this array.
		$this->templates = array(
			'/includes/template.php' => 'Shop Layout',
		);

		function add_inline_script() {
			$gAnalytics = get_option('outshifter_blocks_g_analytics');
			if (OUTSHIFTER_API_URL === 'https://api.reachu.io') {
				if( is_page('shop') ) {
					if ($gAnalytics !== '') {
						echo '
							<script async src="https://www.googletagmanager.com/gtag/js?id=' .  esc_html($gAnalytics) . '"></script>
							<script>
								window.dataLayer = window.dataLayer || [];
								function gtag(){dataLayer.push(arguments);}
								gtag("js", new Date());
								gtag("config", "' . esc_html($gAnalytics) . '");
							</script>
						';
					}
				}
			}
		}
		add_action( 'wp_head', 'add_inline_script', 0 );
	} 

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} 

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		
		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 

		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo esc_html($file);
		}

		// Return template
		return $template;

	}

}
 
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

run_outshifter_blocks();