<?php
if (!defined('ABSPATH')) {
    exit;
}

/*===========================================================
--------- Main WooCommerce Payment Class  ---------- 
=========================================================== */
class WC_Gateway_Payment_Gateway_Boilerplate extends WC_Payment_Gateway
{

    public $api_key;
    public $title;
    public $description;
    public $enabled;

    public function __construct()
    {
        $this->id                 = 'payment_gateway_name';
        $this->method_title       = __('Enter Here your Payment Gateway title', 'wc-payments');
        $this->method_description = __('Pay securely using Payments.', 'wc-payments');

        $this->supports = array('products');

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title       = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'));
        $this->api_key     = sanitize_text_field($this->get_option('api_key'));
        $this->enabled     = sanitize_text_field($this->get_option('enabled'));

        // Save admin options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /*=====================================================================
    --------- Create a Fileds in a Woocommerce Payment option  ---------- 
    ======================================================================= */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'wc-payments'),
                'type'    => 'checkbox',
                'label'   => __('Enable AddyourpaymentGatewayName Payments ', 'wc-payments'),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __('Title', 'wc-payments'),
                'type'        => 'text',
                'description' => __('This controls the title seen during checkout.', 'wc-payments'),
                'default'     => __('AddTitle Payments', 'wc-payments'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'wc-payments'),
                'type'        => 'textarea',
                'description' => __('Payment method description shown at checkout.', 'wc-payments'),
                'default'     => __('Pay securely via Payments.', 'wc-payments'),
                'desc_tip'    => true,
            ),
            'payment_gateway_mode' => array(
                'title'       => __('Payment Gateway Mode', 'wc-payments'),
                'type'        => 'select',
                'options'     => array(
                    "0"        => __('Select Mode', 'wc-payments'),
                    "sandbox"  => __('Sandbox', 'wc-payments'),
                    "live"     => __('Live', 'wc-payments')
                ),
                'description' => __('Select the mode for the  Payment gateway (Sandbox for testing or Live for production).', 'wc-payments'),
            ),
            'api_key' => array(
                'title'       => __('API Key', 'wc-payments'),
                'type'        => 'text',
                'description' => __('Enter your  Payments API key here.', 'wc-payments'),
                'default'     => '',
            ),
            'api_salt' => array(
                'title'       => __('API Salt', 'wc-payments'),
                'type'        => 'text',
                'description' => __('Enter your  Payments API Salt here.', 'wc-payments'),
                'default'     => '',
            ),
        );
    }
    /*=====================================================================
    --------- Click on Payment proceed option go to payment page  ---------- 
    ======================================================================= */
    // public function process_payment($order_id)
    // {
    //     global $wpdb;

    //     $order = wc_get_order($order_id);
    //     $paymentGatewayMode = get_option('woocommerce_payment_gateway_name_settings');

    //     $payment_url = 'https://test.mode/';
    //     if (isset($paymentGatewayMode['payment_gateway_mode']) && $paymentGatewayMode['payment_gateway_mode'] === 'live') {
    //         $payment_url = 'https://live.mode';
    //     }

    //     // Add your Payment Gateway API Here
    //     $payment_url = '';

    //     $params = array(
    //         'order_id' => $order->get_id(),
    //         'amount'   => $order->get_total(),
    //         'key'      => $order->get_order_key(),
    //         'email'    => $order->get_billing_email(),
    //         'phone'    => $order->get_billing_phone(),
    //         'return_url' => $this->get_return_url($order),
    //     );

    //     // Final Redirect URL
    //     $redirect_url = add_query_arg($params, $payment_url);

    //     // Mark order as 'on-hold' till payment confirmation
    //     $order->update_status('on-hold', __('Awaiting Payments payment.', 'wc-payments'));

    //     return [
    //         'result'   => 'success',
    //         'redirect' => $redirect_url
    //     ];
    // }
  
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
    
        // Get payment settings
        $AdminPaymentOptionSettings = get_option('woocommerce_payment_gateway_name_settings');
    
        // Determine payment URL based on mode
        $payment_url = 'https://test.payu.in/_payment';
        if (!empty($AdminPaymentOptionSettings['payment_gateway_mode']) && $AdminPaymentOptionSettings['payment_gateway_mode'] === 'live') {
            $payment_url = 'https://secure.payu.in/_payment';
        }
    
        // Fetch API credentials
        $merchant_key = isset($AdminPaymentOptionSettings['api_key']) ? trim($AdminPaymentOptionSettings['api_key']) : '';
        $salt         = isset($AdminPaymentOptionSettings['api_salt']) ? trim($AdminPaymentOptionSettings['api_salt']) : '';
    
        // Validate credentials
        if (empty($merchant_key) || empty($salt)) {
            wc_add_notice(__('Payment gateway credentials are missing. Please configure API Key and Salt.', 'wc-payments'), 'error');
            return ['result' => 'failure'];
        }
    
        // Generate unique transaction ID
        $txnid = $order->get_id() . '_' . time();
    
        // Required PayU parameters
        $params = [
            'key'           => $merchant_key,
            'txnid'         => $txnid,
            'amount'        => number_format($order->get_total(), 2, '.', ''), // Ensure valid format
            'productinfo'   => 'Order ' . $order->get_id(),
            'firstname'     => $order->get_billing_first_name(),
            'email'         => $order->get_billing_email(),
            'phone'         => $order->get_billing_phone(),
            'surl'          => $this->get_return_url($order), // Success URL
            'furl'          => wc_get_checkout_url(), // Failure URL
            'service_provider' => 'payu_paisa',
            'udf1'          => '', // Optional
            'udf2'          => '',
            'udf3'          => '',
            'udf4'          => '',
            'udf5'          => ''
        ];
    
        // Generate Secure Hash (Fix)
        $hash_sequence = [
            $params['key'],
            $params['txnid'],
            $params['amount'],
            $params['productinfo'],
            $params['firstname'],
            $params['email'],
            $params['udf1'],
            $params['udf2'],
            $params['udf3'],
            $params['udf4'],
            $params['udf5'],
            '', '', '', '', '',
            $salt
        ];
        
        $hash_string = implode('|', $hash_sequence);
        $params['hash'] = hash('sha512', $hash_string); // Hash should not be lowercase
    
        // Construct redirect URL
        $redirect_url = $payment_url . '?' . http_build_query($params);
    
        // Mark order as "on-hold"
        $order->update_status('on-hold', __('Awaiting PayU payment.', 'wc-payments'));
    
        return [
            'result'   => 'success',
            'redirect' => $redirect_url
        ];
    }
    
 
}
