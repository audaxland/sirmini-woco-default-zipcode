<?php
/*
 * Plugin Name: Sirmini WoCo Default Zipcode
 * Plugin URI: http://www.sirmons.fr/sirmini-plugins/sirmini-woco-default-zipcode
 * Author: Nathanael SIRMONS
 * Author URI: http://www.sirmons.fr
 * Description: This plugin is an addon for WooCommerce, this allows to define a default zipcode and default city for shipping adresses and shipping cost calculation
 * Version: 1.0.0
 * Text Domain: sirmini-woco-default-zipcode
 * Domain Path: languages/
 * Licence: GPLv2 or later version
 */

/*  Copyright 2015 Nathanael SIRMONS  (email : sirmini-woco-default-zipcode@sirmons.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2 or
(at your option) any later version, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * @since 1.0.0
 * Sirmini_WC_Default_Zipcode is the main class for this plugin
 */
class Sirmini_WoCo_Default_Zipcode
{
	/* first instance of the Sirmini_WC_Default_Zipcode class
	 * @since 1.0.0
	 * @var object : instance of Sirmini_WC_Default_Zipcode
	 */
	protected static $_instance = null ;
	
	/* returns the first instance of this class
	 * @since 1.0.0
	 * @return object : instance of Sirmini_WC_Default_Zipcode
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) self::$_instance = new self();
		return self::$_instance ;
	}
	
	/*
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( is_null( self::$_instance ) ) self::$_instance = $this;
	}
	
	/* loader() is the first method that is executed, it is executed as soon as this current file is included
	 * @since 1.0.0
	 * @return void
	 */
	public static function loader() {
		$I = self::instance();
		add_action( 'woocommerce_loaded', array( $I, 'woco_loaded' ) ) ;
	}
	
	/* Executed once WooCommerce is loaded
	 * @since 1.0.0
	 * @hook action woocommerce_loaded : executed during the woocommerce_loaded action hook
	 * @return void
	 */
	public function woco_loaded() {
		// loads the textdomain for this plugin 
		load_plugin_textdomain( 'sirmini-woco-default-zipcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		// adds exra fields ont the shipping settings page
		add_action( 'woocommerce_shipping_settings', array( $this, 'woco_shipping_settings' ) );
		// defines the default shipping address for the shipping cost calulator
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'woco_cart_shipping_packages' ) ) ;
		// sets to default the shipping city and postcode in the order form
		add_filter( 'woocommerce_form_field_args', array( $this, 'woco_form_field_args' ), 10, 3 );
		add_filter( 'woocommerce_form_field_text', array( $this, 'woco_form_field_text' ), 10, 4 );
	}
	
	/* This function adds fields on the WooCommerce Shipping settings page to define the default zipcode
	 * and default city to use for default settings for the shipping address 
	 * @since 1.0.0
	 * @hook filter woocommerce_cart_shipping_packages : executed during the woocommerce_cart_shipping_packages filter hook
	 * @param array $settings : settings fields for the WooCommerce Shipping settings page
	 * @return array : the $settings array with added fields
	 */
	public function woco_shipping_settings( $settings ) {
		$settings[] = array( 
				'title' => esc_html__( 'Sirmini WoCo Default Zipcode', 'sirmini-woco-default-zipcode' ), 
				'type' => 'title', 
				'id' => 'sirmini_woco_daufault_zipcode' 
			);
		$settings[] = array(
				'title'   => esc_html__( 'Defaut Shipping Zipcode', 'sirmini-woco-default-zipcode' ),
				'desc'    => esc_html__( 'A default zipcode for the shipping address', 'sirmini-woco-default-zipcode' ),
				'id'      => 'sirmini_woco_default_shipping_zipcode',
				'default' => '',
				'type'    => 'text',
				'autoload'        => false,
				'desc_tip'        =>  true,
				'show_if_checked' => 'option',
			);
		$settings[] = array(
				'title'   => esc_html__( 'Defaut Shipping City', 'sirmini-woco-default-zipcode' ),
				'desc'    => esc_html__( 'A default city for the shipping address', 'sirmini-woco-default-zipcode' ),
				'id'      => 'sirmini_woco_default_shipping_city',
				'default' => '',
				'type'    => 'text',
				'autoload'        => false,
				'desc_tip'        =>  true,
				'show_if_checked' => 'option',
		);
		$settings[] = array( 
				'type' => 'sectionend', 
				'id' => 'sirmini_woco_daufault_zipcode' 
			);
		return $settings;
	}
	
	/* Applies the default shipping address for the shipping cost caluculator
	 * @since 1.0.0
	 * @hook filter woocommerce_cart_shipping_packages : executed during the woocommerce_cart_shipping_packages filter hook
	 * @param array $packages : the data used to calculate the shipping cost
	 * @return array : the $packages array with the default sipping info
	 */
	public function woco_cart_shipping_packages( $packages ){
		$zipcode = get_option( 'sirmini_woco_default_shipping_zipcode', '' );
		$city = get_option( 'sirmini_woco_default_shipping_city', '' );
		foreach ( $packages as $key => $value ) {
			if ( empty( $packages[0]['destination']['postcode'] ) ) {
				$packages[$key]['destination']['postcode'] = $zipcode ;
			}
			if ( empty( $packages[0]['destination']['city'] ) ) {
				$packages[$key]['destination']['city'] = $city ;
			}
		}
		return $packages;
	}
	
	/* Modifies the default values for shipping city and postcode
	 * @since 1.0.0
	 * @hook filter woocommerce_form_field_args : executed during the woocommerce_form_field_args filter hook
	 * @param array $args : the arguments for that field
	 * @param string $key : the name of that field
	 * @param string $value : the value for that field
	 * @return array : the $args array with modified default value
	 */
	public function woco_form_field_args( $args, $key, $value ) {
		
		if ( 'shipping_postcode' == $key && empty( $value ) && empty( $args['default'] ) ) {
			$zipcode = get_option( 'sirmini_woco_default_shipping_zipcode', '' );
			$args['default'] = $zipcode;
		}
		if ( 'shipping_city' == $key && empty( $value ) && empty( $args['default'] ) ) {
			$city = get_option( 'sirmini_woco_default_shipping_city', '' );
			$args['default'] = $city ;
		}
		return $args;
	}
	
	/* Modifies the zipcode value on the checkout page if the zipcode is not yet defined
	 * @since 1.0.0
	 * @hook filter woocommerce_form_field_text : executed during the woocommerce_form_field_text filter hook
	 * @param string $field : the html code for a form field
	 * @param string $key : the name of the field
	 * @param array $ags : the arguments for this field
	 * @param string $value : the value for this field
	 * @return string : html code for the field, modified for the shipping postcode, unchanged for other fields
	 */
	public function woco_form_field_text( $field, $key, $args, $value ) {
		if ( 'shipping_postcode' == $key && '' == $value ) {
			$zipcode = get_option( 'sirmini_woco_default_shipping_zipcode', '' );
			$field = str_replace( 'value=""', 'value="' . $zipcode . '"', $field );
		}
		return $field;
	}
}


// main : loads the plugin
Sirmini_WoCo_Default_Zipcode::loader();
