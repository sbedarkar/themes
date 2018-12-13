<?php
/*
  PIN Payment Gateway (www.pin.net.au)
 */

class TC_Gateway_PIN extends TC_Gateway_API {

	var $plugin_name				 = 'pin';
	var $admin_name				 = '';
	var $public_name				 = '';
	var $method_img_url			 = '';
	var $admin_img_url			 = '';
	var $force_ssl;
	var $ipn_url;
	var $publishable_key, $private_key, $currency;
	var $currencies				 = array();
	var $automatically_activated	 = false;
	var $skip_payment_screen		 = false;

	function on_creation() {
		global $tc;

		$settings = get_option( 'tc_settings' );

		$this->admin_name	 = __( 'PIN', 'tc' );
		$this->public_name	 = __( 'PIN', 'tc' );
		$this->public_key	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'public_key' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'public_key' ] : '';
		$this->private_key	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'private_key' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'private_key' ] : '';
		$this->force_ssl	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] : '';
		$this->currency		 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : '';

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/pin.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-pin.png';

		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

		$currencies = array(
			"AUD"	 => __( 'AUD - Australian Dollar', 'tc' ),
			"USD"	 => __( 'USD - United States Dollar', 'tc' ),
			"NZD"	 => __( 'NZD - New Zealand Dollar', 'tc' ),
			"SGD"	 => __( 'SGD - Singaporean Dollar', 'tc' ),
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' ),
			"HKD"	 => __( 'HKD - Hong Kong Dollar', 'tc' ),
			"JPY"	 => __( 'JPY - Japanese Yen', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function enqueue_scripts() {
		global $tc, $wp;

		if ( array_key_exists( 'page_payment', $wp->query_vars ) || (isset( $wp->query_vars[ 'pagename' ] ) && preg_match( '/' . tc_get_payment_page_slug() . '/', $wp->query_vars[ 'pagename' ], $matches, PREG_OFFSET_CAPTURE, 3 )) || (isset( $wp->query_vars[ 'pagename' ] ) == tc_get_payment_page_slug()) ) {

			$settings = get_option( 'tc_settings' );
			if ( !isset( $settings[ 'gateways' ][ 'active' ] ) || !is_array( $settings[ 'gateways' ][ 'active' ] ) ) {
				$settings[ 'gateways' ][ 'active' ] = array();
			}

			if ( in_array( $this->plugin_name, $settings[ 'gateways' ][ 'active' ] ) ) {
				if ( $tc->get_setting( 'gateways->pin->is_ssl' ) ) {
					wp_enqueue_script( 'js-pin', 'https://api.pin.net.au/pin.js', array( 'jquery' ) );
				} else {
					wp_enqueue_script( 'js-pin', 'https://test-api.pin.net.au/pin.js', array( 'jquery' ) );
				}

				wp_enqueue_script( 'pin-handler', $tc->plugin_url . '/includes/gateways/pin/pin-handler.js', array( 'js-pin', 'jquery' ) );
				wp_localize_script( 'pin-handler', 'pin_vars', array(
					'publishable_api_key' => $this->public_key,
				)
				);
			}
		}
	}

	function payment_form( $cart ) {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$cart_contents = $tc->get_cart_cookie();

		$buyer_first_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'first_name_post_meta' ] : '';
		$buyer_last_name	 = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'last_name_post_meta' ] : '';

		$name = $buyer_first_name . ' ' . $buyer_last_name;

		$content = '';

		$content .= '<div id="pin_checkout_errors"><ul></ul></div>';

		$content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __( 'Enter Your Credit Card Information:', 'tc' ) . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __( 'Cardholder Name:', 'tc' ) . '</td>
          <td><input id="cc-name" type="text" value="' . esc_attr( $name ) . '" /> </td>
          </tr>';

		if ( !session_id() ) {
			session_start();
		}

		$cart_total = $_SESSION[ 'tc_cart_total' ];

		$discounted_total = isset( $_SESSION[ 'discounted_total' ] ) ? $_SESSION[ 'discounted_total' ] : '';

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
		$content .= '<input type="text" autocomplete="off" id="cc-number"/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'Expiration:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="cc-expiry-month">';
		$content .= tc_months_dropdown();
		$content .= '</select>';
		$content .= '<span> / </span>';
		$content .= '<select id="cc-expiry-year">';
		$content .= tc_years_dropdown( '', true );
		$content .= '</select>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'CVC:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" size="4" autocomplete="off" id="cc-cvc" />';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= '<span id="pin_processing" style="display: none;float: right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __( 'Processing...', 'psts' ) . '</span>';

		return $content;
	}

	function order_confirmation( $order, $payment_info = '', $cart_info = '' ) {
		global $tc;
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
			$content .= '<p>' . sprintf( __( 'Your payment via PIN for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via PIN for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();
		unset( $_SESSION[ 'card_token' ] );

		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php _e( 'PIN', 'tc' ) ?></span></h3>
			<div class="inside">
				<p class="description"><?php _e( "Accept all major credit cards directly on your site. Your sales proceeds are deposited to any Australian bank account, no merchant account required.", 'tc' ); ?> <a href="https://pin.net.au/" target="_blank"><?php _e( 'More Info &raquo;', 'tc' ) ?></a></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'PIN Mode', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'When in live mode PIN recommends you have an SSL certificate setup for the site where the checkout form will be displayed.', 'tc' ); ?> </span><br/>
							<select name="tc[gateways][pin][is_ssl]">
								<option value="1"<?php selected( $tc->get_setting( 'gateways->pin->is_ssl' ), 1 ); ?>><?php _e( 'Force SSL (Live Site)', 'tc' ) ?></option>
								<option value="0"<?php selected( $tc->get_setting( 'gateways->pin->is_ssl', 0 ), 0 ); ?>><?php _e( 'No SSL (Testing)', 'tc' ) ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PIN API Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'You must login to PIN to <a target="_blank" href="https://dashboard.pin.net.au/account">get your API credentials</a>. You can enter your test keys, then live ones when ready.', 'tc' ) ?></span>
							<p><label><?php _e( 'Secret API Key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( $tc->get_setting( 'gateways->pin->private_key' ) ); ?>" size="70" name="tc[gateways][pin][private_key]" type="text" />
								</label></p>
							<p><label><?php _e( 'Publishable API Key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( $tc->get_setting( 'gateways->pin->public_key' ) ); ?>" size="70" name="tc[gateways][pin][public_key]" type="text" />
								</label></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select PIN currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />
							<select name="tc[gateways][pin][currency]">
								<?php
								$sel_currency	 = $tc->get_setting( 'gateways->pin->currency', isset( $settings[ 'currency' ] ) ? $settings[ 'currency' ] : ''  );
								$currencies		 = $this->currencies;

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

		if ( isset( $_POST[ 'card_token' ] ) ) {
			if ( !isset( $_SESSION ) ) {
				session_start();
			}
			$_SESSION[ 'card_token' ] = $_POST[ 'card_token' ];
		}

		if ( !isset( $_SESSION[ 'card_token' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'The PIN Token was not generated correctly.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
			return false;
		}

		if ( $this->force_ssl ) {
			define( 'PIN_API_CHARGE_URL', 'https://api.pin.net.au/1/charges' );
		} else {
			define( 'PIN_API_CHARGE_URL', 'https://test-api.pin.net.au/1/charges' );
		}

		define( 'PIN_API_KEY', $this->private_key );

		$token = $_SESSION[ 'card_token' ];

		if ( $token ) {

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

			$order_id	 = $tc->generate_order_id();
			$buyer_email = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] : '';

			try {

				$args = array(
					'method'		 => 'POST',
					'httpversion'	 => '1.1',
					'timeout'		 => apply_filters( 'http_request_timeout', 30 ),
					'blocking'		 => true,
					'compress'		 => true,
					'headers'		 => array( 'Authorization' => 'Basic ' . base64_encode( PIN_API_KEY . ':' . '' ) ),
					'body'			 => array(
						'amount'		 => (int) ($total * 100),
						'currency'		 => strtolower( $this->currency ),
						'description'	 => sprintf( __( '%s Store Purchase - Order ID: %s, Email: %s', 'tc' ), get_bloginfo( 'name' ), $order_id, $buyer_email ),
						'email'			 => $buyer_email,
						'ip_address'	 => $_SESSION[ 'ip_address' ],
						'card_token'	 => $_SESSION[ 'card_token' ]
					),
					'cookies'		 => array()
				);

				$charge	 = wp_remote_post( PIN_API_CHARGE_URL, $args );
				$charge	 = json_decode( $charge[ 'body' ], true );
				$charge	 = $charge[ 'response' ];

				if ( $charge[ 'success' ] == true ) {
					$payment_info							 = array();
					$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
					$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
					$payment_info[ 'method' ]				 = sprintf( __( '%1$s Card %2$s', 'tc' ), ucfirst( $charge[ 'card' ][ 'scheme' ] ), $charge[ 'card' ][ 'display_number' ] );
					$payment_info[ 'transaction_id' ]		 = $charge[ 'token' ];
					$payment_info[ 'subtotal' ]				 = $subtotal;
					$payment_info[ 'fees_total' ]			 = $fees_total;
					$payment_info[ 'tax_total' ]			 = $tax_total;
					$payment_info[ 'total' ]				 = $total;
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
					unset( $_SESSION[ 'card_token' ] );
					$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card.', 'tc' ) );
					wp_redirect( $tc->get_payment_slug( true ) );
					exit;

					return false;
				}
			} catch ( Exception $e ) {
				unset( $_SESSION[ 'card_token' ] );
				$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card: "%s".', 'tc' ), $e->getMessage() );
				wp_redirect( $tc->get_payment_slug( true ) );
				exit;
				return false;
			}
		}
	}

	function ipn() {
		global $tc;
		$settings = get_option( 'tc_settings' );
	}

}

tc_register_gateway_plugin( 'TC_Gateway_PIN', 'pin', __( 'PIN', 'tc' ) );
?>