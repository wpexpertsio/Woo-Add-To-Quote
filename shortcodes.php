<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function watq_get_quote($atts) {
    extract(shortcode_atts(array(
        'quote_elem' => isset($_COOKIE['_quotes_elem']) ? $_COOKIE['_quotes_elem'] : '',
    ), $atts));
    if(!isset($quote_elem)) {
        $quote_elem = '';
    }

    ?>
    <div class="woocommerce quote">
    <?php
    wc_print_notices();
    if(!empty($quote_elem)) {
        if(count(json_decode(stripslashes($quote_elem))) > 0) {
            ?>
            <div class="quote_data_wrapper">
                <form method="post" id="watq_send_quote_form_wrapper" action="<?php echo get_the_permalink(); ?>">
                    <table class="shop_table cart" cellpadding="0">
                        <thead>
                            <tr>
                                <th class="product-remove"><?php echo __('remove', WATQ); ?></th>
                                <th class="product-thumbnail"><?php echo __('product Image', WATQ); ?></th>
                                <th class="product-name"><?php echo __('product', WATQ); ?></th>
                                <th class="product-price"><?php echo __('price', WATQ); ?></th>
                                <th class="product-quantity"><?php echo __('Quantity', WATQ); ?></th>
                                <th class="product-subtotal"><?php echo __('Total', WATQ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cookie_data = json_decode(stripslashes($quote_elem), true);
                            if(is_array($cookie_data)) {
                                $whole_quote_sub_total = null;
                                foreach($cookie_data as $data) {
                                    $product_obj = '';
                                    if($data['product_type'] == 'simple') {
                                        $product_obj = wc_get_product($data['product_id']);
                                    }
                                    elseif($data['product_type'] == "variation") {
                                        $product_obj = new WC_Product_Variation($data['product_variation_id'] );
                                    }
                                    $price_currency = watq_get_product_price($data['product_variation_id'], $data['product_type']);
                                    $id = 'product_id';
                                    $image = 'product_image';
                                    $title = 'product_title';
                                    $price = 'product_price';
                                    $quantity = 'product_quantity';
                                    $type = 'product_type';
                                    $variation_id = 'variation_id';
                                    $total_price = 'sub_total';
                                    $product_variation = 'product_variation';
                                    ?>
                                    <tr>
                                        <td class="product-remove" data-delete-id="" id="product_<?php echo $data['product_id']; ?>"><span>X<span></td>
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $id; ?>]" class="" value="<?php echo $data['product_id']; ?>" />
                                        <td class="product-image"><a href="<?php echo get_permalink($data['product_id']); ?>" ><img src="<?php echo $data['product_image']; ?>" /></a></td>
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $image; ?>]" class="" value="<?php echo $data['product_image']; ?>" />
                                        <td class="product-title">
                                            <a href="<?php echo get_permalink($data['product_id']); ?>" ><?php echo $data['product_title']; ?></a>
                                            <?php
                                            if($data['product_type'] == "variation"){
                                                if(isset($data['variations_attr'])) {
                                                    echo watq_get_product_variations($data['variations_attr'], true);
                                                ?>
                                                <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $product_variation; ?>]" class="" value="<?php echo esc_html(json_encode($data['variations_attr'])); ?>" />
                                                <?php
                                                }
                                            }
                                            ?>
                                        </td>
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $title; ?>]" class="" value="<?php echo $data['product_title']; ?>" />
                                        <td class="product-price"><?php echo WC()->cart->get_product_price( $product_obj ); ?></td>
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $price; ?>]" class="" value="<?php echo esc_html($price_currency['price']); ?>" />
                                        <td class="product-quantity"><?php echo $data['product_quantity']; ?></td>
                                        <td class="product-total"><?php echo WC()->cart->get_product_subtotal( $product_obj, $data['product_quantity']); ?></td>
                                        <?php
                                        $product_sub_total = WC()->cart->get_product_subtotal( $product_obj, $data['product_quantity']);
                                        $currency = get_woocommerce_currency_symbol();
                                        $price_with_currency = strrchr($product_sub_total,$currency);
                                        $price_num = str_replace($currency, '', $price_with_currency);
                                        $whole_quote_sub_total += floatval($price_num);
                                        ?>
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $total_price; ?>]" class="" value="<?php echo esc_html(WC()->cart->get_product_subtotal( $product_obj, $data['product_quantity'])); ?>" />
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $quantity; ?>]" class="" value="<?php echo $data['product_quantity']; ?>" />
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $type; ?>]" class="" value="<?php echo $data['product_type']; ?>" />
                                        <input type="hidden" name="data[<?php echo $data['product_variation_id']; ?>][<?php echo $variation_id; ?>]" class="variation_id" value="<?php echo $data['product_variation_id']; ?>" />
                                        <input type="hidden" name="quote_total" class="quote_total" value="<?php echo $whole_quote_sub_total; ?>" />
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"><?php echo __('Sub Total', WATQ); ?></td>
                                    <td><?php echo wc_price($whole_quote_sub_total); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        <div id="_send_quote_popup" style="display:none;">
                            <?php
                            if(is_user_logged_in()) {
                                ?>
                                <div class="_send_quote_form_wrapper">
                                    <label>
                                        <?php echo __('Write comma seprate email addresses.', WATQ); ?>
                                    </label>
                                    <?php
                                    $current_user = wp_get_current_user();
                                    $user_email = $current_user->user_email;
                                    ?>
                                    <input type="text" name="_to_send_email" id="_to_send_email" value="<?php echo $user_email; ?>">
                                    <button class="button" id="send_trigger" ><?php echo __('Send', WATQ); ?></button>
                                </div>
                                </div>
                                <a href="#TB_inline?width=350&height=250&inlineId=_send_quote_popup" id="_send_quote_email_" class="thickbox"><?php echo __('Send', WATQ); ?></a>
                                <?php
                            }
                            else {
                                if((boolean)get_option( 'wc_settings_allow_guest_user' )) {
                                    ?>
                                    <div class="_send_quote_form_wrapper">
                                        <label>
                                            <?php echo __('Write comma seprate email addresses.', WATQ); ?>
                                        </label>

                                        <input type="text" name="_to_send_email" id="_to_send_email" value="">
                                        <button class="button" id="send_trigger" ><?php echo __('Send', WATQ); ?></button>
                                    </div>
                                </div>
                                <a href="#TB_inline?width=350&height=250&inlineId=_send_quote_popup" id="_send_quote_email_" class="thickbox"><?php echo __('Send', WATQ); ?></a>
                                    <?php
                                }
                                else {
                                    ?>
                                    </div>
                                    <a href="<?php echo get_permalink(get_page_by_path('my-account')).'?rq=login'; ?>" id="_send_quote_email_"><?php echo __('Send', WATQ); ?></a>
                                    <?php
                                }
                            }
                            ?>
                        <input type="hidden" name="_to_send_email" class="_to_send_email" value="" />
                        <input type="hidden" name="action" value="send_quote" />
                        <input type="submit" value="email quote" class="_submit" />
                    </form>
                </div>

                <div class="_quoteall_buttons_wrapper">
                <?php
                if(get_option( 'wc_settings_quote_to_cart_select' ) == "true") {
                    ?>
                        <form method="post" id="_add_quote_to_cart" action="<?php echo get_the_permalink(); ?>">
                            <?php
                            foreach($cookie_data as $data_c) {
                            $id = 'product_id';
                            $quantity = 'product_quantity';
                            $type = 'product_type';
                            $variation_id = 'variation_id';
                            $variation_attr = 'variation_attr';
                            ?>
                            <input type="hidden" name="data[<?php echo $data_c['product_variation_id']; ?>][<?php echo $id; ?>]" class="" value="<?php echo $data_c['product_id']; ?>" />
                            <input type="hidden" name="data[<?php echo $data_c['product_variation_id']; ?>][<?php echo $quantity; ?>]" class="" value="<?php echo $data_c['product_quantity']; ?>" />
                            <input type="hidden" name="data[<?php echo $data_c['product_variation_id']; ?>][<?php echo $type; ?>]" class="" value="<?php echo $data_c['product_type']; ?>" />
                            <input type="hidden" name="data[<?php echo $data_c['product_variation_id']; ?>][<?php echo $variation_id; ?>]" class="" value="<?php echo $data_c['product_variation_id']; ?>" />
                            <input type="hidden" name="data[<?php echo $data_c['product_variation_id']; ?>][<?php echo $variation_attr; ?>]" class="" value="<?php echo esc_html(json_encode($data_c['variations_attr'])); ?>" />
                            <?php
                            }
                            ?>
                            <input type="hidden" name="action" value="add_to_cart_q">
                            <input type="submit" value="<?php echo __('Add to Cart', WATQ); ?>" class="_submit button" />
                        </form>
                    <?php
                }
                ?>
                    <form method="post" id="clear_quotes" action="<?php echo get_the_permalink(); ?>">
                        <input type="hidden" name="action" value="_clear_quotes" />
                        <input type="submit" value="<?php echo __('Empty Quote', WATQ); ?>" class="_submit button" />
                    </form>
                    <button id="_email_quote_trigger" class="button"><?php echo __('Email', WATQ); ?></button>
                </div>
                <?php
            }
        }
    }
    else {
        ?>
        <p><?php echo __('Your Current Quote is empty', WATQ); ?></P>
        <a href="<?php echo get_permalink(get_page_by_path('shop')); ?>" class="return_shop_quote"><?php echo __('Return To Shop', WATQ); ?></a>
        <?php
    }
    ?>
    </div>
    <?php
}
add_shortcode('_quote', 'watq_get_quote');