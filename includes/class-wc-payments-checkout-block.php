<?php
/**
 * WC_Checkout_Blocks — Block-Based Checkout Integration
 *
 * Registers this gateway with the WooCommerce Blocks payment method registry
 * so it appears in the Gutenberg block-based checkout.
 *
 * @package WC_PG_Boilerplate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Class WC_Checkout_Blocks
 */
final class WC_Checkout_Blocks extends AbstractPaymentMethodType {

    /**
     * Gateway ID — must match WC_Gateway_PG_Boilerplate::$id.
     *
     * @var string
     */
    protected $name = 'pg_boilerplate';

    /**
     * Initialise the block integration.
     * Loads the gateway's saved settings.
     */
    public function initialize() {
        $this->settings = get_option( 'woocommerce_pg_boilerplate_settings', array() );
    }

    /**
     * Whether the gateway is currently active/enabled.
     * Checks the saved 'enabled' setting instead of returning hardcoded true.
     *
     * @return bool
     */
    public function is_active() {
        return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
    }

    /**
     * Register and return the script handle(s) for this payment method.
     *
     * @return string[]
     */
    public function get_payment_method_script_handles() {

        wp_register_script(
            'wc-pg-boilerplate-blocks-integration',
            WC_PG_BOILERPLATE_DIR_URL . 'assets/js/checkout-payments-block.js',
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ),
            WC_PG_BOILERPLATE_VERSION,
            true
        );

        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations(
                'wc-pg-boilerplate-blocks-integration',
                'wc-pg-boilerplate',
                WC_PG_BOILERPLATE_DIR_PATH . 'languages'
            );
        }

        return array( 'wc-pg-boilerplate-blocks-integration' );
    }

    /**
     * Return the data that will be available to the frontend block JS
     * via window.wc.wcSettings.getSetting('pg_boilerplate_data').
     *
     * @return array
     */
    public function get_payment_method_data() {

        return array(
            'title'       => $this->settings['title']       ?? __( 'Custom Payment', 'wc-pg-boilerplate' ),
            'description' => $this->settings['description'] ?? __( 'Pay securely via our payment gateway.', 'wc-pg-boilerplate' ),
            // TODO: Replace with your gateway's logo URL or remove if not needed.
            // 'icon'     => WC_PG_BOILERPLATE_DIR_URL . 'assets/images/gateway-logo.png',
            'icon'        => '',
            'supports'    => $this->get_supported_features(),
        );
    }
}
