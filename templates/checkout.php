<?php
/**
 * Block-Based Checkout — Registration Template
 *
 * Registers WC_Checkout_Blocks with the WooCommerce Blocks
 * payment method registry.
 *
 * @package WC_PG_Boilerplate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

/**
 * Register block checkout support.
 */
add_action( 'woocommerce_blocks_loaded', 'wc_pg_boilerplate_block_support' );

function wc_pg_boilerplate_block_support() {

    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    require_once WC_PG_BOILERPLATE_DIR_PATH . 'includes/class-wc-payments-checkout-block.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function ( PaymentMethodRegistry $payment_method_registry ) {

            $container = Automattic\WooCommerce\Blocks\Package::container();

            $container->register(
                WC_Checkout_Blocks::class,
                function () {
                    return new WC_Checkout_Blocks();
                }
            );

            $payment_method_registry->register( $container->get( WC_Checkout_Blocks::class ) );
        }
    );
}
