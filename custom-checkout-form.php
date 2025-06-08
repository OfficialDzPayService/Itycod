<?php
// Add Quantity Input Beside Product Name
add_filter( 'woocommerce_checkout_cart_item_quantity', 'bbloomer_checkout_item_quantity_input', 9999, 3 );
  
function bbloomer_checkout_item_quantity_input( $product_quantity, $cart_item, $cart_item_key ) {
   $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
   $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
   if ( ! $product->is_sold_individually() ) {
      $product_quantity = woocommerce_quantity_input( array(
         'input_name'  => 'shipping_method_qty_' . $product_id,
         'input_value' => $cart_item['quantity'],
         'max_value'   => $product->get_max_purchase_quantity(),
         'min_value'   => '0',
      ), $product, false );
      $product_quantity .= '<input type="hidden" name="product_key_' . $product_id . '" value="' . $cart_item_key . '">';
   }
   return $product_quantity;
}
 
// ----------------------------
// Detect Quantity Change and Recalculate Totals
 
add_action( 'woocommerce_checkout_update_order_review', 'bbloomer_update_item_quantity_checkout' );
 
function bbloomer_update_item_quantity_checkout( $post_data ) {
   parse_str( $post_data, $post_data_array );
   $updated_qty = false;
   foreach ( $post_data_array as $key => $value ) {   
      if ( substr( $key, 0, 20 ) === 'shipping_method_qty_' ) {         
         $id = substr( $key, 20 );   
         WC()->cart->set_quantity( $post_data_array['product_key_' . $id], $post_data_array[$key], false );
         $updated_qty = true;
      }     
   }  
   if ( $updated_qty ) WC()->cart->calculate_totals();
}

add_filter('woocommerce_checkout_fields', 'customize_woocommerce_checkout_fields');
function add_whatsapp_order_button( $product_param = null ) {
    if ( get_option('itycod_whatsapp_enabled', 'no') !== 'yes' ) {
        return;
    }

    // Use the passed product or fallback to the global product.
    if ( $product_param ) {
        $product = $product_param;
    } else {
        global $product;
    }

    // Check if $product is a valid WC_Product object.
    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return; // Exit if no valid product is found.
    }

    // Get product details
    $product_name    = $product->get_name();
    $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol() );

    // Start building the message in Arabic
    $message  = "مرحباً، أريد الطلب:\n\n";
    $message .= "📌 المنتج: $product_name\n";

    // Handle the selected variation for variable products.
    if ( $product->is_type('variable') && isset($_POST['variation_id']) ) {
        $variation_id = intval($_POST['variation_id']);
        $variation = wc_get_product($variation_id);

        if ( $variation ) {
            $variation_attributes = $variation->get_attributes();
            $selected_attributes  = [];

            foreach ( $variation_attributes as $key => $value ) {
                // Decode percent-encoded values.
                $decoded_value = urldecode($value);
                if ( ! empty($decoded_value) ) {
                    $selected_attributes[] = $decoded_value;
                }
            }

            $variation_price = wc_get_price_including_tax($variation);
            $message .= "🛒 المتغير: " . implode(", ", $selected_attributes) . "\n";
            $message .= "💰 السعر: $variation_price $currency_symbol\n";
        }
    } else {
        // For simple products, show the product price.
        $product_price = $product->get_price();
        $message .= "💰 السعر: $product_price $currency_symbol\n";
    }

    // Get WhatsApp number from settings.
    $whatsapp_number = get_option('itycod_whatsapp_number', '');
    if ( empty($whatsapp_number) ) {
        echo '<div style="color:red; font-weight:bold;">Error: WhatsApp number is not set in settings.</div>';
        return;
    }

    // Build WhatsApp URL.
    $whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($message);

    // Output the centered WhatsApp button with modern styling and Font Awesome icon.
    echo '<div style="text-align: center; margin-top: 10px;">';
    echo '<a href="' . esc_url($whatsapp_url) . '" target="_blank" class="whatsapp-order-button" 
            style="
                display: inline-block;
                padding: 10px 20px;
                background: #25D366;
                color: #fff;
                border-radius: 50px;
                font-size: 16px;
                font-weight: bold;
                text-decoration: none;
                transition: background 0.3s ease;
        " onmouseover="this.style.background=\'#1ebe5d\'" onmouseout="this.style.background=\'#25D366\'">
        <i class="fab fa-whatsapp" style="font-size: 20px; margin-right: 8px;"></i> اطلب عبر الواتساب
      </a>';
echo '</div>';
}



add_filter('woocommerce_checkout_fields', 'make_city_field_optional', 99);

function make_city_field_optional($fields) {
    // Set billing city field as optional
    if (isset($fields['billing']['billing_city'])) {
        $fields['billing']['billing_city']['required'] = false;
    }

    // Set shipping city field as optional
    if (isset($fields['shipping']['shipping_city'])) {
        $fields['shipping']['shipping_city']['required'] = false;
    }

    return $fields;
}


function customize_woocommerce_checkout_fields($fields) {
    // Enable only first name, last name, phone, city, and state
    // Billing fields
    $allowed_billing_fields = ['billing_first_name', 'billing_last_name', 'billing_phone', 'billing_city', 'billing_state'];
    
    foreach ($fields['billing'] as $key => $field) {
        if (!in_array($key, $allowed_billing_fields)) {
            unset($fields['billing'][$key]); // Remove all other billing fields
        }
    }

    // Remove all shipping fields
    unset($fields['shipping']);

    // Remove all account fields if guest checkout is allowed
    unset($fields['account']);


    return $fields;
}


function decode_attribute($encoded_string) {
    return urldecode($encoded_string);
}

if (!session_id()) {
    session_start();
}

function add_custom_checkout_form_to_product() {
    global $post;
    if (is_product()) {
        
    static $form_displayed = false;
    if ($form_displayed) {
        return;
    }
    $form_displayed = true;
        $product = wc_get_product($post->ID);
        $attributes = $product->get_attributes();
        $text_variation = get_option('itycod_text_variation', 'العروض:');
        $text_info = get_option('itycod_text_info', 'للطلب أضف معلوماتك في الأسفل 👇');
        $text_submit = get_option('itycod_text_submit', 'اطلب الآن');
        $enable_security_check = get_option('itycod_enable_security_check') === 'yes';
        $display_attributes = get_option('itycod_display_attributes') === 'yes';

$global_disable = get_option('itycod_variation_swatches_global_disable', 'enable');
$specific_ids   = get_option('itycod_variation_swatches_specific_ids', array());
if ( ! is_array( $specific_ids ) ) {
    $specific_ids = array();
}

// Determine if variation swatches should be enabled.
$enable_variation_swatches = true;
if ( $global_disable === 'disable' ) {
    // Globally disabled.
    $enable_variation_swatches = false;
} else {
    // If the current product is in the specific disable list, turn off swatches.
    if ( in_array( $product->get_id(), $specific_ids ) ) {
        $enable_variation_swatches = false;
    }
}

        // Generate random security code
        if ($enable_security_check) {
            $security_code = strtoupper(substr(md5(mt_rand()), 0, 6)); // Random 6-character code
            $_SESSION['security_code'] = $security_code; // Store in session
        }

if ($product->is_type('simple') || $product->is_type('variable')) {
    ?>
    <form id="custom-checkout-form" method="POST" action="" style="margin-bottom: 65px;">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <!-- Animated Text Info -->
        <div id="text-info-container">
            <span class="animated-text-info">
                <?php echo esc_html($text_info); ?>
            </span>
        </div>

        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="field-container">
                        <?php
                        woocommerce_form_field('billing_first_name', array(
                            'type'        => 'text',
                            'placeholder' => get_option('itycod_billing_first_name_placeholder', __('الاسم الكامل', 'textdomain')),
                            'required'    => true,
                            'class'       => array('form-row-wide'),
                        ));
                        ?>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="field-container">
                        <?php
                        woocommerce_form_field('billing_phone', array(
                            'type'             => 'tel',
                            'placeholder'      => get_option('itycod_billing_phone_placeholder', __('رقم الهاتف', 'textdomain')),
                            'required'         => true,
                            'class'            => array('form-row-wide'),
                            'custom_attributes'=> array('maxlength' => '10')
                        ));
                        ?>
                    </div>
                </td>
            </tr>
<tr>
    <td style="width: 50%;">
        <div class="field-container">
            <?php
            if (get_option('itycod_field_editor_state_type', 'select') === 'text') {
                // Render state field as text
                woocommerce_form_field('billing_state', array(
                    'type'        => 'text',
                    'placeholder' => get_option('itycod_billing_state_placeholder', __('الولاية / المنطقة', 'textdomain')),
                    'required'    => true,
                    'class'       => array('form-row-wide'),
                ));
            } else {
                // Render state field as select
                $country = WC()->countries->get_base_country();
                $states  = WC()->countries->get_states($country) ?: [];
                woocommerce_form_field('billing_state', array(
                    'type'        => 'select',
                    'placeholder' => get_option('itycod_billing_state_placeholder', __('الولاية / المنطقة', 'textdomain')),
                    'required'    => true,
                    'class'       => array('form-row-wide'),
                    'options' => array('' => get_option('itycod_billing_state_select_default', __('اختر الولاية...', 'textdomain'))) + $states,
                ));
            }
            ?>
        </div>
    </td>
    <td style="width: 50%;">
        <?php if (get_option('itycod_disable_city_field') !== 'yes') : ?>
        <div class="field-container">
            <?php
            if (get_option('itycod_field_editor_city_type', 'select') === 'text') {
                woocommerce_form_field('billing_city', array(
                    'type'        => 'text',
                    'placeholder' => get_option('itycod_billing_city_placeholder', __('البلدية', 'textdomain')),
                    'required'    => true,
                    'class'       => array('form-row-wide'),
                ));
            } else {
                woocommerce_form_field('billing_city', array(
                    'type'        => 'select',
                    'placeholder' => get_option('itycod_billing_city_placeholder', __('البلدية', 'textdomain')),
                    'required'    => true,
                    'class'       => array('form-row-wide'),
                    'options' => array('' => get_option('itycod_billing_city_select_default', __('اختر البلدية...', 'textdomain'))),
                ));
            }
            ?>
        </div>
        <?php endif; ?>
    </td>
</tr>

            <?php if (get_option('itycod_disable_address') !== 'yes') : ?>
            <tr>
                <td colspan="2">
                    <div class="field-container">
                        <?php
                        woocommerce_form_field('billing_address_1', array(
                            'type'        => 'text',
                            'placeholder' => get_option('itycod_billing_address_1_placeholder', __('العنوان الكامل', 'textdomain')),
                            'required'    => true,
                            'class'       => array('form-row-wide'),
                        ));
                        ?>
                    </div>
                </td>
            </tr>
           <?php endif; ?>
            <?php if (get_option('itycod_field_editor_email_enable', 'no') === 'yes') : ?>
            <tr>
                <td colspan="2">
                    <div class="field-container">
                        <?php
                        woocommerce_form_field('billing_email', array(
                            'type'        => 'email',
                            'placeholder' => get_option('itycod_billing_email_placeholder', __('Email', 'textdomain')),
                            'required'    => true,
                            'class'       => array('form-row-wide'),
                        ));
                        ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php
            if (get_option('itycod_field_editor_note_enable', 'no') === 'yes') : ?>
            <tr>
                <td colspan="2">
                    <div class="field-container">
                        <?php
                        woocommerce_form_field('order_note', array(
                            'type'        => 'textarea',
                            'placeholder' => get_option('itycod_order_note_placeholder', __('Order Note', 'textdomain')),
                            'required'    => false,
                            'class'       => array('form-row-wide'),
                        ));
                        ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($enable_security_check) { ?>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <div style="margin-top: 20px;"><strong>كود الأمان: <?php echo $security_code; ?></strong></div>
                    <div style="margin-top: 5px;">
                        <?php
                        woocommerce_form_field('security_code', array(
                            'type'        => 'text',
                            'placeholder' => get_option('itycod_security_code_placeholder', __('أدخل كود الأمان', 'textdomain')),
                            'required'    => true,
                            'class'       => array('form-row-wide'),
                            'input_class' => array('security-code-field')
                        ));
                        ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>

                    <?php
					
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    $product = wc_get_product($product_id);
    
    if ($product->is_type('simple')) {
        $attributes = $product->get_attributes();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute_name => $attribute) {
                if (isset($_POST['attribute_' . $attribute_name])) {
                    $cart_item_data['variation'][$attribute_name] = sanitize_text_field($_POST['attribute_' . $attribute_name]);
                }
            }
            $cart_item_data['variation_id'] = $product_id; // Treat it as a variation
        }
    }

    return $cart_item_data;
}, 10, 2);
?>
<?php
// Decide which UI to load based on your setting.
if ( $enable_variation_swatches && $product->is_type( 'variable' ) ) : 
    // ----------------------------------------------------------------
    // CUSTOM SWATCHES CODE
    // ----------------------------------------------------------------
    $available_variations = $product->get_available_variations();
    if ( $available_variations ) : 
        // Get the product’s default shipping cost (assumes it’s stored as a custom field).
        $default_shipping_cost = floatval( get_post_meta( $product->get_id(), '_shipping_cost', true ) );
        // Loop through each variation to add a shipping cost.
        foreach ( $available_variations as &$variation ) :
            $variation_id             = $variation['variation_id'];
            $variation_shipping_cost  = get_post_meta( $variation_id, '_shipping_cost', true );
            $variation['shipping_cost'] = $variation_shipping_cost ? floatval( $variation_shipping_cost ) : $default_shipping_cost;
        endforeach;
        unset( $variation );
    
        // Build an array of available option values per attribute.
        $attributes_keys   = array_keys( current( $available_variations )['attributes'] );
        $attribute_options = array();
        foreach ( $attributes_keys as $attr_key ) :
            $attribute_options[ $attr_key ] = array();
        endforeach;
        foreach ( $available_variations as $variation ) :
            foreach ( $variation['attributes'] as $attr_name => $attr_value ) :
                if ( ! empty( $attr_value ) && ! in_array( $attr_value, $attribute_options[ $attr_name ], true ) ) :
                    $attribute_options[ $attr_name ][] = $attr_value;
                endif;
            endforeach;
        endforeach;
        ?>
        <!-- Custom Swatches Markup & Styles -->
                <style>
 
