<?php
/**
 * Created by PhpStorm.
 * User: kylemoore
 * Date: 2018-12-20
 * Time: 15:31
 */

/**
 * @package geplugin
 */
/*
Plugin Name: GePlugin
Plugin URI: KyleMoore1.github.io
Description: GE checkout plugin
Author: Kyle Moore
Author URI: KyleMoore1.github.io
License: GPLv2 or later
Text Domain: geplugin
*/

defined( 'ABSPATH' ) or die( 'Go away' );

class GePlugin {

	function activate() {
		flush_rewrite_rules();
	}

	function deactivate() {
		flush_rewrite_rules();
	}

	function displayForm() {
		echo file_get_contents(dirname(__FILE__ ) . '/content.html');
	}

	function enqueue() {
		// enqueue all our scripts
		wp_enqueue_style( 'mypluginscript', plugins_url( 'style.css', __FILE__ ) , array(), filemtime( dirname(__FILE__). '/script.js'), true );
		wp_enqueue_script( 'mypluginscript', plugins_url( 'script.js', __FILE__ ) , array(), filemtime( dirname(__FILE__). '/script.js'), true );
	}


}

if ( class_exists( 'GePlugin' ) ) {
	$gePlugin = new GePlugin();
}

//activation
register_activation_hook( __FILE__, array($gePlugin , 'activate') );

//deactivation
register_deactivation_hook( __FILE__, array($gePlugin , 'deactivate') );

//adds content.html before add to cart button
add_action( 'woocommerce_before_add_to_cart_button' , array($gePlugin, 'displayForm'));

//enqueues style.css and script.js
add_action( 'wp_enqueue_scripts', array($gePlugin , 'enqueue'), 100, 1);


/**
 * CODE FOR SENDING FORM DATA TO CART ITEM BELOW
 */

/**
 * @param $passed
 * @param $product_id
 * @param $qty
 * This function validates form data before product is added to cart
 * @return bool
 */
function kylem_add_to_cart_validation($passed, $product_id, $qty){

	//if user didn't choose a design method print error message
	if ( ! isset( $_POST['design_method'] )) {
		wc_add_notice("Error: please choose a design option");
		return false;
	}

	//if user chose to design themselves but quantity is invalid print error
	if ( $_POST['design_method'] == 'dy' && ! kylem_validQuantity()) {
		wc_add_notice("Error: invalid quantity");
		return false;
	}

	//if user chose to use a ge designer and left description blank print error message
	if ( $_POST['design_method'] == 'ge' && sanitize_textarea_field( $_POST['ge_description'] == "" ) ) {
		wc_add_notice("Error: please add a description");
		return false;
	}

	return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'kylem_add_to_cart_validation', 10, 3 );


/*** form data to cart item.
 *
 * @param array $cart_item_data
 * @param int   $product_id
 * @param int   $variation_id
 *
 * @return array
 */
function kylem_add_data_to_cart_item( $cart_item_data, $product_id, $variation_id ) {

	//if user chose to design themselves
	if($_POST["design_method"] == "dy") {
		//sending data to cart item
		$cart_item_data['price'] = kylem_calcPrice();
		$cart_item_data['design-method'] = 'dy';
		$cart_item_data['colors-front'] = $_POST['colors_front'];
		$cart_item_data['colors-back'] = $_POST['colors_back'];
		$cart_item_data['xs-quantity'] = sanitize_text_field($_POST['xs_quantity']);
		$cart_item_data['s-quantity'] = sanitize_text_field($_POST['s_quantity']);
		$cart_item_data['m-quantity'] = sanitize_text_field($_POST['m_quantity']);
		$cart_item_data['l-quantity'] = sanitize_text_field($_POST['l_quantity']);
		$cart_item_data['xl-quantity'] = sanitize_text_field($_POST['xl_quantity']);
	}
	//if user chose ge designer
	else {
		$cart_item_data['price'] = 0;
		$cart_item_data['design-method'] = 'ge';
		$cart_item_data['ge-description'] = sanitize_textarea_field($_POST['ge_description']);
		$cart_item_data['est-quantity'] = $_POST['est_quantity'];
	}


    return $cart_item_data;
}

add_filter( 'woocommerce_add_cart_item_data', 'kylem_add_data_to_cart_item', 10, 3 );


/**
 * Get item data to display in cart
 * @param array $other_data
 * @param array $cart_item
 * @return array
 */
