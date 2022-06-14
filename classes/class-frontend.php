<?php

class WIP_Frontend {
  
  	public function __construct() {

        add_action( 'template_redirect',                            [$this, 'maybe_add_included_product'] );
        add_action( 'woocommerce_before_calculate_totals',          [$this, 'set_included_products_price'] );

        add_filter( 'woocommerce_cart_item_name',                   [$this, 'set_included_products_select'], 10, 2 );
        add_filter( 'woocommerce_update_cart_action_cart_updated',  [$this, 'update_cart_included_product'], 10, 1 );
  
    }


	/**
	 *
	 */
	public function maybe_add_included_product() {

        //if( is_cart() || is_checkout() ){

        $wc_cart = WC()->cart;

        // cart is empty, return
        if ( $wc_cart->is_empty() )
            return;

                        
        // Loop through cart
        foreach ( $wc_cart->get_cart() as $cart_item_key => $cart_item ) {

            // Remove orphaned included product.
            if ( isset( $cart_item['parent_cart_item_key'] ) && empty( $wc_cart->find_product_in_cart( $cart_item['parent_cart_item_key'] ) ) ) {
                $wc_cart->remove_cart_item( $cart_item_key );
            }

            // Get possible included products of cart item.
            $included_ids = maybe_unserialize( get_post_meta( $cart_item['product_id'], 'included_ids', true ) );                    

            //Debug.
            //error_log($cart_item_key);
            //error_log(print_r($cart_item,true));
            //error_log($cart_item['product_id']);
            //error_log(print_r($included_ids,true));

            // Process product that should have an included product.
            if ( ! empty( $included_ids ) ) {

                // Var
                $included_cart_item_key = false;

                // Data based on parent cart item key.
                $cart_item_data = array( 'parent_cart_item_key' => $cart_item_key, 'included_product_options' => $included_ids );
                
                // Loop through all possible included products to see if they are in the cart.
                foreach ( $included_ids as $included_id ) {

                    // Generate cart item key for possible included product to check cart with.
                    $possible_cart_item_key = $wc_cart->generate_cart_id( $included_id, null, null, $cart_item_data );

                    // Return cart item key or empty string.
                    $found = $wc_cart->find_product_in_cart( $possible_cart_item_key );

                    // If first is found, then delete others tied to this parent?
                    if( ! empty( $found ) && ! $included_cart_item_key ) {
                        $included_cart_item_key = $found;
                    }

                    //Debug.
                    //error_log('Possible included product (cart item key: '.$product_cart_id.', parent ID: '.$cart_item['product_id'].') in cart: '.$in_cart);
                    //error_log('Possible included product found: '.$found);
                }

                // If not found add first included product with cart_item_data
                if ( ! $included_cart_item_key ) {
                    $included_cart_item_key = $wc_cart->add_to_cart( $included_ids[0], 1, null, null, $cart_item_data );
                }

            } // END IF product has included products
                
        } // END FOREACH loop through cart
                        
        //}

    }


    /**
     * Set all included product prices to zero
     */
    public function set_included_products_price( $wc_cart ) {

        foreach ( $wc_cart->get_cart() as $cart_item_key => $cart_item ) {

            // Check if is 'included product'
            if( isset( $cart_item['parent_cart_item_key'] ) ) {
                
                $cart_item_product = $cart_item['data'];
                $cart_item_product->set_price( 0 );
                $cart_item_product->set_sold_individually( true );
            }
        }            
    }


    /**
     * Set included product names to select inputs
     */
    public function set_included_products_select( $link_text, $product_data ) {

        if( is_cart() && isset( $product_data['parent_cart_item_key'] ) && isset( $product_data['included_product_options'] ) ) {

            // WooCommerce uses wp_kses_post() around apply_filter(), so we extend the allowed tags, since it's the only way.
          	global $allowedposttags;
          	$allowed_atts = array('class'=>array(), 'data'=>array(), 'name'=>array(), 'value'=>array(), 'selected'=>array());
          
          	$allowedposttags['option'] = $allowed_atts;
          	$allowedposttags['select'] = $allowed_atts;

            //echo ('<pre>'.print_r( $product_data, true ).'</pre>');

            $product_id = $product_data['product_id'];
            $product_cart_item_key = $product_data['key'];
            //$parent_cart_item_key = $product_data['parent_cart_item_key'];
            $included_product_option_ids = $product_data['included_product_options'];

            $options = '';

      			foreach ( $included_product_option_ids as $included_product_option_id ) {
      
      				$included_product_option = wc_get_product( $included_product_option_id );
      
      				if ( is_object( $included_product_option ) ) {
      					$options .= '<option value="' . esc_attr( $included_product_option_id ) . '"' . selected( $product_id, $included_product_option_id, false ) . '>' . wp_kses_post( $included_product_option->get_name() ) . '</option>';
      				}
      			}

            return sprintf( '<select data-action="" class="included_product" name="cart[' . $product_cart_item_key . '][included_product]">%s</select>', $options );
        }

        return $link_text;
    }

    /**
     * Update cart with included product from select options
     * Format: included_product[product_cart_item_key][parent_cart_item_key] => selected product_id
     */
    public function update_cart_included_product( $cart_updated ) {

    		if ( ! isset( $_REQUEST['update_cart'] ) ) {
    			  return $cart_updated;
    		}

    		$cart_totals = isset( $_POST['cart'] ) ? wp_unslash( $_POST['cart'] ) : ''; // PHPCS: input var ok, CSRF ok, sanitization ok.

        //Debug.
        //error_log( '$_POST[cart]: ' . print_r( $cart_totals, true ) );

    		if ( ! WC()->cart->is_empty() && is_array( $cart_totals ) ) {
    			  foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

    				    $_product = $cart_item['data'];


                // Skip product if no updated included_product was posted.
                if ( ! isset( $cart_totals[ $cart_item_key ] ) || ! isset( $cart_totals[ $cart_item_key ]['included_product'] ) )
                    continue;


                //error_log($cart_item_key);


                $cart_item_data = array( 'parent_cart_item_key' => $cart_item['parent_cart_item_key'], 'included_product_options' => $cart_item['included_product_options'] );

                WC()->cart->add_to_cart( $cart_totals[ $cart_item_key ]['included_product'], 1, null, null, $cart_item_data );
                WC()->cart->remove_cart_item( $cart_item_key );

                $cart_updated = true;
            }

        }

        return $cart_updated;
    }


}