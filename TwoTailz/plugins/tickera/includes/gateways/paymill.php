<?php
/*
  Paymill - Payment Gateway
 */

class TC_Gateway_Paymill extends TC_Gateway_API {

	var $plugin_name				 = 'paymill';
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

		$this->admin_name	 = __( 'Paymill', 'tc' );
		$this->public_name	 = __( 'Credit Card', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/paymill.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-paymill.png';

		if ( isset( $settings[ 'gateways' ][ 'paymill' ][ 'public_key' ] ) ) {
			$this->public_key	 = isset( $settings[ 'gateways' ][ 'paymill' ][ 'public_key' ] ) ? $settings[ 'gateways' ][ 'paymill' ][ 'public_key' ] : '';
			$this->private_key	 = isset( $settings[ 'gateways' ][ 'paymill' ][ 'private_key' ] ) ? $settings[ 'gateways' ][ 'paymill' ][ 'private_key' ] : '';
		}

		$this->force_ssl = (bool) ( isset( $settings[ 'gateways' ][ 'paymill' ][ 'is_ssl' ] ) && $settings[ 'gateways' ][ 'paymill' ][ 'is_ssl' ] );
		$this->currency	 = isset( $settings[ 'gateways' ][ 'paymill' ][ 'currency' ] ) ? $settings[ 'gateways' ][ 'paymill' ][ 'currency' ] : 'EUR';

		$currencies = array(
			"EUR"	 => __( 'EUR - Euro', 'tc' ),
			"CZK"	 => __( 'CZK - Czech Koruna', 'tc' ),
			"DKK"	 => __( 'DKK - Danish Krone', 'tc' ),
			"HUF"	 => __( 'HUF - Hungarian Forint', 'tc' ),
			"ISK"	 => __( 'ISK - Iceland Krona', 'tc' ),
			"ILS"	 => __( 'ILS - Israeli Shekel', 'tc' ),
			"LVL"	 => __( 'LVL - Latvian Lat', 'tc' ),
			"CHF"	 => __( 'CHF - Swiss Franc', 'tc' ),
			"LTL"	 => __( 'LTL - Lithuanian Litas', 'tc' ),
			"NOK"	 => __( 'NOK - Norwegian Krone', 'tc' ),
			"PLN"	 => __( 'PLN - Polish Zloty', 'tc' ),
			"SEK"	 => __( 'SEK - Swedish Krona', 'tc' ),
			"TRY"	 => __( 'TRY - Turkish Lira', 'tc' ),
			"GBP"	 => __( 'GBP - British Pound', 'tc' )
		);

		$this->currencies = $currencies;

		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	}

