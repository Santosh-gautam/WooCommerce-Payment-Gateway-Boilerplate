<?php
/**
 * Plugin Name:  WooCommerce Payment Gateway Boilerplate
 * Plugin URI:   https://www.hisantosh.com
 * Description:  A professional, generic boilerplate for building a custom WooCommerce payment gateway. Replace TODO comments with your payment gateway's API logic.
 * Version:      2.0.0
 * Author:       Santosh Gautam
 * Author URI:   https://www.hisantosh.com
 * Tags:         payment, gateway, woocommerce, boilerplate
 * Requires at least: 5.6
 * Tested up to: 6.7.1
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License:      GPL v3 or later
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:  wc-pg-boilerplate
 * Copyright:    © 2025, Santosh Gautam. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/*===========================================================
 * Plugin Constants
 *=========================================================== */
define( 'WC_PG_BOILERPLATE_VERSION',  '2.0.0' );
define( 'WC_PG_BOILERPLATE_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_PG_BOILERPLATE_DIR_URL',  plugin_dir_url( __FILE__ ) );

/*===========================================================
 * 1. Declare HPOS Compatibility
 *=========================================================== */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

/*===========================================================
 * 2. WooCommerce Active Check
 *    If WC is not active, show an admin notice and bail out.
 *=========================================================== */
add_action( 'plugins_loaded', 'wc_pg_boilerplate_init', 0 );

function wc_pg_boilerplate_init() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'wc_pg_boilerplate_missing_wc_notice' );
        return; // Stop loading anything else.
    }

    // WooCommerce is active — load the gateway.
    wc_pg_boilerplate_load_gateway();
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function wc_pg_boilerplate_missing_wc_notice() {
    /* translators: %s: WooCommerce download URL */
    $message = sprintf(
        __( '<strong>WooCommerce Payment Gateway Boilerplate</strong> requires <a href="%s" target="_blank">WooCommerce</a> to be installed and active.', 'wc-pg-boilerplate' ),
        esc_url( 'https://woocommerce.com/' )
    );
    echo '<div class="notice notice-error"><p>' . wp_kses_post( $message ) . '</p></div>';
}

/*===========================================================
 * 3. Register Payment Gateway
 *=========================================================== */
function wc_pg_boilerplate_load_gateway() {

    // Load template for Block Checkout registration.
    require_once WC_PG_BOILERPLATE_DIR_PATH . 'templates/checkout.php';

    // Register gateway class with WooCommerce.
    add_filter( 'woocommerce_payment_gateways', 'wc_pg_boilerplate_register_gateway' );
}

function wc_pg_boilerplate_register_gateway( $gateways ) {
    require_once WC_PG_BOILERPLATE_DIR_PATH . 'includes/class-wc-payments-gateway.php';
    $gateways[] = 'WC_Gateway_PG_Boilerplate';
    return $gateways;
}

/*===========================================================
 * 4. Plugin Action Links (Settings / Support / Docs)
 *=========================================================== */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_pg_boilerplate_plugin_action_links' );

function wc_pg_boilerplate_plugin_action_links( $links ) {

    $settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pg_boilerplate' );
    $extra_links  = array(
        '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'wc-pg-boilerplate' ) . '</a>',
        '<a href="' . esc_url( 'https://hisantosh.com' ) . '" target="_blank">' . __( 'Support', 'wc-pg-boilerplate' ) . '</a>',
        '<a href="' . esc_url( 'https://github.com/isantoshg/WooCommerce-Payment-Gateway-Boilerplate/blob/master/README.md' ) . '" target="_blank">' . __( 'Docs', 'wc-pg-boilerplate' ) . '</a>',
    );

    return array_merge( $extra_links, $links );
}

/*===========================================================
 * 5. Enqueue Admin Assets — ONLY on WC Settings Page
 *=========================================================== */
add_action( 'admin_enqueue_scripts', 'wc_pg_boilerplate_enqueue_admin_assets' );

function wc_pg_boilerplate_enqueue_admin_assets( $hook ) {

    // Only load on the WooCommerce settings page.
    if ( 'woocommerce_page_wc-settings' !== $hook ) {
        return;
    }

    // Only load on the Checkout / Payments tab.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( ! isset( $_GET['tab'] ) || 'checkout' !== sanitize_key( $_GET['tab'] ) ) {
        return;
    }

    wp_enqueue_style(
        'wc-pg-boilerplate-admin-style',
        WC_PG_BOILERPLATE_DIR_URL . 'assets/css/admin-style.css',
        array(),
        WC_PG_BOILERPLATE_VERSION
    );

    wp_enqueue_script(
        'wc-pg-boilerplate-admin-script',
        WC_PG_BOILERPLATE_DIR_URL . 'assets/js/admin-script.js',
        array( 'jquery' ),
        WC_PG_BOILERPLATE_VERSION,
        true
    );

    // Pass PHP data to admin JS if needed.
    wp_localize_script(
        'wc-pg-boilerplate-admin-script',
        'wcPgBoilerplate',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wc_pg_boilerplate_admin_nonce' ),
        )
    );
}
