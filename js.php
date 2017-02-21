<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once('quote_on_cart.php');
add_action('wp_head', 'watq_quote_js');
function watq_quote_js() {
    ?>
    <script>
        jQuery(document).ready(
            function($) {
                $('._add_to_quote_submit').click(
                    function(e) {
                        e.preventDefault();
                        <?php
                        if( function_exists('get_product') ) {
                            $product_ = wc_get_product(get_the_ID());
                            if($product_->is_type('variable')){
                        ?>
                        var $variation = {};
                        var $count = 0;
                        var $product_variation_id = $('table.variations').find('tr').each(
                            function() {
                                var $var_value = $(this).find('td.value').find('select').val();
                                var $var_key = $(this).find('td.value').find('select').attr('name');
                                $variation[$count] = [$var_key, $var_value];
                                $count++;
                            }
                        );

                        $('.single-product').find('._add_to_quote').find('input.variations_attr').val(JSON.stringify($variation)).change();
                        <?php
                        }
                        }
                        ?>
                        var $product_quantity = $('.single-product').find('form.cart').find('.quantity').find('input[type="number"]').val();
                        var $product_variation_id = $('.single-product').find('form.variations_form').find('input.variation_id').val();
                        $('.single-product').find('._add_to_quote').find('input.quantity').val($product_quantity).change();
                        $('.single-product').find('._add_to_quote').find('input.variation_id').val($product_variation_id).change();
                        var $elem = document.getElementById('_add_to_quote_form_wrapper');
                        $elem.submit();
                    }
                );

                $('.woocommerce.quote').find('tbody').find('td.product-remove').click(
                    function() {
                        var $to_delete_id = $(this).siblings('.variation_id').val();

                        /* In front end of WordPress we have to define ajaxurl */
                        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                        var data = {
                            'action': 'quote_remove',
                            'product_id' : $to_delete_id

                        };

                        $.post(ajaxurl, data, function(response) {
                            var responseArray = $.parseJSON(response);
                            location.reload();
                        });
                    }
                );
                <?php
                if(get_option('wc_settings_quote_on_cart_select') === "true") {
                ?>
                var $quote_on_cart_form = '<form method="post" action="' + window.location.href + '">';
                <?php
                    $cart_products = watq_from_cart_to_quote();
                    $loo_counter = 0;
                    if(is_array($cart_products) && !empty($cart_products)) {
                        foreach($cart_products as $pro) {
                            ?>
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_id]" value="<?php echo $pro['product_id'] ?>" />';
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_image]" value="<?php echo $pro['product_image'] ?>" />';
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_title]" value="<?php echo $pro['product_title'] ?>" />';
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_quantity]" value="<?php echo $pro['product_quantity'] ?>" />';
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_type]" value="<?php echo $pro['product_type'] ?>" />';
                            <?php
                            if(isset($pro['product_variation_id'])) {
                            ?>
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_variation_id]" value="<?php echo $pro['product_variation_id'] ?>" />';
                            <?php
                            }
                            if(isset($pro['product_variations'])) {
                            ?>
                            $quote_on_cart_form += '<input type="hidden" name="product[<?php echo $loo_counter; ?>][product_variations]" value="<?php echo esc_html(json_encode($pro['product_variations'])) ?>" />';
                            <?php
                            }
                            $loo_counter++;
                        }
                    }
                ?>
                            $quote_on_cart_form += '<input type="hidden" name="action" value="from_cart_to_quote" />';
                            $quote_on_cart_form += '<input type="submit" name="_cart_to_quote" class="button cart_to_quote_submit" value="<?php echo __('Built a Quote', WATQ); ?>" />';
                            $quote_on_cart_form += '</form>';
                            $($quote_on_cart_form).insertAfter($('div.wc-proceed-to-checkout').find('a.checkout-button'));
                <?php
                }
                ?>
            }
        );
    </script>
    <?php
}

add_action( 'wp_ajax_quote_remove', 'watq_quote_ajax_callback' );
add_action('wp_ajax_nopriv_quote_remove', 'watq_quote_ajax_callback');
function watq_quote_ajax_callback() {
    $product_id = (array_key_exists('product_id', $_POST) ? $_POST['product_id'] :'');

    $cookie_data = isset($_COOKIE['_quotes_elem']) ? $_COOKIE['_quotes_elem'] : '';
    $update_quote =array();
    if (!empty($cookie_data)) {
        $exists_quote = json_decode(stripslashes($cookie_data), true);
        if(is_array($exists_quote)) {
            foreach ($exists_quote as $quote_array) {
                if (!in_array($product_id, $quote_array)) {
                    $update_param = array(
                        "product_id" => $quote_array["product_id"],
                        "product_image" => $quote_array["product_image"],
                        "product_title" => $quote_array["product_title"],
                        "product_quantity" => $quote_array["product_quantity"],
                        "product_type" => $quote_array["product_type"],
                        "product_variation_id" => $quote_array["product_variation_id"],
                    );
                    if (!empty($update_quote)) {
                        $update_quote[] = $update_param;
                    } else {
                        $update_quote = array($update_param);
                    }
                }
            }
        }

        if(!empty($update_quote)) {
            $expire = time() + 3600 * 24 * 100;
        }
        else {
            $expire = time() - 3600 * 24 * 100;
        }
        $result_id = setcookie('_quotes_elem', json_encode($update_quote), $expire, COOKIEPATH, COOKIE_DOMAIN, false);

        echo json_encode(array(true, $product_id));
        die();
    }
}

function watq_from_cart_to_quote() {
    global $woocommerce;
    $loop_value = 0;
    $product_from_cart = array();
    if(is_object($woocommerce) || is_array($woocommerce)) {
        foreach($woocommerce->cart->get_cart() as $item_key=> $item_value) {

            $product_from_cart[$loop_value]['product_id'] = $item_value['product_id'];
            $product_from_cart[$loop_value]['product_image'] = wp_get_attachment_url( get_post_thumbnail_id($item_value['product_id']) );
            $product_from_cart[$loop_value]['product_title'] = $item_value['data']->post->post_title;
            $product_from_cart[$loop_value]['product_quantity'] = $item_value['quantity'];
            $product_from_cart[$loop_value]['product_type'] = $item_value['data']->product_type;
            if($item_value['data']->product_type == "variation" || $item_value['data']->product_type == "variable" ){
                $product_from_cart[$loop_value]['product_variation_id'] = $item_value['variation_id'];
                $product_from_cart[$loop_value]['product_variations'] = array_key_exists('variation', $item_value) ? $item_value['variation'] : '';
            }
            else {
                $product_from_cart[$loop_value]['product_variation_id'] = $item_value['product_id'];
            }
            $loop_value++;
        }
    }
    return $product_from_cart;
}