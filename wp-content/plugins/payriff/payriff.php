<?php
/*
Plugin Name: Payriff WooCommerce Payment Gateway
Plugin URI: https://payriff.az/
Description: Payriff Payment Gateway for Woocommerce
Version: 2.1.0
Author: GlobalSoft
Author URI: https://globalsoft.az/
*/

class WC_Payriff {

	/**
	 * Constructor
	 */
	public function __construct(){
		define( 'WC_PAYRIFF_VERSION', '1.0.0' );
		define( 'WC_PAYRIFF_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_PAYRIFF_PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
		define( 'WC_PAYRIFF_MAIN_FILE', __FILE__ );

		// Actions
		add_action( 'wp_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
	}

	/**
	 * Init localisations and files
	 */
	public function init() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		// Includes
		include_once( 'includes/class-payriff-gateway-woocommerce.php' );
		include_once( 'payriff-callback.php' );
		include_once( 'reversal.php' );

	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {

		$methods[] = 'WC_Gateway_Payriff';
		return $methods;

	}
}

new WC_Payriff();

add_filter( 'woocommerce_gateway_description', 'gateway_bacs_custom_fields', 20, 2 );

function gateway_bacs_custom_fields( $description, $payment_id ){

    if( 'payriff' === $payment_id ){

        ob_start(); // Start buffering



        echo '<div  class="bacs-fields" style="padding:10px 0;">';



        woocommerce_form_field( 'month', array(

            'type'          => 'select',

            'label'         => __("Aylar", "woocommerce"),

            'class'         => array('form-row-wide'),

            'required'      => true,

            'options'       => array(
				'0'         => __("Tək Ödəniş", "payriff"),

                '2'         => __("2 aylıq", "payriff"),

                '3'         => __("3 aylıq", "payriff"),

                '6'         => __("6 aylıq", "payriff"),

                '9'         => __("9 aylıq", "payriff"),

                '12'        => __("12 aylıq", "payriff"),

            ),

        ), '');



        echo '<div>';



        $description .= ob_get_clean(); // Append buffered content

    }

    return $description;

}
