<?php
/**
 * WC_Gateway_PG_Boilerplate — Core Payment Gateway Class
 *
 * Extend this class and fill in the TODO sections with your
 * specific payment gateway API logic.
 *
 * @package WC_PG_Boilerplate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WC_Gateway_PG_Boilerplate
 *
 * Extends WC_Payment_Gateway to integrate a custom payment provider.
 */
class WC_Gateway_PG_Boilerplate extends WC_Payment_Gateway {

    /*===========================================================
     * Class Properties
     *=========================================================== */

    /** @var string API key from gateway settings. */
    public $api_key;

    /** @var string API salt/secret from gateway settings. */
    public $api_salt;

    /** @var bool Whether sandbox/test mode is active. */
    public $testmode;

    /** @var WC_Logger Logger instance. */
    public $logger;

    /** @var string Log source identifier. */
    const LOG_SOURCE = 'wc-pg-boilerplate';

    /*===========================================================
     * Constructor
     *=========================================================== */
    public function __construct() {

        $this->id                 = 'pg_boilerplate';
        $this->has_fields         = false; // Set true if you render inline card fields.
        $this->method_title       = __( 'Payment Gateway Boilerplate', 'wc-pg-boilerplate' );
        $this->method_description = __( 'A generic payment gateway boilerplate. Replace the TODO sections with your gateway\'s API logic.', 'wc-pg-boilerplate' );

        // Supported features.
        $this->supports = array(
            'products',
            'refunds',
        );

        // Load settings fields and saved values.
        $this->init_form_fields();
        $this->init_settings();

        // Map settings to class properties.
        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->api_key     = $this->get_option( 'api_key' );
        $this->api_salt    = $this->get_option( 'api_salt' );
        $this->testmode    = 'yes' === $this->get_option( 'testmode', 'yes' );
        $this->enabled     = $this->get_option( 'enabled' );

        // Initialise logger.
        $this->logger = wc_get_logger();

        // Hooks.
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array( $this, 'process_admin_options' )
        );

