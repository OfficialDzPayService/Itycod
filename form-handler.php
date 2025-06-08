<?php
// Function to log orders for an IP
function log_order_for_ip($ip) {
    $order_limits = get_option('itycod_order_limits', array());
    $current_time = time();
    
    // Get order limit duration from settings (convert hours to seconds)
    $limit_duration = get_option('itycod_order_limit_time', 24) * 60 * 60;

    // Remove old logs (older than the limit duration)
    if (isset($order_limits[$ip])) {
        $order_limits[$ip] = array_filter($order_limits[$ip], function($timestamp) use ($current_time, $limit_duration) {
            return ($current_time - $timestamp) <= $limit_duration;
        });
    }

    // Log current order
    $order_limits[$ip][] = $current_time;

    // Save the updated list
    update_option('itycod_order_limits', $order_limits);
}

// Function to check if an IP has exceeded the order limit
function has_exceeded_order_limit($ip) {
    $order_limits = get_option('itycod_order_limits', array());
    $current_time = time();
    
    // Get order limit count and time from settings
    $limit_duration = get_option('itycod_order_limit_time', 24) * 60 * 60; // Default 24 hours in seconds
    $order_limit_count = get_option('itycod_order_limit_count', 0); // Default to 0 (disabled)

    // If the limit is set to 0, return false (no limit)
    if ($order_limit_count <= 0) {
        return false; 
    }

    // Remove old logs (older than the limit duration)
    if (isset($order_limits[$ip])) {
        $order_limits[$ip] = array_filter($order_limits[$ip], function($timestamp) use ($current_time, $limit_duration) {
            return ($current_time - $timestamp) <= $limit_duration;
        });

        // Check if the limit is exceeded
        if (count($order_limits[$ip]) >= $order_limit_count) {
            block_ip_for_time($ip, $limit_duration); // Block IP for the limit duration
            return true;
        }
    }

    return false;
}

// Function to block an IP for a specific time
function block_ip_for_time($ip, $block_duration = 24 * 60 * 60) {
    $blocked_ips = get_option('itycod_blocked_ips', array());
    $blocked_ips[$ip] = time() + $block_duration;
    update_option('itycod_blocked_ips', $blocked_ips);
}

// Function to check if an IP is blocked
function check_ip_order_limits($customer_ip) {
    $blocked_ips = get_option('itycod_blocked_ips', array());

    // Check if the IP is blocked
    if (isset($blocked_ips[$customer_ip])) {
        $current_time = time();
        if ($blocked_ips[$customer_ip] > $current_time) {
            return array('status' => 'blocked', 'message' => __('لقد وصلت إلى الحد الأقصى للطلب. يرجى محاولة مرة أخرى في وقت لاحق.', 'textdomain'));
        } else {
            // Remove expired IP from blocked list
            unset($blocked_ips[$customer_ip]);
            update_option('itycod_blocked_ips', $blocked_ips);
        }
    }
    
        // Automatically unblock if order limit is disabled
    if (get_option('itycod_order_limit_count', 0) <= 0) {
        // If the limit is set to 0, clear all blocked IPs
        delete_option('itycod_blocked_ips'); // Clear the entire blocked IP list
    }

    return array('status' => 'ok');
}

// Handle form submission
function handle_custom_checkout_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' 
        && isset($_POST['quantity']) 
        && isset($_POST['product_id'])) {

        $customer_ip = $_SERVER['REMOTE_ADDR']; // Get customer's IP address

        // Check IP blocking
        $ip_check = check_ip_order_limits($customer_ip);
        if ($ip_check['status'] !== 'ok') {
            wc_add_notice($ip_check['message'], 'error');
            return;
        }

        // Check if the IP has exceeded order limits
        if (has_exceeded_order_limit($customer_ip)) {
            wc_add_notice(__('لقد وصلت إلى الحد الأقصى للطلب. يرجى محاولة مرة أخرى في وقت لاحق.', 'textdomain'), 'error');
            return;
        }

        $first_name = get_option('itycod_disable_first_name') === 'yes' ? '' : (isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : '');
        $phone = get_option('itycod_disable_phone') === 'yes' ? '' : (isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '');
		$email      = get_option('itycod_disable_email') === 'yes' ? '' : (isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : '');
        $address_1 = get_option('itycod_disable_address') === 'yes' ? '' : (isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : '');
        $state = get_option('itycod_disable_state') === 'yes' ? '' : (isset($_POST['billing_state']) ? sanitize_text_field($_POST['billing_state']) : '');
        $city = get_option('itycod_disable_city') === 'yes' ? '' : (isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : '');
        $quantity = intval($_POST['quantity']);
        $product_id = intval($_POST['product_id']);
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
        
  $product = wc_get_product($product_id);
if (!$product->is_in_stock()) {
    wc_add_notice(
        get_option('itycod_error_product_out_of_stock', 'عذراً، المنتج الذي اخترته غير متوفر حالياً في المخزون.'), 
        'error'
    );
    return;
}

      if ($variation_id) {
    $variation = wc_get_product($variation_id);
    if ($variation && !$variation->is_in_stock()) {
        wc_add_notice(
            get_option('itycod_error_variation_out_of_stock', 'عذراً، هذا النوع من المنتج غير متوفر حالياً.'), 
            'error'
        );
        return;
    }
}
        $enable_security_check = get_option('itycod_enable_security_check') === 'yes';

        // Collect attribute selections
        $attributes = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'attribute_') === 0) {
                $attributes[$key] = sanitize_text_field($value);
            }
        }

        // Validate security code if enabled
        if ($enable_security_check) {
            if (isset($_POST['security_code']) 
                && isset($_SESSION['security_code']) 
                && $_POST['security_code'] === $_SESSION['security_code']) {
                // Security code is correct
            } else {
                wc_add_notice(__('كود الأمان غير صحيح.', 'textdomain'), 'error');
                return;
            }
        }

