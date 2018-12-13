<?php
/*
  Paygate - Payment Gateway
 */

class TC_Gateway_Paygate extends TC_Gateway_API {

	var $plugin_name				 = 'paygate';
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
	var $currency				 = 'ZAR';
	var $skip_payment_screen		 = true;
	var $encryption_key			 = 'secret';

	function on_creation() {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'Paygate', 'tc' );
		$this->public_name	 = __( 'Paygate', 'tc' );
		$this->live_url		 = 'https://www.paygate.co.za/paywebv2/process.trans';
		$this->notify_url	 = $this->ipn_url;
		$this->success_url	 = '';

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/paygate.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-paygate.png';

		if ( isset( $settings[ 'gateways' ][ $this->plugin_name ] ) ) {
			$this->currencyCode		 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'ZAR';
			$this->merchant_id		 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] : '10011013800';
			$this->encryption_key	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'encryption_key' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'encryption_key' ] : 'secret';
		}

		$currencies = array(
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"USD"	 => __( 'USD - U.S. Dollar', 'tc' ),
			"ZAR"	 => __( 'ZAR - South Africa', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function payment_form( $cart ) {
		global $tc;
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

		$fields = array(
			'PAYGATE_ID'		 => $this->merchant_id,
			'REFERENCE'			 => $order_id,
			'AMOUNT'			 => $total * 100,
			'CURRENCY'			 => $this->currencyCode,
			'RETURN_URL'		 => esc_url( $tc->get_confirmation_slug( true, $order_id ) ),
			'TRANSACTION_DATE'	 => date( 'Y-m-d H:m:s' ),
			'EMAIL'				 => $buyer_email,
		);

		$checksum_source = $fields[ 'PAYGATE_ID' ] . "|" . $fields[ 'REFERENCE' ] . "|" . $fields[ 'AMOUNT' ] . "|" . $fields[ 'CURRENCY' ] . "|" . $fields[ 'RETURN_URL' ] . "|" . $fields[ 'TRANSACTION_DATE' ] . "|" . $fields[ 'EMAIL' ] . "|" . $this->encryption_key;

		$CHECKSUM = md5( $checksum_source );

		$fields[ 'CHECKSUM' ] = $CHECKSUM;

		$tc->create_order( $order_id, $cart_contents, $cart_info, $payment_info, $paid );
		?>
		<form action="<?php echo esc_attr( $this->live_url ); ?>" method="post" name="paygate">
			<?php foreach ( $fields as $field_key => $field_val ) {
				?>
				<input name="<?php echo esc_attr( $field_key ); ?>" type="hidden" value="<?php echo esc_attr( $field_val ); ?>" />
				<?php
			}
			?>
		</form>
		<script>document.forms['paygate'].submit();</script>
		<?php
		die;
	}

	function get_status() {
		global $tc;

		if ( isset( $_POST[ 'REFERENCE' ] ) ) {
			$key = $_POST[ 'REFERENCE' ]; //order id

			$order = tc_get_order_id_by_name( $key );

			if ( $_POST[ 'TRANSACTION_STATUS' ] == '1' ) {
				$tc->update_order_status( $order->ID, 'order_paid' );
			} else {
				//Payment failed
			}
		}
	}

	function order_confirmation_message( $order, $cart_info = '' ) {
		global $tc;

		$cart_info = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;

		$order = tc_get_order_id_by_name( $order );

		$order = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via Paygate for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via Paygate for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
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
			<h3 class='handle'><span><?php _e( 'Paygate', 'tc' ); ?></span></h3>
			<div class="inside">
				<span class="description"><?php
					//_e( 'Please note that gateway supports only South African Rand (ZAR) currency.', 'tc' );
					?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Paygate Credentials', 'tc' ) ?></th>
						<td>
							<p>
								<label><?php _e( 'Merchant ID', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'merchant_id' ] : '10011013800'  ); ?>" name="tc[gateways][<?php echo $this->plugin_name ?>][merchant_id]" type="text" />
								</label>
							</p>
						</td>

					</tr>
					<tr>
						<th scope="row"></th>
						<td>
							<p>
								<label><?php _e( 'Encryption Key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'encryption_key' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'encryption_key' ] : 'secret'  ); ?>" name="tc[gateways][<?php echo $this->plugin_name ?>][encryption_key]" type="text" />
								</label>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Paygate Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />

							<select name="tc[gateways][<?php echo $this->plugin_name; ?>][currency]">
								<?php
								$sel_currency	 = (isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] )) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'ZAR';

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
		$this->get_status();
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Paygate', 'paygate', __( 'Paygate', 'tc' ) );
?>