/* Styling for disabled swatches with a visual indicator */
.variation-swatch.disabled {
    opacity: 0.4;
    position: relative;
    pointer-events: none;
    filter: grayscale(80%);
}
.variation-swatch.disabled::after {
    content: "\2716";
    position: absolute;
    top: 50%;
    right: 50%;
    transform: translate(50%, -50%);
    background-color: red;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
}

/* Swatch container for alignment */
.variation-swatch-container {
    margin: 15px 0;
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
}




/* Swatch button styling */
.variation-swatch {
    display: inline-flex;                /* Use flex to center content */
    align-items: center;                 /* Vertically center the text */
    justify-content: center;             /* Horizontally center the text */
    padding: 4px;
    border: 2px solid #ccc;              /* Light gray border */
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    font-size: 14px;                      /* Adjust font size as needed */
    text-align: center;
    text-transform: uppercase;
    font-weight: 700;
    font-family: 'Cairo', sans-serif;
    min-width: 40px;                     /* Minimum width */
    min-height: 20px;                    /* Minimum height */
    margin: 2px;
    background-color: #f8f8f8;
    color: #333;
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    
    /* Allow text to wrap if it overflows */
    white-space: normal;                
    overflow-wrap: break-word;          
    word-break: break-word;
}



  #variation-price-container {
                display: none;
            }

/* Active/Selected Swatch */
.variation-swatch.active {
                border-color: black !important;
                font-weight: bold;
}

/* Attribute label styling */
.attribute-label {
    margin-right: 10px;
    font-weight: bold;
    color: #444;
}

/* Hide pricing elements until a swatch is clicked */
#product-price,
#shipping-cost,
#total-cost {
    display: none;
}
        </style>



    
        <?php
        // Output a separate container for each attribute.
        foreach ( $attribute_options as $attr_key => $terms ) :
            // Remove the 'attribute_' prefix to get the taxonomy.
            $taxonomy = urldecode( str_replace( 'attribute_', '', $attr_key ) );
            ?>
            <div class="variation-swatch-container" data-attribute="<?php echo esc_attr( $attr_key ); ?>">
                <?php 
                // Display the attribute label.
                $attribute_label = wc_attribute_label( $taxonomy );
                ?>
                <span class="attribute-label">
                    <strong><?php echo esc_html( $attribute_label ); ?></strong>:&nbsp;
                </span>
                <?php
                foreach ( $terms as $term_slug ) :
                    $term  = get_term_by( 'slug', $term_slug, $taxonomy );
                    $style = '';
                    if ( $term ) :
                        $bg_color   = get_term_meta( $term->term_id, 'attribute_bg_color', true );
                        $text_color = get_term_meta( $term->term_id, 'attribute_text_color', true );
                        if ( $bg_color && $text_color ) :
                            $style = "background-color: {$bg_color}; color: {$text_color}; border-color: {$bg_color};";
                        endif;
                        $display_name = $term->name;
                    else :
                        $display_name = $term_slug;
                    endif;
                    ?>
                    <span class="variation-swatch" data-value="<?php echo esc_attr( $term_slug ); ?>" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo esc_html( urldecode( $display_name ) ); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    
        <!-- Hidden input to store the selected variation ID -->
        <input type="hidden" name="variation_id" id="selected-variation-id" value="">
    
        <!-- A container where the variation’s price will be updated -->
        <div id="variation-price-container"></div>
    
               <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pass PHP variations array to JavaScript.
            var availableVariations = <?php echo json_encode( $available_variations ); ?>;
            var selectedAttributes  = {};
    
            // Base product price variables.
            var basePrice     = <?php echo floatval( $product->get_price() ); ?>;
            var basePriceHTML = '<?php echo $product->get_price_html(); ?>';
    
            // Default shipping cost.
            var defaultShippingCost = <?php echo $default_shipping_cost; ?>;
    
            // Element references.
            var qtyInput       = document.getElementById('quantity');
            var productPriceEl = document.getElementById('product-price');
            var totalCostEl    = document.getElementById('total-cost');
            var shippingCostEl = document.getElementById('shipping-cost');
    
            // Helper: Format a number as a price string.
            function formatPrice( price ) {
                return '<?php echo get_woocommerce_currency_symbol(); ?>' + parseFloat( price ).toFixed(2);
            }
    
            // Returns the matching variation object based on current swatch selections.
            function getMatchingVariation() {
                var requiredCount = 0, selectedCount = 0;
                document.querySelectorAll('.variation-swatch-container').forEach(function(container) {
                    var attr = container.getAttribute('data-attribute');
                    if ( attr !== 'attribute_state' ) {
                        requiredCount++;
                        if ( selectedAttributes[attr] ) { 
                            selectedCount++; 
                        }
                    }
                });
                if ( selectedCount !== requiredCount ) {
                    return null;
                }
    
                for ( var i = 0; i < availableVariations.length; i++ ) {
                    var variation = availableVariations[i];
                    var isMatch = true;
                    for ( var attr in variation.attributes ) {
                        if ( attr === 'attribute_state' ) continue;
                        if ( variation.attributes[attr] !== selectedAttributes[attr] ) {
                            isMatch = false;
                            break;
                        }
                    }
                    if ( isMatch && variation.is_in_stock ) {
                        return variation;
                    }
                }
                return null;
            }
    
            function updateTotalPrice(quantity) {
                var qty = parseInt(quantity) || 1;
                var matchingVariation = getMatchingVariation();
                var price;
    
                if (matchingVariation) {
                    price = matchingVariation.display_price ? parseFloat(matchingVariation.display_price) : parseFloat(matchingVariation.regular_price);
                    document.getElementById('selected-variation-id').value = matchingVariation.variation_id;
                } else {
                    price = basePrice;
                    document.getElementById('selected-variation-id').value = '';
                }
    
                // Update the product price element with only the price.
                productPriceEl.innerHTML = formatPrice(price);
                // Update the quantity display separately.
                document.getElementById('quantity-display').innerHTML = 'x' + qty;
    
                var shippingMethod = document.querySelector('input[name="shipping_method"]:checked');
                var shippingCost = shippingMethod && shippingMethod.dataset.cost !== undefined ? parseFloat(shippingMethod.dataset.cost) : defaultShippingCost;
    
                var total = (price * qty) + shippingCost;
                totalCostEl.innerHTML = formatPrice(total);
    
                shippingCostEl.innerHTML = shippingCost === 0 ? '<span class="free-shipping">مجانا</span>' : formatPrice(shippingCost);
            }
    
            // Disables swatches that would not result in a valid variation.
            function updateSwatchAvailability() {
                document.querySelectorAll('.variation-swatch-container').forEach(function(container) {
                    var attributeName = container.getAttribute('data-attribute');
                    container.querySelectorAll('.variation-swatch').forEach(function(swatch) {
                        var value = swatch.getAttribute('data-value');
                        var tempSelection = Object.assign({}, selectedAttributes);
                        tempSelection[attributeName] = value;
    
                        var valid = availableVariations.some(function(variation) {
                            var isValid = true;
                            for (var attr in tempSelection) {
                                if (variation.attributes[attr] !== tempSelection[attr]) {
                                    isValid = false;
                                    break;
                                }
                            }
                            if (isValid && !variation.is_in_stock) {
                                isValid = false;
                            }
                            return isValid;
                        });
                        if (!valid) {
                            swatch.classList.add('disabled');
                            swatch.style.pointerEvents = 'none';
                        } else {
                            swatch.classList.remove('disabled');
                            swatch.style.pointerEvents = 'auto';
                        }
                    });
                });
            }
    
            updateSwatchAvailability();
    
            qtyInput.addEventListener('change', function() {
                document.getElementById('quantity-display').innerText = 'x' + this.value;
                updateTotalPrice(this.value);
            });
    
            window.changeQuantity = function(delta) {
                var currentQty = parseInt(qtyInput.value) || 1;
                var newQty = currentQty + delta;
                if (newQty < 1) { newQty = 1; }
                qtyInput.value = newQty;
                document.getElementById('quantity-display').innerText = 'x' + newQty;
                updateTotalPrice(newQty);
            };
    
            // Use event delegation for shipping method change.
            document.addEventListener('change', function(event) {
                if (event.target.name === 'shipping_method') {
                    updateTotalPrice(qtyInput.value);
                }
            });
    
            document.querySelectorAll('.variation-swatch').forEach(function(swatch) {
                swatch.addEventListener('click', function() {
                    if (this.classList.contains('disabled')) {
                        return;
                    }
    
                    var container = this.parentElement;
                    var attributeName = container.getAttribute('data-attribute');
    
                    if (this.classList.contains('active')) {
                        this.classList.remove('active');
                        delete selectedAttributes[attributeName];
                    } else {
                        container.querySelectorAll('.variation-swatch').forEach(function(s) {
                            s.classList.remove('active');
                        });
                        this.classList.add('active');
                        selectedAttributes[attributeName] = this.getAttribute('data-value');
                    }
    
                    updateSwatchAvailability();
                    updateTotalPrice(qtyInput.value);
    
                    productPriceEl.style.display = 'block';
                    shippingCostEl.style.display = 'block';
                    totalCostEl.style.display = 'block';
                });
            });
    
            updateTotalPrice(qtyInput.value);
        });
        </script>
    <?php
    endif;
