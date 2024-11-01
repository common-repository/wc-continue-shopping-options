<?php
/*
Plugin Name: WC Continue Shopping Options
Description: This plugin will show options for Shop owners to select different redirecting options for customers when they are on Cart Page.
Author: Team Midriff
Author URI: http://www.midriffinfosolution.org/
Version: 3.0
*/
/*
 This file is part of WC Continue Shopping Options.
 WooCommerce Continue Shopping is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 WooCommerce Continue Shopping is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with WC Continue Shopping Options.  If not, see <http://www.gnu.org/licenses/>.
 */


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { // check if woocommerce is activated

//====== Add Continue Shopping Options setting on dashboard
add_filter( 'woocommerce_get_sections_products', 'wc_continue_shopping_section' );
function wc_continue_shopping_section( $sections ) {
	//== create new section inside product tab
	$sections['wc_continue_shopping_section'] = __( 'WC Continue Shopping Option', 'woocommerce-continue-shopping-extension' );
	return $sections;
	
}
add_filter( 'woocommerce_get_settings_products', 'wc_continue_shopping_settings', 10, 2 );
function wc_continue_shopping_settings( $settings, $current_section ) {//=== check current section
	if ( $current_section == 'wc_continue_shopping_section' ) {		$_check_redirect_to_cart_option = get_option( 'woocommerce_cart_redirect_after_add' ); //
		$continue_shopping_option = get_option( 'wc_continue_shopping_section_url' );
		$custom_link = get_option( 'custom_link_for_continue' );

		$settings_continue_option = array();
		// Setting Heading
		$settings_continue_option[] = array( 'name' => __( 'WC Continue Shopping Settings', 'woocommerce-continue-shopping-extension' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure continue shopping option', 'woocommerce-continue-shopping-extension' ), 'id' => 'wc_continue_shopping_section' );

		// Add first checkbox option
		$settings_continue_option[] = array(
				'name'           => __( 'Continue Shopping URL', 'wc-continue-shopping-options' ),
				'id'              => 'wc_continue_shopping_section_url',
				'default'         => 'shop',
				'type'            => 'select',
				'options'         => array(
					'shop_page'        => __( 'Go to the Shop Page', 'wc-continue-shopping-options' ),
					'home_page'        => __( 'Go to the Home Page', 'wc-continue-shopping-options' ),
					'recent_product' => __( 'Back to Recent Product', 'wc-continue-shopping-options' ),
					'recent_category'  => __( 'Back to the recently Category', 'wc-continue-shopping-options' ),
					'custom_link'      => __( 'Insert Custom Link', 'wc-continue-shopping-options' ),
				),
				'show_if_checked' => 'option',
			);
		
		$settings_continue_option[] = array(
					'title'       => __( 'Custom Link', 'wc-continue-shopping-options' ),
					'id'          => 'custom_link_for_continue',
					'desc'        => 'Insert Your Custom Link here (only insert link if you choose Custom link option from the dropdown )',
					'type'        => 'text',
			);
		$settings_continue_option[] = array( 'type' => 'sectionend', 'id' => 'wc_continue_shopping_section' );
		return $settings_continue_option;
	//=== redirect option check
	} else {
		return $settings;
	}
}
//=== store recent category or product page url  ====//
function wc_update__recent_url_in_transient(){ 
	if( is_shop() || is_product_category() ) :
		global $wp;
		$current_page = home_url( $wp->request );
		unset( $_COOKIE['recent_category_url'] );
		setcookie('recent_category_url', $current_page, time()+60*60*24);
    endif; 
	if( is_product() ):
		global $wp;
		$current_page = home_url( $wp->request );
		unset( $_COOKIE['recent_product_url'] );
		setcookie('recent_product_url', $current_page, time()+60*60*24);
	endif;
	
}
add_action('template_redirect', 'wc_update__recent_url_in_transient');


//================ Change redirect url on cart page ===================//


add_action( 'woocommerce_before_cart_table', 'woo_add_continue_shopping_button_to_cart' );
function woo_add_continue_shopping_button_to_cart() {
 $home_url = site_url();
	$shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
	$category_page_url = $_COOKIE['recent_category_url'];	$recent_product_url = $_COOKIE['recent_product_url'];	$continue_shopping_option = get_option( 'wc_continue_shopping_section_url' );
	$custom_link = get_option( 'custom_link_for_continue' );
	
	switch( $continue_shopping_option ):
	
	case 'home_page': // return home page url
		$redirect__url = $home_url;
	break;
	case 'shop_page': //return shop page url
		$redirect__url = $shop_page_url;
	break;
	case 'recent_product': // return recent product url
		if(empty($recent_product_url)):
			$redirect__url = $shop_page_url;
		else:
			$redirect__url = $recent_product_url;
		endif;	
		
	break;
	case 'recent_category': //return recent category url
		if(empty($category_page_url)):
			$redirect__url = $shop_page_url;
		else:
			$redirect__url = $category_page_url;
		endif;	
	break;
	case 'custom_link': // return custom url
		$redirect__url = $custom_link;
	break;
	default: // default return home page url
		$redirect__url = $home_url; 
	break;	
	endswitch;
	
 
	echo '<div class="woocommerce-message">';
		echo ' <a href="'.$redirect__url.'" class="button">Continue Shopping →</a> Would you like some more goods?';
	echo '</div>';
}


}