function kylem_get_item_data( $other_data, $cart_item ) {

	if($cart_item['design-method'] == "dy") {
		$other_data[] = array(
			'name' => __( 'Data', 'kylem' ),
			'value' => '<div>Colors Front: ' . $cart_item['colors-front'] . '</div>' .
			           '<div>Colors Back: ' . $cart_item['colors-back'] . '</div>' .
			           '<div>XS: ' . $cart_item['xs-quantity'] . '</div>' .
			           '<div>S: ' . $cart_item['s-quantity'] . '</div>' .
			           '<div>M: ' . $cart_item['m-quantity'] . '</div>' .
			           '<div>L: ' . $cart_item['l-quantity'] . '</div>' .
			           '<div>XL: ' . $cart_item['xl-quantity'] . '</div>'
		);
	}

	if($cart_item['design-method'] == 'ge') {
		$other_data[] = array(
			'name' => __( 'Data', 'kylem-plugin-textdomain' ),
			'value' => '<div>Estimated Quantity: ' . $cart_item['est-quantity'] . '</div>' .
			           '<div style = "width:300px">Description: ' . $cart_item['ge-description'] . '</div>'

		);
	}

    return $other_data;

}
add_filter( 'woocommerce_get_item_data', 'kylem_get_item_data', 10, 2 );

/**
 * Add custom data to order
 *
 * @param WC_Order_Item_Product $item
 * @param string                $cart_item_key
 * @param array                 $values
 * @param WC_Order              $order
 */
function kylem_add_metadata_to_order_items( $item, $cart_item_key, $values, $order ) {

	if($values['design-method'] == 'dy') {
		$item->add_meta_data( __( 'Colors Front', 'kylem' ), $values['colors-front'] );
		$item->add_meta_data( __( 'Colors Back', 'kylem' ), $values['colors-back'] );
		$item->add_meta_data( __( 'XS', 'kylem' ), $values['xs-quantity'] );
		$item->add_meta_data( __( 'S', 'kylem' ), $values['s-quantity'] );
		$item->add_meta_data( __( 'M', 'kylem' ), $values['m-quantity'] );
		$item->add_meta_data( __( 'L', 'kylem' ), $values['l-quantity'] );
		$item->add_meta_data( __( 'XL', 'kylem' ), $values['xl-quantity'] );

	}

	if($values['design-method'] == 'ge') {
		$item->add_meta_data( __( 'Estimated Quantity', 'kylem' ), $values['est-quantity'] );
		$item->add_meta_data( __( 'Description', 'kylem' ), $values['ge-description'] );
	}

}

add_action( 'woocommerce_checkout_create_order_line_item', 'kylem_add_metadata_to_order_items', 10, 4 );

/** Updates price of everything in the cart
 * @param $cart_obj
 */
function kylem_before_calculate_totals( $cart_obj ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	// Iterate through each cart item
	foreach ( $cart_obj->get_cart() as $key => $value ) {
		if( isset( $value['price'] ) ) {
			$price = $value['price'];
			$value['data']->set_price( ( $price ) );
		}

	}
}
add_action( 'woocommerce_before_calculate_totals', 'kylem_before_calculate_totals', 10, 1 );

/**
 * returns the sum of the quanity of each individual size
 * @return mixed
 */
function kylem_getQuantity() {
	return $_POST['xs_quantity'] + $_POST['s_quantity'] + $_POST['m_quantity'] + $_POST['l_quantity'] + $_POST['xl_quantity'];
}

/**
 * checks if quantity is valid
 * @return bool
 */
function kylem_validQuantity() {

	//defining sizes as their respective quanitites
	$xs = sanitize_text_field($_POST['xs_quantity']);
	$s = sanitize_text_field($_POST['s_quantity']);
	$m = sanitize_text_field($_POST['m_quantity']);
	$l = sanitize_text_field($_POST['l_quantity']);
	$xl = sanitize_text_field($_POST['xl_quantity']);

	//checks if each field is positive and non-negative
	if ( !is_numeric($xs) || $xs < 0) {
		return false;
	}
	if ( !is_numeric($s) || $s < 0) {
		return false;
	}
	if ( !is_numeric($m) || $m < 0) {
		return false;
	}
	if ( !is_numeric($l) || $l < 0) {
		return false;
	}
	if ( !is_numeric($xl) || $xl < 0) {
		return false;
	}

	//checks if the quantity is positive
	if ( ($xs + $s + $m + $l + $xl) <= 0 ) {
		return false;
	}

	return true;
}
