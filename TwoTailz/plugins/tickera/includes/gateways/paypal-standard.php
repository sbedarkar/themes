<?php
/*
  PayPal Standard - Payment Gateway
 * ENABLE AUTO-RETURN https://www.paypal.com/rs/cgi-bin/webscr?cmd=p/mer/express_return_summary-outside
 */

class TC_Gateway_PayPal_Standard extends TC_Gateway_API {

	var $plugin_name				 = 'paypal_standard';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $business, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $version, $currencyCode, $locale;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = true;

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'PayPal Standard', 'tc' );
		$this->public_name	 = __( 'PayPal', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/paypal-standard.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-paypal-standard.png';

		if ( isset( $settings[ 'gateways' ][ 'paypal_standard' ] ) ) {
			$this->currencyCode	 = isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'currency' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'currency' ] : '';
			$this->SandboxFlag	 = isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] : 'sandbox';
			$this->business		 = isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'email' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'email' ] : '';
			$this->locale		 = isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'locale' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'locale' ] : '';
		}

		$currencies = array(
			"AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
			"BRL"	 => __( 'BRL - Brazilian Real', 'tc' ),
			"CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
			"CZK"	 => __( 'CZK - Czech Koruna', 'tc' ),
			"DKK"	 => __( 'DKK - Danish Krone', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"HKD"	 => __( 'HKD - Hong Kong Dollar', 'tc' ),
			"HUF"	 => __( 'HUF - Hungarian Forint', 'tc' ),
			"ILS"	 => __( 'ILS - Israeli New Shekel', 'tc' ),
			"JPY"	 => __( 'JPY - Japanese Yen', 'tc' ),
			"MYR"	 => __( 'MYR - Malaysian Ringgit', 'tc' ),
			"MXN"	 => __( 'MXN - Mexican Peso', 'tc' ),
			"NOK"	 => __( 'NOK - Norwegian Krone', 'tc' ),
			"NZD"	 => __( 'NZD - New Zealand Dollar', 'tc' ),
			"PHP"	 => __( 'PHP - Philippine Peso', 'tc' ),
			"PLN"	 => __( 'PLN - Polish Zloty', 'tc' ),
			"GBP"	 => __( 'GBP - Pound Sterling', 'tc' ),
			"RUB"	 => __( 'RUB - Russian Ruble', 'tc' ),
			"SGD"	 => __( 'SGD - Singapore Dollar', 'tc' ),
			"SEK"	 => __( 'SEK - Swedish Krona', 'tc' ),
			"CHF"	 => __( 'CHF - Swiss Franc', 'tc' ),
			"TWD"	 => __( 'TWD - Taiwan New Dollar', 'tc' ),
			"TRY"	 => __( 'TRY - Turkish Lira', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
			"THB"	 => __( 'THB - Thai Baht', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
		if ( isset( $_GET[ 'paypal_cancel' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Your transaction has been canceled.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function process_payment( $cart ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		$cart_contents	 = $tc->get_cart_cookie();

		$buyer_first_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] : '';
		$buyer_last_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] : '';
		$buyer_full_name	 = $buyer_first_name . ' ' . $buyer_last_name;
		$buyer_email		 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] : '';

		if ( !session_id() ) {
			session_start();
		}

		$cart_total = $_SESSION[ 'tc_cart_total' ];

		$discounted_total								 = isset( $_SESSION[ 'discounted_total' ] ) ? $_SESSION[ 'discounted_total' ] : '';
		$_SESSION[ 'cart_info' ][ 'gateway' ]			 = $this->plugin_name;
		$_SESSION[ 'cart_info' ][ 'gateway_admin_name' ] = $this->admin_name;
		$_SESSION[ 'cart_info' ][ 'gateway_class' ]		 = get_class( $this );
		$subtotal										 = $_SESSION[ 'tc_cart_subtotal' ];
		$fees_total										 = $_SESSION[ 'tc_total_fees' ];
		$tax_total										 = $_SESSION[ 'tc_tax_value' ];


		$cart_info = $_SESSION[ 'cart_info' ];

		if ( isset( $discounted_total ) && is_numeric( $discounted_total ) ) {
			$total = round( $discounted_total, 2 );
		} else {
			$total = round( $cart_total, 2 );
		}

		$order_id = $tc->generate_order_id();

		$params						 = array();
		$params[ 'no_shipping' ]	 = '1'; //do not prompt for an address
		$params[ 'cmd' ]			 = '_xclick';
		$params[ 'business' ]		 = $this->business;
		$params[ 'currency_code' ]	 = $this->currencyCode;
		$params[ 'item_name' ]		 = apply_filters( 'tc_item_name_paypal_standard', $order_id );
		$params[ 'amount' ]			 = $total;
		$params[ 'custom' ]			 = $order_id;
		$params[ 'return' ]			 = $tc->get_confirmation_slug( true, $order_id );
		$params[ 'cancel_return' ]	 = apply_filters( 'tc_paypal_standard_cancel_url', $tc->get_payment_slug( true ) . '?paypal_cancel' );
		$params[ 'notify_url' ]		 = $this->ipn_url;
		$params[ 'charset' ]		 = apply_filters( 'paypal_standard_charset', 'UTF-8' );
		$params[ 'rm' ]				 = '2'; //the buyer's browser is redirected to the return URL by using the POST method, and all payment variables are included
		$params[ 'lc' ]				 = $this->locale;
		$params[ 'email' ]			 = $buyer_email;
		$params[ 'first_name' ]		 = $buyer_first_name;
		$params[ 'last_name' ]		 = $buyer_last_name;


		if ( $this->SandboxFlag == 'live' ) {
			$url = 'https://www.paypal.com/cgi-bin/webscr';
		} else {
			$params[ 'demo' ]	 = 'Y';
			$url				 = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$payment_info[ 'subtotal' ]		 = $subtotal;
		$payment_info[ 'fees_total' ]	 = $fees_total;
		$payment_info[ 'tax_total' ]	 = $tax_total;

		$param_list = array();

		foreach ( $params as $k => $v ) {
			$param_list[] = "{$k}=" . rawurlencode( $v );
		}

		$param_str = implode( '&', $param_list );

		$paid = false;

		$payment_info							 = array();
		$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
		$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
		$payment_info[ 'method' ]				 = $this->admin_name;
		//$payment_info[ 'transaction_id' ]		 = $charge[ 'id' ];
		$payment_info[ 'total' ]				 = $total;
		$payment_info[ 'subtotal' ]				 = $subtotal;
		$payment_info[ 'fees_total' ]			 = $fees_total;
		$payment_info[ 'tax_total' ]			 = $tax_total;
		$payment_info[ 'currency' ]				 = $this->currency;

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$_SESSION[ 'tc_payment_info' ] = $payment_info;

		$tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );

		wp_redirect( "{$url}?{$param_str}" );

		exit( 0 );
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayPal for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters( 'tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment', 'tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayPal for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters( 'tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();

		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		if ( isset( $_POST[ 'payment_status' ] ) || isset( $_POST[ 'txn_type' ] ) ) {
			echo '';

			$total		 = $_REQUEST[ 'mc_gross' ];
			$order_var	 = $_REQUEST[ 'custom' ];
			$order		 = tc_get_order_id_by_name( $order_var );

			$raw_post_data	 = file_get_contents( 'php://input' );
			$raw_post_array	 = explode( '&', $raw_post_data );
			$myPost			 = array();

			foreach ( $raw_post_array as $keyval ) {
				$keyval					 = explode( '=', $keyval );
				if ( count( $keyval ) == 2 )
					$myPost[ $keyval[ 0 ] ]	 = urldecode( $keyval[ 1 ] );
			}

			$req = 'cmd=_notify-validate';

			if ( function_exists( 'get_magic_quotes_gpc' ) ) {
				$get_magic_quotes_exists = true;
			}

			foreach ( $myPost as $key => $value ) {
				if ( $get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1 ) {
					$value = urlencode( stripslashes( $value ) );
				} else {
					$value = urlencode( $value );
				}
				$req .= "&$key=$value";
			}

			if ( $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] == 'sandbox' ) {
				$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$url = 'https://www.paypal.com/cgi-bin/webscr';
			}

			$args[ 'user-agent' ]	 = $tc->title;
			$args[ 'body' ]			 = $req;
			$args[ 'sslverify' ]	 = false;
			$args[ 'timeout' ]		 = 60;

			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) { //|| $response[ 'body' ] != 'VERIFIED' 
				//do nothing, wait for IPN message
			} else {//request is verified
				switch ( $_POST[ 'payment_status' ] ) {
					case 'Completed':
						$tc->update_order_payment_status( $order->ID, true );
						break;

					case 'Processed':
						$tc->update_order_payment_status( $order->ID, true );
						break;

					case 'Canceled-Reversal':
						$tc->update_order_payment_status( $order->ID, true );
						break;

					default:
					//do nothing, wait for IPN message
				}
				$tc->remove_order_session_data();
			}
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'PayPal Standard', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'Sell tickets via PayPal standard payment gateway', 'tc' ) ?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Mode', 'tc' ) ?></th>
						<td>
							<p>
								<select name="tc[gateways][paypal_standard][mode]">
									<option value="sandbox" <?php selected( isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] : 'sandbox', 'sandbox' ) ?>><?php _e( 'Sandbox', 'tc' ) ?></option>
									<option value="live" <?php selected( isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'mode' ] : 'sandbox', 'live' ) ?>><?php _e( 'Live', 'tc' ) ?></option>
								</select>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PayPal Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"></span>
							<p>
								<label><?php _e( 'PayPal E-Mail', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'email' ] ) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'email' ] : ''  ); ?>" size="30" name="tc[gateways][paypal_standard][email]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'PayPal Standard Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select PayPal currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />
							<select name="tc[gateways][paypal_standard][currency]">
								<?php
								$sel_currency	 = (isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'currency' ] )) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'currency' ] : 'USD';

								$currencies = $this->currencies;

								foreach ( $currencies as $k => $v ) {
									echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html( $v, true ) . '</option>' . "\n";
								}
								?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'PayPal Locale', 'tc' ) ?></th>
						<td>
							<select name="tc[gateways][paypal_standard][locale]">
								<?php
								$sel_locale = (isset( $settings[ 'gateways' ][ 'paypal_standard' ][ 'locale' ] )) ? $settings[ 'gateways' ][ 'paypal_standard' ][ 'locale' ] : 'US';

								$locales = array(
									'AU' => __( 'Australia', 'tc' ),
									'AT' => __( 'Austria', 'tc' ),
									'BE' => __( 'Belgium', 'tc' ),
									'CA' => __( 'Canada', 'tc' ),
									'CN' => __( 'China', 'tc' ),
									'FR' => __( 'France', 'tc' ),
									'DE' => __( 'Germany', 'tc' ),
									'HK' => __( 'Hong Kong', 'tc' ),
									'IT' => __( 'Italy', 'tc' ),
									'MX' => __( 'Mexico', 'tc' ),
									'NL' => __( 'Netherlands', 'tc' ),
									'NZ' => __( 'New Zealand', 'tc' ),
									'PL' => __( 'Poland', 'tc' ),
									'SG' => __( 'Singapore', 'tc' ),
									'ES' => __( 'Spain', 'tc' ),
									'SE' => __( 'Sweden', 'tc' ),
									'CH' => __( 'Switzerland', 'tc' ),
									'GB' => __( 'United Kingdom', 'tc' ),
									'US' => __( 'United States', 'tc' )
								);

								foreach ( $locales as $key => $value ) {
									echo '<option value="' . $key . '"' . ($key == $sel_locale ? ' selected' : '') . '>' . esc_html( $value, true ) . '</option>' . "\n";
								}
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	function process_gateway_settings( $settings ) {
		return $settings;
	}

	function ipn() {
		global $tc;
		if ( isset( $_REQUEST[ 'custom' ] ) ) {
			$this->order_confirmation( $_REQUEST[ 'custom' ] );
		}
	}

}

tc_register_gateway_plugin( 'TC_Gateway_PayPal_Standard', 'paypal_standard', __( 'PayPal Standard', 'tc' ) );
?>