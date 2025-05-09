const settings = window.wc.wcSettings.getSetting("payment_gateway_name_data", {});
// console.log(settings);
// console.log("==============================");
const label =
  window.wp.htmlEntities.decodeEntities(settings.title) ||
  window.wp.i18n.__("WooCommerce BoliterPlate payment CommercePro Plugin", "wc-payments");
const PaymentGatewayimage = window.wp.htmlEntities.decodeEntities(settings.PaymentGatewayimage);

const Content = () => {
  const description = settings.description;
  return description;
};

// Add Image With a wc-payments title
const PaymentBlock = () => {
  return Object(window.wp.element.createElement)(
    "div",
    {
      className: "Wcpayment-content",
      style: { display: "flex", alignItems: "center", gap: "18px" },
    },

    Object(window.wp.element.createElement)("div", null, label),
    Object(window.wp.element.createElement)("img", {
      src: PaymentGatewayimage,
      alt: "payment Logo",
      style: { width: "25%", marginBottom: "10px", marginTop: "10px" },
    })
  );
};

const Block_Gateway = {
  name: "payment_gateway_name",
  label: Object(window.wp.element.createElement)(PaymentBlock, null),
  content: Object(window.wp.element.createElement)(Content, null),
  edit: Object(window.wp.element.createElement)(Content, null),
  canMakePayment: () => {
    // alert("✅ canMakePayment() called");
    // console.log("✅ Checking canMakePayment()");
    return true;
  },
  ariaLabel: label,
  supports: {
    features: settings.supports,
  },
};
// console.log("====== Block Gateway ==============");
// console.log(Block_Gateway);
window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);

document.addEventListener('DOMContentLoaded', () => {

  const observer = new MutationObserver((mutationsList, observer) => {
      const getTitleDiv = document.querySelector('#radio-control-wc-payment-method-options-payment_gateway_name__label');

      if (getTitleDiv) {
          // console.log("===========================");
          getTitleDiv.classList = '';
          observer.disconnect(); 
      }
  });

  observer.observe(document.body, {
      childList: true,
      subtree: true
  });
});
