<?php
/*
  iPay88 - Payment Gateway
 */

class TC_Gateway_iPay88 extends TC_Gateway_API {

	var $plugin_name				 = 'ipay';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl				 = false;
	var $ipn_url;
	var $currencyCode;
	var $currencies				 = array();
	var $live_url;
	var $merchant_id				 = '';
	var $notify_url				 = '';
	var $success_url				 = '';
	var $fail_url				 = '';
	var $automatically_activated	 = false;
	var $currency				 = '';
	var $skip_payment_screen		 = false;

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'iPay88', 'tc' );
		$this->public_name	 = __( 'iPay88', 'tc' );
		$this->live_url		 = 'https://www.mobile88.com/epayment/entry.asp';
		$this->notify_url	 = $this->ipn_url;
		$this->success_url	 = '';

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/ipay88.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-ipay88.png';

		if ( isset( $settings[ 'gateways' ][ $this->plugin_name ] ) ) {
			$this->currencyCode = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'MYR';

			$this->MerchantCode	 = $settings[ 'gateways' ][ $this->plugin_name ][ 'MerchantCode' ];
			$this->MerchantKey	 = $settings[ 'gateways' ][ $this->plugin_name ][ 'MerchantKey' ];
		}

		$this->hash_amount		 = 0;
		$this->formatted_amount	 = 0;

		$currencies = array(
			"MYR"	 => __( 'MYR - Malaysian Ringgit', 'tc' ),
			"THB"	 => __( 'THB - Thailand Baht', 'tc' ),
			"SGD"	 => __( 'SGD - Singapore Dollar', 'tc' ),
			"CNY"	 => __( 'CNY - Chinese Yuan Renminbi', 'tc' ),
			"AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"CAD"	 => __( 'CAD - Canadian Dollar', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
		);

		$this->paymenttype_options = array(
			'2'		 => __( 'Credit Card', 'tc' ),
			'6'		 => __( 'Maybank2U', 'tc' ),
			'8'		 => __( 'Alliance Online', 'tc' ),
			'10'	 => __( 'AmBank', 'tc' ),
			'14'	 => __( 'RHB', 'tc' ),
			'15'	 => __( 'Hong Leong Online', 'tc' ),
			'16'	 => __( 'FPX', 'tc' ),
			'17'	 => __( 'Mobile Money', 'tc' ),
			'20'	 => __( 'CIMB Click', 'tc' ),
			'22'	 => __( 'Web Cash', 'tc' ),
			'23'	 => __( 'MEPS Cash', 'tc' ),
			'33'	 => __( 'PayPal', 'tc' ),
			'103'	 => __( 'AffinBank', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$saved_payment_option_values = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'payment_types' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'payment_types' ] : array();

		$content = '
			<table class="tc_cart_billing">
<thead>
<tr>
<th colspan="2">' . __( 'Choose payment method:', 'tc' ) . '</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2">';
		$first	 = true;
		foreach ( $this->paymenttype_options as $payment_option => $payment_title ) {
			if ( in_array( $payment_option, $saved_payment_option_values ) ) {
				$content .= '<input type="radio" name="ipay_payment_method" value="' . esc_attr( $payment_option ) . '" ' . ($first ? 'checked' : '') . ' /> ' . $payment_title . '<br />';
				$first = false;
			}
		}
		$content .= '</td>
</tr>
</tbody></table>';


		return $content;
	}

	function process_payment( $cart ) {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

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

		$order_id			 = $tc->generate_order_id();
		$this->success_url	 = $tc->get_confirmation_slug( true, $order_id );

		if ( !isset( $_SESSION ) ) {
			session_start();
		}

		$payment_info[ 'subtotal' ]		 = $subtotal;
		$payment_info[ 'fees_total' ]	 = $fees_total;
		$payment_info[ 'tax_total' ]	 = $tax_total;

		$param_list = array();

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

		$this->format_amount( $total );

		$ipay88_args = array(
			'MerchantCode'	 => $this->MerchantCode,
			'RefNo'			 => $order_id,
			'Amount'		 => $this->formatted_amount,
			'Currency'		 => $this->currencyCode,
			'ProdDesc'		 => __( 'Order: ', 'tc' ) . $order_id,
			'UserName'		 => $buyer_full_name,
			'UserEmail'		 => $buyer_email,
			'UserContact'	 => $buyer_email,
			'ResponseURL'	 => esc_url( $tc->get_confirmation_slug( true, $order_id ) ),
			'BackendURL'	 => esc_url( $tc->get_confirmation_slug( true, $order_id ) ),
			'PaymentId'		 => $_POST[ 'ipay_payment_method' ]
		);

		$ipay88_args[ 'signature' ] = $this->generate_sha1_signature( $ipay88_args, false );

		$ipay88_form_array = array();

		foreach ( $ipay88_args as $key => $value ) {
			$ipay88_form_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		$tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );
		?>
		<form action="<?php echo esc_attr( $this->live_url ); ?>" method="post" name="ipay88_payment_form">
			<?php echo implode( '', $ipay88_form_array ); ?>
		</form>
		<script>document.forms['ipay88_payment_form'].submit();</script>
		<?php
		die;
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via iPay88 for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via iPay88 for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );
		$tc->remove_order_session_data();
		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;

		if ( isset( $_POST[ 'ErrDesc' ] ) && !empty( $_POST[ 'ErrDesc' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = $_POST[ 'ErrDesc' ];
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} else {
			$this->ipn();
		}
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;

		$settings					 = get_option( 'tc_settings' );
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'iPay88', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php
					_e( 'iPay88 is a payment gateway for Malaysia. It works by redirecting the customer to iPay88 server to make a payment and then returns the customer back to your confirmation page.', 'tc' );
					?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'iPay88 Credentials', 'tc' ) ?></th>
						<td>
							<p>
								<label><?php _e( 'Merchant Code', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'MerchantCode' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'MerchantCode' ] : ''  ); ?>" name="tc[gateways][<?php echo $this->plugin_name ?>][MerchantCode]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"></th>
						<td>
							<p>
								<label><?php _e( 'Merchant Key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'MerchantKey' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'MerchantKey' ] : ''  ); ?>" name="tc[gateways][<?php echo $this->plugin_name ?>][MerchantKey]" type="password" />
								</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Payment Types', 'tc' ); ?></th>
						<td>
							<p>
								
									<?php
									$saved_payment_option_values = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'payment_types' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'payment_types' ] : array();

									foreach ( $this->paymenttype_options as $payment_option => $payment_title ) {
										?>
                                                                            <label>
										<input type="checkbox" name="tc[gateways][<?php echo $this->plugin_name; ?>][payment_types][]" value="<?php echo esc_attr( $payment_option ); ?>" <?php
										if ( in_array( $payment_option, $saved_payment_option_values ) ) {
											echo 'checked';
										}
										?> /> <?php echo $payment_title; ?><br />
                                                                            </label>
                                                                                <?php
										   }
                                                                                ?>
								
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'iPay88 Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />

							<select name="tc[gateways][<?php echo $this->plugin_name; ?>][currency]">
								<?php
								$sel_currency = (isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] )) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'MYR';

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

	function ipn() {
		$this->check_status_response_ipay88();
	}

	function check_status_response_ipay88() {
		global $tc;
		
		$posted = stripslashes_deep( $_POST );

		if ( $this->validate_response() ) {

			$refno	 = $_POST[ 'RefNo' ];
			$transid = $_POST[ 'TransId' ];
			$estatus = $_POST[ 'Status' ];
			$errdesc = $_POST[ 'ErrDesc' ];

			$order = tc_get_order_id_by_name( $refno );

			if ( $estatus == 1 ) {
				$tc->update_order_status( $order->ID, 'order_paid' );
			}else{
				//not paid
			}
		} else {
			//echo 'INVALID RESPONSE';
		}
	}

	function validate_response() {

		$signature = $this->generate_sha1_signature( $_POST );

		if ( $_POST[ 'Signature' ] == $signature ) {
			return true;
		} else {
			return false;
		}
	}

	private function generate_sha1_signature( $params, $is_response = true ) {

		$string = '';
		if ( $is_response ) {
			$this->format_amount( str_replace( ',', '', $params[ 'Amount' ] ) );
			$string = $this->MerchantKey . $this->MerchantCode . $params[ 'PaymentId' ] . $params[ 'RefNo' ] . $this->hash_amount . $params[ 'Currency' ] . $params[ 'Status' ];
		} else {
			$string = $this->MerchantKey . $this->MerchantCode . $params[ 'RefNo' ] . $this->hash_amount . $params[ 'Currency' ];
		}

		return base64_encode( $this->hex2bin( sha1( $string ) ) );
	}

	function format_amount( $amount ) {
		if ( is_numeric( $amount ) ) {
			$this->hash_amount		 = number_format( $amount, 2, '', '' );
			$this->formatted_amount	 = number_format( $amount, 2, '.', ',' );
		}
	}

	function hex2bin( $hexSource ) {
		$bin = '';
		for ( $i = 0; $i < strlen( $hexSource ); $i = $i + 2 ) {
			$bin .= chr( hexdec( substr( $hexSource, $i, 2 ) ) );
		}

		return $bin;
	}

}

tc_register_gateway_plugin( 'TC_Gateway_iPay88', 'ipay', __( 'iPay88', 'tc' ) );
?>