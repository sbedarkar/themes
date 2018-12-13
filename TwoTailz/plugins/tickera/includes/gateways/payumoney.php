<?php
/*
  PayUMoney - Payment Gateway
 */

class TC_Gateway_PayUMoney extends TC_Gateway_API {

	var $plugin_name				 = 'payumoney';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $API_Username, $API_Password, $mode, $returnURL, $cancelURL, $API_Endpoint, $version, $currencyCode, $locale;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = true;
	var $currency				 = 'INR';

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'PayUMoney', 'tc' );
		$this->public_name	 = __( 'PayUMoney', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/payumoney.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-payumoney.png';

		if ( isset( $settings[ 'gateways' ][ $this->plugin_name ] ) ) {
			$this->currencyCode	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'INR';
			$this->merchantid	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] : '';
			$this->salt			 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'salt' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'salt' ] : '';
			$this->mode			 = $settings[ 'gateways' ][ $this->plugin_name ][ 'mode' ];
		}

		$currencies = array(
			"INR" => __( 'INR - Indian Rupee', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		if ( isset( $_GET[ 'payumoney_failed' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Payment Failed.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}

		if ( isset( $_GET[ 'payumoney_cancelled' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Your transaction has been canceled.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function process_payment( $cart ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

		if ( $this->mode == 'sandbox' ) {
			$url = 'https://test.payu.in/_payment';
		} else {
			$url = 'https://secure.payu.in/_payment';
		}

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

		//Hash data
		$hash_data[ 'key' ]			 = $this->merchantid;
		$hash_data[ 'txnid' ]		 = $order_id; //substr( hash( 'sha256', mt_rand() . microtime() ), 0, 20 ); // Unique alphanumeric Transaction ID
		$hash_data[ 'amount' ]		 = $total;
		$hash_data[ 'productinfo' ]	 = __( 'Order: #', 'tc' ) . $order_id;
		$hash_data[ 'firstname' ]	 = $buyer_first_name;
		$hash_data[ 'email' ]		 = $buyer_email;
		$hash_data[ 'hash' ]		 = $this->calculate_hash_before_transaction( $hash_data );

		$counter	 = 0;
		$cart_total	 = 0;

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$payment_info[ 'subtotal' ]		 = $subtotal;
		$payment_info[ 'fees_total' ]	 = $fees_total;
		$payment_info[ 'tax_total' ]	 = $tax_total;

		$paid = false;

		$payment_info							 = array();
		$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
		$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
		$payment_info[ 'method' ]				 = $this->admin_name;
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

		// PayU Args
		$payu_in_args = array(
			// Merchant details
			'key'				 => $this->merchantid,
			'surl'				 => $tc->get_confirmation_slug( true, $order_id ),
			'furl'				 => $tc->get_payment_slug( true ) . '?payumoney_failed',
			'curl'				 => $tc->get_payment_slug( true ) . '?payumoney_cancelled',
			'service_provider'	 => 'payu_paisa',
			// Customer details
			'firstname'			 => $buyer_first_name,
			'lastname'			 => $buyer_last_name,
			'email'				 => $buyer_email,
			'address1'			 => '', //$order->billing_address_1,
			'address2'			 => '', //$order->billing_address_2,
			'city'				 => '', //$order->billing_city,
			'state'				 => '', //$order->billing_state,
			'zipcode'			 => '', //$order->billing_postcode,
			'country'			 => '', //$order->billing_country,
			'phone'				 => '', //$order->billing_phone,
			// Item details
			'productinfo'		 => __( 'Order: #', 'tc' ) . $order_id,
			'amount'			 => $total,
			// Pre-selection of the payment method tab
			'pg'				 => 'CC'
		);

		$payuform = '';

		foreach ( $payu_in_args as $key => $value ) {
			if ( $value ) {
				$payuform .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
			}
		}

		$payuform .= '<input type="hidden" name="txnid" value="' . $hash_data[ 'txnid' ] . '" />' . "\n";
		$payuform .= '<input type="hidden" name="hash" value="' . $hash_data[ 'hash' ] . '" />' . "\n";
		$payuform .= __( 'Redirecting to the payment page...', 'tc' );

		// The form
		echo '<form action="' . $url . '" method="POST" name="payumoney_form" id="payumoney_form">
				' . $payuform . '
				<script type="text/javascript">
					document.getElementById("payumoney_form").submit();
				</script>
			</form>';
	}

	function calculate_hash_before_transaction( $hash_data ) {

		$hash_sequence	 = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
		$hash_vars_seq	 = explode( '|', $hash_sequence );
		$hash_string	 = '';

		foreach ( $hash_vars_seq as $hash_var ) {
			$hash_string .= isset( $hash_data[ $hash_var ] ) ? $hash_data[ $hash_var ] : '';
			$hash_string .= '|';
		}

		$hash_string .= $this->salt;
		$hash_data[ 'hash' ] = strtolower( hash( 'sha512', $hash_string ) );

		return $hash_data[ 'hash' ];
	}

	function check_hash_after_transaction( $salt, $txnRs ) {

		$hash_sequence	 = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
		$hash_vars_seq	 = explode( '|', $hash_sequence );
		//generation of hash after transaction is = salt + status + reverse order of variables
		$hash_vars_seq	 = array_reverse( $hash_vars_seq );

		$merc_hash_string = $salt . '|' . $txnRs[ 'status' ];

		foreach ( $hash_vars_seq as $merc_hash_var ) {
			$merc_hash_string .= '|';
			$merc_hash_string .= isset( $txnRs[ $merc_hash_var ] ) ? $txnRs[ $merc_hash_var ] : '';
		}

		$merc_hash = strtolower( hash( 'sha512', $merc_hash_string ) );

		/* The hash is valid */
		if ( $merc_hash == $txnRs[ 'hash' ] ) {
			return true;
		} else {
			return false;
		}
	}

	function calculate_hash_before_verification( $hash_data ) {

		$hash_sequence	 = "key|command|var1";
		$hash_vars_seq	 = explode( '|', $hash_sequence );
		$hash_string	 = '';

		foreach ( $hash_vars_seq as $hash_var ) {
			$hash_string .= isset( $hash_data[ $hash_var ] ) ? $hash_data[ $hash_var ] : '';
			$hash_string .= '|';
		}

		$hash_string .= $this->salt;
		$hash_data[ 'hash' ] = strtolower( hash( 'sha512', $hash_string ) );

		return $hash_data[ 'hash' ];
	}

	function get_post_var( $name ) {
		if ( isset( $_POST[ $name ] ) ) {
			return $_POST[ $name ];
		}
		return NULL;
	}

	function get_get_var( $name ) {
		if ( isset( $_GET[ $name ] ) ) {
			return $_GET[ $name ];
		}
		return NULL;
	}

	function payu_in_transaction_verification( $txnid ) {

		$this->verification_liveurl	 = 'https://info.payu.in/merchant/postservice';
		$this->verification_testurl	 = 'https://test.payu.in/merchant/postservice';

		$host = $this->verification_liveurl;

		if ( $this->mode == 'sandbox' ) {
			$host = $this->verification_testurl;
		}

		$hash_data[ 'key' ]		 = $this->merchantid;
		$hash_data[ 'command' ]	 = 'verify_payment';
		$hash_data[ 'var1' ]	 = $txnid;
		$hash_data[ 'hash' ]	 = $this->calculate_hash_before_verification( $hash_data );

		// Call the PayU, and verify the status
		$response = $this->send_request( $host, $hash_data );

		$response = unserialize( $response );

		return $response[ 'transaction_details' ][ $txnid ][ 'status' ];
	}

	function send_request( $host, $data ) {

		$response = wp_remote_post( $host, array(
			'method'	 => 'POST',
			'body'		 => $data,
			'timeout'	 => 70,
			'sslverify'	 => false
		) );

		if ( is_wp_error( $response ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'There was a problem connecting to the payment gateway.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
		}

		if ( empty( $response[ 'body' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Empty PayUMoney response.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}

		$parsed_response = $response[ 'body' ];

		return $parsed_response;
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayUMoney for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayUMoney for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();

		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		$settings	 = get_option( 'tc_settings' );
		$order		 = tc_get_order_id_by_name( $order );
		// IPN
		if ( isset( $_POST[ 'mihpayid' ] ) ) {
			if ( isset( $_POST[ 'status' ] ) ) {
				if ( $_POST[ 'status' ] == 'success' ) {
					$paid = true;
					$tc->update_order_payment_status( $order->ID, true );
				}
			}

			$order = new TC_Order( $order->ID );

			if ( round( $_POST[ 'amount' ], 2 ) >= round( $order->details->tc_payment_info[ 'total' ], 2 ) ) {
				//Amount is OK
			} else {
				$tc->update_order_status( $order->details->ID, 'order_fraud' );
			}
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'PayUMoney', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php
					_e( 'PayUMoney works by sending the user to <a href="https://www.payumoney.com/">PayUMoney</a> to enter their payment information. Note that PayUMoney will only take payments in Indian Rupee.', 'tc' );
					?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Mode', 'tc' ) ?></th>
						<td>
							<p>
								<select name="tc[gateways][<?php echo $this->plugin_name; ?>][mode]">
									<option value="sandbox" <?php selected( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'mode' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'mode' ] : '', 'sandbox' ) ?>><?php _e( 'Test', 'tc' ) ?></option>
									<option value="live" <?php selected( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'mode' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'mode' ] : '', 'live' ) ?>><?php _e( 'Live', 'tc' ) ?></option>
								</select>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PayUMoney Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php print sprintf( __( 'Login to your <a target="_blank" href="%s">PayUMoney dashboard</a> to obtain the Merchant ID and SALT', 'tc' ), "https://www.payumoney.com/" ); ?></span>
							<p>
								<label><?php _e( 'Merchant ID', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] : ''  ); ?>" name="tc[gateways][<?php echo $this->plugin_name; ?>][merchantid]" type="text" />
								</label>
							</p>
							<p>
								<label><?php _e( 'SALT', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'salt' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'salt' ] : ''  ); ?>"  name="tc[gateways][<?php echo $this->plugin_name; ?>][salt]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'PayUMoney Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select PayUMoney currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />

							<select name="tc[gateways][<?php echo $this->plugin_name; ?>][currency]">
								<?php
								$sel_currency	 = (isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] )) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'INR';

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

}

tc_register_gateway_plugin( 'TC_Gateway_PayUMoney', 'payumoney', __( 'PayUMoney', 'tc' ) );
?>