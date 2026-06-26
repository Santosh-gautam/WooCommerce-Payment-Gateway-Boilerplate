<?php
/**
 * Uninstall Script — WooCommerce Payment Gateway Boilerplate
 *
 * This file runs automatically when a user deletes the plugin
 * from the WordPress admin (Plugins > Delete).
 *
 * It removes any database entries created by this plugin so the
 * site is left in a clean state.
 *
 * IMPORTANT: This file must be named `uninstall.php` and placed
 * in the plugin root directory.
 *
 * @package WC_PG_Boilerplate
 */

// Security check — WordPress sets this constant before calling uninstall.php.
// Never execute if this constant is not defined.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/*===========================================================
 * Remove Gateway Settings from wp_options
 *
 * WooCommerce stores gateway settings as a single serialised
 * option named: woocommerce_{gateway_id}_settings
 *=========================================================== */
delete_option( 'woocommerce_pg_boilerplate_settings' );

/*===========================================================
 * Multisite: Remove options from each sub-site
 *=========================================================== */
if ( is_multisite() ) {

    $sites = get_sites( array( 'fields' => 'ids' ) );

    foreach ( $sites as $site_id ) {
        switch_to_blog( $site_id );
        delete_option( 'woocommerce_pg_boilerplate_settings' );
        restore_current_blog();
    }
}

/*===========================================================
 * TODO: Remove any custom database tables your gateway
 *       created (if applicable).
 *
 * global $wpdb;
 * $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pg_boilerplate_transactions" );
 *=========================================================== */
