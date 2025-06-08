<?php
function custom_checkout_form_styles() {
    // Fetch saved options
    $checkout_border_color = get_option('itycod_checkout_border_color', '#654ad1');
    $button_bg_color = get_option('itycod_button_bg_color', '#654ad1');
    $button_text_color = get_option('itycod_button_text_color', '#ffffff');
    $button_hover_color = get_option('itycod_button_hover_color', '#654ad1');
    $quantity_button_text_color = get_option('itycod_quantity_button_text_color', '#ffffff');
    $quantity_button_color = get_option('itycod_quantity_button_color', '#007bff');
    $quantity_button_hover_color = get_option('itycod_quantity_button_hover_color', '#0056b3');
    $quantity_button_hover_text_color = get_option('itycod_quantity_button_hover_text_color', '#ffffff');
   $checkout_direction       = get_option('itycod_checkout_direction', 'rtl');
$fields_direction         = get_option('itycod_fields_direction', 'rtl');
$order_review_direction   = get_option('itycod_order_review_direction', 'rtl');
$text_align               = get_option('itycod_text_align', 'right');
$product_info_direction   = get_option('itycod_product_info_direction', 'rtl');
$product_info_text_align  = get_option('itycod_product_info_text_align', 'right');
$icons_direction          = get_option('itycod_icons_direction', 'rtl');
    ?>
    <style>
                    .field-container {
                    position: relative;
                    margin-bottom: 15px;
                }

                .field-sticker {
                    position: absolute;
                    top: 50%;
                    left: 10px;
                    transform: translateY(-50%);
                    pointer-events: none;
                    font-size: 15px;
                }

                .woocommerce-input-wrapper input[type="text"],
                .woocommerce-input-wrapper input[type="tel"],
                .woocommerce-input-wrapper select {
                    padding-left: 35px; /* Adjust padding to make space for the icon */
                }
                
                
                    .field-container {
                    position: relative;
                }
                


/* Enhanced styling for variation options with higher contrast text */
.variation-option {
    display: inline-block; /* Fit the width to the text */
    padding: 8px 16px; /* Adjust padding for a more premium feel */
    border-radius: 10px; /* More rounded corners for a modern look */
    border: 1px solid #ddd; /* Softer light gray border */
    margin-bottom: 12px; /* Slightly more space below each option */
    transition: border-color 0.3s, box-shadow 0.3s, background-color 0.3s; /* Smooth transitions for hover effects */
    font-size: 16px; /* Readable font size */
    line-height: 1.4; /* Comfortable line spacing */
    color: #000; /* Black text color for maximum contrast */
    max-width: 100%; /* Ensure it doesn't exceed the container width */
    word-wrap: break-word; /* Break long words to fit the container */
    cursor: pointer; /* Pointer cursor to indicate clickability */
}

/* Styling for radio buttons inside variation options */
.variation-option input[type="radio"] {
    margin-right: 10px; /* Space between radio button and text */
    transform: scale(0.9); /* Increase the size of radio buttons */
}


/* Hover effect for variation options */
.variation-option:hover {
    border-color: #007cba; /* WooCommerce blue border on hover */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow on hover */
}

/* Active/selected state for variation options */
.variation-option input[type="radio"]:checked + label {
    border-color: #007cba; /* WooCommerce blue border for selected state */
    background-color: #e6f7ff; /* Light blue background for selected state */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow for selected state */
    color: #007cba; /* WooCommerce blue text for selected state */
    font-weight: bold; /* Bold text for selected state */
}


/* Responsive styling */
@media (max-width: 768px) {
    .variation-option {
        font-size: 14px; /* Slightly smaller font size on tablets */
        padding: 4px 10px; /* Adjust padding for smaller screens */
    }

    .variation-option input[type="radio"] {
        transform: scale(1.0); /* Adjust radio button size on tablets */
      display: none;
  }
}

@media (max-width: 480px) {
    .variation-option {
        font-size: 14px; /* Smaller font size on mobile devices */
        padding: 3px 8px; /* Adjust padding for mobile screens */
    }

    .variation-option input[type="radio"] {
        transform: scale(0.5); /* Adjust radio button size on mobile */
    display: none;
  }
}


        .order-review h3 {
            margin-bottom: 15px;
            font-size: 17px;
            color: #333;
            font-weight: bold;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            font-family: 'Cairo', sans-serif;

        }

		

table, td, th {
    border: 0px solid rgba(0,0,0,.1);
}
		
        #custom-checkout-form h5 {
            margin-bottom: 20px;
            margin-top: 05px;
            font-family: 'Cairo', sans-serif;
                padding: 0 20px;
    color: #000;
    font-size: 15px;

        }
        
        

.select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-clip: padding-box;
    background-size: 9px;
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
}

