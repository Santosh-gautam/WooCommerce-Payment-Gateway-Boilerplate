# WooCommerce Payment Gateway Boilerplate – Build Custom Payment Gateways for Razorpay, PayU, Stripe & More

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![License](https://img.shields.io/badge/license-GPL_v3-green.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-purple.svg)
![HPOS](https://img.shields.io/badge/HPOS-Compatible-success.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)

> A professional, production-ready WooCommerce payment gateway plugin boilerplate. Supports WooCommerce Block Checkout (Gutenberg), Classic Checkout, HPOS, Webhooks/IPN, and Refunds — out of the box. Works with **any** payment gateway API: Razorpay, PayU, Stripe, Cashfree, CCAvenue, or your own custom API.

---

## 📌 What Is This?

Building a **custom WooCommerce payment gateway plugin** from scratch is time-consuming. You have to handle:

- WooCommerce Classic Checkout integration (`process_payment()`)
- WooCommerce Block Checkout (Gutenberg block) integration
- HPOS (High-Performance Order Storage) compatibility
- Webhook / IPN handler for payment callbacks
- Admin settings panel with Sandbox / Live mode toggle
- Refund API integration (`process_refund()`)

This boilerplate gives you a **rock-solid, ready-to-customize foundation** for all of the above. Simply clone the repository, search for every `// TODO:` comment in the codebase, and replace it with your specific payment gateway's API logic.

**No hardcoded vendor APIs — 100% generic. Bring your own payment gateway.**

📚 **In-Depth Guide:** [WooCommerce Payment Gateway Development Guide](https://www.hisantosh.com/blog/woocommerce-payment-gateway-development-guide)

---

## ✨ Features

| Feature | Status | Notes |
|---|---|---|
| WooCommerce Classic Checkout | ✅ | Fully functional `process_payment()` skeleton |
| WooCommerce Block Checkout (Gutenberg) | ✅ | React-based block integration included |
| Sandbox / Live Mode Toggle | ✅ | Built-in environment switching |
| WooCommerce Logger Integration | ✅ | Easy debugging via `wc_get_logger()` |
| Webhook / IPN Handler | ✅ | Secure payment callback endpoint included |
| Refund Support | ✅ | `process_refund()` method ready to implement |
| HPOS Compatible | ✅ | Works with WooCommerce High-Performance Order Storage |
| Scoped Admin Asset Enqueue | ✅ | JS/CSS only loads on WC settings page |
| Clean Uninstall | ✅ | Removes all WP Options on deletion (Multisite safe) |
| Translation Ready | ✅ | Text domain prepared for `.pot` generation |

---

## 🎯 Who Is This For?

This WooCommerce payment gateway boilerplate is perfect for:

- **WordPress developers** building a custom payment gateway plugin for a client
- **Payment gateway companies** who want to ship a WooCommerce plugin quickly
- **Freelancers** integrating Indian payment gateways like Razorpay, PayU, Cashfree, or CCAvenue with WooCommerce
- **Developers** who want a production-ready starting point with HPOS and Block Checkout already handled

---

## 📂 File Structure

```text
woocommerce-payment-gateway-boilerplate/
│
├── woocommerce-payment-gateway-boilerplate.php  ← Main plugin entry point
├── uninstall.php                                ← Cleanup on plugin deletion
│
├── includes/
│   ├── class-wc-payments-gateway.php            ← Core gateway class (extends WC_Payment_Gateway)
│   └── class-wc-payments-checkout-block.php     ← Block checkout integration
│
├── templates/
│   └── checkout.php                             ← Block registration template
│
├── assets/
│   ├── css/
│   │   └── admin-style.css                      ← Admin-only CSS
│   └── js/
│       ├── admin-script.js                      ← Admin-only JS
│       └── checkout-payments-block.js           ← Frontend block checkout JS
│
└── README.md
```

---

## ⚙️ Installation

### 1. Upload the Plugin
- Download the repository as a ZIP file.
- Go to **WordPress Admin → Plugins → Add New → Upload Plugin**.
- Choose the ZIP file and click **Install Now**, then **Activate**.

### 2. Configure the Payment Gateway
- Go to **WooCommerce → Settings → Payments**.
- Find **Payment Gateway Boilerplate** and click **Manage**.
- Enter your API credentials and choose Sandbox or Live mode.
- Save changes.

---

## 🛠️ Developer Guide — How to Customise

### Step 1: Rename the Plugin Identifiers

Search and replace the following strings across all files to match your gateway name:

| Find | Replace With |
|---|---|
| `pg_boilerplate` | `your_gateway_id` (lowercase, underscores) |
| `WC_Gateway_PG_Boilerplate` | `WC_Gateway_YourGateway` |
| `WC_PG_BOILERPLATE_` | `WC_YOURGATEWAY_` |
| `wc-pg-boilerplate` | `wc-yourgateway` (text domain) |

### Step 2: Implement `process_payment()`

Open `includes/class-wc-payments-gateway.php` and find the `process_payment()` method.  
Replace the `// TODO:` block with your gateway's API call to create a payment session or transaction.

### Step 3: Implement `handle_webhook()` (IPN / Payment Callback)

In the same file, find `handle_webhook()`.
1. Parse the POST body from your payment gateway.
2. **Verify the webhook signature** — never skip this step.
3. Extract the order ID and payment status.
4. Call `$order->payment_complete()` or `$order->update_status('failed')`.

### Step 4: Implement `process_refund()`

Find `process_refund()` and replace the `// TODO:` block with your gateway's refund API call.

### Step 5: Update Block Checkout JS

In `assets/js/checkout-payments-block.js`, update `'pg_boilerplate_data'` to match your gateway ID (`'{your_gateway_id}_data'`).

---

## 🔗 Webhook / IPN URL

Your WooCommerce payment gateway webhook URL is dynamically generated:

```text
https://yoursite.com/?wc-api=wc_payments_gateway_boilerplate
```

> **Note:** If you renamed the class in Step 1, replace `wc_payments_gateway_boilerplate` with your new class name. Register this URL in your payment provider's dashboard as the IPN or webhook endpoint.

---

## 📊 WooCommerce Debug Logs

All payment gateway events, errors, and webhook payloads are automatically logged for easy debugging.

To view logs: **WooCommerce → Status → Logs** → Select your gateway's log file from the dropdown.

---

## ❓ Frequently Asked Questions

**Q: Which payment gateways can I integrate with this boilerplate?**  
A: Any payment gateway with an API — Razorpay, PayU, Stripe, Cashfree, CCAvenue, Instamojo, or a fully custom gateway. The boilerplate is 100% vendor-agnostic.

**Q: Is this compatible with WooCommerce Block Checkout (Gutenberg)?**  
A: Yes. The boilerplate includes a full React-based block checkout integration in `class-wc-payments-checkout-block.php` and `checkout-payments-block.js`.

**Q: Is it HPOS compatible?**  
A: Yes. The boilerplate is fully compatible with WooCommerce High-Performance Order Storage (custom order tables).

**Q: Does it support payment refunds?**  
A: Yes. The `process_refund()` method skeleton is included. You just need to add your gateway's refund API call.

**Q: Can I use this for a production WooCommerce store?**  
A: After implementing your gateway's API logic inside the `// TODO:` sections, yes — the boilerplate is production-ready.

---

## 🤝 Support & Contribution

Found a bug or want to improve the boilerplate? Open an issue or submit a pull request on GitHub. Contributions are always welcome!

---

## 📄 License

This project is licensed under the **GPL v3 or later**. See the `LICENSE` file for details.

---

## 👨‍💻 About the Author

Hi, I'm **Santosh Gautam**, a WordPress & WooCommerce Developer. I build tools and write tutorials to make developers' lives easier.

🌐 **Website:** [hisantosh.com](https://www.hisantosh.com)  
🔗 **Project Details:** [WooCommerce Payment Gateway Boilerplate](https://www.hisantosh.com/project/woocommerce-payment-gateway-boilerplate)  
📝 **Read the Guide:** [WooCommerce Payment Gateway Development Guide](https://www.hisantosh.com/blog/woocommerce-payment-gateway-development-guide)  
📧 **Contact:** [gautamsantosh317@gmail.com](mailto:gautamsantosh317@gmail.com)

If this boilerplate saved you time, please consider ⭐ **starring the repo** — it helps other developers find it!