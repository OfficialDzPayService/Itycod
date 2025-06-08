<?php
function clear_cart_after_order($order_id) {
    WC()->cart->empty_cart();
}
add_action('woocommerce_thankyou', 'clear_cart_after_order');
function localize_checkout_script() {
    wp_localize_script('wc-add-to-cart', 'wc_add_to_cart_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'localize_checkout_script');
function save_selected_shipping_method_to_order($order, $data) {
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    if (!empty($chosen_shipping_methods)) {
        $order->update_meta_data('_chosen_shipping_method', $chosen_shipping_methods[0]);
    }
}
add_action('woocommerce_checkout_create_order', 'save_selected_shipping_method_to_order', 10, 2);
function itycod_check_license_status() {
    $license_key   = get_option('itycod_license_key', '');
    $stored_domain = get_option('itycod_license_domain', '');
    $current_domain = parse_url(home_url(), PHP_URL_HOST);
    if ($stored_domain !== $current_domain) {
        update_option('itycod_license_status', 'not_activated');
        return;
    }
    $remote_api_url = 'https://ityweb.com/?ityplug=register';
    $body = array(
        'site_url'       => home_url(),
        'plugin_version' => '3.9',
        'license_key'    => $license_key,
        'license_status' => 'check'
    );
    $args = array(
        'body'    => json_encode($body),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 60,
    );
    $response = wp_remote_post($remote_api_url, $args);
    if (!is_wp_error($response)) {
        $result = json_decode(wp_remote_retrieve_body($response), true);        
        if (isset($result['license_status'])) {
            update_option('itycod_license_status', $result['license_status']);
        }
    }
}
function prevent_auto_select_shipping_method($rates, $package) {
    if (count($rates) === 1) {
        WC()->session->set('chosen_shipping_methods', array(''));
    }
    return $rates;
}
add_filter('woocommerce_package_rates', 'prevent_auto_select_shipping_method', 10, 2);
function enforce_shipping_method_selection() {
    if (!is_checkout()) return;

    $chosen_methods = WC()->session->get('chosen_shipping_methods');
    
    if (empty($chosen_methods[0])) {
        wc_add_notice(__('Please select a shipping method.', 'woocommerce'), 'error');
    }
}
add_action('woocommerce_checkout_process', 'enforce_shipping_method_selection');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    itycod_check_domain();
    $license_status = get_option('itycod_license_status', 'not_activated');
    if ($license_status !== 'activated') {
        wp_send_json_error('');
        exit;
    }
}
function display_chosen_shipping_method_on_thankyou_page($order_id) {
    $order = wc_get_order($order_id);
    $shipping_method = $order->get_meta('_chosen_shipping_method');

    if ($shipping_method) {
        $available_methods = WC()->shipping->get_shipping_methods();
        if (isset($available_methods[$shipping_method])) {
            $method_title = $available_methods[$shipping_method]->get_title();
            echo '<p><strong>' . __('Selected Shipping Method:', 'woocommerce') . '</strong> ' . esc_html($method_title) . '</p>';
        }
    }
}
add_action('woocommerce_thankyou', 'display_chosen_shipping_method_on_thankyou_page', 20);
function itycod_remote_license_activation( $license_key ) {
    $remote_api_url = 'https://ityweb.com/?ityplug=register';
    $body = array(
        'site_url'       => home_url(),
        'plugin_version' => '3.9',
        'license_key'    => $license_key,
        'license_status' => 'pending'
    );
    $args = array(
        'body'    => json_encode( $body ),
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 60,
    );
    $response = wp_remote_post( $remote_api_url, $args );

    if ( is_wp_error( $response ) ) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }
    return json_decode( wp_remote_retrieve_body( $response ), true );
}
function itycod_ajax_process_license_activation() {
    $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
    if ( empty($license_key) ) {
        wp_send_json_error('No license key provided.');
    }
    $result = itycod_remote_license_activation($license_key);
    if ( isset($result['success']) && $result['success'] === true ) {
        update_option('itycod_license_status', 'activated');
        update_option('itycod_license_domain', home_url());
        update_option('stored_domain', $_SERVER['SERVER_NAME']);
        wp_send_json_success($result['message']);
    } else {
        update_option('itycod_license_status', 'not_activated');
        wp_send_json_error( isset($result['error']) ? $result['error'] : 'Unknown error' );
    }
}
add_action('wp_ajax_itycod_activate_license', 'itycod_ajax_process_license_activation');
function fetch_shipping_methods() {
    $state = sanitize_text_field($_POST['state']);
    $quantity = intval($_POST['quantity']);
    $product_id = intval($_POST['product_id']);

    WC()->customer->set_shipping_state($state);
    WC()->customer->set_shipping_country(WC()->countries->get_base_country());
    WC()->customer->set_shipping_postcode('');
    WC()->customer->set_shipping_city('');
    WC()->customer->set_shipping_address_1('');

    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($product_id, $quantity);
    WC()->shipping()->calculate_shipping(WC()->cart->get_shipping_packages());
    $packages = WC()->shipping()->get_packages();

    WC()->session->set('chosen_shipping_methods', array(''));

    $response = array(
        'methods' => array()
    );

    if (!empty($packages[0]['rates'])) {
        foreach ($packages[0]['rates'] as $method) {
            $response['methods'][] = array(
                'id' => $method->get_id(),
                'label' => $method->get_label(),
                'cost' => $method->get_cost(),
                'cost_html' => wc_price($method->get_cost())
            );
        }
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_fetch_shipping_methods', 'fetch_shipping_methods');
add_action('wp_ajax_nopriv_fetch_shipping_methods', 'fetch_shipping_methods');
?>