else :
    // ----------------------------------------------------------------
    // FALLBACK: Custom Variation Radio Options
    // ----------------------------------------------------------------
if ( $product->is_type( 'variable' ) ) : 
        ?>
        <?php
        $available_variations = $product->get_available_variations();
        $variation_count      = count( $available_variations );
    
        if ( $available_variations ) : 
            ?>
        <style>
        /* Container for variation radio options */
        .variation-radio-container {
            margin: 15px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
        }
        .variation-radio-container.scroll-enabled {
            max-height: 200px;
    overflow-y: scroll; /* Always show the scrollbar */
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
        }
        
        /* Style each variation option */
        .variation-option {
            width: 100%;
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 12px;
            background: #fff;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            /* Force LTR ordering regardless of page direction */
            direction: ltr;
        }
        .variation-option:hover {
            background: #f8f8f8;
            border-color: #ccc;
        }
        
        /* Price element – always displayed on the left */
        .variation-price {
            order: 1; /* First in order */
            margin-right: 20px; /* Spacing on its right */
            white-space: nowrap;
            font-size: 15px;
                        margin-left: 20px; /* Spacing on its right */

        }
        
        /* Container for the swatch terms – displayed on the right */
        .variation-term-container {
            order: 2; /* Second in order */
            display: flex;
            gap: 5px;
            justify-content: flex-end;  /* Align terms to the right edge */
            text-align: right;
        }
        
        /* Swatch styling for each attribute term */
        .variation-term {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border: 2px solid #d0d0d0;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
            min-width: 40px;
            text-align: center;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.3px;
            font-family: 'Cairo', sans-serif;
            margin: 2px;
            /* Let the term’s content auto-detect its proper direction */
            direction: auto;
            unicode-bidi: isolate;
        }
        
        /* Active state for a variation term */
        .variation-option.active .variation-term {
            border-color: black !important;
            box-shadow: 0 1px 4px rgba(0, 123, 255, 0.2);
        }
        
        /* Hide the radio inputs */
        .variation-option input[type="radio"] {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
        }
        .variation-option input[type="radio"] + span:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border: 2px solid #ddd;
            border-radius: 50%;
            background: #fff;
            transition: all 0.3s ease;
        }
        .variation-option input[type="radio"]:checked + span:before {
            background: #000;
            border-color: #000;
            box-shadow: inset 0 0 0 3px #fff;
        }
        .variation-option ins {
            text-decoration: none;
            color: #77a464;
            font-weight: 700;
            margin-left: 5px;
        }
        .variation-option del {
            color: #999;
            margin-right: 5px;
        }
        /* Attribute label styling */
        .attribute-label {
            font-size: 12px;
            margin-right: 4px;
            text-transform: capitalize;
        }
        </style>   
            <div class="variation-radio-container" id="variation-container">
                <?php
                foreach ( $available_variations as $variation ) : 
                    $variation_id  = $variation['variation_id'];
                    $variation_obj = wc_get_product( $variation_id );
                    $regular_price = $variation_obj->get_regular_price();
                    $sale_price    = $variation_obj->get_sale_price();
                    $current_price = $variation_obj->get_price();
    
                    // Build a swatch for each attribute on this variation.
                    $swatch_html = array();
                    foreach ( $variation['attributes'] as $attribute_name => $attribute_value ) : 
                        if ( $attribute_value ) : 
                            $taxonomy        = urldecode( str_replace( 'attribute_', '', $attribute_name ) );
                            $attribute_label = wc_attribute_label( $taxonomy );
                            $swatch_html_part = '';
                            $term = get_term_by( 'slug', $attribute_value, $taxonomy );
if ( $term ) : 
    $bg_color   = get_term_meta( $term->term_id, 'attribute_bg_color', true );
    $text_color = get_term_meta( $term->term_id, 'attribute_text_color', true );
    $style = "background-color: {$bg_color}; color: {$text_color}; border-color: {$bg_color};";
    $swatch_html_part .= sprintf(
        '<span class="variation-term" style="%s">%s</span>',
        esc_attr( $style ),
        esc_html( rawurldecode( $term->name ) )  // Use rawurldecode() here
    );
else : 
    $swatch_html_part .= sprintf(
        '<span class="variation-term">%s</span>',
        esc_html( rawurldecode( $attribute_value ) )  // And here if needed
    );
endif;

                            $swatch_html[] = $swatch_html_part;
                        endif;
                    endforeach;
                    $attributes_html = implode( ' ', $swatch_html );
                    ?>
<label class="variation-option">
    <input type="radio" name="variation_id"
           value="<?php echo esc_attr( $variation_id ); ?>"
           data-price="<?php echo esc_attr( $current_price ); ?>"
           data-attributes="<?php echo esc_attr( strip_tags( $attributes_html ) ); ?>"
           required>
    <span class="variation-term-container">
        <?php echo wp_kses_post( $attributes_html ); ?>
    </span>
    <span class="variation-price">
        <?php if ( $sale_price ) : ?>
            <del><?php echo wc_price( $regular_price ); ?></del>
            <ins><?php echo wc_price( $sale_price ); ?></ins>
        <?php else : ?>
            <?php echo wc_price( $regular_price ); ?>
        <?php endif; ?>
    </span>
</label>


                <?php endforeach; ?>
            </div>
    
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const variationContainer = document.getElementById('variation-container');
                    const radioOptions = variationContainer.querySelectorAll('.variation-option input[type="radio"]');
                    const totalRows = document.querySelectorAll('.order-review .total-row');
    
                    // Enable scrolling if more than 3 variations
                    if ( <?php echo $variation_count; ?> > 3 ) {
                        variationContainer.classList.add('scroll-enabled');
                    }
    
                    // When a variation radio is selected, reveal the totals (if needed)
                    radioOptions.forEach(radio => {
                        radio.addEventListener('change', function() {
                            totalRows.forEach(row => {
                                row.style.visibility = 'visible';
                                row.style.opacity = '1';
                            });
                        });
                    });
    
                    // Toggle the active state on labels
                    const variationLabels = variationContainer.querySelectorAll('.variation-option');
                    variationLabels.forEach(label => {
                        label.addEventListener('click', function() {
                            variationLabels.forEach(lbl => lbl.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });
                });
            </script>
        <?php
        endif;
    else : 
        ?>
        <input type="hidden" name="variation_id" value="0">
        <?php 
    endif;
endif;
?>

<div id="shipping-methods-container" style="margin-bottom: 20px; margin-top: 07px;"></div> <!-- Container for shipping methods -->

<?php if (get_option('itycod_disable_form_add_to_cart', 'no') !== 'yes') : ?>
    <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="full-width-button custom-add-to-cart" id="add-to-cart-button">
        <?php echo esc_html(get_option('itycod_add_to_cart_text', 'أضف إلى السلة')); ?>
    </button>
<?php endif; ?>




<div class="quantity-and-submit">
    <div class="quantity-wrapper">
        <button type="button" onclick="changeQuantity(-1)">-</button>
        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
        <button type="button" onclick="changeQuantity(1)">+</button>
    </div>
    

  <button type="submit" class="full-width-button" id="submit-button">
    <?php echo esc_html($text_submit); ?>
</button>
</div>
 <!-- WhatsApp Order Button inserted below the submit button -->
        <?php add_whatsapp_order_button(); ?>

<div class="order-review" style="margin-top: 12px;">
  <h3>
    <?php echo esc_html(get_option('itycod_order_review_title', 'ملخص الطلب')); ?>
    <span class="order-summary-icon">
      <?php echo get_option('itycod_order_summary_icon', '&#x1F6D2;'); ?>
    </span>
  </h3>
  <div class="order-summary">
    <div class="order-item">
      <!-- Product name and quantity together -->
      <div class="product-info">
        <span class="item-label"><?php echo esc_html($post->post_title); ?></span>
        <span id="quantity-display" class="quantity-label">x1</span>
      </div>
      <!-- Price displayed separately -->
      <span class="item-value" id="product-price">
        <?php echo wc_price($product->get_price()); ?>
      </span>
    </div>
    <div class="order-item">
      <span class="item-label"><?php echo esc_html(get_option('itycod_shipping_cost_label', 'سعر الشحن')); ?></span>
      <span class="item-value" id="shipping-cost">-</span>
    </div>
    <div class="order-item total">
      <span class="item-label">
        <strong><?php echo esc_html(get_option('itycod_total_cost_label', 'السعر الإجمالي')); ?></strong>
      </span>
      <span class="item-value">
        <strong id="total-cost"><?php echo wc_price($product->get_price()); ?></strong>
      </span>
    </div>
  </div>
</div>




<input type="hidden" name="product_id" value="<?php echo esc_attr($post->ID); ?>">
</form>
<style>
#custom-checkout-form button.custom-add-to-cart {
    animation: none !important;
    transform: none !important;
    transition: none !important;
    box-shadow: none !important; 
     display: block !important;
  width: 100% !important;
  margin: 20px auto !important;  
}
</style>