	function enqueue_scripts() {
		global $tc, $wp;

		$settings = get_option( 'tc_settings' );
		if ( !isset( $settings[ 'gateways' ][ 'active' ] ) || !is_array( $settings[ 'gateways' ][ 'active' ] ) ) {
			$settings[ 'gateways' ][ 'active' ] = array();
		}

		if ( array_key_exists( 'page_payment', $wp->query_vars ) || (isset( $wp->query_vars[ 'pagename' ] ) && preg_match( '/' . tc_get_payment_page_slug() . '/', $wp->query_vars[ 'pagename' ], $matches, PREG_OFFSET_CAPTURE, 3 )) || (isset( $wp->query_vars[ 'pagename' ] ) == tc_get_payment_page_slug()) ) {
			if ( in_array( $this->plugin_name, $settings[ 'gateways' ][ 'active' ] ) ) {
				wp_enqueue_script( 'js-paymill', 'https://bridge.paymill.com/', array( 'jquery' ) );
				wp_enqueue_script( 'paymill-token', $tc->plugin_url . '/includes/gateways/paymill/paymill_token.js', array( 'js-paymill', 'jquery' ) );
				wp_localize_script( 'paymill-token', 'paymill_token', array(
					'public_key'		 => $this->public_key,
					'invalid_cc_number'	 => __( 'Please enter a valid Credit Card Number.', 'tc' ),
					'invalid_expiration' => __( 'Please choose a valid Expiration Date.', 'tc' ),
					'invalid_cvc'		 => __( 'Please enter a valid Card CVC', 'tc' ),
					'expired_card'		 => __( 'Card is no longer valid or has expired', 'tc' ),
					'invalid_cardholder' => __( 'Invalid cardholder', 'tc' ),
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

		$content .= '<div id="paymill_checkout_errors"></div>';

		$content .= '<table class="tc_cart_billing">
        <thead><tr>
          <th colspan="2">' . __( 'Enter Your Credit Card Information:', 'tc' ) . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td>' . __( 'Cardholder Name:', 'tc' ) . '</td>
          <td><input class="card-holdername tickera-input-field" type="text" value="' . esc_attr( $name ) . '" /> </td>
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
		$content .= '<input type="text" autocomplete="off" class="card-number"/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'Expiration:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select class="card-expiry-month">';
		$content .= tc_months_dropdown();
		$content .= '</select>';
		$content .= '<span> / </span>';
		$content .= '<select class="card-expiry-year">';
		$content .= tc_years_dropdown( '', true );
		$content .= '</select>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'CVC:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" size="4" autocomplete="off" class="card-cvc" />';
		$content .= '<input type="hidden" class="currency" value="' . $this->currency . '" />';
		$content .= '<input type="hidden" class="amount" value="' . $total * 100 . '" />';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= '<span id="paymill_processing" style="display: none;float: right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __( 'Processing...', 'psts' ) . '</span>';
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
			$content .= '<p>' . sprintf( __( 'Your payment via Paymill for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via Paymill for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();
		unset( $_SESSION[ 'paymillToken' ] );

		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php _e( 'Paymill', 'tc' ) ?></span></h3>
			<div class="inside">
				<p class="description"><?php _e( "Accept all major credit and debit cards directly on your site. Credit cards go directly to Paymill's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'tc' ); ?> <a href="https://www.paymill.com/en-gb/support-3/worth-knowing/pci-security/" target="_blank"><?php _e( 'Read More &raquo;', 'tc' ) ?></a></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Paymill Mode', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'When in live mode Paymill recommends you have an SSL certificate setup for the site where the checkout form will be displayed.', 'tc' ); ?> <a href="https://www.paymill.com/en-gb/support-3/support/faqs/" target="_blank"><?php _e( 'More Info &raquo;', 'tc' ) ?></a></span><br/>
							<select name="tc[gateways][paymill][is_ssl]">
								<option value="1"<?php selected( $settings[ 'gateways' ][ 'paymill' ][ 'is_ssl' ], 1 ); ?>><?php _e( 'Force SSL (Live Site)', 'tc' ) ?></option>
								<option value="0"<?php selected( $settings[ 'gateways' ][ 'paymill' ][ 'is_ssl' ], 0 ); ?>><?php _e( 'No SSL (Testing)', 'tc' ) ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Paymill API Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'You must login to Paymill to <a target="_blank" href="https://app.paymill.com/en-gb/auth/login">get your API credentials</a>. You can enter your test keys, then live ones when ready.', 'tc' ) ?></span>
							<p><label><?php _e( 'Private key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ 'paymill' ][ 'private_key' ] ) ? $settings[ 'gateways' ][ 'paymill' ][ 'private_key' ] : ''  ); ?>" size="70" name="tc[gateways][paymill][private_key]" type="text" />
								</label></p>
							<p><label><?php _e( 'Public key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ 'paymill' ][ 'public_key' ] ) ? $settings[ 'gateways' ][ 'paymill' ][ 'public_key' ] : ''  ); ?>" size="70" name="tc[gateways][paymill][public_key]" type="text" />
								</label></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Select Paymill currency. Make sure it matches global currency set in the General Settings.', 'tc' ); ?></span><br />
							<select name="tc[gateways][paymill][currency]">
								<?php
								$sel_currency = isset( $settings[ 'gateways' ][ 'paymill' ][ 'currency' ] ) ? $settings[ 'gateways' ][ 'paymill' ][ 'currency' ] : $settings[ 'currency' ];

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

		if ( isset( $_POST[ 'paymillToken' ] ) ) {
			if ( !isset( $_SESSION ) ) {
				session_start();
			}
			$_SESSION[ 'paymillToken' ] = $_POST[ 'paymillToken' ];
		}

		if ( !isset( $_SESSION[ 'paymillToken' ] ) ) {
			$_SESSION[ 'tc_gateway_error' ] = __( 'The Paymill Token was not generated correctly.', 'tc' );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
			return false;
		}

		define( 'PAYMILL_API_HOST', 'https://api.paymill.com/v2/' );
		define( 'PAYMILL_API_KEY', $settings[ 'gateways' ][ 'paymill' ][ 'private_key' ] );

		$token = $_SESSION[ 'paymillToken' ];

		if ( $token ) {
			require "paymill/lib/Services/Paymill/Transactions.php";
			$transactionsObject = new Services_Paymill_Transactions( PAYMILL_API_KEY, PAYMILL_API_HOST );

			$cart_total = $_SESSION[ 'tc_cart_total' ];

			if ( !isset( $_SESSION ) ) {
				session_start();
			}

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
				$params = array(
					'amount'		 => $total * 100, //// I.e. 49 * 100 = 4900 Cents = 49 EUR
					'currency'		 => strtolower( $this->currency ), // ISO 4217
					'token'			 => $token,
					'description'	 => sprintf( __( '%s Store Purchase - Order ID: %s, Email: %s', 'tc' ), get_bloginfo( 'name' ), $order_id, $buyer_email )
				);

				$charge = $transactionsObject->create( $params );

				if ( $charge[ 'status' ] == 'closed' ) {
					//setup our payment details
					$payment_info							 = array();
					$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
					$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
					$payment_info[ 'method' ]				 = sprintf( __( '%1$s Card ending in %2$s - Expires %3$s', 'tc' ), ucfirst( $charge[ 'payment' ][ 'card_type' ] ), $charge[ 'payment' ][ 'last4' ], $charge[ 'payment' ][ 'expire_month' ] . '/' . $charge[ 'payment' ][ 'expire_year' ] );
					$payment_info[ 'transaction_id' ]		 = $charge[ 'id' ];
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
				}
			} catch ( Exception $e ) {
				unset( $_SESSION[ 'paymillToken' ] );
				$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card: "%s".', 'tc' ), $e->getMessage() );
				wp_redirect( $tc->get_payment_slug( true ) );
				exit;
				return false;
			}
		}
	}

	function ipn() {
		global $tc;
	}

}

tc_register_gateway_plugin( 'TC_Gateway_Paymill', 'paymill', __( 'Paymill', 'tc' ) );
?>