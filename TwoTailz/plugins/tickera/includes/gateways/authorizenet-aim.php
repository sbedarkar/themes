<?php
/*
  Authorize.net (AIM) - Payment Gateway
 */

class TC_Gateway_AuthorizeNet_AIM extends TC_Gateway_API {

	var $plugin_name				 = 'authorizenet-aim';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = true;
	var $ipn_url;
	var $API_Username, $API_Password, $API_Signature, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $currencyCode, $locale;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = false;

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'Authorize.net', 'tc' );
		$this->public_name	 = __( 'Authorize.net', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/authorize.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-authorize.png';

		if ( isset( $settings[ 'gateways' ][ 'authorizenet-aim' ] ) ) {
			$this->API_Username	 = isset( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_user' ] ) ? $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_user' ] : '';
			$this->API_Password	 = isset( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_pass' ] ) ? $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_pass' ] : '';
			$this->API_Signature = isset( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_sig' ] ) ? $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_sig' ] : '';
			$this->currencyCode	 = isset( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'currency' ] ) ? $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'currency' ] : 'USD';
			$this->locale		 = isset( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'locale' ] ) ? $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'locale' ] : '';

			if ( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'mode' ] == 'sandbox' ) {
				$this->API_Endpoint	 = "https://test.authorize.net/gateway/transact.dll";
				$this->force_ssl	 = false;
			} else {
				$this->API_Endpoint = "https://secure.authorize.net/gateway/transact.dll";
			}
		}

		$currencies = array(
			'CAD'	 => __( 'CAD - Canadian Dollar', 'tc' ),
			'EUR'	 => __( 'EUR - Euro', 'tc' ),
			'GBP'	 => __( 'GBP - Pound Sterling', 'tc' ),
			'USD'	 => __( 'USD - U.S. Dollar', 'tc' )
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
		$content = '';

		if ( isset( $_GET[ 'cancel' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Your transaction has been canceled.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}

		$settings = get_option( 'tc_settings' );

		$content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __( 'Billing Information:', 'tc' ) . '</th>
        </tr></thead>
        <tbody>
       
          <tr>
            <td>' . __( 'Credit Card Number:', 'tc' ) . '*</td>
            <td>
              <input name="card_num"  id="card_num" class="credit_card_number input_field noautocomplete" type="text" size="22" maxlength="22" /><div class="hide_after_success nocard cardimage"  id="cardimage" style="background: url(' . $tc->plugin_url . 'images/card_array.png) no-repeat;"></div></td>
          </tr>
          
          <tr>
            <td>' . __( 'Expiration Date:', 'tc' ) . '*</td>
            <td>
            <label class="inputLabel" for="exp_month">' . __( 'Month', 'tc' ) . '</label>
		        <select name="exp_month" id="exp_month">
		          ' . tc_months_dropdown() . '
		        </select>
		        <label class="inputLabel" for="exp_year">' . __( 'Year', 'tc' ) . '</label>
		        <select name="exp_year" id="exp_year">
		          ' . tc_years_dropdown( '', true ) . '
		        </select>
		        </td>
          </tr>
          
          <tr>
            <td>' . __( 'CCV:', 'tc' ) . '</td>
            <td>
            <input id="card_code" name="card_code" class="input_field noautocomplete" type="text" size="4" maxlength="4" /></td>
          </tr>
  
        </tbody>
      </table>';

		return $content;
	}

	function process_payment( $cart ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

		$billing_info = $_SESSION[ 'tc_billing_info' ];

		$payment = new TC_Gateway_Worker_AuthorizeNet_AIM( $this->API_Endpoint, 'yes', ',', '', $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_user' ], $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_key' ], ($settings[ 'gateways' ][ 'authorizenet-aim' ][ 'mode' ] == 'sandbox' ) );

		$payment->transaction( $_POST[ 'card_num' ] );

		$totals = array();

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

		$payment->setParameter( "x_card_code", $_POST[ 'card_code' ] );
		$payment->setParameter( "x_exp_date ", $_POST[ 'exp_month' ] . $_POST[ 'exp_year' ] );
		$payment->setParameter( "x_amount", $total );
		$payment->setParameter( "x_currency_code", $this->currencyCode );

		$payment->setParameter( "x_description", __( 'Order ID: ', 'tc' ) . $order_id );
		$payment->setParameter( "x_invoice_num", $order_id );
		if ( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'mode' ] == 'sandbox' ) {
			$payment->setParameter( "x_test_request", true );
		} else {
			$payment->setParameter( "x_test_request", false );
		}
		$payment->setParameter( "x_duplicate_window", 30 );

		$buyer_email		 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] : '';
		$buyer_first_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] : '';
		$buyer_last_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] : '';
		$address			 = $_POST[ 'address1' ];

		$payment->setParameter( "x_first_name", $buyer_first_name );
		$payment->setParameter( "x_last_name", $buyer_last_name );
		$payment->setParameter( "x_email", $buyer_email );
		$payment->setParameter( "x_customer_ip", $_SERVER[ 'REMOTE_ADDR' ] );

		$payment->process();

		if ( $payment->isApproved() ) {
			$payment_info							 = array();
			$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
			$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
			$payment_info[ 'method' ]				 = $payment->getMethod();
			$payment_info[ 'transaction_id' ]		 = $payment->getTransactionID();
			$payment_info[ 'total' ]				 = $total;
			$payment_info[ 'subtotal' ]				 = $subtotal;
			$payment_info[ 'fees_total' ]			 = $fees_total;
			$payment_info[ 'tax_total' ]			 = $tax_total;
			$payment_info[ 'currency' ]				 = $this->currency;

			if ( !isset( $_SESSION ) ) {
				session_start();
			}

			$_SESSION[ 'tc_payment_info' ] = $payment_info;

			$paid	 = true;
			$order	 = $tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );

			wp_redirect( $tc->get_confirmation_slug( true, $order_id ) );
			exit;
		} else {
			$_SESSION[ 'tc_gateway_error' ] = $payment->getResponseText();
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function order_confirmation_email( $msg, $order = null ) {
		return $msg;
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via Authorize.net for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via Authorize.net for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();
		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php _e( 'Authorize.net AIM Settings', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e( 'A SSL certificate is required for live transactions.', 'tc' ) ?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Mode', 'tc' ) ?></th>
						<td>
							<p>
								<select name="tc[gateways][authorizenet-aim][mode]">
									<option value="sandbox" <?php selected( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'mode' ], 'sandbox' ) ?>><?php _e( 'Sandbox', 'tc' ) ?></option>
									<option value="live" <?php selected( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'mode' ], 'live' ) ?>><?php _e( 'Live', 'tc' ) ?></option>
								</select>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Gateway Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Login to your Authorize.net dashboard to obtain the API login ID and API transaction key.', 'tc' ); ?></span>
							<p>
								<label><?php _e( 'Login ID', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_user' ] ); ?>" name="tc[gateways][authorizenet-aim][api_user]" type="text" />
								</label>
							</p>
							<p>
								<label><?php _e( 'Transaction Key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'api_key' ] ); ?>" name="tc[gateways][authorizenet-aim][api_key]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<th scope="row"><?php _e( 'Currency', 'tc' ) ?></th>
					<td>
						<span class="description"><?php _e( 'Select Authorize.net currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />
						<select name="tc[gateways][authorizenet-aim][currency]">
							<?php
							$sel_currency	 = ($settings[ 'gateways' ][ 'authorizenet-aim' ][ 'currency' ]) ? $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'currency' ] : $tc->get_setting( 'currency' );
							$currencies		 = $this->currencies;

							foreach ( $currencies as $k => $v ) {
								echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html( $v, true ) . '</option>' . "\n";
							}
							?>
						</select>
					</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'MD5 Hash', 'tc' ) ?></th>
						<td>
							<input value="<?php echo esc_attr( $settings[ 'gateways' ][ 'authorizenet-aim' ][ 'md5_hash' ] ); ?>" size="32" name="tc[gateways][authorizenet-aim][md5_hash]" type="text" />
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

if ( !class_exists( 'TC_Gateway_Worker_AuthorizeNet_AIM' ) ) {

	class TC_Gateway_Worker_AuthorizeNet_AIM {

		var $login;
		var $transkey;
		var $params		 = array();
		var $results		 = array();
		var $line_items	 = array();
		var $approved	 = false;
		var $declined	 = false;
		var $error		 = true;
		var $method		 = "";
		var $fields;
		var $response;
		var $instances	 = 0;

		function __construct( $url, $delim_data, $delim_char, $encap_char, $gw_username, $gw_tran_key, $gw_test_mode ) {
			if ( $this->instances == 0 ) {
				$this->url = $url;

				$this->params[ 'x_delim_data' ]		 = ($delim_data == 'yes') ? 'TRUE' : 'FALSE';
				$this->params[ 'x_delim_char' ]		 = $delim_char;
				$this->params[ 'x_encap_char' ]		 = $encap_char;
				$this->params[ 'x_relay_response' ]	 = "FALSE";
				$this->params[ 'x_url' ]			 = "FALSE";
				$this->params[ 'x_version' ]		 = "3.1";
				$this->params[ 'x_method' ]			 = "CC";
				$this->params[ 'x_type' ]			 = "AUTH_CAPTURE";
				$this->params[ 'x_login' ]			 = $gw_username;
				$this->params[ 'x_tran_key' ]		 = $gw_tran_key;
				$this->params[ 'x_test_request' ]	 = $gw_test_mode;

				$this->instances++;
			} else {
				return false;
			}
		}

		function transaction( $cardnum ) {
			$this->params[ 'x_card_num' ] = trim( $cardnum );
		}

		function addLineItem( $id, $name, $description, $quantity, $price, $taxable = 0 ) {
			$this->line_items[] = "{$id}<|>{$name}<|>{$description}<|>{$quantity}<|>{$price}<|>{$taxable}";
		}

		function process( $retries = 1 ) {
			global $tc;

			$this->_prepareParameters();
			$query_string = rtrim( $this->fields, "&" );

			$count = 0;
			while ( $count < $retries ) {
				$args[ 'user-agent' ]	 = $tc->title;
				;
				$args[ 'body' ]			 = $query_string;
				$args[ 'sslverify' ]	 = false;
				$args[ 'timeout' ]		 = 30;

				$response = wp_remote_post( $this->url, $args );

				if ( is_array( $response ) && isset( $response[ 'body' ] ) ) {
					$this->response = $response[ 'body' ];
				} else {
					$this->response	 = "";
					$this->error	 = true;
					return;
				}

				$this->parseResults();

				if ( $this->getResultResponseFull() == "Approved" ) {
					$this->approved	 = true;
					$this->declined	 = false;
					$this->error	 = false;
					$this->method	 = $this->getMethod();
					break;
				} else if ( $this->getResultResponseFull() == "Declined" ) {
					$this->approved	 = false;
					$this->declined	 = true;
					$this->error	 = false;
					break;
				}
				$count++;
			}
		}

		function parseResults() {
			$this->results = explode( $this->params[ 'x_delim_char' ], $this->response );
		}

		function setParameter( $param, $value ) {
			$param					 = trim( $param );
			$value					 = trim( $value );
			$this->params[ $param ]	 = $value;
		}

		function setTransactionType( $type ) {
			$this->params[ 'x_type' ] = strtoupper( trim( $type ) );
		}

		function _prepareParameters() {
			foreach ( $this->params as $key => $value ) {
				$this->fields .= "$key=" . urlencode( $value ) . "&";
			}
			for ( $i = 0; $i < count( $this->line_items ); $i++ ) {
				$this->fields .= "x_line_item={$this->line_items[ $i ]}&";
			}
		}

		function getMethod() {
			if ( isset( $this->results[ 51 ] ) ) {
				return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 51 ] );
			}
			return "";
		}

		function getGatewayResponse() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 0 ] );
		}

		function getResultResponseFull() {
			$response = array( "", "Approved", "Declined", "Error" );
			return $response[ str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 0 ] ) ];
		}

		function isApproved() {
			return $this->approved;
		}

		function isDeclined() {
			return $this->declined;
		}

		function isError() {
			return $this->error;
		}

		function getResponseText() {
			return $this->results[ 3 ];
			$strip = array( $this->params[ 'x_delim_char' ], $this->params[ 'x_encap_char' ], '|', ',' );
			return str_replace( $strip, '', $this->results[ 3 ] );
		}

		function getAuthCode() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 4 ] );
		}

		function getAVSResponse() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 5 ] );
		}

		function getTransactionID() {
			return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 6 ] );
		}

	}

}

//register payment gateway plugin
tc_register_gateway_plugin( 'TC_Gateway_AuthorizeNet_AIM', 'authorizenet-aim', __( 'Authorize.net (AIM)', 'tc' ) );
?>