<script>
    const qtyInput = document.getElementById('quantity');
    const qtyDisplay = document.getElementById('quantity-display');
    const totalCostEl = document.getElementById('total-cost');
    const shippingCostEl = document.getElementById('shipping-cost');
    const stateSelect = document.querySelector('select[name="billing_state"]');
    const citySelect = document.querySelector('select[name="billing_city"]');
    const radioButtons = document.querySelectorAll('input[name="variation_id"]');
    const productPriceEl = document.getElementById('product-price');
    const shippingMethodsContainer = document.getElementById('shipping-methods-container');
    
 const addToCartIndicator = '<?php echo esc_js(get_option('itycod_add_to_cart_indicator', 'جاري إضافة المنتج...')); ?>';
    const submitIndicator    = '<?php echo esc_js(get_option('itycod_submit_indicator', 'جاري التحميل...')); ?>';

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('custom-checkout-form');
        const addToCartButton = document.getElementById('add-to-cart-button');
        const submitButton = document.getElementById('submit-button');
        
        if (addToCartButton) {
            addToCartButton.addEventListener('click', function(e) {
                this.innerHTML = addToCartIndicator;
            });
        }
        if (form) {
            form.addEventListener('submit', function(e) {
                if (e.submitter && e.submitter.id === 'submit-button') {
                    submitButton.innerHTML = submitIndicator;
                }
            });
        }
        checkLicenseStatus();
    });
    function changeQuantity(change) {
        let currentQty = parseInt(qtyInput.value);
        currentQty = isNaN(currentQty) ? 0 : currentQty;
        currentQty += change;
        currentQty = currentQty < 1 ? 1 : currentQty;
        qtyInput.value = currentQty;
        qtyDisplay.textContent = 'x' + currentQty;
        updateTotalPrice(currentQty);
    }

    qtyInput.addEventListener('input', () => {
        let currentQty = parseInt(qtyInput.value);
        currentQty = isNaN(currentQty) ? 0 : currentQty;
        currentQty = currentQty < 1 ? 1 : currentQty;
        qtyDisplay.textContent = 'x' + currentQty;
        updateTotalPrice(currentQty);
    });

 stateSelect.addEventListener('change', () => {
    // Only fetch cities if the city field is present in the DOM.
    if (citySelect) {
        fetchCities();
    }
    qtyInput.value = 1;
    qtyDisplay.textContent = ' x1';
    updateTotalPrice(1);
    resetShippingCostAndMethod();
    fetchShippingMethods();
});


    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            fetchShippingMethods();  // Fetch shipping methods after selecting a variation
            updateTotalPrice(qtyInput.value);
        });
    });