/* Style the product title */
ul.products li.product h2 {
    font-size: 18px;
    margin-bottom: 10px;
}

/* Style the product price */
ul.products li.product span.price {
    display: block;
    font-size: 16px;
}
#custom-checkout-form {
    background: #F9F9F9; /* Pure white for a cleaner look */
    border: 1px solid rgba(224, 224, 224, 0.8); /* Soft, light border */
    width: 100%;
    max-width: 630px; /* Slightly reduced width for a sleek look */
    margin: 20px auto;
    padding: 20px 30px; /* Better spacing */
    border-radius: 12px; /* Softer rounded corners */
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08); /* More modern shadow */
    direction: <?php echo $checkout_direction ?: 'ltr'; ?>;
    text-align: <?php echo $text_align ?: 'left'; ?>;
    transition: all 0.3s ease-in-out;
    font-family: 'Cairo', sans-serif;
    line-height: 1.4; /* Slightly more spaced lines for readability */
}




	
	
/* Apply Cairo font style to WooCommerce prices */
.woocommerce-Price-amount, 
.woocommerce-Price-currencySymbol {
    font-family: 'Cairo', sans-serif;
    font-weight: bold; /* Adjust weight as needed */
}

/* Optional: Additional styling for discounted prices */
.woocommerce span.price del,
.woocommerce span.price ins {
    font-family: 'Cairo', sans-serif;
}

/* Optional: Style for product list prices */
.woocommerce ul.products li.product .price {
    font-family: 'Cairo', sans-serif;
}
    


#custom-checkout-form .form-row {
    margin-bottom: 10px; /* Reduce space between rows */
}
        
#custom-checkout_woo_single_form input,
#custom-checkout_state,
#custom-checkout_city {
            border: 1px solid <?php echo $checkout_border_color; ?>;
    height: 50px;
    width: 100%;
    margin: 0;
    border-radius: 3px;
    box-sizing: border-box;
    padding: 3%;
                 font-family: 'Cairo', sans-serif;

}

#custom-checkout.form-footer {
    display: grid;
    grid-gap: 15px;
    grid-template-columns: 125px 1fr;
    margin-top: 10px;
                 font-family: 'Cairo', sans-serif;

}

input#nrwooconfirm{
    background-color: #5b4ebb;
    border: none;
    color: #fff !important;
    height: 50px;
    line-height: 1;
    border-radius: 3px;
    margin: 0;
    overflow: hidden;
                 font-family: 'Cairo', sans-serif;
}
    
        
        #custom-checkout-input {
    display: grid;
    grid-gap: 10px;
    grid-template-columns: repeat(2, 1fr);
                 font-family: 'Cairo', sans-serif;

}

/* Additional styling for textareas (e.g., order note) */
#custom-checkout-form textarea {
    width: 100%;
    border: 1px solid <?php echo $checkout_border_color; ?>;
    padding: 3.5%;
    border-radius: 9px;
    font-size: 14px;
    background: #fff;
    color: #495057;
    box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    direction: <?php echo $fields_direction; ?>;
    max-width: 630px;
    margin: 0;
    font-family: 'Cairo', sans-serif;
    min-height: 70px; /* Allow more space for multi-line text */
    text-align: <?php echo $text_align; ?>;
}


         /* Modern input and button styling */
    #custom-checkout-form input[type="text"],
    #custom-checkout-form input[type="email"],
    #custom-checkout-form input[type="tel"],
    #custom-checkout-form input[type="number"],
    #custom-checkout-form button[type="submit"],
    #custom-checkout-form select {
        width: 100%;
                border: 1px solid <?php echo $checkout_border_color; ?>;

padding: 3.5%; /* Adjust padding for a cleaner look */
        border-radius: 09px; /* Modern rounded corners */
        font-size: 14px;
        background: #fff;
        color: #495057;
        box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
        direction:rtl;
                  height: 45px;
            max-width: 630px;
            margin:0;
    direction: <?php echo $fields_direction; ?>;
             font-family: 'Cairo', sans-serif;
                 display: grid;
    grid-gap: 05px;
    grid-template-columns: repeat(2, 1fr);
                 font-family: 'Cairo', sans-serif;
				                      font-weight: 200; /* Regular for body text */
font-weight: 700; /* Bold for section titles or important labels */
    text-align: <?php echo $text_align; ?>;


    }
    
    /* Optional: Reset table cell padding */
#custom-checkout-form td {
    padding: 04px;
    margin: 1;
        border: 0px solid transparent;

}

        #custom-checkout-form.locked::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            color: red;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        

    /* Focus effect for inputs */
    #custom-checkout-form input[type="text"]:focus,
    #custom-checkout-form select:focus,
    #custom-checkout-form input[type="tel"]:focus,
    #custom-checkout-form input[type="number"]:focus {
        border-color: <?php echo $checkout_border_color; ?>;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
    }


