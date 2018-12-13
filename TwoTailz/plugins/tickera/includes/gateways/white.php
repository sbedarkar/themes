<?php
/*
  White Payments - Payment Gateway
 */

class TC_Gateway_White extends TC_Gateway_API {

	var $plugin_name				 = 'white';
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

		$this->admin_name	 = __( 'White', 'tc' );
		$this->public_name	 = __( 'White', 'tc' );

		$this->method_img_url	 = $tc->plugin_url . 'images/gateways/whitepayment.png';
		$this->admin_img_url	 = $tc->plugin_url . 'images/gateways/small-whitepayment.png';

		if ( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'publishable_key' ] ) ) {
			$this->publishable_key	 = $settings[ 'gateways' ][ $this->plugin_name ][ 'publishable_key' ];
			$this->private_key		 = $settings[ 'gateways' ][ $this->plugin_name ][ 'private_key' ];
		}

		$this->force_ssl = (bool) ( isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] ) && $settings[ 'gateways' ][ $this->plugin_name ][ 'is_ssl' ] );
		$this->currency	 = isset( $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] ) ? $settings[ 'gateways' ][ $this->plugin_name ][ 'currency' ] : 'AED';

		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

		$currencies = array(
			"AED"	 => __( 'AED - United Arab Emirates Dirham', 'tc' ),
			"BHD"	 => __( 'BHD - Bahraini Dinar', 'tc' ),
			"USD"	 => __( 'USD - United States Dollar', 'tc' ),
		);

		$this->currencies = $currencies;
	}

	function enqueue_scripts() {
		global $tc, $wp;

		$settings = get_option( 'tc_settings' );
		if ( !isset( $settings[ 'gateways' ][ 'active' ] ) || !is_array( $settings[ 'gateways' ][ 'active' ] ) ) {
			$settings[ 'gateways' ][ 'active' ] = array();
		}

		if ( array_key_exists( 'page_payment', $wp->query_vars ) || (isset( $wp->query_vars[ 'pagename' ] ) && preg_match( '/' . tc_get_payment_page_slug() . '/', $wp->query_vars[ 'pagename' ], $matches, PREG_OFFSET_CAPTURE, 3 )) || (isset( $wp->query_vars[ 'pagename' ] ) == tc_get_payment_page_slug()) ) {
			if ( in_array( $this->plugin_name, $settings[ 'gateways' ][ 'active' ] ) ) {
				wp_enqueue_script( 'js-white', 'https://fast.whitepayments.com/whitejs/white.js', array( 'jquery' ) );
				wp_enqueue_script( 'white-token', $tc->plugin_url . '/includes/gateways/white-lib/white_token.js', array( 'js-white', 'jquery' ) );
				wp_localize_script( 'white-token', 'white', array( 'publisher_key'	 => $this->publishable_key,
					'name'			 => __( 'Please enter the full Cardholder Name.', 'tc' ),
					'number'		 => __( 'Please enter a valid Credit Card Number.', 'tc' ),
					'expiration'	 => __( 'Please choose a valid expiration date.', 'tc' ),
					'cvv2'			 => __( 'Please enter a valid card security code. This is the 3 digits on the signature panel, or 4 digits on the front of Amex cards.', 'tc' )
				) );
			}
		}
	}

	function payment_form( $cart ) {
		global $tc;
		$settings = get_option( 'tc_settings' );

		$content = '';

		$content .= '<div id="white_checkout_errors"></div>';

		$content .= '<table class="tc_cart_billing">
  <thead><tr>
  <th colspan="2">' . __( 'Enter Your Credit Card Information:', 'tc' ) . '</th>
  </tr></thead>
  <tbody>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'Card Number', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" autocomplete="off" id="cc_number"/>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'Expiration:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="cc_month">';
		$content .= tc_months_dropdown();
		$content .= '</select>';
		$content .= '<span> / </span>';
		$content .= '<select id="cc_year">';
		$content .= tc_years_dropdown( '', true );
		$content .= '</select>';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= __( 'CVC:', 'tc' );
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="text" size="4" autocomplete="off" id="cc_cvv2" />';
		$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= '<span id="white_processing" style="display:none; float:right;"><img src="' . $tc->plugin_url . 'images/loading.gif" /> ' . __( 'Processing...', 'tc' ) . '</span>';
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

		$cart_info	 = isset( $_SESSION[ 'cart_info' ] ) ? $_SESSION[ 'cart_info' ] : $cart_info;
		$order		 = tc_get_order_id_by_name( $order );
		$order		 = new TC_Order( $order->ID );

		$content = '';

		if ( $order->details->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via White Payments for this order totaling <strong>%s</strong> is not yet complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
			$content .= '<p>' . __( 'Current order status:', 'tc' ) . ' <strong>' . __( 'Pending Payment','tc' ) . '</strong></p>';
		} else if ( $order->details->post_status == 'order_fraud' ) {
			$content .= '<p>' . __( 'Your payment is under review. We will back to you soon.', 'tc' ) . '</p>';
		} else if ( $order->details->post_status == 'order_paid' ) {
			$content .= '<p>' . sprintf( __( 'Your payment via White Payments for this order totaling <strong>%s</strong> is complete.', 'tc' ), apply_filters('tc_cart_currency_and_format', $order->details->tc_payment_info[ 'total' ] ) ) . '</p>';
		}

		$content = apply_filters( 'tc_order_confirmation_message_content_' . $this->plugin_name, $content );

		$content = apply_filters( 'tc_order_confirmation_message_content', $content, $order );

		$tc->remove_order_session_data();
		unset( $_SESSION[ 'stripeToken' ] );

		return $content;
	}

	function gateway_admin_settings( $settings, $visible ) {
		global $tc;
		?>
		<div id="<?php echo $this->plugin_name; ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
			<h3 class='hndle'><span><?php _e( 'White Payments', 'tc' ) ?></span></h3>
			<div class="inside">
				<p class="description"><?php _e( "Accept cards directly on your site. Credit cards go directly to White's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'tc' ); ?> <a href="https://whitepayments.com/" target="_blank"><?php _e( 'More Info &raquo;', 'tc' ) ?></a></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'White Mode', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'When in live mode it is recommended that you have an SSL certificate setup for the site where the checkout form will be displayed.', 'tc' ); ?></span><br/>
							<select name="tc[gateways][white][is_ssl]">
								<option value="1"<?php selected( $settings[ 'gateways' ][ 'white' ][ 'is_ssl' ], 1 ); ?>><?php _e( 'Force SSL', 'tc' ) ?></option>
								<option value="0"<?php selected( $settings[ 'gateways' ][ 'white' ][ 'is_ssl' ], 0 ); ?>><?php _e( 'No SSL', 'tc' ) ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'White API Credentials', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'You must login to White to <a target="_blank" href="https://dashboard.whitepayments.com/">get your API credentials</a>.', 'tc' ) ?></span>
							<p><label><?php _e( 'Secret key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ 'white' ][ 'private_key' ] ) ? $settings[ 'gateways' ][ 'white' ][ 'private_key' ] : ''  ); ?>" size="70" name="tc[gateways][white][private_key]" type="text" />
								</label></p>
							<p><label><?php _e( 'Publishable key', 'tc' ) ?><br />
									<input value="<?php echo esc_attr( isset( $settings[ 'gateways' ][ 'white' ][ 'publishable_key' ] ) ? $settings[ 'gateways' ][ 'white' ][ 'publishable_key' ] : ''  ); ?>" size="70" name="tc[gateways][white][publishable_key]" type="text" />
								</label></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Currency', 'tc' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Selecting a currency other than that used for your store may cause problems at checkout.', 'tc' ); ?></span><br />
							<select name="tc[gateways][white][currency]">
								<?php
								$sel_currency = isset( $settings[ 'gateways' ][ 'white' ][ 'currency' ] ) ? $settings[ 'gateways' ][ 'white' ][ 'currency' ] : 'AED';

								$currencies = $this->currencies;

								foreach ( $currencies as $k => $v ) {
									echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html( $v, true ) . '</option>' . "\n";
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

		$_SESSION[ 'whiteToken' ] = $_POST[ 'whiteToken' ];

		$cart_contents = $tc->get_cart_cookie();

		if ( !isset( $_SESSION[ 'whiteToken' ] ) ) {
			$tc->cart_checkout_error( __( 'The White Token was not generated correctly. Please go back and try again.', 'tc' ) );
			return false;
		}

		if ( !class_exists( 'White' ) ) {
			require_once($tc->plugin_dir . "/includes/gateways/white-lib/White.php");
		}

		White::setApiKey( $this->private_key );

		if ( !session_id() ) {
			session_start();
		}

		$cart_total = $_SESSION[ 'tc_cart_total' ];

		$subtotal	 = $_SESSION[ 'tc_cart_subtotal' ];
		$fees_total	 = $_SESSION[ 'tc_total_fees' ];
		$tax_total	 = $_SESSION[ 'tc_tax_value' ];

		$discounted_total								 = isset( $_SESSION[ 'discounted_total' ] ) ? $_SESSION[ 'discounted_total' ] : '';
		$_SESSION[ 'cart_info' ][ 'gateway' ]			 = $this->plugin_name;
		$_SESSION[ 'cart_info' ][ 'gateway_admin_name' ] = $this->admin_name;
		$_SESSION[ 'cart_info' ][ 'gateway_class' ]		 = get_class( $this );
		$payment_info[ 'subtotal' ]						 = $subtotal;
		$payment_info[ 'fees_total' ]					 = $fees_total;
		$payment_info[ 'tax_total' ]					 = $tax_total;

		$cart_info = $_SESSION[ 'cart_info' ];

		if ( isset( $discounted_total ) && is_numeric( $discounted_total ) ) {
			$total = round( $discounted_total, 2 );
		} else {
			$total = round( $cart_total, 2 );
		}

		$order_id	 = $tc->generate_order_id();
		$buyer_email = isset( $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] ) ? $_SESSION[ 'cart_info' ][ 'buyer_data' ][ 'email_post_meta' ] : '';

		try {

			$charge = White_Charge::create( array(
				"amount"		 => $total * 100,
				"currency"		 => strtolower( $this->currency ),
				"card"			 => $_SESSION[ 'whiteToken' ],
				"description"	 => sprintf( __( '%s Store Purchase - Order ID - %s, Email - %s', 'tc' ), get_bloginfo( 'name' ), $order_id, $buyer_email ),
				"email"			 => $buyer_email,
				"ip"			 => $_SERVER[ 'REMOTE_ADDR' ] )
			);


			if ( $charge[ 'object' ] == 'charge' ) {

				$payment_info							 = array();
				$payment_info[ 'gateway_public_name' ]	 = $this->public_name;
				$payment_info[ 'gateway_private_name' ]	 = $this->admin_name;
				$payment_info[ 'method' ]				 = sprintf( __( '%1$s Card ending in %2$s - Expires %3$s', 'tc' ), $charge[ 'card' ][ 'brand' ], $charge[ 'card' ][ 'last4' ], $charge[ 'card' ][ 'exp_month' ] . '/' . $charge[ 'card' ][ 'exp_year' ] );
				$payment_info[ 'transaction_id' ]		 = $charge[ 'id' ];
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
			}
		} catch ( White_Error_Banking $e ) {
			// Since it's a decline, White_Error_Banking will be caught
			unset( $_SESSION[ 'whiteToken' ] );
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} catch ( White_Error_Request $e ) {
			// Invalid parameters were supplied to White's API
			unset( $_SESSION[ 'whiteToken' ] );
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} catch ( White_Error_Authentication $e ) {
			// Invalid API key
			unset( $_SESSION[ 'whiteToken' ] );
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} catch ( White_Error_Processing $e ) {
			// Something wrong on White's end
			unset( $_SESSION[ 'whiteToken' ] );
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} catch ( White_Error $e ) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			unset( $_SESSION[ 'whiteToken' ] );
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		} catch ( Exception $e ) {
			// Something else happened, completely unrelated to White
			unset( $_SESSION[ 'whiteToken' ] );
			$_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
			wp_redirect( $tc->get_payment_slug( true ) );
			exit;
		}
	}

	/* catch ( Exception $e ) {
	  unset( $_SESSION[ 'whiteToken' ] );
	  $_SESSION[ 'tc_gateway_error' ] = sprintf( __( 'There was an error processing your card - "%s".', 'tc' ), $e->getMessage() );
	  wp_redirect( $tc->get_payment_slug( true ) );
	  exit;
	  }
	  return false;
	  } */

	function ipn() {
		global $tc;
	}

}

tc_register_gateway_plugin( 'TC_Gateway_White', 'white', __( 'White', 'tc' ) );
?>