function fetchCities() {
    const state = stateSelect.value;

    // Check if the city element exists. If not, exit the function.
    if (!citySelect) {
        return;
    }

        const communes = {
            'DZ-01': ['Adrar', 'Tamest', 'Charouine', 'Reggane', 'Inozghmir', 'Tit', 'Ksar Kaddour', 'Tsabit', 'Timimoun', 'Ouled Said', 'Zaouiet Kounta', 'Aoulef', 'Timokten', 'Tamentit', 'Fenoughil', 'Tinerkouk', 'Deldoul', 'Sali', 'Akabli', 'Metarfa', 'O Ahmed Timmi', 'Bouda', 'Aougrout', 'Talmine', 'B Badji Mokhtar', 'Sbaa', 'Ouled Aissa', 'Timiaouine'],
'DZ-02': [
    'Chlef', 'Tenes', 'Benairia', 'El Karimia', 'Tadjna', 'Taougrite', 
    'Beni Haoua', 'Sobha', 'Harchoun', 'Ouled Fares', 'Sidi Akacha', 
    'Boukadir', 'Beni Rached', 'Talassa', 'Herenfa', 'Oued Goussine', 
    'Dahra', 'Ouled Abbes', 'Sendjas', 'Zeboudja', 'Oued Sly', 
    'Abou El Hassen', 'El Marsa', 'Chettia', 'Sidi Abderrahmane', 
    'Moussadek', 'El Hadjadj', 'Labiod Medjadja', 'Oued Fodda', 
    'Ouled Ben Abdelkader', 'Bouzghaia', 'Ain Merane', 'Oum Drou', 
    'Breira', 'Ben Boutaleb'],

'DZ-03': [
    'Laghouat', 'Ksar El Hirane', 'Benacer Ben Chohra', 'Sidi Makhlouf', 
    'Hassi Delaa', 'Hassi R Mel', 'Ain Mahdi', 'Tadjmout', 
    'Kheneg', 'Gueltat Sidi Saad', 'Ain Sidi Ali', 'Beidha', 
    'Brida', 'El Ghicha', 'Hadj Mechri', 'Sebgag', 
    'Taouiala', 'Tadjrouna', 'Aflou', 'El Assafia', 
    'Oued Morra', 'Oued M Zi', 'El Haouaita', 'Sidi Bouzid'
],

 'DZ-04': [
    'Oum El Bouaghi', 'Ain Beida', 'Ainmlila', 'Behir Chergui', 
    'El Amiria', 'Sigus', 'El Belala', 'Ain Babouche', 
    'Berriche', 'Ouled Hamla', 'Dhala', 'Ain Kercha', 
    'Hanchir Toumghani', 'El Djazia', 'Ain Diss', 'Fkirina', 
    'Souk Naamane', 'Zorg', 'El Fedjoudj Boughrar', 'Ouled Zouai', 
    'Bir Chouhada', 'Ksar Sbahi', 'Oued Nini', 'Meskiana', 
    'Ain Fekroune', 'Rahia', 'Ain Zitoun', 'Ouled Gacem', 
    'El Harmilia'
],

        
'DZ-05': [
    'Batna', 'Ghassira', 'Maafa', 'Merouana', 
    'Seriana', 'Menaa', 'El Madher', 'Tazoult', 
    'Ngaous', 'Guigba', 'Inoughissen', 'Ouyoun El Assafir', 
    'Djerma', 'Bitam', 'Metkaouak', 'Arris', 
    'Kimmel', 'Tilatou', 'Ain Djasser', 'Ouled Selam', 
    'Tigherghar', 'Ain Yagout', 'Fesdis', 'Sefiane', 
    'Rahbat', 'Tighanimine', 'Lemsane', 'Ksar Belezma', 
    'Seggana', 'Ichmoul', 'Foum Toub', 'Beni Foudhala El Hakania', 
    'Oued El Ma', 'Talkhamt', 'Bouzina', 'Chemora', 
    'Oued Chaaba', 'Taxlent', 'Gosbat', 'Ouled Aouf', 
    'Boumagueur', 'Barika', 'Djezzar', 'Tkout', 
    'Ain Touta', 'Hidoussa', 'Teniet El Abed', 'Oued Taga', 
    'Ouled Fadel', 'Timgad', 'Ras El Aioun', 'Chir', 
    'Ouled Si Slimane', 'Zanat El Beida', 'Amdoukal', 'Ouled Ammar', 
    'El Hassi', 'Lazrou', 'Boumia', 'Boulhilat', 
    'Larbaa'
],

'DZ-06': [
    'Bejaia', 'Amizour', 'Ferraoun', 'Taourirt Ighil', 
    'Chelata', 'Tamokra', 'Timzrit', 'Souk El Thenine', 
    'Mcisna', 'Thinabdher', 'Tichi', 'Semaoun', 
    'Kendira', 'Tifra', 'Ighram', 'Amalou', 
    'Ighil Ali', 'Ifelain Ilmathen', 'Toudja', 'Darguina', 
    'Sidi Ayad', 'Aokas', 'Beni Djellil', 'Adekar', 
    'Akbou', 'Seddouk', 'Tazmalt', 'Ait Rizine', 
    'Chemini', 'Souk Oufella', 'Taskriout', 'Tibane', 
    'Tala Hamza', 'Barbacha', 'Beni Ksila', 'Ouzallaguen', 
    'Bouhamza', 'Beni Melikeche', 'Sidi Aich', 'El Kseur', 
    'Melbou', 'Akfadou', 'Leflaye', 'Kherrata', 
    'Draa Kaid', 'Tamridjet', 'Ait Smail', 'Boukhelifa', 
    'Tizi Nberber', 'Beni Maouch', 'Oued Ghir', 'Boudjellil'
],

'DZ-07': [
    'Biskra', 'Oumache', 'Branis', 'Chetma', 
    'Ouled Djellal', 'Ras El Miaad', 'Besbes', 'Sidi Khaled', 
    'Doucen', 'Ech Chaiba', 'Sidi Okba', 'Mchouneche', 
    'El Haouch', 'Ain Naga', 'Zeribet El Oued', 'El Feidh', 
    'El Kantara', 'Ain Zaatout', 'El Outaya', 'Djemorah', 
    'Tolga', 'Lioua', 'Lichana', 'Ourlal', 
    'Mlili', 'Foughala', 'Bordj Ben Azzouz', 'Meziraa', 
    'Bouchagroun', 'Mekhadma', 'El Ghrous', 'El Hadjab', 
    'Khanguet Sidinadji'
],

'DZ-08': [
    'Bechar', 'Erg Ferradj', 'Ouled Khoudir', 'Meridja', 
    'Timoudi', 'Lahmar', 'Beni Abbes', 'Beni Ikhlef', 
    'Mechraa Houari B', 'Kenedsa', 'Igli', 'Tabalbala', 
    'Taghit', 'El Ouata', 'Boukais', 'Mogheul', 
    'Abadla', 'Kerzaz', 'Ksabi', 'Tamtert', 
    'Beni Ounif'
],

'DZ-09': [
    'Blida', 'Chebli', 'Bouinan', 'Oued El Alleug', 
    'Ouled Yaich', 'Chrea', 'El Affroun', 'Chiffa', 
    'Hammam Melouane', 'Ben Khlil', 'Soumaa', 'Mouzaia', 
    'Souhane', 'Meftah', 'Ouled Selama', 'Boufarik', 
    'Larbaa', 'Oued Djer', 'Beni Tamou', 'Bouarfa', 
    'Beni Mered', 'Bougara', 'Guerrouaou', 'Ain Romana', 
    'Djebabra'
],

'DZ-10': [
    'Bouira', 'El Asnam', 'Guerrouma', 'Souk El Khemis', 
    'Kadiria', 'Hanif', 'Dirah', 'Ait Laaziz', 
    'Taghzout', 'Raouraoua', 'Mezdour', 'Haizer', 
    'Lakhdaria', 'Maala', 'El Hachimia', 'Aomar', 
    'Chorfa', 'Bordj Oukhriss', 'El Adjiba', 'El Hakimia', 
    'El Khebouzia', 'Ahl El Ksar', 'Bouderbala', 'Zbarbar', 
    'Ain El Hadjar', 'Djebahia', 'Aghbalou', 'Taguedit', 
    'Ain Turk', 'Saharidj', 'Dechmia', 'Ridane', 
    'Bechloul', 'Boukram', 'Ain Bessam', 'Bir Ghbalou', 
    'Mchedallah', 'Sour El Ghozlane', 'Maamora', 'Ouled Rached', 
    'Ain Laloui', 'Hadjera Zerga', 'Ath Mansour', 'El Mokrani', 
    'Oued El Berdi'
],

'DZ-11': [
    'Tamanghasset', 'Abalessa', 'In Ghar', 'In Guezzam', 
    'Idles', 'Tazouk', 'Tinzaouatine', 'In Salah', 
    'In Amguel', 'Foggaret Ezzaouia'
],

'DZ-12': [
    'Tebessa', 'Bir El Ater', 'Cheria', 'Stah Guentis', 
    'El Aouinet', 'Lahouidjbet', 'Safsaf El Ouesra', 
    'Hammamet', 'Negrine', 'Bir El Mokadem', 
    'El Kouif', 'Morsott', 'El Ogla', 
    'Bir Dheheb', 'El Ogla El Malha', 'Gorriguer', 
    'Bekkaria', 'Boukhadra', 'Ouenza', 
    'El Ma El Biodh', 'Oum Ali', 'Thlidjene', 
    'Ain Zerga', 'El Meridj', 'Boulhaf Dyr', 
    'Bedjene', 'El Mazeraa', 'Ferkane'
],

'DZ-13': [
    'Tlemcen', 'Beni Mester', 'Ain Tallout', 'Remchi', 
    'El Fehoul', 'Sabra', 'Ghazaouet', 'Souani', 
    'Djebala', 'El Gor', 'Oued Chouly', 'Ain Fezza', 
    'Ouled Mimoun', 'Amieur', 'Ain Youcef', 'Zenata', 
    'Beni Snous', 'Bab El Assa', 'Dar Yaghmouracene', 
    'Fellaoucene', 'Azails', 'Sebbaa Chioukh', 
    'Terni Beni Hediel', 'Bensekrane', 'Ain Nehala', 
    'Hennaya', 'Maghnia', 'Hammam Boughrara', 
    'Souahlia', 'Msirda Fouaga', 'Ain Fetah', 
    'El Aricha', 'Souk Thlata', 'Sidi Abdelli', 
    'Sebdou', 'Beni Ouarsous', 'Sidi Medjahed', 
    'Beni Boussaid', 'Marsa Ben Mhidi', 'Nedroma', 
    'Sidi Djillali', 'Beni Bahdel', 'El Bouihi', 
    'Honaine', 'Tianet', 'Ouled Riyah', 
    'Bouhlou', 'Souk El Khemis', 'Ain Ghoraba', 
    'Chetouane', 'Mansourah', 'Beni Semiel', 
    'Ain Kebira'
],

'DZ-14': [
    'Tiaret', 'Medroussa', 'Ain Bouchekif', 'Sidi Ali Mellal', 
    'Ain Zarit', 'Ain Deheb', 'Sidi Bakhti', 'Medrissa', 
    'Zmalet El Emir Aek', 'Madna', 'Sebt', 'Mellakou', 
    'Dahmouni', 'Rahouia', 'Mahdia', 'Sougueur', 
    'Sidi Abdelghani', 'Ain El Hadid', 'Ouled Djerad', 
    'Naima', 'Meghila', 'Guertoufa', 'Sidi Hosni', 
    'Djillali Ben Amar', 'Sebaine', 'Tousnina', 
    'Frenda', 'Ain Kermes', 'Ksar Chellala', 
    'Rechaiga', 'Nadorah', 'Tagdemt', 
    'Oued Lilli', 'Mechraa Safa', 'Hamadia', 
    'Chehaima', 'Takhemaret', 'Sidi Abderrahmane', 
    'Serghine', 'Bougara', 'Faidja', 'Tidda'
],

'DZ-15': [
    'Tizi Ouzou', 'Ain El Hammam', 'Akbil', 'Freha', 
    'Souamaa', 'Mechtrass', 'Irdjen', 'Timizart', 
    'Makouda', 'Draa El Mizan', 'Tizi Ghenif', 
    'Bounouh', 'Ait Chaffaa', 'Frikat', 'Beni Aissi', 
    'Beni Zmenzer', 'Iferhounene', 'Azazga', 
    'Iloula Oumalou', 'Yakouren', 'Larba Nait Irathen', 
    'Tizi Rached', 'Zekri', 'Ouaguenoun', 
    'Ain Zaouia', 'Mkira', 'Ait Yahia', 
    'Ait Mahmoud', 'Maatka', 'Ait Boumehdi', 
    'Abi Youcef', 'Beni Douala', 'Illilten', 
    'Bouzguen', 'Ait Aggouacha', 'Ouadhia', 
    'Azzefoun', 'Tigzirt', 'Ait Aissa Mimoun', 
    'Boghni', 'Ifigha', 'Ait Oumalou', 
    'Tirmitine', 'Akerrou', 'Yatafen', 
    'Beni Ziki', 'Draa Ben Khedda', 'Ouacif', 
    'Idjeur', 'Mekla', 'Tizi Nthlata', 
    'Beni Yenni', 'Aghrib', 'Iflissen', 
    'Boudjima', 'Ait Yahia Moussa', 'Souk El Thenine', 
    'Ait Khelil', 'Sidi Naamane', 'Iboudraren', 
    'Aghni Goughran', 'Mizrana', 'Imsouhal', 
    'Tadmait', 'Ait Bouadou', 'Assi Youcef', 
    'Ait Toudert'
],

'DZ-16': [
    'Alger Centre', 'Sidi Mhamed', 'El Madania', 'Hamma Anassers', 
    'Bab El Oued', 'Bologhine Ibn Ziri', 'Casbah', 'Oued Koriche', 
    'Bir Mourad Rais', 'El Biar', 'Bouzareah', 'Birkhadem', 
    'El Harrach', 'Baraki', 'Oued Smar', 'Bourouba', 
    'Hussein Dey', 'Kouba', 'Bachedjerah', 'Dar El Beida', 
    'Bab Azzouar', 'Ben Aknoun', 'Dely Ibrahim', 'Bains Romains', 
    'Rais Hamidou', 'Djasr Kasentina', 'El Mouradia', 'Hydra', 
    'Mohammadia', 'Bordj El Kiffan', 'El Magharia', 'Beni Messous', 
    'Les Eucalyptus', 'Birtouta', 'Tassala El Merdja', 
    'Ouled Chebel', 'Sidi Moussa', 'Ain Taya', 
    'Bordj El Bahri', 'Marsa', 'Haraoua', 
    'Rouiba', 'Reghaia', 'Ain Benian', 
    'Staoueli', 'Zeralda', 'Mahelma', 
    'Rahmania', 'Souidania', 'Cheraga', 
    'Ouled Fayet', 'El Achour', 'Draria', 
    'Douera', 'Baba Hassen', 'Khracia', 
    'Saoula'
],

'DZ-17': [
    'Djelfa', 'Moudjebara', 'El Guedid', 'Hassi Bahbah',
    'Ain Maabed', 'Sed Rahal', 'Feidh El Botma', 'Birine',
    'Bouira Lahdeb', 'Zaccar', 'El Khemis', 'Sidi Baizid',
    'Mliliha', 'El Idrissia', 'Douis', 'Hassi El Euch',
    'Messaad', 'Guettara', 'Sidi Ladjel', 'Had Sahary',
    'Guernini', 'Selmana', 'Ain Chouhada', 'Oum Laadham',
    'Dar Chouikh', 'Charef', 'Beni Yacoub', 'Zaafrane',
    'Deldoul', 'Ain El Ibel', 'Ain Oussera', 'Benhar',
    'Hassi Fedoul', 'Amourah', 'Ain Fekka', 'Tadmit'
],
'DZ-18': [
    'Jijel', 'Erraguene', 'El Aouana', 'Ziamma Mansouriah',
    'Taher', 'Emir Abdelkader', 'Chekfa', 'Chahna',
    'El Milia', 'Sidi Maarouf', 'Settara', 'El Ancer',
    'Sidi Abdelaziz', 'Kaous', 'Ghebala', 'Bouraoui Belhadef',
    'Djmila', 'Selma Benziada', 'Boussif Ouled Askeur', 
    'El Kennar Nouchfi', 'Ouled Yahia Khadrouch', 
    'Boudria Beni Yadjis', 'Kemir Oued Adjoul', 
    'Texena', 'Djemaa Beni Habibi', 'Bordj Taher', 
    'Ouled Rabah', 'Ouadjana'
],

'DZ-19': [
    'Setif', 'Ain El Kebira', 'Beni Aziz', 'Ouled Sidi Ahmed',
    'Boutaleb', 'Ain Roua', 'Draa Kebila', 'Bir El Arch',
    'Beni Chebana', 'Ouled Tebben', 'Hamma', 'Maaouia',
    'Ain Legraj', 'Ain Abessa', 'Dehamcha', 'Babor',
    'Guidjel', 'Ain Lahdjar', 'Bousselam', 'El Eulma',
    'Djemila', 'Beni Ouartilane', 'Rosfa', 'Ouled Addouane',
    'Belaa', 'Ain Arnat', 'Amoucha', 'Ain Oulmane',
    'Beidha Bordj', 'Bouandas', 'Bazer Sakhra', 
    'Hammam Essokhna', 'Mezloug', 'Bir Haddada', 
    'Serdj El Ghoul', 'Harbil', 'El Ouricia', 
    'Tizi Nbechar', 'Salah Bey', 'Ain Azal', 
    'Guenzet', 'Talaifacene', 'Bougaa', 
    'Beni Fouda', 'Tachouda', 'Beni Mouhli', 
    'Ouled Sabor', 'Guellal', 'Ain Sebt', 
    'Hammam Guergour', 'Ait Naoual Mezada', 
    'Ksar El Abtal', 'Beni Hocine', 'Ait Tizi', 
    'Maouklane', 'Guelta Zerka', 'Oued El Barad', 
    'Taya', 'El Ouldja', 'Tella'
],

'DZ-20': [
    'Saida', 'Doui Thabet', 'Ain El Hadjar', 'Ouled Khaled',
    'Moulay Larbi', 'Youb', 'Hounet', 'Sidi Amar',
    'Sidi Boubekeur', 'El Hassasna', 'Maamora', 'Sidi Ahmed',
    'Ain Sekhouna', 'Ouled Brahim', 'Tircine', 'Ain Soltane'
],

'DZ-21': [
    'Skikda', 'Ain Zouit', 'El Hadaik', 'Azzaba',
    'Djendel Saadi Mohamed', 'Ain Cherchar', 'Bekkouche Lakhdar', 
    'Benazouz', 'Es Sebt', 'Collo', 'Beni Zid', 
    'Kerkera', 'Ouled Attia', 'Oued Zehour', 'Zitouna', 
    'El Harrouch', 'Zerdazas', 'Ouled Hebaba', 
    'Sidi Mezghiche', 'Emdjez Edchich', 'Beni Oulbane', 
    'Ain Bouziane', 'Ramdane Djamel', 'Beni Bachir', 
    'Salah Bouchaour', 'Tamalous', 'Ain Kechra', 
    'Oum Toub', 'Bein El Ouiden', 'Fil Fila', 
    'Cheraia', 'Kanoua', 'El Ghedir', 
    'Bouchtata', 'Ouldja Boulbalout', 'Kheneg Mayoum', 
    'Hamadi Krouma', 'El Marsa'
],

'DZ-22': [
    'Sidi Bel Abbes', 'Tessala', 'Sidi Brahim', 'Mostefa Ben Brahim',
    'Telagh', 'Mezaourou', 'Boukhanafis', 'Sidi Ali Boussidi',
    'Badredine El Mokrani', 'Marhoum', 'Tafissour', 'Amarnas',
    'Tilmouni', 'Sidi Lahcene', 'Ain Thrid', 'Makedra',
    'Tenira', 'Moulay Slissen', 'El Hacaiba', 'Hassi Zehana',
    'Tabia', 'Merine', 'Ras El Ma', 'Ain Tindamine',
    'Ain Kada', 'Mcid', 'Sidi Khaled', 'Ain El Berd',
    'Sfissef', 'Ain Adden', 'Oued Taourira', 'Dhaya',
    'Zerouala', 'Lamtar', 'Sidi Chaib', 'Sidi Dahou Dezairs',
    'Oued Sbaa', 'Boudjebaa El Bordj', 'Sehala Thaoura', 
    'Sidi Yacoub', 'Sidi Hamadouche', 'Belarbi', 'Oued Sefioun',
    'Teghalimet', 'Ben Badis', 'Sidi Ali Benyoub', 
    'Chetouane Belaila', 'Bir El Hammam', 'Taoudmout', 
    'Redjem Demouche', 'Benachiba Chelia', 'Hassi Dahou'
],

'DZ-23': [
    'Annaba', 'Berrahel', 'El Hadjar', 'Eulma',
    'El Bouni', 'Oued El Aneb', 'Cheurfa', 'Seraidi',
    'Ain Berda', 'Chetaibi', 'Sidi Amer', 'Treat'
],

'DZ-24': [
    'Guelma', 'Nechmaya', 'Bouati Mahmoud', 'Oued Zenati',
    'Tamlouka', 'Oued Fragha', 'Ain Sandel', 'Ras El Agba',
    'Dahouara', 'Belkhir', 'Ben Djarah', 'Bou Hamdane',
    'Ain Makhlouf', 'Ain Ben Beida', 'Khezara', 'Beni Mezline',
    'Bou Hachana', 'Guelaat Bou Sbaa', 'Hammam Maskhoutine',
    'El Fedjoudj', 'Bordj Sabat', 'Hamman Nbail', 'Ain Larbi',
    'Medjez Amar', 'Bouchegouf', 'Heliopolis', 'Ain Hessania',
    'Roknia', 'Salaoua Announa', 'Medjez Sfa', 'Boumahra Ahmed',
    'Ain Reggada', 'Oued Cheham', 'Djeballah Khemissi'
],

'DZ-25': [
    'Constantine', 'Hamma Bouziane', 'El Haria', 
    'Zighoud Youcef', 'Didouche Mourad', 'El Khroub', 
    'Ain Abid', 'Beni Hamiden', 'Ouled Rahmoune', 
    'Ain Smara', 'Mesaoud Boudjeriou', 'Ibn Ziad'
],

'DZ-26': [
    'Medea', 'Ouzera', 'Ouled Maaref', 'Ain Boucif',
    'Aissaouia', 'Ouled Deide', 'El Omaria', 'Derrag',
    'El Guelbelkebir', 'Bouaiche', 'Mezerena', 'Ouled Brahim',
    'Damiat', 'Sidi Ziane', 'Tamesguida', 'El Hamdania',
    'Kef Lakhdar', 'Chelalet El Adhaoura', 'Bouskene',
    'Rebaia', 'Bouchrahil', 'Ouled Hellal', 'Tafraout',
    'Baata', 'Boghar', 'Sidi Naamane', 'Ouled Bouachra',
    'Sidi Zahar', 'Oued Harbil', 'Benchicao', 'Sidi Damed',
    'Aziz', 'Souagui', 'Zoubiria', 'Ksar El Boukhari',
    'El Azizia', 'Djouab', 'Chahbounia', 'Meghraoua',
    'Cheniguel', 'Ain Ouksir', 'Oum El Djalil', 'Ouamri',
    'Si Mahdjoub', 'Tlatet Eddoair', 'Beni Slimane', 
    'Berrouaghia', 'Seghouane', 'Meftaha', 'Mihoub', 
    'Boughezoul', 'Tablat', 'Deux Bassins', 'Draa Essamar',
    'Sidi Errabia', 'Bir Ben Laabed', 'El Ouinet', 
    'Ouled Antar', 'Bouaichoune', 'Hannacha', 'Sedraia', 
    'Medjebar', 'Khams Djouamaa', 'Saneg'
],

'DZ-27': [
    'Mostaganem', 'Sayada', 'Fornaka', 
    'Stidia', 'Ain Nouissy', 'Hassi Maameche', 
    'Ain Tadles', 'Sour', 'Oued El Kheir', 
    'Sidi Bellater', 'Kheiredine', 'Sidi Ali', 
    'Abdelmalek Ramdane', 'Hadjadj', 'Nekmaria', 
    'Sidi Lakhdar', 'Achaacha', 'Khadra', 
    'Bouguirat', 'Sirat', 'Ain Sidi Cherif', 
    'Mesra', 'Mansourah', 'Souaflia', 
    'Ouled Boughalem', 'Ouled Maallah', 'Mezghrane', 
    'Ain Boudinar', 'Tazgait', 'Safsaf', 
    'Touahria', 'El Hassiane'
],

'DZ-28': [
    'Msila', 'Maadid', 'Hammam Dhalaa', 
    'Ouled Derradj', 'Tarmount', 'Mtarfa', 
    'Khoubana', 'Mcif', 'Chellal', 
    'Ouled Madhi', 'Magra', 'Berhoum', 
    'Ain Khadra', 'Ouled Addi Guebala', 'Belaiba', 
    'Sidi Aissa', 'Ain El Hadjel', 'Sidi Hadjeres', 
    'Ouanougha', 'Bou Saada', 'Ouled Sidi Brahim', 
    'Sidi Ameur', 'Tamsa', 'Ben Srour', 
    'Ouled Slimane', 'El Houamed', 'El Hamel', 
    'Ouled Mansour', 'Maarif', 'Dehahna', 
    'Bouti Sayah', 'Khettouti Sed Djir', 'Zarzour', 
    'Oued Chair', 'Benzouh', 'Bir Foda', 
    'Ain Fares', 'Sidi Mhamed', 'Ouled Atia', 
    'Souamaa', 'Ain El Melh', 'Medjedel', 
    'Slim', 'Ain Errich', 'Beni Ilmane', 
    'Oultene', 'Djebel Messaad'
],

'DZ-29': [
    'Mascara', 'Bou Hanifia', 'Tizi', 'Hacine', 
    'Maoussa', 'Teghennif', 'El Hachem', 'Sidi Kada', 
    'Zelmata', 'Oued El Abtal', 'Ain Ferah', 'Ghriss', 
    'Froha', 'Matemore', 'Makdha', 'Sidi Boussaid', 
    'El Bordj', 'Ain Fekan', 'Benian', 'Khalouia', 
    'El Menaouer', 'Oued Taria', 'Aouf', 'Ain Fares', 
    'Ain Frass', 'Sig', 'Oggaz', 'Alaimia', 
    'El Gaada', 'Zahana', 'Mohammadia', 'Sidi Abdelmoumene', 
    'Ferraguig', 'El Ghomri', 'Sedjerara', 'Moctadouz', 
    'Bou Henni', 'Guettena', 'El Mamounia', 'El Keurt', 
    'Gharrous', 'Gherdjoum', 'Chorfa', 'Ras Ain Amirouche', 
    'Nesmot', 'Sidi Abdeldjebar', 'Sehailia'
],

'DZ-30': [
    'Ouargla', 'Ain Beida', 'Ngoussa', 'Hassi Messaoud', 
    'Rouissat', 'Balidat Ameur', 'Tebesbest', 'Nezla', 
    'Zaouia El Abidia', 'Sidi Slimane', 'Sidi Khouiled', 'Hassi Ben Abdellah', 
    'Touggourt', 'El Hadjira', 'Taibet', 'Tamacine', 
    'Benaceur', 'Mnaguer', 'Megarine', 'El Allia', 
    'El Borma'
],

'DZ-31': [
    'Oran', 'Gdyel', 'Bir El Djir', 'Hassi Bounif', 
    'Es Senia', 'Arzew', 'Bethioua', 'Marsat El Hadjadj', 
    'Ain Turk', 'El Ancar', 'Oued Tlelat', 'Tafraoui', 
    'Sidi Chami', 'Boufatis', 'Mers El Kebir', 'Bousfer', 
    'El Karma', 'El Braya', 'Hassi Ben Okba', 'Ben Freha', 
    'Hassi Mefsoukh', 'Sidi Ben Yabka', 'Messerghin', 'Boutlelis', 
    'Ain Kerma', 'Ain Biya'
],

'DZ-32': [
    'El Bayadh', 'Rogassa', 'Stitten', 'Brezina', 
    'Ghassoul', 'Boualem', 'El Abiodh Sidi Cheikh', 'Ain El Orak', 
    'Arbaouat', 'Bougtoub', 'El Kheither', 'Kef El Ahmar', 
    'Boussemghoun', 'Chellala', 'Krakda', 'El Bnoud', 
    'Cheguig', 'Sidi Ameur', 'El Mehara', 'Tousmouline', 
    'Sidi Slimane', 'Sidi Tifour'
],

'DZ-33': [
    'Illizi', 'Djanet', 'Debdeb', 'Bordj Omar Driss', 
    'Bordj El Haouasse', 'In Amenas'
],

'DZ-34': [
    'Bordj Bou Arreridj', 'Ras El Oued', 'Bordj Zemoura', 'Mansoura', 
    'El Mhir', 'Ben Daoud', 'El Achir', 'Ain Taghrout', 
    'Bordj Ghdir', 'Sidi Embarek', 'El Hamadia', 'Belimour', 
    'Medjana', 'Teniet En Nasr', 'Djaafra', 'El Main', 
    'Ouled Brahem', 'Ouled Dahmane', 'Hasnaoua', 'Khelil', 
    'Taglait', 'Ksour', 'Ouled Sidi Brahim', 'Tafreg', 
    'Colla', 'Tixter', 'El Ach', 'El Anseur', 
    'Tesmart', 'Ain Tesra', 'Bir Kasdali', 'Ghilassa', 
    'Rabta', 'Haraza'
],

'DZ-35': [
    'Boumerdes', 'Boudouaou', 'Afir', 'Bordj Menaiel', 
    'Baghlia', 'Sidi Daoud', 'Naciria', 'Djinet', 
    'Isser', 'Zemmouri', 'Si Mustapha', 'Tidjelabine', 
    'Chabet El Ameur', 'Thenia', 'Timezrit', 'Corso', 
    'Ouled Moussa', 'Larbatache', 'Bouzegza Keddara', 'Taourga', 
    'Ouled Aissa', 'Ben Choud', 'Dellys', 'Ammal', 
    'Beni Amrane', 'Souk El Had', 'Boudouaou El Bahri', 
    'Ouled Hedadj', 'Laghata', 'Hammedi', 
    'Khemis El Khechna', 'El Kharrouba'
],

'DZ-36': [
    'El Tarf', 'Bouhadjar', 'Ben Mhidi', 'Bougous', 
    'El Kala', 'Ain El Assel', 'El Aioun', 'Bouteldja', 
    'Souarekh', 'Berrihane', 'Lac Des Oiseaux', 'Chefia', 
    'Drean', 'Chihani', 'Chebaita Mokhtar', 'Besbes', 
    'Asfour', 'Echatt', 'Zerizer', 'Zitouna', 
    'Ain Kerma', 'Oued Zitoun', 'Hammam Beni Salah', 
    'Raml Souk'
],

'DZ-37': [
    'Tindouf', 'Oum El Assel'
],

'DZ-38': [
    'Tissemsilt', 'Bordj Bou Naama', 'Theniet El Had', 'Lazharia', 
    'Beni Chaib', 'Lardjem', 'Melaab', 'Sidi Lantri', 
    'Bordj El Emir Abdelkader', 'Layoune', 'Khemisti', 
    'Ouled Bessem', 'Ammari', 'Youssoufia', 'Sidi Boutouchent', 
    'Larbaa', 'Maasem', 'Sidi Abed', 'Tamalaht', 
    'Sidi Slimane', 'Boucaid', 'Beni Lahcene'
],

'DZ-39': [
    'El Oued', 'Robbah', 'Oued El Alenda', 'Bayadha', 
    'Nakhla', 'Guemar', 'Kouinine', 'Reguiba', 
    'Hamraia', 'Taghzout', 'Debila', 'Hassani Abdelkrim', 
    'Hassi Khelifa', 'Taleb Larbi', 'Douar El Ma', 'Sidi Aoun', 
    'Trifaoui', 'Magrane', 'Beni Guecha', 'Ourmas', 
    'Still', 'Mrara', 'Sidi Khellil', 'Tendla', 
    'El Ogla', 'Mih Ouansa', 'El Mghair', 'Djamaa', 
    'Oum Touyour', 'Sidi Amrane'
],

'DZ-40': [
    'Khenchela', 'Mtoussa', 'Kais', 'Baghai', 
    'El Hamma', 'Ain Touila', 'Taouzianat', 'Bouhmama', 
    'El Oueldja', 'Remila', 'Cherchar', 'Djellal', 
    'Babar', 'Tamza', 'Ensigha', 'Ouled Rechache', 
    'El Mahmal', 'Msara', 'Yabous', 'Khirane', 
    'Chelia'
],

'DZ-41': [
    'Souk Ahras', 'Sedrata', 'Hanancha', 'Mechroha',
    'Ouled Driss', 'Tiffech', 'Zaarouria', 'Taoura',
    'Drea', 'Haddada', 'Khedara', 'Merahna',
    'Ouled Moumen', 'Bir Bouhouche', 'Mdaourouche', 'Oum El Adhaim',
    'Ain Zana', 'Ain Soltane', 'Quillen', 'Sidi Fredj',
    'Safel El Ouiden', 'Ragouba', 'Khemissa', 'Oued Keberit',
    'Terraguelt', 'Zouabi'
],

'DZ-42': [
    'Tipaza', 'Menaceur', 'Larhat', 'Douaouda',
    'Bourkika', 'Khemisti', 'Aghabal', 'Hadjout',
    'Sidi Amar', 'Gouraya', 'Nodor', 'Chaiba',
    'Ain Tagourait', 'Cherchel', 'Damous', 'Meurad',
    'Fouka', 'Bou Ismail', 'Ahmer El Ain', 'Bou Haroun',
    'Sidi Ghiles', 'Messelmoun', 'Sidi Rached', 'Kolea',
    'Attatba', 'Sidi Semiane', 'Beni Milleuk', 'Hadjerat Ennous'
],

'DZ-43': [
    'Mila', 'Ferdjioua', 'Chelghoum Laid', 'Oued Athmenia',
    'Ain Mellouk', 'Telerghma', 'Oued Seguen', 'Tadjenanet',
    'Benyahia Abderrahmane', 'Oued Endja', 'Ahmed Rachedi', 'Ouled Khalouf',
    'Tiberguent', 'Bouhatem', 'Rouached', 'Tessala Lamatai',
    'Grarem Gouga', 'Sidi Merouane', 'Tassadane Haddada', 'Derradji Bousselah',
    'Minar Zarza', 'Amira Arras', 'Terrai Bainen', 'Hamala',
    'Ain Tine', 'El Mechira', 'Sidi Khelifa', 'Zeghaia',
    'Elayadi Barbes', 'Ain Beida Harriche', 'Yahia Beniguecha', 'Chigara'
],

'DZ-44': [
    'Ain Defla', 'Miliana', 'Boumedfaa', 'Khemis Miliana',
    'Hammam Righa', 'Arib', 'Djelida', 'El Amra',
    'Bourached', 'El Attaf', 'El Abadia', 'Djendel',
    'Oued Chorfa', 'Ain Lechiakh', 'Oued Djemaa', 'Rouina',
    'Zeddine', 'El Hassania', 'Bir Ouled Khelifa', 'Ain Soltane',
    'Tarik Ibn Ziad', 'Bordj Emir Khaled', 'Ain Torki', 'Sidi Lakhdar',
    'Ben Allal', 'Ain Benian', 'Hoceinia', 'Barbouche',
    'Djemaa Ouled Chikh', 'Mekhatria', 'Bathia', 'Tachta Zegagha',
    'Ain Bouyahia', 'El Maine', 'Tiberkanine', 'Belaas'
],

'DZ-45': [
    'Naama', 'Mechria', 'Ain Sefra', 'Tiout',
    'Sfissifa', 'Moghrar', 'Assela', 'Djeniane Bourzeg',
    'Ain Ben Khelil', 'Makman Ben Amer', 'Kasdir', 'El Biod'
],

'DZ-46': [
    'Ain Temouchent', 'Chaabet El Ham', 'Ain Kihal', 'Hammam Bouhadjar',
    'Bou Zedjar', 'Oued Berkeche', 'Aghlal', 'Terga',
    'Ain El Arbaa', 'Tamzoura', 'Chentouf', 'Sidi Ben Adda',
    'Aoubellil', 'El Malah', 'Sidi Boumediene', 'Oued Sabah',
    'Ouled Boudjemaa', 'Ain Tolba', 'El Amria', 'Hassi El Ghella',
    'Hassasna', 'Ouled Kihal', 'Beni Saf', 'Sidi Safi',
    'Oulhaca El Gheraba', 'Tadmaya', 'El Emir Abdelkader', 'El Messaid'
],

'DZ-47': [
    'Ghardaia', 'El Meniaa', 'Dhayet Bendhahoua', 'Berriane',
    'Metlili', 'El Guerrara', 'El Atteuf', 'Zelfana',
    'Sebseb', 'Bounoura', 'Hassi Fehal', 'Hassi Gara',
    'Mansoura'
],

'DZ-48': [
    'Relizane', 'Oued Rhiou', 'Belaassel Bouzegza', 'Sidi Saada',
    'Ouled Aiche', 'Sidi Lazreg', 'El Hamadna', 'Sidi Mhamed Ben Ali',
    'Mediouna', 'Sidi Khettab', 'Ammi Moussa', 'Zemmoura',
    'Beni Dergoun', 'Djidiouia', 'El Guettar', 'Hamri',
    'El Matmar', 'Sidi Mhamed Ben Aouda', 'Ain Tarek', 'Oued Essalem',
    'Ouarizane', 'Mazouna', 'Kalaa', 'Ain Rahma',
    'Yellel', 'Oued El Djemaa', 'Ramka', 'Mendes',
    'Lahlef', 'Beni Zentis', 'Souk El Haad', 'Dar Ben Abdellah',
    'El Hassi', 'Had Echkalla', 'Bendaoud', 'El Ouldja',
    'Merdja Sidi Abed', 'Ouled Sidi Mihoub'
],

'DZ-49': [
    'Timimoun', 'Charouine', 'Ksar Kaddour', 'Ouled Said',
    'Tinerkouk', 'Deldoul', 'Metarfa', 'Aougrout',
    'Talmine', 'Ouled Aissa'
],

'DZ-50': [
    'B Badji Mokhtar', 'Timiaouine'
],

'DZ-51': [
    'Ouled Djellal', 'Sidi Khaled', 'Ras El Miad', 'Besbes',
    'Chaiba', 'Doucen'
],

'DZ-52': [
    'Beni Abbes', 'Tamtert', 'Kerzaz', 'Timoudi',
    'Beni Ikhlef', 'El Ouata', 'Tabelbala', 'Ouled Khoudir',
    'Ksabi', 'Igli'
],

'DZ-53': [
    'In Salah', 'In Ghar', 'Foggaret Azzaouia'
],

'DZ-54': [
    'In Guezzam', 'Tinzaouatine'
],

'DZ-55': [
    'Touggourt', 'Nezla', 'Tebesbest', 'Zaouia El Abidia',
    'Temacine', 'Blidet Amor', 'Megarine', 'Mnaguer',
    'Taibet', 'Benaceur', 'Sidi Slimane', 'El-hadjira',
    'El Alia'
],

'DZ-56': [
    'Djanet', 'Bordj El Haouasse'
],

'DZ-57': [
    'El-mghair', 'Oum Touyour', 'Still', 'Sidi Khelil',
    'Djamaa', 'Sidi Amrane', 'Tenedla', 'Mrara'
],

'DZ-58': [
    'El Meniaa', 'Hassi Gara', 'Hassi Fehal'
],
            
        };

        if (state && communes[state]) {
citySelect.innerHTML = '<option value=""><?php echo esc_js(get_option('itycod_city_select_default', __('اختر البلدية...', 'textdomain'))); ?></option>';
            communes[state].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        } else {
citySelect.innerHTML = '<option value=""><?php echo esc_js(get_option('itycod_city_select_default', __('اختر البلدية...', 'textdomain'))); ?></option>';
        }
    }

