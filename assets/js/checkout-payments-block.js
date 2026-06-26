/**
 * WooCommerce Payment Gateway Boilerplate
 * Block-Based Checkout — Frontend Payment Method Registration
 *
 * This file registers the payment method with the WooCommerce Blocks
 * registry so it appears in the Gutenberg block-based checkout.
 *
 * Data available via: window.wc.wcSettings.getSetting( 'pg_boilerplate_data', {} )
 */

( function () {

    const settings = window.wc.wcSettings.getSetting( 'pg_boilerplate_data', {} );

    const label = window.wp.htmlEntities.decodeEntities( settings.title )
        || window.wp.i18n.__( 'Custom Payment', 'wc-pg-boilerplate' );

    const description = window.wp.htmlEntities.decodeEntities( settings.description || '' );

    const icon = settings.icon || '';

    /**
     * The label component shown next to the radio button.
     * Renders the gateway title and optionally a logo.
     */
    const PaymentMethodLabel = () => {
        const labelText = window.wp.element.createElement(
            'span',
            { className: 'wc-pg-boilerplate__label' },
            label
        );

        if ( ! icon ) {
            return labelText;
        }

        const logoImage = window.wp.element.createElement( 'img', {
            src:       icon,
            alt:       label,
            className: 'wc-pg-boilerplate__icon',
            style:     { height: '24px', verticalAlign: 'middle', marginLeft: '8px' },
        } );

        return window.wp.element.createElement(
            'span',
            { style: { display: 'flex', alignItems: 'center', gap: '8px' } },
            labelText,
            logoImage
        );
    };

    /**
     * The content component shown below the radio button when this
     * payment method is selected.
     */
    const PaymentMethodContent = () => {
        if ( ! description ) {
            return null;
        }

        return window.wp.element.createElement(
            'p',
            { className: 'wc-pg-boilerplate__description' },
            description
        );
    };

    /**
     * Register the payment method with the WooCommerce Blocks registry.
     */
    const blockGateway = {
        name:            'pg_boilerplate',
        label:           window.wp.element.createElement( PaymentMethodLabel, null ),
        content:         window.wp.element.createElement( PaymentMethodContent, null ),
        edit:            window.wp.element.createElement( PaymentMethodContent, null ),
        canMakePayment:  () => true,
        ariaLabel:       label,
        supports: {
            features: settings.supports || [ 'products' ],
        },
    };

    window.wc.wcBlocksRegistry.registerPaymentMethod( blockGateway );

} )();