        // Webhook listener endpoint: yoursite.com/?wc-api=wc_payments_gateway_boilerplate
        add_action(
            'woocommerce_api_wc_payments_gateway_boilerplate',
            array( $this, 'handle_webhook' )
        );
    }

    /*===========================================================
     * Admin Settings Fields
     *=========================================================== */

    /**
     * Define settings fields shown in WooCommerce > Settings > Payments.
     */
    public function init_form_fields() {

        $this->form_fields = array(

            'enabled' => array(
                'title'   => __( 'Enable / Disable', 'wc-pg-boilerplate' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Payment Gateway Boilerplate', 'wc-pg-boilerplate' ),
                'default' => 'no',
            ),

            'title' => array(
                'title'       => __( 'Title', 'wc-pg-boilerplate' ),
                'type'        => 'text',
                'description' => __( 'The payment method title the customer sees during checkout.', 'wc-pg-boilerplate' ),
                'default'     => __( 'Custom Payment', 'wc-pg-boilerplate' ),
                'desc_tip'    => true,
            ),

            'description' => array(
                'title'       => __( 'Description', 'wc-pg-boilerplate' ),
                'type'        => 'textarea',
                'description' => __( 'The payment method description shown to the customer on the checkout page.', 'wc-pg-boilerplate' ),
                'default'     => __( 'Pay securely via our payment gateway.', 'wc-pg-boilerplate' ),
                'desc_tip'    => true,
            ),

            'testmode' => array(
                'title'       => __( 'Test Mode', 'wc-pg-boilerplate' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable Test / Sandbox Mode', 'wc-pg-boilerplate' ),
                'default'     => 'yes',
                'description' => __( 'When checked, transactions will use sandbox credentials and will NOT process real payments.', 'wc-pg-boilerplate' ),
                'desc_tip'    => true,
            ),

            'api_credentials_title' => array(
                'title' => __( 'API Credentials', 'wc-pg-boilerplate' ),
                'type'  => 'title',
                'description' => __( 'Enter the credentials provided by your payment gateway provider.', 'wc-pg-boilerplate' ),
            ),

            'api_key' => array(
                'title'       => __( 'API Key', 'wc-pg-boilerplate' ),
                'type'        => 'text',
                'description' => __( 'Your payment gateway API key (also called Merchant Key or Client ID).', 'wc-pg-boilerplate' ),
                'default'     => '',
                'desc_tip'    => true,
            ),

            'api_salt' => array(
                'title'       => __( 'API Salt / Secret', 'wc-pg-boilerplate' ),
                'type'        => 'password',
                'description' => __( 'Your payment gateway API salt, secret, or client secret.', 'wc-pg-boilerplate' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    /*===========================================================
     * Process Payment — Called when customer places an order
     *=========================================================== */

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id The WooCommerce Order ID.
     * @return array        Result array with 'result' and 'redirect' keys.
     */
    public function process_payment( $order_id ) {

        try {

            $order = wc_get_order( $order_id );

            if ( ! $order ) {
                throw new Exception( __( 'Order not found.', 'wc-pg-boilerplate' ) );
            }

            // Validate API credentials are configured.
            if ( empty( $this->api_key ) || empty( $this->api_salt ) ) {
                wc_add_notice( __( 'Payment gateway is not configured. Please contact the store administrator.', 'wc-pg-boilerplate' ), 'error' );
                $this->logger->error(
                    'Payment attempted but API Key or Salt is empty.',
                    array( 'source' => self::LOG_SOURCE )
                );
                return array( 'result' => 'failure' );
            }

            // -------------------------------------------------------
            // Collect order & billing details for your API call.
            // -------------------------------------------------------
            $order_data = array(
                'order_id'      => $order->get_id(),
                'order_key'     => $order->get_order_key(),
                'amount'        => $order->get_total(),
                'currency'      => get_woocommerce_currency(),
                'first_name'    => $order->get_billing_first_name(),
                'last_name'     => $order->get_billing_last_name(),
                'email'         => $order->get_billing_email(),
                'phone'         => $order->get_billing_phone(),
                'address'       => $order->get_billing_address_1(),
                'city'          => $order->get_billing_city(),
                'state'         => $order->get_billing_state(),
                'postcode'      => $order->get_billing_postcode(),
                'country'       => $order->get_billing_country(),
                'return_url'    => $this->get_return_url( $order ),
                'webhook_url'   => add_query_arg( 'wc-api', 'wc_payments_gateway_boilerplate', home_url( '/' ) ),
                'testmode'      => $this->testmode,
            );

            // -------------------------------------------------------
            // TODO: Replace the block below with your gateway's API
            //       call. Use $this->api_key, $this->api_salt,
            //       and $order_data to build your request.
            //
            // Example flow for a redirect-based gateway:
            //
            //   $api_url = $this->testmode
            //       ? 'https://sandbox.yourgateway.com/pay'
            //       : 'https://secure.yourgateway.com/pay';
            //
            //   $response = wp_remote_post( $api_url, array(
            //       'body'    => $your_params,
            //       'timeout' => 45,
            //   ) );
            //
            //   if ( is_wp_error( $response ) ) {
            //       throw new Exception( $response->get_error_message() );
            //   }
            //
            //   $body = json_decode( wp_remote_retrieve_body( $response ), true );
            //
            //   // Retrieve the payment redirect URL from the response.
            //   $payment_redirect_url = $body['payment_url'] ?? '';
            //
            // -------------------------------------------------------

            // TEMPORARY: Redirect to the WooCommerce order-pay page
            // until the TODO above is implemented.
            $payment_redirect_url = $order->get_checkout_payment_url( true );

            // Mark order as pending payment (stock is reduced here).
            $order->update_status( 'pending', __( 'Awaiting payment confirmation.', 'wc-pg-boilerplate' ) );

            // Reduce stock levels.
            wc_reduce_stock_levels( $order_id );

            // Remove cart contents.
            WC()->cart->empty_cart();

            $this->logger->info(
                sprintf( 'Payment initiated for Order #%d. Redirecting customer.', $order_id ),
                array( 'source' => self::LOG_SOURCE )
            );

            return array(
                'result'   => 'success',
                'redirect' => $payment_redirect_url,
            );

        } catch ( Exception $e ) {

            $this->logger->error(
                sprintf( 'process_payment() Exception for Order #%d: %s', $order_id, $e->getMessage() ),
                array( 'source' => self::LOG_SOURCE )
            );

            wc_add_notice(
                __( 'Payment processing failed. Please try again or contact support.', 'wc-pg-boilerplate' ),
                'error'
            );

            return array( 'result' => 'failure' );
        }
    }

    /*===========================================================
     * Webhook / IPN Handler
     *=========================================================== */

    /**
     * Handle incoming webhook / IPN notifications from the gateway.
     *
     * Endpoint: {site_url}/?wc-api=wc_payments_gateway_boilerplate
     *
     * How to register your webhook URL with the gateway:
     *   add_query_arg( 'wc-api', 'wc_payments_gateway_boilerplate', home_url( '/' ) )
     */
    public function handle_webhook() {

        // Read the raw POST body.
        $raw_body = file_get_contents( 'php://input' );

        $this->logger->info(
            'Webhook received. Raw body: ' . $raw_body,
            array( 'source' => self::LOG_SOURCE )
        );

        // -------------------------------------------------------
        // TODO: Parse the incoming data from your gateway.
        //
        //   $data = json_decode( $raw_body, true );
        //   // OR for form-encoded responses:
        //   $data = $_POST;
        // -------------------------------------------------------

        // -------------------------------------------------------
        // TODO: Verify the webhook signature / hash to ensure the
        //       request is genuinely from your payment gateway.
        //       NEVER skip this step in production.
        //
        // Example (SHA256 HMAC):
        //   $received_signature = $_SERVER['HTTP_X_GATEWAY_SIGNATURE'] ?? '';
        //   $expected_signature = hash_hmac( 'sha256', $raw_body, $this->api_salt );
        //
        //   if ( ! hash_equals( $expected_signature, $received_signature ) ) {
        //       $this->logger->warning( 'Webhook signature mismatch.', array( 'source' => self::LOG_SOURCE ) );
        //       status_header( 401 );
        //       exit;
        //   }
        // -------------------------------------------------------

        // -------------------------------------------------------
        // TODO: Extract the Order ID and payment status from the
        //       gateway's response payload.
        //
        //   $order_id      = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;
        //   $payment_status = sanitize_text_field( $data['status'] ?? '' );
        // -------------------------------------------------------

        // --- EXAMPLE logic (replace with real data) ---
        $order_id       = 0;   // TODO: Replace with extracted order ID.
        $payment_status = '';  // TODO: Replace with extracted status string.
        // --- END EXAMPLE ---

        if ( $order_id > 0 ) {

            $order = wc_get_order( $order_id );

            if ( $order ) {

                // -------------------------------------------------------
                // TODO: Map your gateway's status strings to WooCommerce
                //       order statuses.
                // -------------------------------------------------------

                if ( 'success' === $payment_status /* TODO: match your gateway's success string */ ) {

                    if ( ! $order->is_paid() ) {
                        $order->payment_complete();
                        $order->add_order_note( __( 'Payment confirmed via webhook. Order marked as Processing.', 'wc-pg-boilerplate' ) );
                        $this->logger->info(
                            sprintf( 'Order #%d payment confirmed via webhook.', $order_id ),
                            array( 'source' => self::LOG_SOURCE )
                        );
                    }
                } else {

                    $order->update_status( 'failed', __( 'Payment failed or was declined. Notified via webhook.', 'wc-pg-boilerplate' ) );
                    $this->logger->warning(
                        sprintf( 'Order #%d payment failed via webhook. Status: %s', $order_id, $payment_status ),
                        array( 'source' => self::LOG_SOURCE )
                    );
                }
            } else {
                $this->logger->error(
                    sprintf( 'Webhook received but Order #%d not found.', $order_id ),
                    array( 'source' => self::LOG_SOURCE )
                );
            }
        }

        // Always respond with 200 OK to acknowledge receipt.
        status_header( 200 );
        exit;
    }

    /*===========================================================
     * Refund Handler
     *=========================================================== */

    /**
     * Process a refund for a given order.
     *
     * @param int        $order_id  WooCommerce Order ID.
     * @param float|null $amount    Amount to refund. Null = full refund.
     * @param string     $reason    Reason for the refund.
     * @return bool|WP_Error        True on success, WP_Error on failure.
     */
    public function process_refund( $order_id, $amount = null, $reason = '' ) {

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return new WP_Error( 'invalid_order', __( 'Order not found.', 'wc-pg-boilerplate' ) );
        }

        if ( is_null( $amount ) || $amount <= 0 ) {
            return new WP_Error( 'invalid_amount', __( 'Invalid refund amount. Amount must be greater than zero.', 'wc-pg-boilerplate' ) );
        }

        // -------------------------------------------------------
        // TODO: Retrieve the original gateway transaction ID stored
        //       on the order (you should save this in process_payment).
        //
        //   $transaction_id = $order->get_transaction_id();
        //
        //   if ( empty( $transaction_id ) ) {
        //       return new WP_Error( 'no_transaction_id', __( 'No transaction ID found for this order.', 'wc-pg-boilerplate' ) );
        //   }
        // -------------------------------------------------------

        try {

            // -------------------------------------------------------
            // TODO: Make the API call to your gateway's refund endpoint.
            //
            // Example:
            //   $response = wp_remote_post( 'https://api.yourgateway.com/refund', array(
            //       'body' => array(
            //           'api_key'        => $this->api_key,
            //           'transaction_id' => $transaction_id,
            //           'amount'         => $amount,
            //           'reason'         => $reason,
            //       ),
            //       'timeout' => 45,
            //   ) );
            //
            //   if ( is_wp_error( $response ) ) {
            //       throw new Exception( $response->get_error_message() );
            //   }
            //
            //   $body = json_decode( wp_remote_retrieve_body( $response ), true );
            //
            //   if ( empty( $body['refund_id'] ) ) {
            //       throw new Exception( $body['message'] ?? __( 'Refund failed.', 'wc-pg-boilerplate' ) );
            //   }
            // -------------------------------------------------------

            // Add a note to the order confirming the refund.
            $order->add_order_note(
                sprintf(
                    /* translators: 1: refund amount, 2: reason */
                    __( 'Refund of %1$s processed. Reason: %2$s', 'wc-pg-boilerplate' ),
                    wc_price( $amount ),
                    $reason ?: __( 'N/A', 'wc-pg-boilerplate' )
                )
            );

            $this->logger->info(
                sprintf( 'Refund of %s processed for Order #%d. Reason: %s', $amount, $order_id, $reason ),
                array( 'source' => self::LOG_SOURCE )
            );

            return true;

        } catch ( Exception $e ) {

            $this->logger->error(
                sprintf( 'Refund failed for Order #%d: %s', $order_id, $e->getMessage() ),
                array( 'source' => self::LOG_SOURCE )
            );

            return new WP_Error( 'refund_failed', $e->getMessage() );
        }
    }
}