const $shippingMethodsContainer = jQuery('#shipping-methods-container');
const $shippingCostEl = jQuery('#shipping-cost');
const $qtyInput = jQuery('#quantity');
function fetchShippingMethods() {
    const state = stateSelect.value;
    const quantity = $qtyInput.val();
    const product_id = <?php echo $product->get_id(); ?>;
    const isVariableProduct = <?php echo $product->is_type('variable') ? 'true' : 'false'; ?>;

    if (!state) {
        $shippingMethodsContainer.html('');
        resetShippingCostAndMethod();
        updateTotalPrice(quantity);
        return;
    }

    jQuery.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        data: {
            action: 'fetch_shipping_methods',
            state: state,
            quantity: quantity,
            product_id: product_id
        },
        success: function(response) {
            const data = JSON.parse(response);
            const methods = data.methods;

            if (methods.length > 0) {
let html = '<h5 style="margin-top: 20px; margin-bottom: 10px; font-family: \'Cairo\', sans-serif;"><?php echo esc_js(get_option('itycod_shipping_method_heading', 'اختر طريقة الشحن:')); ?></h5>';
                methods.forEach((method, index) => {
                    html += `<label>
                        <input type="radio" name="shipping_method" value="${method.id}" data-cost="${method.cost}" ${index === 0 ? 'checked' : ''} required> 
                        ${method.label} - ${method.cost_html}
                    </label><br>`;
                });
                $shippingMethodsContainer.html(html);

                // Trigger change event for the first (preselected) shipping method
                const firstMethod = jQuery('input[name="shipping_method"]:checked');
                if (firstMethod.length > 0) {
                    const shippingCost = parseFloat(firstMethod.data('cost'));
                     $shippingCostEl.html(shippingCost === 0 
                       ? '<span class="free-shipping"><?php echo esc_js(get_option('itycod_free_shipping_text', 'مجانا')); ?></span>' 
                       : `<?php echo get_woocommerce_currency_symbol(); ?>${shippingCost.toFixed(2)}`);
                    updateTotalPrice($qtyInput.val());
                }

                // Update shipping cost on method change
                jQuery('input[name="shipping_method"]').on('change', function() {
                    const selectedMethod = jQuery('input[name="shipping_method"]:checked');
                    if (selectedMethod.length > 0) {
                        const shippingCost = parseFloat(selectedMethod.data('cost'));
                       $shippingCostEl.html(shippingCost === 0 
                       ? '<span class="free-shipping"><?php echo esc_js(get_option('itycod_free_shipping_text', 'مجانا')); ?></span>' 
                       : `<?php echo get_woocommerce_currency_symbol(); ?>${shippingCost.toFixed(2)}`);

                        updateTotalPrice($qtyInput.val());
                    }
                });
            } else {
                // Display free shipping when no methods are available
                $shippingMethodsContainer.html('');
                $shippingCostEl.html('<span class="free-shipping"><?php echo esc_js(get_option('itycod_free_shipping_text', 'مجانا')); ?></span>');
                updateTotalPrice(quantity);
            }
        }
    });
}
    function resetShippingCostAndMethod() {
        $shippingCostEl.html('-');
    }