#custom-checkout-form button[type="submit"] {
    background: <?php echo $button_bg_color; ?>;
    color: <?php echo $button_text_color; ?>;
    border: 0px solid <?php echo $checkout_border_color; ?>;
    border-radius: 09px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s, box-shadow 0.3s;
    width: 100%;
    padding: 15px;
    margin: 0 auto;
    margin-bottom: 20px;
    height: auto;
    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
    direction: rtl;
    line-height: 20px;
    font-family: 'Cairo', sans-serif;
    text-align: center;
    display: block;
    margin-right: 41px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}


    #custom-checkout-form button[type="submit"] {
        background: <?php echo $button_bg_color; ?>;
        color: <?php echo $button_text_color; ?>;
        padding: 12px; /* Reduced padding for a sleek look */
        border-radius: 09px; /* More rounded for a modern style */
        font-size: 15px;
        cursor: pointer;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }
    
 #custom-checkout-form.locked {
        pointer-events: none; 
        opacity: 0.5;          
        position: relative;               
    }

        
         #custom-checkout-form-title h3{
    padding: 0 20px;
    display: inline-block;
    color: #000;
    font-size: 16px;
                font-family: 'Cairo', sans-serif;

}

        #custom-checkout-form button[type="submit"]:hover {
            background-color: <?php echo $button_hover_color; ?>;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        
            #custom-checkout-form .quantity-wrapper input {
            text-align: center;
            margin: 0 0;
            width: 80px;
            direction:rtl;
            padding: 0 12px;
            line-height: 32px;
            height: 40px;
            border-radius: 0;

        }
        
         .quantity-and-submit {
            display: flex;
            justify-content: space-between;
            align-items: center;
                        direction:rtl;

        }

        .quantity-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            width: 130px;
                        direction:rtl;

        }
        

        .quantity-wrapper button {
			            margin: 0 0;
            padding: 0 12px;
            font-size: 18px;
            border-radius: 0;
            line-height: 32px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            background: white;
            color: black;
            border: 1px solid <?php echo $checkout_border_color; ?>;
        }
        
           

        .quantity-wrapper input {
            text-align: center;
            margin: 0 0;
            width: 80px;
            padding: 15px;
            font-size: 15px;
            border-radius: 15px;
            box-sizing: border-box;
            background: #fff;
            color: #495057;
            height: auto;
            border: 2px solid <?php echo $checkout_border_color; ?>;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
        }
        

        .full-width-button {
            width: 100%;
            background-color: #0071a1;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
        }

        .full-width-button:hover {
            background-color: <?php echo $button_hover_color; ?>;
        }

        .order-review .total-row {
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 1;
            padding-left: 10px;
            color:<?php echo $button_bg_color; ?>;
        }
        
        
        .order-review table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        
           .order-review {
            margin-top: 20px;
            direction: <?php echo $order_review_direction; ?>;
    text-align: <?php echo $text_align; ?>;
            font-family: 'Cairo', sans-serif;


        }
        .order-review th,
        .order-review td {
            border: none;
            padding: 10px;
            text-align: left;
            font-size: 16px;
            border-bottom: 1px solid #dee2e6;
            direction:ltr;

        }

        .order-review th {
            background-color: #f8f9fa;
            font-weight: bold;
        }


.quantity-label {
    background-color: <?php echo $button_bg_color; ?>;
    color: <?php echo $button_text_color; ?>;
    padding: 0px 6px; /* Small padding */
    border-radius: 3px;
    font-weight: bold;
    font-size: 12px; /* Smaller font size */
    margin: 2px; /* Small margin to ensure it doesn't touch other elements */
}



.woocommerce-Price-amount.amount {
    font-weight: bold;
    color:<?php echo $button_bg_color; ?>;
}

.free-shipping {
    background-color: <?php echo $button_bg_color; ?>;
    color: <?php echo $button_text_color; ?>;
    padding: 0px 5px;
    border-radius: 5px;
    font-weight: bold;
}

/* Hide elements not related to checkout */
#main .single-product-category { display: none; }
.woocommerce-table--order-details .shipped_via { display: none; }


tbody > tr:nth-of-type(2) > .value { display: none; }
tbody > tr:nth-of-type(1) > .value { display: none; }
tbody > tr:nth-of-type(1) > .label { display: none; }
tbody > tr:nth-of-type(2) > .label { display: none; }

#payment p { display: none; }
#payment label { display: none; }
#payment .wc_payment_method { display: none; }
#ship-to-different-address { display: none; }
#customer_details .woocommerce-shipping-fields { display: none; }


#main .product_meta { display: none; }
#primary .product_meta { display: none; }