if (!empty($phone)) {
    // Validate that the phone number consists of exactly 10 digits
    if (!preg_match('/^\d{10}$/', $phone)) {
        wc_add_notice(
            get_option('itycod_error_invalid_phone', 'الرجاء إدخال رقم هاتف صالح .'), 
            'error'
        );
        return;
    }
}


        // Skip WooCommerce default Add to Cart handling
add_action('woocommerce_add_to_cart', function() {
    if (isset($_REQUEST['add-to-cart'])) {
        remove_action('woocommerce_add_to_cart', 'woocommerce_add_to_cart_action', 20);
    }
}, 1);

// Custom Add to Cart handling
if (isset($_REQUEST['add-to-cart'])) {
    $product_id = intval($_REQUEST['add-to-cart']);
    $quantity = isset($_REQUEST['quantity']) ? intval($_REQUEST['quantity']) : 1;
    $variation_id = isset($_REQUEST['variation_id']) ? intval($_REQUEST['variation_id']) : 0;
    $variation_attributes = isset($_REQUEST['attribute']) ? array_map('sanitize_text_field', $_REQUEST['attribute']) : array();

    if ($product_id) {
        $product = wc_get_product($product_id);
        if ($product->is_type('variable')) {
            if ($variation_id > 0 && !empty($variation_attributes)) {
                WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_attributes);
                wc_clear_notices();
                wc_add_notice(
                    get_option('itycod_success_add_to_cart', 'تم إضافة المنتج إلى السلة بنجاح!'), 
                    'success'
                );
            }
        } else {
      
        }
    }
    $redirect_url = get_permalink($product_id);
    wp_safe_redirect($redirect_url);
    exit;
}

if (
    (get_option('itycod_disable_first_name') !== 'yes' && empty($first_name)) ||
    (get_option('itycod_disable_state') !== 'yes' && empty($state)) ||
    (get_option('itycod_disable_phone') !== 'yes' && empty($phone)) ||
    empty($quantity) ||
    empty($product_id)
) {
    wc_add_notice(
        get_option('itycod_error_required_fields', 'الرجاء ملء جميع الحقول المطلوبة.'), 
        'error'
    );
    return;
}

    // Check for required attribute selections
foreach ($attributes as $key => $value) {
    if (empty($value)) {
        wc_add_notice(
            get_option('itycod_error_select_attributes', 'الرجاء اختيار جميع المواصفات.'), 
            'error'
        );
        return;
    }
}

        $address = array(
            'first_name' => $first_name,
			'email'      => $email,
            'phone'      => $phone,
            'address_1'  => $address_1,
            'state'      => $state,
            'city'       => $city,
            'country'    => WC()->countries->get_base_country(),
        );

// Validate the product selection before creating the order
$product   = wc_get_product($product_id);
$variation = $variation_id ? wc_get_product($variation_id) : null;

if (!$product || ($product->is_type('variable') && !$variation)) {
    wc_add_notice(
        get_option('itycod_error_invalid_product', 'الرجاء اختيار منتج صالح.'), 
        'error'
    );
    return;
}

// Create the order only if the product is valid
$order = wc_create_order();
$order->set_address($address, 'billing');