function updateTotalPrice(quantity) {
    let price = <?php echo $product->get_price(); ?>;
    let salePrice = null;
    let regularPrice = null;

    const selectedVariation = document.querySelector('input[name="variation_id"]:checked');
    if (selectedVariation) {
        price = parseFloat(selectedVariation.dataset.price);
        salePrice = parseFloat(selectedVariation.dataset.salePrice);
        regularPrice = parseFloat(selectedVariation.dataset.regularPrice);
    }

    let priceHtml;
    if (salePrice !== null && !isNaN(salePrice)) {
        priceHtml = `<span class="sale-price"><?php echo get_woocommerce_currency_symbol(); ?>${salePrice.toFixed(2)}</span>
                     <span class="regular-price"><s><?php echo get_woocommerce_currency_symbol(); ?>${regularPrice.toFixed(2)}</s></span>`;
        price = salePrice;
    } else {
        priceHtml = `<?php echo get_woocommerce_currency_symbol(); ?>${price.toFixed(2)}`;
    }

    // Update the product price element with price only
    document.getElementById('product-price').innerHTML = priceHtml;
    
    // Update the separate quantity display element
    const qtyDisplayEl = document.getElementById('quantity-display');
    if (qtyDisplayEl) {
        qtyDisplayEl.innerHTML = `x${quantity}`;
    }

    let shippingCost = 0;
    const selectedMethod = document.querySelector('input[name="shipping_method"]:checked');
    if (selectedMethod) {
        shippingCost = parseFloat(selectedMethod.dataset.cost);
    }

    const total = (price * quantity) + shippingCost;
    document.getElementById('total-cost').innerHTML = `<?php echo get_woocommerce_currency_symbol(); ?>${total.toFixed(2)}`;
}
</script>

            <?php
        }
    }
}
add_action('woocommerce_short_description', 'add_custom_checkout_form_to_product', 20);
add_action('woocommerce_single_product_summary', 'add_custom_checkout_form_to_product', 25);
	function save_order_ip($order_id) {
    if (!empty($order_id)) {
        $customer_ip = $_SERVER['REMOTE_ADDR'];
        update_post_meta($order_id, '_customer_ip_address', $customer_ip);
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_order_ip');


function add_attribute_color_fields($taxonomy) {
    ?>
    <div class="form-field">
        <label for="attribute_bg_color"><?php _e('Background Color', 'textdomain'); ?></label>
        <input type="text" name="attribute_bg_color" id="attribute_bg_color" class="color-picker" value="#ffffff">
    </div>
    <div class="form-field">
        <label for="attribute_text_color"><?php _e('Text Color', 'textdomain'); ?></label>
        <input type="text" name="attribute_text_color" id="attribute_text_color" class="color-picker" value="#000000">
    </div>
    <?php
}

// Add color fields to attribute term edit form
function edit_attribute_color_fields($term, $taxonomy) {
    $bg_color = get_term_meta($term->term_id, 'attribute_bg_color', true);
    $text_color = get_term_meta($term->term_id, 'attribute_text_color', true);
    ?>
    <tr class="form-field">
        <th>
            <label for="attribute_bg_color"><?php _e('Background Color', 'textdomain'); ?></label>
        </th>
        <td>
            <input type="text" name="attribute_bg_color" id="attribute_bg_color" class="color-picker" value="<?php echo esc_attr($bg_color); ?>">
        </td>
    </tr>
    <tr class="form-field">
        <th>
            <label for="attribute_text_color"><?php _e('Text Color', 'textdomain'); ?></label>
        </th>
        <td>
            <input type="text" name="attribute_text_color" id="attribute_text_color" class="color-picker" value="<?php echo esc_attr($text_color); ?>">
        </td>
    </tr>
    <?php
}

function save_attribute_color_fields($term_id, $tt_id) {
    if (isset($_POST['attribute_bg_color'])) {
        update_term_meta($term_id, 'attribute_bg_color', sanitize_hex_color($_POST['attribute_bg_color']));
    }
    if (isset($_POST['attribute_text_color'])) {
        update_term_meta($term_id, 'attribute_text_color', sanitize_hex_color($_POST['attribute_text_color']));
    }
}

function register_attribute_color_fields() {
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    
    if ($attribute_taxonomies) {
        foreach ($attribute_taxonomies as $taxonomy) {
            $taxonomy_name = 'pa_' . $taxonomy->attribute_name;
            
            add_action("{$taxonomy_name}_add_form_fields", 'add_attribute_color_fields');
            add_action("{$taxonomy_name}_edit_form_fields", 'edit_attribute_color_fields', 10, 2);
            add_action("created_{$taxonomy_name}", 'save_attribute_color_fields', 10, 2);
            add_action("edited_{$taxonomy_name}", 'save_attribute_color_fields', 10, 2);
        }
    }
}
add_action('init', 'register_attribute_color_fields');

function enqueue_attribute_color_picker($hook) {
    if ('edit-tags.php' !== $hook && 'term.php' !== $hook) {
        return;
    }
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'enqueue_attribute_color_picker');
function initialize_attribute_color_picker() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('.color-picker').wpColorPicker();
        });
    </script>
    <?php
}
add_action('admin_footer', 'initialize_attribute_color_picker');
function itycod_add_upsell_button_to_footer() {
    if (is_product()) :
        $bg_color = get_option('itycod_upsell_button_bg_color', '#5580FF');
        $text_color = get_option('itycod_upsell_button_text_color', '#ffffff');
        $text_upsell = get_option('itycod_text_upsell', __('اشتري الآن', 'itycod'));
        ?>
        <div class="sticky-atc-container">
            <div class="sticky-atc-btn" style="display: none;">
                <a href="#custom-checkout-form" id="upsell-btn" style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($text_upsell); ?></a>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $(window).scroll(function() {
                    var threshold = 950; // Adjust this value to control when the button appears
                    var hT = $('#custom-checkout-form').offset().top,
                        hH = $('#custom-checkout-form').outerHeight(),
                        wH = $(window).height(),
                        wS = $(this).scrollTop();
                    if (wS > (hT - wH + threshold)){
                        $(".sticky-atc-btn").fadeIn();
                    } else {
                        $(".sticky-atc-btn").fadeOut(); 
                    }
                });

                $('#upsell-btn').click(function(event) {
                    event.preventDefault();
                    $('html, body').animate({
                        scrollTop: $('#custom-checkout-form').offset().top
                    }, 100);
                    $('.sticky-atc-container').fadeOut();
                });
            });
        </script>
    <?php
    endif;
}
add_action('wp_footer', 'itycod_add_upsell_button_to_footer');
function itycod_upsell_button_styles() {
    $bg_color = get_option('itycod_upsell_button_bg_color', '#5580FF');
    $text_color = get_option('itycod_upsell_button_text_color', '#ffffff');
    $hover_color = '#0056b3'; // Default hover color, can be made dynamic too if needed.
    ?>
<style>

.field-container i {
    position: absolute;
    left: 15px; /* Adjust icon position inside the field */
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #555;
}

.with-icon input, 
.with-icon select {
    padding-left: 35px !important; /* Space for the icon */
}



@media (max-width: 1024px) { /* Adjusting for tablets and smaller screens */

  #custom-checkout-form table td {
        width: 100% !important;
        display: block;
                    padding: 2px 0 !important;

    }
    .field-container {
        width: 100%;
    }
}

