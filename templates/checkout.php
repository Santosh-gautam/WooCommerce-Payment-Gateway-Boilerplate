<?php
/*=========================================================================================
------------------- Payment Block Based Checkout -------------------------------
========================================================================================= */

add_action('before_woocommerce_init', function () {
  if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
  }
});

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

add_action('woocommerce_blocks_loaded', 'payment_woocommerce_block_support');

function payment_woocommerce_block_support()
{
  if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
    require_once plugin_dir_path(__FILE__) . '../includes/class-wc-payments-checkout-block.php';

    add_action(
      'woocommerce_blocks_payment_method_type_registration',
      function (PaymentMethodRegistry $payment_method_registry) {
        $container = Automattic\WooCommerce\Blocks\Package::container();
        $container->register(
          WC_Checkout_Blocks::class,
          function () {
            return new WC_Checkout_Blocks();
          }
        );
        $payment_method_registry->register($container->get(WC_Checkout_Blocks::class));
      },
    );
  }
}
