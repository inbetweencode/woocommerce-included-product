<?php

class WIP_Admin {
  
  	public function __construct() {

        add_action( 'woocommerce_product_options_advanced',         [$this, 'add_included_product_meta_box'] );
        add_action( 'woocommerce_process_product_meta',             [$this, 'product_save_fields'], 10, 2 );
//         add_action( 'template_redirect',                            [$this, 'maybe_add_included_product'] );
//         add_action( 'woocommerce_before_calculate_totals',          [$this, 'set_included_products_price'] );



//         add_filter( 'woocommerce_cart_item_name',                   [$this, 'set_included_products_select'], 10, 2 );
//         add_filter( 'woocommerce_update_cart_action_cart_updated',  [$this, 'update_cart_included_product'], 10, 1 );
  
    }
  
  	/**
  	 *
  	 */
    public function add_included_product_meta_box(){
        global $post;
    	?>
        <div class="options_group">
    		<p class="form-field hide_if_grouped hide_if_external">
    			<label for="included_ids"><?php esc_html_e( 'Included product choice', 'woocommerce-included-product' ); ?></label>
    			<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="included_ids" name="included_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
    				<?php
    				$product_ids = maybe_unserialize(get_post_meta($post->ID, 'included_ids', true));
    				foreach ( $product_ids as $product_id ) {
    					$product = wc_get_product( $product_id );
    					if ( is_object( $product ) ) {
    						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
    					}
    				}
    				?>
    			</select> <?php echo wc_help_tip( __( 'One of the included products is added free of charge. The product can be switched in the cart.', 'woocommerce-included-product' ) ); // WPCS: XSS ok. ?>
    		</p>
		</div>
        <?php         
    }
  
  
  	/**
  	 *
  	 */
    public function product_save_fields( $id, $post ){
    	if( !empty( $_POST['included_ids'] ) ) {
    		update_post_meta( $id, 'included_ids', $_POST['included_ids'] );
    	} else {
    		delete_post_meta( $id, 'included_ids' );
    	}
    }
  
  
  
  
  
  
  
  
}