.order-summary {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
      direction: <?php echo $order_review_direction; ?>;
    text-align: <?php echo $text_align; ?>;

}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f2f2f2;
    border-radius: 8px;

}

.order-item.total {
    font-weight: bold;
    background: #eaeaea;
        color:<?php echo $button_bg_color; ?>;


}

.item-label {
    font-size: 16px;
    font-weight: 600;

}

.item-value {
            text-align: center;
    background-color: <?php echo $button_bg_color; ?>;
    color:<?php echo $button_bg_color; ?>;
    padding: 0px 0px; /* Small padding */
    border-radius: 3px;
    font-weight: bold;
    font-size: 18px; /* Smaller font size */
    margin: 2px; /* Small margin to ensure it doesn't touch other elements */
        text-align: left; /* Aligns quantity under the price */
            display: block;
  


}

.product-info {
  display: inline-flex;
  align-items: center;
  gap: 8px; /* Adjust spacing as needed */
  direction: <?php echo $product_info_direction; ?>;
    text-align: <?php echo $product_info_text_align; ?>;
}

/* Quantity display styling */
#quantity-display {
  font-size: 13px;
  padding: 2px 6px;
  border-radius: 4px;
}

/* Price styling: Simply display the price as a block element */
#product-price {
    display: block;
    text-align: <?php echo $product_info_text_align; ?>;
}



    .sticky-atc-container {
        width: 100%;
        position: fixed;
        bottom: 0;
        left: 0;
        background-color: #f8f9fa; /* Light grey background */
        z-index: 1000;
        text-align: center;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
    }
    .sticky-atc-btn {
        padding: 0px 0; /* Space above and below the button */
        display: inline-block;
    }
    .sticky-atc-btn a {
        color: <?php echo esc_attr($text_color); ?>; /* Dynamic text color */
        text-decoration: none;
        font-size: 20px; /* Slightly larger font size */
        border: 2px solid <?php echo esc_attr($bg_color); ?>; /* Dynamic border color */
        border-radius: 10px; /* More rounded corners */
        padding: 08px 20px; /* Larger padding */
        background-color: <?php echo esc_attr($bg_color); ?>; /* Dynamic background color */
        transition: background-color 0.3s ease, color 0.3s ease;
        display: inline-block;
        margin: 10px auto; /* Center the button with some margin */
        width: 250px;
        position: relative;
        overflow: hidden;
                    font-family: 'Cairo', sans-serif;

    }
    .sticky-atc-btn a:hover {
        background-color: <?php echo esc_attr($hover_color); ?>; /* Darker blue on hover */
        color: #ffffff;
    }
    .sticky-atc-btn a:focus {
        background-color: <?php echo esc_attr($hover_color); ?>; /* Darker blue on focus */
        color: #ffffff;
        outline: none;
    }
    .sticky-atc-btn a:active {
        background-color: #004085; /* Even darker blue on active */
        color: #ffffff;
    }
    .sticky-atc-btn a::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        transform: rotate(45deg);
        transition: transform 0.5s ease;
    }
    .sticky-atc-btn a:hover::before {
        transform: rotate(45deg) translate(200%, 200%);
    }

    
    #text-info-container {
    text-align: center;
    margin: 20px auto;
    position: relative; /* Ensure proper centering */
}

.animated-text-info {
    display: inline-block;
    font-size: 16px;
    font-weight: bold;
    background: linear-gradient(90deg, black, black, black);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    opacity: 0; /* Start hidden */
    transform: translateY(-20px) scale(0.8); /* Start with a smaller scale and offset */
    animation: move-text-once 1.5s ease-out forwards; /* Same animation */
}

@keyframes move-text-once {
    0% {
        opacity: 0;
        transform: translateY(-20px) scale(0.8);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

</style>



    <?php
}
add_action('wp_head', 'itycod_upsell_button_styles');
?>