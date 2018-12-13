<?php
/*
  PayTabs - Payment Gateway
 */

class TC_Gateway_PayTabs extends TC_Gateway_API {

	var $plugin_name				 = 'paytabs';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = true;
	var $currency				 = 'USD';
	var $liveurl					 = 'https://www.paytabs.com/';
	var $language				 = 'English';

	function on_creation() {
		global $tc;
		$settings			 = get_option( 'tc_settings' );
		$tc_general_settings = get_option( 'tc_general_setting', false );

		$this->admin_name	 = __( 'PayTabs', 'tc' );
		$this->public_name	 = __( 'PayTabs', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/paytabs.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-paytabs.png';

		$this->currency = isset( $tc_general_settings[ 'currencies' ] ) ? $tc_general_settings[ 'currencies' ] : 'USD';

		if ( isset( $settings[ 'gateways' ][ $this->plugin_name ] ) ) {
			//$this->currencyCode = isset($settings['gateways'][$this->plugin_name]['currency']) ? $settings['gateways'][$this->plugin_name]['currency'] : 'USD';
			$this->merchantid	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] : '';
			$this->password		 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'password' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'password' ] : '';
			$this->language		 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'language' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'language' ] : '';
		}
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

	function get_paytabs_args( $order_id, $buyer_first_name, $buyer_last_name, $buyer_email, $total ) {
		global $tc;

		$txnid = $order_id;

		$redirect = $tc->get_confirmation_slug( true, $order_id );

		//array values for authentication
		$loginarray = array(
			'merchant_id'		 => $this->merchantid,
			'merchant_password'	 => $this->password
		);

		//authentication process begine
		$request_login		 = http_build_query( $loginarray );
		$response_data_login = $this->sendRequest( $this->liveurl . 'api/authentication', $request_login );

		//get response data from authentication (api_key)
		$object_login = json_decode( $response_data_login );

		if ( $object_login->access == 'denied' ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Merchant ID and password does not match', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}

		//store api into session variable
		$_SESSION[ 'api_key' ] = $object_login->api_key;

		// PayTabs Args
		$paytabs_args = array(
			'key'				 => $this->merchantid,
			'txnid'				 => $txnid,
			'productinfo'		 => __( 'Order #' . $txnid ),
			'firstname'			 => $buyer_first_name,
			'lastname'			 => $buyer_last_name,
			'address1'			 => '',
			'address2'			 => '',
			'zipcode'			 => '',
			'phone'				 => '',
			'api_key'			 => $_SESSION[ 'api_key' ],
			"cc_first_name"		 => $buyer_first_name,
			"cc_last_name"		 => $buyer_last_name,
			"phone_number"		 => '',
			"billing_address"	 => '',
			'state'				 => '',
			'city'				 => '',
			"postal_code"		 => '',
			'country'			 => '',
			'email'				 => $buyer_email,
			'amount'			 => $total,
			'reference_no'		 => $txnid,
			"currency"			 => strtoupper( $this->currency ),
			"title"				 => __( 'Order #', 'tc' ) . $txnid,
			'ip_customer'		 => $_SERVER[ 'REMOTE_ADDR' ],
			'ip_merchant'		 => $_SERVER[ 'SERVER_ADDR' ],
			"return_url"		 => $redirect,
			'msg_lang'			 => $this->language
		);

		$paytabs_args[ 'products_per_title' ]	 = __( 'Order #', 'tc' ) . $txnid;
		$paytabs_args[ 'ProductName' ]			 = __( 'Order #', 'tc' ) . $txnid;
		$paytabs_args[ 'quantity' ]				 = 1;
		$paytabs_args[ 'unit_price' ]			 = $total;

		$paytabs_args[ "CustomerID" ]			 = get_current_user_id();
		$paytabs_args[ "channelOfOperations" ]	 = "channelOfOperations";

		$paytabs_args	 = apply_filters( 'tc_paytabs_args', $paytabs_args );
		$pay_url		 = $this->before_process( $paytabs_args );
		return $pay_url;
	}

	/**
	 * Check process for form submittion
	 * */
	function before_process( $array ) {
		$gateway_url	 = $this->liveurl;
		$request_string	 = http_build_query( $array );
		$response_data	 = $this->sendRequest( $gateway_url . 'api/create_pay_page', $request_string );
		return $object			 = json_decode( $response_data );
	}

	function sendRequest( $gateway_url, $request_string ) {

		/* $response = wp_remote_post($gateway_url, array(
		  'method' => 'POST',
		  'timeout' => 30,
		  'body' => $request_string,
		  )
		  );

		  if (is_wp_error($response)) {
		  echo 'Something when wrong with the CURL request';
		  } else {
		  return $response;
		  } */

		$ch		 = @curl_init();
		@curl_setopt( $ch, CURLOPT_URL, $gateway_url );
		@curl_setopt( $ch, CURLOPT_POST, true );
		@curl_setopt( $ch, CURLOPT_POSTFIELDS, $request_string );
		@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		@curl_setopt( $ch, CURLOPT_HEADER, false );
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		@curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		@curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		@curl_setopt( $ch, CURLOPT_VERBOSE, true );
		$result	 = @curl_exec( $ch );
		if ( !$result )
			die( curl_error( $ch ) );

		@curl_close( $ch );

		return $result;
	}

	function process_payment( $cart ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		$cart_contents	 = $tc->get_cart_cookie();

		$order_id = $tc->generate_order_id();

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

		/* PAY TABS SPECIFIC */
		$paytabs_payment_url = $this->get_paytabs_args( $order_id, $buyer_first_name, $buyer_last_name, $buyer_email, $total );
		$paytabs_adr		 = $paytabs_payment_url->payment_url;

		//check if api is wrong or dont get payment url
		if ( $paytabs_adr == '' || $paytabs_payment_url->error_code == '0002' ) {
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'Transaction declined, Merchant information is wrong', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} else {
			$tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );
			wp_redirect( $paytabs_adr );
			exit;
		}
		/* PAY TABS SPECIFIC */
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayTabs for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PayTabs for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
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

		if ( isset( $_REQUEST[ 'payment_reference' ] ) ) {
			$request_string = array(
				'api_key'			 => $_SESSION[ 'api_key' ],
				'payment_reference'	 => $_REQUEST[ 'payment_reference' ]
			);

			$gateway_url	 = $this->liveurl . 'api/verify_payment';
			$getdataresponse = $this->sendRequest( $gateway_url, $request_string );
			$object			 = json_decode( $getdataresponse );

			if ( $object->response == '3' || $object->response == '6' ) {
				$tc->update_order_payment_status( $order->ID, true );
			} else {
				//do nothing, transaction still pending
			}
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'PayTabs', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php
					_e( 'PayTabs works by sending the user to PayTabs to enter their payment information.', 'tc' );
					?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'PayTabs Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"></span>
							<p>
								<label><?php _e( 'Merchant ID / Username', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchantid' ] : ''  ); ?>" name="tc[gateways][<?php echo $this->plugin_name; ?>][merchantid]" type="text" />
								</label>
							</p>
							<p>
								<label><?php _e( 'Password', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'password' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'password' ] : ''  ); ?>"  name="tc[gateways][<?php echo $this->plugin_name; ?>][password]" type="password" />
								</label>
							</p>
							<p>
								<label><?php _e( 'PayTabs page language', 'tc' ) ?><br />
									<?php
									$paytab_language = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'language' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'language' ] : 'English';
									?>
									<select name="tc[gateways][<?php echo $this->plugin_name; ?>][language]">
										<option value="English" <?php selected( $paytab_language, 'English', true ); ?>><?php _e( 'English', 'tc' ); ?></option>
										<option value="Arabic" <?php selected( $paytab_language, 'Arabic', true ); ?>><?php _e( 'Arabic', 'tc' ); ?></option>
									</select>
								</label>
							</p>
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

tc_register_gateway_plugin( 'TC_Gateway_PayTabs', 'paytabs', __( 'PayTabs', 'tc' ) );
?>