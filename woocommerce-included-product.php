<?php
/**
 * WooCommerce: Included product plugin.
 *
 * @package WooCommerce/Included product
 *
 * @wordpress-plugin
 * Plugin Name: WooCommerce: Included product
 * Version:     0.2
 * Plugin URI:  
 * Description: This extension to WooCommerce allows to include a selectable product (free) to another product. This was a real saver when we needed to keep track of stock of 3 possible items that are included with a product. Customer could choose which one, based on his/her possible allergies.
 * Author:      Christophe de Jonge
 * Author URI:  https://studiomaanstof.nl
 * Depends:     WooCommerce
 * Text Domain: woocommerce-included-product
 * Domain Path: /languages/
 *
 */

if ( ! function_exists( 'add_filter' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

if ( file_exists( dirname( __FILE__ ) . '/classes/class-admin.php' ) ) {
  require dirname( __FILE__ ) . '/classes/class-admin.php';
}

if ( file_exists( dirname( __FILE__ ) . '/classes/class-frontend.php' ) ) {
  require dirname( __FILE__ ) . '/classes/class-frontend.php';
}

/**
 * Class WooCommerce_Included_Product
 */
class WooCommerce_Included_Product {

  /**
   * Version of the plugin.
   *
   * @var string
   */
  const VERSION = '0.2';

  const admin = null;
  
  const frontend = null;

  /**
   * Class constructor.
   *
   * @since 0.1
   */
  public function __construct() {
    global $wp_version;

    if ( $this->check_dependencies( $wp_version ) ) {
      $this->initialize();
    }
  }

  /**
   * Checks the dependencies. Sets a notice when requirements aren't met.
   *
   * @param string $wp_version The current version of WordPress.
   *
   * @return bool True whether the dependencies are okay.
   */
  protected function check_dependencies( $wp_version ) {
    if ( ! version_compare( $wp_version, '4.8', '>=' ) ) {
      add_action( 'all_admin_notices', array( $this, 'wordpress_upgrade_error') );

      return false;
    }

    $woocommerce_version = $this->get_woocommerce_version();

    // When Woocommerce is not installed.
    if ( ! $woocommerce_version ) {
      add_action( 'all_admin_notices', array( $this, 'woocommerce_missing_error') );

      return false;
    }

    // Make sure Woocommerce is at least 3.5.
    if ( ! version_compare( $woocommerce_version, '3.5', '>=' ) ) {
      add_action( 'all_admin_notices', array( $this, 'woocommerce_upgrade_error') );

      return false;
    }

    return true;
  }

  /**
   * Returns the WooCommerce version when set.
   *
   * @return bool|string The version whether it is set.
   */
  protected function get_woocommerce_version() {
    if ( ! defined( 'WOOCOMMERCE_VERSION' ) ) {
      return false;
    }

    return WOOCOMMERCE_VERSION;
  }

  /**
   * Throw an error if WordPress is out of date.
   *
   * @since 0.1
   */
  public function wordpress_upgrade_error() {
    echo '<div class="error"><p>';
    printf(
      /* translators: %1$s resolves to WooCommerce Included Product */
      esc_html__( 'Please upgrade WordPress to the latest version to allow WordPress and the %1$s module to work properly.', 'woocommerce-included-product' ),
      'WooCommerce Included Product'
    );
    echo '</p></div>';
  }

  /**
   * Throw an error if WooCommerce is not installed.
   *
   * @since 0.1
   */
  public function woocommerce_missing_error() {
    echo '<div class="error"><p>';
    printf(
      /* translators: %1$s resolves to the plugin search for WooCommerce, %2$s resolves to the closing tag, %3$s resolves to Yoast SEO, %4$s resolves to WooCommerce Included Product */
      esc_html__( 'Please %1$sinstall &amp; activate %3$s%2$s and then enable its "Remove Category Base Slug" functionality to allow the %4$s module to work.', 'yoast-woo-seo' ),
      '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&type=term&s=woocommerce&plugin-search-input=Search+Plugins' ) ) . '">',
      '</a>',
      'WooCommerce',
      'WooCommerce Included Product'
    );
    echo '</p></div>';
  }

  /**
   * Throw an error if WooCommerce is out of date.
   *
   * @since 0.1
   */
  public function woocommerce_upgrade_error() {
    echo '<div class="error"><p>';
    printf(
      /* translators: %1$s resolves to WooCommerce, %2$s resolves to WooCommerce Included Product */
      esc_html__( 'Please upgrade the %1$s plugin to the latest version to allow the %2$s module to work.', 'woocommerce-included-product' ),
      'WooCommerce',
      'WooCommerce Included Product'
    );
    echo '</p></div>';
  }


  /**
   * Initializes the plugin, basically hooks all the required functionality.
   *
   * @since 1.0
   *
   * @return void
   */
  protected function initialize() {

    $this->admin = new WIP_Admin;

    $this->frontend = new WIP_Frontend;


/*
    if ( WPSEO_Options::get( 'stripcategorybase' ) === true ) {

      //$GLOBALS['wpseo_rewrite'] = new WPSEO_Rewrite();
      //add_filter( 'category_rewrite_rules', array( $this, 'category_rewrite_rules' ) );
      
      // Remove filter added by Yoast SEO
      remove_filter( 'category_rewrite_rules', array( $GLOBALS['wpseo_rewrite'], 'category_rewrite_rules' ) );

      // add_filter() called from class contructor
      $GLOBALS['yoast_seo_rewrite'] = new Yoast_SEO_Category_Pages_Rewrite();

      // Add field to the "category" taxonomy
      add_action( 'category_edit_form_fields', array( $this, 'category_taxonomy_rewrite_checkbox' ), 10, 2 );
      
      // Update the changes made on the "category" taxonomy
      add_action( 'edited_category', array( $this, 'update_category_rewrite_meta' ), 10, 2 );

    }
*/

  }

}


/**
 * Initializes the plugin class, to make sure all the required functionality is loaded, do this after plugins_loaded.
 *
 * @since 0.1
 *
 * @return void
 */
function initialize_woocommerce_included_product() {
  global $woocommerce_included_product;

  load_plugin_textdomain( 'woocommerce-included-product', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

  // Initializes the plugin.
  $woocommerce_included_product = new WooCommerce_Included_Product();
}

if ( ! wp_installing() ) {
  add_action( 'plugins_loaded', 'initialize_woocommerce_included_product', 30 );
}

