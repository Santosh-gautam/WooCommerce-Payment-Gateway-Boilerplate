<?php
/*
Plugin Name:  WooCommerce Payment Gateway Boilerplate
Plugin URI: https://www.hisantosh.com
Description: A custom WooCommerce payment gateway for Wordpress WooCommerce Payments.
Version: 1.0.0
Author: Santosh Gautam
Author URI: https://www.hisantosh.com
Tags: payment, gateway
Requires at least: 5.3
Tested up to: 6.7.1
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPL v3 or later
Woo: 7310302:82f4a3fafb07f086f3ebac34a6a03729
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Copyright: © 2025, Wordpress WooCommerce Payment. All rights reserved.
*/

if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}

/*===========================================================
--------- Ensure WooCommerce is active ---------------- 
=========================================================== */
function check_woocommerce_exist()
{
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>Payments requires WooCommerce to be active.</strong></p></div>';
        });
        return;
    }
}
add_action('plugins_loaded', 'check_woocommerce_exist');

/*===========================================================
--------- Included Files ---------------- 
=========================================================== */
require_once plugin_dir_path(__FILE__) . 'templates/checkout.php';

/*===========================================================
---------  Register Payment Gateway ---------------- 
=========================================================== */
function register_payment_gateway($gateways)
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-payments-gateway.php';
    $gateways['payment_gateway_name'] = 'WC_Gateway_Payment_Gateway_Boilerplate';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'register_payment_gateway');

/*====================================================================================
 --------- Add plugin action links(show with a Deactivate button) ---------------- 
 ====================================================================================== */

function admin_plugin_action_links($links, $file)
{

    if ($file === plugin_basename(__FILE__)) {

        // Settings link
        $settings_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=payment_gateway_name');
        $settings_link = '<a href="' . esc_url($settings_url) . '">Settings</a>';

        // Support link(Please Add your Support Link Here)
        $support_url = 'https://hisantosh.com';
        $support_link = '<a href="' . esc_url($support_url) . '" target="_blank">Support</a>';

        // Documentation link(Please Add your Documentation Link Here)
        $documentation_url = 'https://github.com/isantoshg/WooCommerce-Payment-Gateway-Boilerplate/blob/master/README.md';
        $documentation_link = '<a href="' . esc_url($documentation_url) . '" target="_blank">Docs</a>';

        array_unshift($links, $settings_link, $support_link, $documentation_link);
    }
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'admin_plugin_action_links', 10, 2);

/*===========================================================
 --------- Enqueue Admin Scripts ---------------- 
 =========================================================== */
 add_action('admin_enqueue_scripts', 'boilerplate_enqueue_admin_scripts');

 function boilerplate_enqueue_admin_scripts($hook) {
     wp_enqueue_script(
        'payment_admin_scripts',
        plugin_dir_url(__FILE__) . 'assets/js/YourJsFileInclueHere.js',
        array('jquery'),
        '1.0.0',
        true
    );
 }

 
/*===========================================================
 --------- Enqueue Css ---------------- 
 =========================================================== */
add_action('admin_enqueue_scripts', 'boilerplate_enqueue_admin_css');

function boilerplate_enqueue_admin_css($hook) {
    wp_enqueue_style(
        'payment_admin__style',
        plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
        array(),
        '1.0.0'
    );
}


// echo plugin_dir_url(__FILE__);
// exit;