/* Hide elements not related to checkout */
#main .woocommerce-breadcrumb { display: none; }
#content .onsale,
#main .woocommerce-breadcrumb,

.last .ast-onsale-card,
.last .ast-on-card-button,
.loading .ahfb-svg-iconset,
#ast-bag-icon-svg,
.loading #ast-bag-icon-svg,
.single-product-category,
.ajax_add_to_cart .ahfb-svg-iconset,
.product_type_variable .ahfb-svg-iconset,
.has-default-attributes .ahfb-svg-iconset,
.last .count,
.woocommerce-product-details__short-description p:nth-of-type(2),
#main .product_meta,
.last mark,
#tab-title-additional_information a,
#content .count,
#main .orderby,
#main .zoomImg,
#main .woocommerce-product-gallery__trigger,
#main .woocommerce-result-count,
#main .woocommerce-products-header__title,


.woocommerce-table--order-details .shipped_via {
    display: none;
}


/* Media queries for responsiveness */
@media (max-width: 768px) {
    #custom-checkout-form {
        padding: 20px;
        width: 100%;
    }
    
      #custom-checkout-form input[type="text"],
    #custom-checkout-form select,
    #custom-checkout-form input[type="tel"],
    #custom-checkout-form input[type="number"],
    #custom-checkout-form button[type="submit"] {
        width: 100%; /* Full width on all screens */
        padding: 10px;
        font-size: 14px;
        height: 43px;
        font-family: 'Cairo', sans-serif;
    }

    /* Mobile-specific styling */
    @media (max-width: 768px) {
        #custom-checkout-form table,
        #custom-checkout-form tr,
        #custom-checkout-form td {
            width: 100%;
        }

        #custom-checkout-form td .field-container {
            margin-bottom: 10px; /* Add some space between fields */
        }
    
                    #custom-checkout-form .field-container {
                    width: 100% !important;
                }
                
    #custom-checkout-form input[type="text"],
    #custom-checkout-form select,
    #custom-checkout-form input[type="tel"],
    #custom-checkout-form input[type="number"],
    #custom-checkout-form button[type="submit"] {
        padding: 10px;
        font-size: 16px;
        height:auto;
        width;100%;
    }

    #custom-checkout-form button[type="submit"] {
        font-size: 16px;
    }

    .quantity-wrapper button {
        padding: 8px 16px;
        font-size: 16px;
    }

    .order-review {
        padding: 15px;
    }

    .order-review th,
    .order-review td {
        padding: 8px;
        font-size: 14px;
    }

    .order-review .total-row {
        padding-left: 8px;
    }
}

@media (max-width: 480px) {
    #custom-checkout-form {
        padding: 12px;
        width: 100%;
                                font-family: 'Cairo', sans-serif;

    }

    #custom-checkout-form input[type="text"],
    #custom-checkout-form select,
    #custom-checkout-form input[type="tel"],
    #custom-checkout-form input[type="number"],
    #custom-checkout-form button[type="submit"] {
        width: 100%; /* Ensure fields are full-width on mobile devices */
        padding: 10px; /* Adjust padding for better spacing */
        font-size: 14px; /* Smaller font size for mobile */
        height: 43px;
                                        font-family: 'Cairo', sans-serif;

    }
    
        #custom-checkout-input {
        grid-template-columns: 1fr; /* Make the form fields full-width in a single column */
    }
    
    .quantity-label {
    background-color: <?php echo $button_bg_color; ?>;
    color: <?php echo $button_text_color; ?>;
    padding: 0px 6px; /* Small padding */
    border-radius: 3px;
    font-weight: bold;
    font-size: 11px; /* Smaller font size */
    margin: 1px; /* Small margin to ensure it doesn't touch other elements */
}

    
    		#custom-checkout-form td {
        padding: 7px;
    }


    #custom-checkout-form button[type="submit"] {
        font-size: 18px;
        height:auto;
        padding: 12px;
        margin-right: 20px; /* Add margin to the right of the submit button */


    }
    
    
            #custom-checkout-form .quantity-wrapper input {
            text-align: center;
            margin: 0 0;
            width: 50px;
            direction:rtl;
            padding: 0 08px;
            line-height: 32px;
            height: 38px;
            border-radius: 0;

        }


    .quantity-wrapper button {
        font-size: 14px;
            padding: 0 08px;
            line-height: 32px;
            height: 40px;
            border-radius: 0;
    }

    .order-review {
        padding: 10px;
    }

    .order-review th,
    .order-review td {
        padding: 6px;
        font-size: 14px;
    }

    .order-review .total-row {
        padding-left: 6px;
    }
}
    </style>
    <?php
}
add_action('wp_head', 'custom_checkout_form_styles');
?>