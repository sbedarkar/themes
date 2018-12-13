<?php
/*
  PayPal PRO - Payment Gateway
 */

class TC_Gateway_PayPal_Pro extends TC_Gateway_API {

	var $plugin_name				 = 'paypal_pro';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl;
	var $ipn_url;
	var $currency;
	var $currencies				 = array();
	var $api_version				 = '85.0';
	var $api_endpoint			 = '';
	var $sandbox					 = false;
	var $api_username			 = '';
	var $api_password			 = '';
	var $api_signature			 = '';
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = false;

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'PayPal PRO', 'tc' );
		$this->public_name	 = __( 'Credit Card', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/paypal-pro.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-paypal-pro.png';

		$this->sandbox		 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] : TRUE;
		$this->api_endpoint	 = $this->sandbox == '0' ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$this->api_username	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'api_username' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'api_username' ] : '';
		$this->api_password	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'api_password' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'api_password' ] : '';
		$this->api_signature = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'api_signature' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'api_signature' ] : '';

		$this->force_ssl = (bool) ( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] ) && $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] );
		$this->currency	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'USD';

		$currencies = array(
			"AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
			"CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
			"CZK"	 => __( 'CZK - Czech Koruna', 'tc' ),
			"DKK"	 => __( 'DKK - Danish Krone', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"HKD"	 => __( 'HKD - Hong Kong Dollar', 'tc' ),
			"HUF"	 => __( 'HUF - Hungarian Forint', 'tc' ),
			"JPY"	 => __( 'JPY - Japanese Yen', 'tc' ),
			"NOK"	 => __( 'NOK - Norwegian Krone', 'tc' ),
			"NZD"	 => __( 'NZD - New Zealand Dollar', 'tc' ),
			"PLN"	 => __( 'PLN - Polish Zloty', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"SGD"	 => __( 'SGD - Singapore Dollar', 'tc' ),
			"SEK"	 => __( 'SEK - Swedish Krona', 'tc' ),
			"CHF"	 => __( 'CHF - Swiss Franc', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

		$buyer_first_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] : '';
		$buyer_last_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] : '';

		$name = $buyer_first_name . ' ' . $buyer_last_name;

		$content = '';

		$content .= '<div id="paymill_checkout_errors"></div>';

		$content .= '<table class="tc_cart_billing" cellpadding="10">
        <thead><tr>
          <th colspan="2">' . __( 'Enter Your Credit Card Information:', 'tc' ) . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __( 'Cardholder First Name:', 'tc' ) . '</td>
          <td><input class="card-holdername tickera-input-field" name="FIRSTNAME" type="text" value="' . esc_attr( $buyer_first_name ) . '" /> </td>
          </tr>';

		$content .= '<tr>
          <td>' . __( 'Cardholder Last Name:', 'tc' ) . '</td>
          <td><input class="card-holdername tickera-input-field" name="LASTNAME" type="text" value="' . esc_attr( $buyer_last_name ) . '" /> </td>
          </tr>';

		$content .= '<tr>
          <td>' . __( 'Street:', 'tc' ) . '</td>
          <td><input class="card-street tickera-input-field" name="STREET" type="text" value="" /> </td>
          </tr>';

		$content .= '<tr>
          <td>' . __( 'City:', 'tc' ) . '</td>
          <td><input class="card-city tickera-input-field" name="CITY" type="text" value="" /> </td>
          </tr>';

		$content .= '<tr>
          <td>' . __( 'State or province:', 'tc' ) . '</td>
          <td><input class="card-state tickera-input-field" name="STATE" type="text" value="" /> </td>
          </tr>';

		$content .= '<tr>
          <td>' . __( 'Country:', 'tc' ) . '</td>
          <td>' . tc_countries( '', 'COUNTRYCODE' ) . '</td>
          </tr>';

		$content .= '<tr>
          <td>' . __( 'ZIP Code:', 'tc' ) . '</td>
          <td><input class="card-state tickera-input-field" name="ZIP" "type="text" value="" /> </td>
          </tr>';

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		if ( !session_id() ) {
			session_start();
		}

		$cart_total			 = $_SESSION[ 'tc_cart_total' ];
		$discounted_total	 = isset( $_SESSION[ 'discounted_total' ] ) ? $_SESSION[ 'discounted_total' ] : '';

		if ( isset( $discounted_total ) && is_numeric( $discounted_total ) ) {
			$total = round( $discounted_total, 2 );
		} else {
			$total = round( $cart_total, 2 );
		}

		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'Card Number', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" name="ACCT" autocomplete="off" class="card-number tickera-input-field"/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'Expiration:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select class="card-expiry-month" name="CARD_MONTH">';
		$content .= tc_months_dropdown();
		$content .= '</select>';
		$content .= '<span> / </span>';
		$content .= '<select class="card-expiry-year" name="CARD_YEAR">';
		$content .= tc_years_dropdown( '', true );
		$content .= '</select>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'CVC:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" size="4" autocomplete="off" name="CCV2" class="card-cvc tickera-input-field" />';
		$content .= '<input type="hidden" name="CURRENCYCODE" value="' . $this->currency . '" />';
		$content .= '<input type="hidden" class="AMT" value="' . $total . '" />';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= '<span id="paypal_processing" style="display: none;float: right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __( 'Processing...', 'psts' ) . '</span>';
		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;
	}

	function order_confirmation_email( $msg, $order = null ) {
		return $msg;
	}

	function order_confirmation_message( $order ) {
		global $tc;

		$cart_info = $_SESSION[ 'cart_info' ];

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayPal for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayPal for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();

		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php _e( 'PayPal PRO', 'tc' ) ?></span></h3>
			<div class="inside">
				<p class="description"><?php _e( "PayPal Payments Pro is an affordable website payment processing solution for businesses with more than 100+ orders/month. Our integration with PayPal PRO will appear seamlessly to your customers.", 'tc' ); ?> <a href="https://www.paymill.com/en-gb/support-3/worth-knowing/pci-security/" target="_blank"><?php _e( 'Read More &raquo;', 'tc' ) ?></a></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'PayPal Mode', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'When in live mode PayPal PRO requires a SSL certificate setup for the site where the checkout form will be displayed.', 'tc' ); ?></span><br/>
							<select name="tc[gateways][<?php echo $this->plugin_name; ?>][is_ssl]">
								<option value="1"<?php selected( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] : '', 1 ); ?>><?php _e( 'Force SSL (Live Site)', 'tc' ) ?></option>
								<option value="0"<?php selected( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] : '', 0 ); ?>><?php _e( 'No SSL (Testing / Sandbox)', 'tc' ) ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PayPal API Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php ?></span>
							<p>
								<label><?php _e( 'API Username', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'api_username' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'api_username' ] : ''  ); ?>" size="70" name="tc[gateways][<?php echo $this->plugin_name; ?>][api_username]" type="text" />
								</label>
							</p>
							<p>
								<label><?php _e( 'API Password', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'api_password' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'api_password' ] : ''  ); ?>" size="70" name="tc[gateways][<?php echo $this->plugin_name; ?>][api_password]" type="text" />
								</label>
							</p>
							<p>
								<label><?php _e( 'API Signature', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'api_signature' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'api_signature' ] : ''  ); ?>" size="70" name="tc[gateways][<?php echo $this->plugin_name; ?>][api_signature]" type="text" />
								</label>
							</p>

						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select PayPal PRO currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />
							<select name="tc[gateways][<?php echo $this->plugin_name; ?>][currency]">
								<?php
								$sel_currency = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'USD';

								$currencies = $this->currencies;

								foreach ( $currencies as $k => $v ) {
									echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html( $v, true ) . '</option>' . "\n";
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

	function process_payment( $cart ) {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

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

		$request_params = array
			(
			'METHOD'		 => 'DoDirectPayment',
			'USER'			 => $this->api_username,
			'PWD'			 => $this->api_password,
			'SIGNATURE'		 => $this->api_signature,
			'VERSION'		 => $this->api_version,
			'PAYMENTACTION'	 => 'Sale',
			'IPADDRESS'		 => $_SERVER[ 'REMOTE_ADDR' ],
			'ACCT'			 => $_POST[ 'ACCT' ],
			'EXPDATE'		 => $_POST[ 'CARD_MONTH' ] . $_POST[ 'CARD_YEAR' ],
			'CVV2'			 => $_POST[ 'CCV2' ],
			'FIRSTNAME'		 => $_POST[ 'FIRSTNAME' ],
			'LASTNAME'		 => $_POST[ 'LASTNAME' ],
			'STREET'		 => $_POST[ 'STREET' ],
			'CITY'			 => $_POST[ 'CITY' ],
			'STATE'			 => $_POST[ 'STATE' ],
			'COUNTRYCODE'	 => $_POST[ 'COUNTRYCODE' ],
			'ZIP'			 => $_POST[ 'ZIP' ],
			'AMT'			 => $total,
			'CURRENCYCODE'	 => $_POST[ 'CURRENCYCODE' ],
			'DESC'			 => __( 'Order: ' . $order_id )
		);

		$nvp_string = '';

		foreach ( $request_params as $var => $val ) {
			$nvp_string .= '&' . $var . '=' . urlencode( $val );
		}

		$response = wp_remote_post( $this->api_endpoint, array(
			'timeout'		 => 120,
			'httpversion'	 => '1.1',
			'body'			 => $request_params,
			'user-agent'	 => $tc->title,
			'sslverify'		 => false,
		) );

		if ( is_wp_error( $response ) ) {
			$error_message					 = $response->get_error_message();
			$_SESSION[ 'tc_gateway_error' ]	 = "Something went wrong:" . $error_message;
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} else {
			$nvp_response = $this->NVPToArray( $response[ 'body' ] );

			if ( $nvp_response[ 'ACK' ] == 'Success' || $nvp_response[ 'ACK' ] == 'SuccessWithWarning' ) {
				//setup our payment details
				$payment_info							 = array();
				$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
				$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
				$payment_info[ 'method' ]				 = __( 'Credit Card', 'tc' );
				$payment_info[ 'transaction_id' ]		 = $nvp_response[ 'TRANSACTIONID' ];
				$payment_info[ 'total' ]				 = $total;
				$payment_info[ 'subtotal' ]				 = $subtotal;
				$payment_info[ 'fees_total' ]			 = $fees_total;
				$payment_info[ 'tax_total' ]			 = $tax_total;
				$payment_info[ 'currency' ]				 = $this->currency;

				if ( !isset( $_SESSION ) ) {
					session_start();
				}

				$_SESSION[ 'tc_payment_info' ]	 = $payment_info;
				$paid							 = true;
				$order							 = $tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );

				wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
				exit;
			} else {
				$_SESSION[ 'tc_gateway_error' ] = $nvp_response[ 'L_LONGMESSAGE0' ];
				wp_redirect( $tc->get_payment_slug( true ) );
				exit;
			}
		}
	}

	function NVPToArray( $NVPString ) {
		$proArray = array();
		while ( strlen( $NVPString ) ) {
			// name
			$keypos				 = strpos( $NVPString, '=' );
			$keyval				 = substr( $NVPString, 0, $keypos );
			// value
			$valuepos			 = strpos( $NVPString, '&' ) ? strpos( $NVPString, '&' ) : strlen( $NVPString );
			$valval				 = substr( $NVPString, $keypos + 1, $valuepos - $keypos - 1 );
			// decoding the respose
			$proArray[ $keyval ] = urldecode( $valval );
			$NVPString			 = substr( $NVPString, $valuepos + 1, strlen( $NVPString ) );
		}
		return $proArray;
	}

	function ipn() {
		global $tc;
		$settings = get_option( 'tc_settings' );
	}

}

tc_register_gateway_plugin( 'TC_Gateway_PayPal_Pro', 'paypal_pro', __( 'PayPal PRO', 'tc' ) );
?>