<?php
/*
  VoguePay - Payment Gateway
 */

class TC_Gateway_VoguePay extends TC_Gateway_API {

	var $plugin_name				 = 'voguepay';
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
	var $currency				 = 'NGN';
	var $skip_payment_screen		 = true;

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'VoguePay', 'tc' );
		$this->public_name	 = __( 'VoguePay', 'tc' );
		$this->live_url		 = 'https://voguepay.com/pay/';
		$this->notify_url	 = $this->ipn_url;
		$this->success_url	 = '';

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/voguepay.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-vogue.png';

		if ( isset( $settings[ 'gateways' ][ $this->plugin_name ] ) ) {
			$this->currencyCode	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'NGN';
			$this->merchant_id	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] : 'demo';
		}

		$currencies = array(
			"NGN" => __( 'NGN - Nigerian Naira', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
		if ( isset( $_GET[ 'voguepay_cancel' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'Your transaction has been canceled.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	function process_payment( $cart ) {
		global $tc;

		$this->fail_url = $tc->get_payment_slug( true ) . '?voguepay_cancel';

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
		header('Content-Type: text/html');
		?>
		<form method='POST' action='<?php echo $this->live_url; ?>' style="display: none;" id="voguepay_form">
			<input type='hidden' name='v_merchant_id' value='<?php echo $this->merchant_id; ?>' />
			<input type='hidden' name='merchant_ref' value='<?php echo $order_id; ?>' />
			<input type='hidden' name='memo' value='<?php
			_e( 'Order', 'tc' );
			echo ' #' . $order_id;
			?>' />
			<input type='hidden' name='total' value='<?php echo $total; ?>' />
			<input type='hidden' name='notify_url' value='<?php echo $this->notify_url; ?>' />
			<input type='hidden' name='success_url' value='<?php echo $this->success_url; ?>' />
			<input type='hidden' name='fail_url' value='<?php echo $this->fail_url; ?>' />
			<input type='hidden' name='developer_code' value='5479c315e3369' />
			<input type="submit" name="voguepay_submit" />
		</form>
		<script>
			document.getElementById( "voguepay_form" ).submit();
		</script>
		<?php
		exit( 0 );
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via VoguePay for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via VoguePay for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );
		$tc->remove_order_session_data();
		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		$this->ipn();
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;

		$settings		 = get_option( 'tc_settings' );
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='handle'><span><?php _e( 'VoguePay', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php
					_e( 'VoguePay Payment Gateway allows you to sell tickets and receive Mastercard, Verve Card and Visa Card Payments. Please note that gateway suppports only Nigerian Naira (NGN) currency.', 'tc' );
					?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'VoguePay Credentials', 'tc' ) ?></th>
						<td>
							<p>
								<label><?php _e( 'Merchant ID', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] : 'demo'  ); ?>" name="tc[gateways][<?php echo $this->plugin_name ?>][merchant_id]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'VoguePay Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />

							<select name="tc[gateways][<?php echo $this->plugin_name; ?>][currency]">
								<?php
								$sel_currency	 = (isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] )) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'NGN';

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
		global $tc;

		$settings = get_option( 'tc_settings' );

		if ( isset( $_POST[ 'transaction_id' ] ) ) {
			$transaction_id	 = $_POST[ 'transaction_id' ];
			$json			 = wp_remote_get( 'https://voguepay.com/?v_transaction_id=' . $transaction_id . '&type=json' );

			$transaction	 = json_decode( $json[ 'body' ], true );
			$transaction_id	 = $transaction[ 'transaction_id' ];
			$merchant_ref	 = $transaction[ 'merchant_ref' ];

			$order_id	 = tc_get_order_id_by_name( $merchant_ref ); //get order id from order name
			$order_id	 = $order_id->ID;

			$order		 = new TC_Order( $order_id );
			$order_total = $order->details->tc_payment_info[ 'total' ];
			$amount_paid = $transaction[ 'total' ];

			if ( $transaction[ 'status' ] == 'Approved' ) {
				if ( round( $amount_paid, 2 ) < round( $order_total, 2 ) ) {
					$tc->update_order_status( $order->ID, 'order_fraud' );
					//die('Fraud detected. Price paid ' . $amount_paid . ' and original price of ' . $order_total . ' do not match.');
					$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'Something went wrong. Price paid %s and original price of %s do not match.', 'tc' ), $amount_paid, $order_total );
					wp_redirect( $tc->get_payment_slug( true ) );
					exit;
				}
				$tc->update_order_payment_status( $order_id, true );
				//die( 'IPN Processed OK. Payment for order successfull.' );
			} else {
				//die( 'IPN Processed OK. Payment Failed' );
			}
		}
	}

}

tc_register_gateway_plugin( 'TC_Gateway_VoguePay', 'voguepay', __( 'VoguePay', 'tc' ) );
?>