if ($product->is_type('variable') && $variation) {
    $order->add_product($variation, $quantity);
} else if ($product->is_type('simple')) {
    $order->add_product($product, $quantity);
}



        // Set billing details
        $order->set_address(array(
            'first_name' => $first_name,
            'last_name'  => '',
            'address_1'  => $address_1,
            'state'      => $state,
            'city'       => $city,
            'phone'      => $phone,
        ), 'billing');
		
		   // Handle Order Note if provided
        if ( isset($_POST['order_note']) && !empty($_POST['order_note']) ) {
            $order_note = sanitize_textarea_field( $_POST['order_note'] );
            $order->set_customer_note( $order_note );
        }

        // Set shipping address for calculation
        WC()->customer->set_shipping_state($state);
        WC()->customer->set_shipping_country(WC()->countries->get_base_country());
        WC()->customer->set_shipping_postcode('');
        WC()->customer->set_shipping_city($city);
        WC()->customer->set_shipping_address_1($address_1);

        // Calculate shipping cost
        WC()->shipping()->calculate_shipping(WC()->cart->get_shipping_packages());
        $shipping_packages = WC()->shipping->get_packages();
        $shipping_cost = 0;
        $shipping_method_title = '';

        if (!empty($shipping_packages) && isset($shipping_packages[0]['rates'])) {
            $chosen_method = WC()->session->get('chosen_shipping_methods')[0] ?? key($shipping_packages[0]['rates']);
            if (isset($shipping_packages[0]['rates'][$chosen_method])) {
                $shipping_rate = $shipping_packages[0]['rates'][$chosen_method];
                $shipping_cost = $shipping_rate->cost;
                $shipping_method_title = $shipping_rate->label;
            }
        }

        $product_price = $variation ? $variation->get_price() : $product->get_price();
        $total_cost = ($product_price * $quantity) + $shipping_cost;

        if (!empty($_POST['shipping_method'])) {
            $chosen_method_id = sanitize_text_field($_POST['shipping_method']);
            $shipping_rate = $shipping_packages[0]['rates'][$chosen_method_id];
            $shipping_cost = $shipping_rate->cost;
            $shipping_method_title = $shipping_rate->label;

            $shipping_item = new WC_Order_Item_Shipping();
            $shipping_item->set_method_title($shipping_method_title);
            $shipping_item->set_method_id($chosen_method_id);
            $shipping_item->set_total($shipping_cost);
            $order->add_item($shipping_item);
        }

        $order->set_total($total_cost);
        $order->calculate_totals();
        $order->save();

        foreach ($order->get_items() as $item_id => $item) {
            foreach ($attributes as $key => $value) {
                $item->update_meta_data($key, $value);
            }
            $item->save();
        }

        $order->update_status('processing', __('Order created dynamically', 'textdomain'));

        WC()->cart->empty_cart();
        log_order_for_ip($customer_ip);
        wp_redirect($order->get_checkout_order_received_url());
        exit;
    }
}
add_action('template_redirect', 'handle_custom_checkout_form_submission');
function itycod_check_domain() {
    if (get_option('itycod_domain_checked') === 'yes') {
        return; 
    }
    $stored_domain  = get_option('stored_domain', '');
    $current_domain = parse_url(home_url(), PHP_URL_HOST);
    if ($stored_domain !== $current_domain) {
        update_option('itycod_license_status', 'not_activated');
        update_option('stored_domain', $current_domain);
    }
    update_option('itycod_domain_checked', 'yes');
}
add_action('init', 'itycod_check_domain');
if (get_option('itycod_license_status', 'not_activated') !== 'activated') {
    add_action('wp_footer', 'itycod_lock_custom_checkout_form');
}
function itycod_lock_custom_checkout_form() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#custom-checkout-form').find('input, select, textarea, button').prop('disabled', true);
        $('#custom-checkout-form').css('opacity', 0.5);
    });
    </script>
    <?php
}
register_activation_hook(__FILE__, 'itycod_save_initial_domain');
function itycod_save_initial_domain() {
    $current_domain = parse_url(home_url(), PHP_URL_HOST);
    update_option('stored_domain', $current_domain);
}
add_filter('woocommerce_cart_needs_payment', function ($needs_payment, $cart) {
    $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    if (empty($available_gateways)) {
        return false;
    }
    return $needs_payment;
}, 10, 2);
function itycod_handle_license_activation_form() {
    if ( isset($_POST['itycod_activate_license']) &&
         check_admin_referer('itycod_license_activation', 'itycod_license_nonce') ) {
        $license_key = sanitize_text_field( $_POST['itycod_license_key'] );
        update_option('itycod_license_key', $license_key);
        $result = itycod_remote_license_activation($license_key);
        $current_domain = parse_url(home_url(), PHP_URL_HOST);
        if ( isset($result['success']) && $result['success'] === true ) {
            update_option('itycod_license_status', 'activated');
            update_option('itycod_license_domain', $current_domain);
            update_option('stored_domain', $current_domain);
            add_settings_error('itycod_license_messages', 'itycod_license_message', $result['message'], 'updated');
        } else {
            update_option('itycod_license_status', 'not_activated');
            $error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
            add_settings_error('itycod_license_messages', 'itycod_license_message', 'License activation failed: ' . esc_html($error_msg), 'error');
        }
    }
}
add_filter('woocommerce_available_payment_gateways', function ($available_gateways) {
    if (empty($available_gateways)) {
        if (isset(WC()->payment_gateways()->payment_gateways()['cod'])) {
            $available_gateways['cod'] = WC()->payment_gateways()->payment_gateways()['cod'];
        }
    }
    return $available_gateways;
});
?>