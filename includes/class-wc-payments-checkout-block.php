<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Checkout_Blocks extends AbstractPaymentMethodType
{
    protected $name = 'payment_gateway_name';

    public function initialize()
    {
        $this->settings = get_option('woocommerce_payment_gateway_name_settings', []);
    }

    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'payment-blocks-integration',
            plugin_dir_url(__FILE__) . '../assets/js/checkout-payments-block.js',
            ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities', 'wp-i18n'],
            null,
            true
        );
        // echo "yes call this class function";

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('payment-blocks-integration', 'payment_gateway_name', plugin_dir_path(__FILE__) . 'languages');
        }

        return ['payment-blocks-integration'];
    }

    public function get_payment_method_data()
    {
        return [
            'title' => $this->settings['title'] ?? __('Pay by AddYourPaymentGatewayName', 'payment_gateway_name'),
            'description' => $this->settings['description'] ?? __('Pay securely via AddYourPaymentGatewayName Payments.', 'payment_gateway_name'),
            'PaymentGatewayimage'       => 'https://woocommerce.com/wp-content/themes/woo/images/woo-logo.svg',
        ];
    }
}
