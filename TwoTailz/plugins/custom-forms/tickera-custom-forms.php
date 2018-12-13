<?php
/*
  Plugin Name: Tickera - Custom Forms Add-on
  Plugin URI: http://tickera.com/
  Description: Add custom forms for buyer and each ticket type owner
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.1.1
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2015 Tickera (http://tickera.com/)
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Custom_Fields' ) ) {

	class TC_Custom_Fields {

		var $version		 = '1.1.1';
		var $title		 = 'Custom Forms';
		var $name		 = 'tc_custom_fields';
		var $dir_name	 = 'custom-forms';
		var $location	 = 'plugins';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->init_vars();
			if ( class_exists( 'TC' ) ) {//Check if Tickera plugin is active / main Tickera class exists
				global $tc;
				add_action( 'tc_load_addons', array( &$this, 'load_addons' ) );
				add_action( $tc->name . '_add_menu_items_after_ticket_templates', array( &$this, 'add_admin_menu_item_to_tc' ) );
				add_action( 'tc_csv_admin_columns', array( &$this, 'add_custom_admin_fields_in_csv_addon' ) );
				add_action( 'tc_pdf_admin_columns', array( &$this, 'add_custom_admin_fields_in_csv_addon' ) );

				add_filter( 'tc_pdf_additional_column_titles', array( &$this, 'add_custom_admin_column_titles_in_pdf' ), 10, 2 );
				add_filter( 'tc_pdf_additional_column_values', array( &$this, 'add_custom_admin_column_values_in_pdf' ), 10, 4 );

				add_filter( 'tc_csv_array', array( &$this, 'add_custom_fields_to_csv_addon_array' ), 10, 4 );
				add_filter( 'tc_admin_capabilities', array( &$this, 'append_capabilities' ) );

				if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'tc_custom_fields' ) {
					add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header' ) );
				}

				add_action( 'wp_enqueue_scripts', array( &$this, 'front_header' ) );
				add_action( 'init', array( &$this, 'register_custom_posts' ), 0 );
				add_filter( 'tc_ticket_fields', array( &$this, 'add_additional_ticket_type_fields' ) );
				add_filter( 'tc_form_field_value', array( &$this, 'modify_form_field_value' ) );
				add_filter( 'tc_buyer_info_fields', array( &$this, 'add_custom_buyer_form_fields' ) );
				add_filter( 'tc_owner_info_fields', array( &$this, 'add_custom_owner_form_fields' ), 10, 2 );
				add_filter( 'tc_order_fields', array( &$this, 'add_custom_buyer_fields_to_order_details_page' ) );
				add_filter( 'tc_owner_info_orders_table_fields', array( &$this, 'add_custom_owner_fields_to_order_details_page' ) );

				add_filter( 'tc_checkin_custom_fields', array( &$this, 'add_checkin_custom_fields' ), 10, 5 );

				add_action( 'plugins_loaded', array( &$this, 'load_virtual_tickets_elements' ) );

//load templates class
				require_once( $this->plugin_dir . 'includes/functions.php' );

//load templates class
				require_once( $this->plugin_dir . 'includes/classes/class.forms.php' );

//load templates class
				require_once( $this->plugin_dir . 'includes/classes/class.form.php' );

//load templates search class
				require_once( $this->plugin_dir . 'includes/classes/class.forms_search.php' );
			}
		}

		function tc_load_custom_form_field_elements() {
//include($this->plugin_dir . 'includes/ticket-elements/ticket_barcode_element.php');
		}

		function init_vars() {
//setup proper directories
			if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'subfolder-plugins';
				$this->plugin_dir	 = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
				$this->plugin_url	 = plugins_url( '/', __FILE__ );
			} else if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'plugins';
				$this->plugin_dir	 = WP_PLUGIN_DIR . '/';
				$this->plugin_url	 = plugins_url( '/', __FILE__ );
			} else if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'mu-plugins';
				$this->plugin_dir	 = WPMU_PLUGIN_DIR;
				$this->plugin_url	 = WPMU_PLUGIN_URL;
			} else {
				wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tc' ), $this->title ) );
			}
		}

		function add_additional_ticket_type_fields( $fields ) {

			$fields[] = array(
				'field_name'		 => 'owner_form_template',
				'field_title'		 => __( 'Owner(s) Form', 'tc' ),
				'field_type'		 => 'function',
				'function'			 => 'tc_custom_form_fields_owner_form_template_select',
				'field_description'	 => __( '', 'tc' ),
				'table_visibility'	 => false,
				'post_field_type'	 => 'post_meta'
			);

			return $fields;
		}

		function add_custom_buyer_fields_to_order_details_page( $fields ) {

			if ( (isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'tc_orders' && isset( $_GET[ 'ID' ] )) || (apply_filters( 'show_custom_fields_as_order_columns', false ) == true) ) {//Show custom fields on the orders details page only
				$forms		 = new TC_Forms();
				$buyer_form	 = $forms->get_forms( 'buyer' );
				if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[ 0 ] ) && !is_null( $buyer_form[ 0 ] )) ) {
					global $wpdb;
					$buyer_form = $buyer_form[ 0 ];

					for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
						$results = $wpdb->get_results(
						$wpdb->prepare(
						"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
						, $buyer_form->ID, $i ), OBJECT
						);

						if ( !empty( $results ) ) {

							$fields[] = array(
								'id'				 => 'separator',
								'field_name'		 => 'separator',
								'field_title'		 => '',
								'field_type'		 => 'separator',
								'field_description'	 => '',
								'table_visibility'	 => false,
								'post_field_type'	 => ''
							);

							foreach ( $results as $result ) {
								$post_meta			 = get_post_meta( $result->ID );
								$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
								if ( class_exists( $element_class_name ) ) {
									$element = new $element_class_name( $result->ID );

									if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
										$fields[] = $element->admin_order_details_page_value();
									}
								}
							}
						}
					}
				}
			}
			return $fields;
		}

		function add_custom_owner_fields_to_order_details_page( $fields ) {
			$fields[] = array(
				'id'				 => 'custom_fields',
				'field_name'		 => 'ticket_type_id',
				'field_title'		 => __( 'Custom', 'tc' ),
				'field_type'		 => 'function',
				'function'			 => 'tc_get_order_details_owner_form_fields_values',
				'field_description'	 => '',
				'post_field_type'	 => 'post_meta'
			);

			return $fields;
		}

		function add_custom_buyer_form_fields( $fields ) {
			$forms		 = new TC_Forms();
//get_post_meta( $event_id, $field_name, true );
			$buyer_form	 = $forms->get_forms( 'buyer' );
			if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[ 0 ] ) && !is_null( $buyer_form[ 0 ] )) ) {
				global $wpdb;
				$buyer_form = $buyer_form[ 0 ];

				for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
					$results = $wpdb->get_results(
					$wpdb->prepare(
					"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
					, $buyer_form->ID, $i ), OBJECT
					);

					if ( !empty( $results ) ) {
						$res = 1;
						foreach ( $results as $result ) {
							$post_meta			 = get_post_meta( $result->ID );
							$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
							if ( class_exists( $element_class_name ) ) {
								$element = new $element_class_name( $result->ID );

								if ( $res == count( $results ) ) {
									$additional_field_class = 'tc_field_col_last_child';
								} else {
									$additional_field_class = '';
								}

								$element_content = array(
									'field_name'			 => $element->standard_field_name( $element->element_name, true ),
									'field_title'			 => $element->standard_field_label( $element->element_name, true ),
									'field_placeholder'		 => $element->standard_field_placeholder( $element->element_name, true ),
									'field_values'			 => $element->standard_field_choice_values( $element->element_name, true ),
									'field_default_value'	 => $element->standard_field_choice_default_values( $element->element_name, true ),
									'field_class'			 => 'tc_field_col_' . count( $results ) . ' ' . $additional_field_class . ' ' . 'tc_' . $element->element_type . '_field',
									'field_type'			 => $element->element_type,
									'field_description'		 => $element->standard_field_description( $element->element_name, true ),
									'post_field_type'		 => 'post_meta',
									'required'				 => $element->standard_field_required( $element->element_name, true )
								);

								$fields[] = $element_content;
							}
							$res++;
						}
					}
				}
			}
			return $fields;
		}

		function add_custom_owner_form_fields( $fields, $ticket_type_id = '' ) {
			$forms		 = new TC_Forms();
			$owner_form	 = $forms->get_forms( 'owner', -1, $ticket_type_id );

			if ( count( $owner_form ) >= 1 && (isset( $owner_form[ 0 ] ) && !is_null( $owner_form[ 0 ] )) ) {
				global $wpdb;
				$owner_form = $owner_form[ 0 ];

				for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
					$results = $wpdb->get_results(
					$wpdb->prepare(
					"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
					, $owner_form->ID, $i ), OBJECT
					);

					if ( !empty( $results ) ) {
						$res = 1;
						foreach ( $results as $result ) {
							$post_meta			 = get_post_meta( $result->ID );
							$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
							if ( class_exists( $element_class_name ) ) {
								$element = new $element_class_name( $result->ID );

								if ( $res == count( $results ) ) {
									$additional_field_class = 'tc_field_col_last_child';
								} else {
									$additional_field_class = '';
								}

								$element_content = array(
									'field_name'			 => $element->standard_field_name( $element->element_name, true ),
									'field_title'			 => $element->standard_field_label( $element->element_name, true ),
									'field_placeholder'		 => $element->standard_field_placeholder( $element->element_name, true ),
									'field_values'			 => $element->standard_field_choice_values( $element->element_name, true ),
									'field_default_value'	 => $element->standard_field_choice_default_values( $element->element_name, true ),
									'field_class'			 => 'tc_field_col_' . count( $results ) . ' ' . $additional_field_class . ' ' . 'tc_' . $element->element_type . '_field',
									'field_type'			 => $element->element_type,
									'field_description'		 => $element->standard_field_description( $element->element_name, true ),
									'post_field_type'		 => 'post_meta',
									'required'				 => $element->standard_field_required( $element->element_name, true )
								);

								$fields[] = $element_content;
							}
							$res++;
						}
					}
				}
			}

			return $fields;
		}

		function add_checkin_custom_fields( $custom_fields, $ticket_instance_id, $event_id, $order, $ticket_type ) {

			$forms = new TC_Forms();

			$buyer_form = $forms->get_forms( 'buyer' );

			if ( count( $buyer_form ) >= 1 && (isset( $buyer_form[ 0 ] ) && !is_null( $buyer_form[ 0 ] )) ) {
				global $wpdb;
				$buyer_form = $buyer_form[ 0 ];

				for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
					$results = $wpdb->get_results(
					$wpdb->prepare(
					"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
					, $buyer_form->ID, $i ), OBJECT
					);

					if ( !empty( $results ) ) {

						foreach ( $results as $result ) {
							$post_meta			 = get_post_meta( $result->ID );
							$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
							if ( class_exists( $element_class_name ) ) {
								$element = new $element_class_name( $result->ID );


								$custom_field_value = $order->details->tc_cart_info[ 'buyer_data' ][ $element->standard_field_name( $element->element_name, true ) . '_post_meta' ];

								if ( isset( $custom_field_value ) && !empty( $custom_field_value ) && !is_null( $custom_field_value ) ) {
									$custom_fields[] = array( $element->standard_field_label( $element->element_name, true ), $custom_field_value ); //$custom_field_value 
								}
							}
						}
					}
				}
			}

			//Owner form

			$owner_form = $forms->get_forms( 'owner', -1, $ticket_type->details->ID );

			if ( count( $owner_form ) >= 1 && (isset( $owner_form[ 0 ] ) && !is_null( $owner_form[ 0 ] )) ) {

				global $wpdb;
				$owner_form = $owner_form[ 0 ];

				for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
					$results = $wpdb->get_results(
					$wpdb->prepare(
					"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
					, $owner_form->ID, $i ), OBJECT
					);

					if ( !empty( $results ) ) {
						foreach ( $results as $result ) {
							$post_meta			 = get_post_meta( $result->ID );
							$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
							if ( class_exists( $element_class_name ) ) {
								$element = new $element_class_name( $result->ID );

								if ( $res == count( $results ) ) {
									$additional_field_class = 'tc_field_col_last_child';
								} else {
									$additional_field_class = '';
								}

								/* $element_content = array(
								  'field_name'			 => $element->standard_field_name( $element->element_name, true ),
								  'field_title'			 => $element->standard_field_label( $element->element_name, true ),
								  'field_placeholder'		 => $element->standard_field_placeholder( $element->element_name, true ),
								  'field_values'			 => $element->standard_field_choice_values( $element->element_name, true ),
								  'field_default_value'	 => $element->standard_field_choice_default_values( $element->element_name, true ),
								  'field_class'			 => 'tc_field_col_' . count( $results ) . ' ' . $additional_field_class . ' ' . 'tc_' . $element->element_type . '_field',
								  'field_type'			 => $element->element_type,
								  'field_description'		 => $element->standard_field_description( $element->element_name, true ),
								  'post_field_type'		 => 'post_meta',
								  'required'				 => $element->standard_field_required( $element->element_name, true )
								  ); */

								$custom_field_value = get_post_meta( $ticket_instance_id, $element->standard_field_name( $element->element_name, true ), true );
								if ( isset( $custom_field_value ) && !empty( $custom_field_value ) && !is_null( $custom_field_value ) ) {
									$custom_fields[] = array( $element->standard_field_label( $element->element_name, true ), $custom_field_value );
								}
							}
						}
					}
				}
			}

			return $custom_fields;
		}

		function add_custom_admin_fields_in_csv_addon() {
			$forms			 = new TC_Forms();
			$both_form_types = $forms->get_forms( 'both', -1, '' );

			if ( count( $both_form_types ) >= 1 ) {
				global $wpdb;

				foreach ( $both_form_types as $form ) {

					for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
						$results = $wpdb->get_results(
						$wpdb->prepare(
						"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
						, $form->ID, $i ), OBJECT
						);

						if ( !empty( $results ) ) {

							foreach ( $results as $result ) {
								$post_meta			 = get_post_meta( $result->ID );
								$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
								if ( class_exists( $element_class_name ) ) {
									$element = new $element_class_name( $result->ID );

//if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
									if ( $element->standard_field_export( $element->element_name, true ) ) {
										$fields[] = $element->admin_order_details_page_value();
									}
//}
								}
							}
						}
					}
				}
			}

			foreach ( $fields as $field ) {
				?>
				<label>
					<input type="checkbox" name="<?php echo $field[ 'id' ]; ?>" checked="checked"><?php echo $field[ 'field_title' ]; ?><br />
				</label>
				<?php
			}
			?>
			<?php
		}

		function add_custom_fields_to_csv_addon_array( $tc_csv_array, $order, $ticket_instance, $post ) {
			$forms			 = new TC_Forms();
			$both_form_types = $forms->get_forms( 'both', -1, '' );

			if ( count( $both_form_types ) >= 1 ) {
				global $wpdb;


				foreach ( $both_form_types as $form ) {
					$fields		 = array();
					$form_type	 = get_post_meta( $form->ID, 'form_type', true );

					for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
						$results = $wpdb->get_results(
						$wpdb->prepare(
						"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
						, $form->ID, $i ), OBJECT
						);

						if ( !empty( $results ) ) {

							foreach ( $results as $result ) {
								$post_meta			 = get_post_meta( $result->ID );
								$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
								if ( class_exists( $element_class_name ) ) {
									$element = new $element_class_name( $result->ID );

//if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
									if ( $element->standard_field_export( $element->element_name, true ) ) {
										$fields[] = $element->admin_order_details_page_value();
									}
//}
								}
							}
						}
					}

					foreach ( $fields as $field ) {
						if ( isset( $_POST[ $field[ 'id' ] ] ) ) {

							if ( $form_type == 'owner' ) {
								$field_value	 = array( $field[ 'field_title' ] => $ticket_instance->details->$field[ 'id' ] );
								$tc_csv_array	 = array_merge( $tc_csv_array, $field_value );
							}

							if ( $form_type == 'buyer' ) {
								$field_value	 = array( $field[ 'field_title' ] => $order->details->tc_cart_info[ 'buyer_data' ][ $field[ 'id' ] . '_post_meta' ] );
								$tc_csv_array	 = array_merge( $tc_csv_array, $field_value );
							}
						}
					}
				}
			}

			return $tc_csv_array;
		}

		function add_custom_admin_column_titles_in_pdf( $rows, $post ) {
			$forms			 = new TC_Forms();
			$both_form_types = $forms->get_forms( 'both', -1, '' );

			if ( count( $both_form_types ) >= 1 ) {
				global $wpdb;

				foreach ( $both_form_types as $form ) {
					$fields		 = array();
					$form_type	 = get_post_meta( $form->ID, 'form_type', true );

					for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
						$results = $wpdb->get_results(
						$wpdb->prepare(
						"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
						, $form->ID, $i ), OBJECT
						);

						if ( !empty( $results ) ) {

							foreach ( $results as $result ) {
								$post_meta			 = get_post_meta( $result->ID );
								$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
								if ( class_exists( $element_class_name ) ) {
									$element = new $element_class_name( $result->ID );

//if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
									if ( $element->standard_field_export( $element->element_name, true ) ) {
										$fields[] = $element->admin_order_details_page_value();
									}
//}
								}
							}
						}
					}

					foreach ( $fields as $field ) {
						if ( isset( $post[ $field[ 'id' ] ] ) ) {
							$rows .= '<th align="center">' . $field[ 'field_title' ] . '</th>';
						}
					}
				}
			}

			return $rows;
		}

		function add_custom_admin_column_values_in_pdf( $rows, $order, $ticket_instance, $post ) {

			$forms			 = new TC_Forms();
			$both_form_types = $forms->get_forms( 'both', -1, '' );

			if ( count( $both_form_types ) >= 1 ) {
				global $wpdb;


				foreach ( $both_form_types as $form ) {
					$fields		 = array();
					$form_type	 = get_post_meta( $form->ID, 'form_type', true );

					for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
						$results = $wpdb->get_results(
						$wpdb->prepare(
						"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
						, $form->ID, $i ), OBJECT
						);

						if ( !empty( $results ) ) {

							foreach ( $results as $result ) {
								$post_meta			 = get_post_meta( $result->ID );
								$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
								if ( class_exists( $element_class_name ) ) {
									$element = new $element_class_name( $result->ID );

//if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
									if ( $element->standard_field_export( $element->element_name, true ) ) {
										$fields[] = $element->admin_order_details_page_value();
									}
//}
								}
							}
						}
					}

					foreach ( $fields as $field ) {
						if ( isset( $post[ $field[ 'id' ] ] ) ) {

							if ( $form_type == 'owner' ) {
								$rows .= '<td>' . $ticket_instance->details->$field[ 'id' ] . '</td>';
							}

							if ( $form_type == 'buyer' ) {
								$rows .= '<td>' . $order->details->tc_cart_info[ 'buyer_data' ][ $field[ 'id' ] . '_post_meta' ] . '</td>';
							}
						}
					}
				}
			}

			return $rows;
		}

		function modify_form_field_value( $value ) {
			if ( $value == 'owner' || $value == 'buyer' ) {
				$value = ucfirst( $value );
			}
			return $value;
		}

		function append_capabilities( $capabilities ) {//Add additional capabilities to staff and admins
			$capabilities[ 'manage_' . $this->name . '_cap' ] = 1;
			return $capabilities;
		}

		function add_admin_menu_item_to_tc() {//Add additional menu item under Tickera admin menu
			global $first_tc_menu_handler;
			$handler = 'custom_fields';

			add_submenu_page( $first_tc_menu_handler, __( $this->title, 'tc' ), __( $this->title, 'tc' ), 'manage_' . $this->name . '_cap', $this->name, $this->name . '_admin' );
			eval( "function " . $this->name . "_admin() {require_once( '" . $this->plugin_dir . "includes/admin-pages/" . $this->name . ".php');}" );
			do_action( $this->name . '_add_menu_items_after_' . $handler );
		}

		function load_addons() {
			require_once($this->plugin_dir . 'includes/classes/class.form_elements.php');
			$this->load_form_elements();
		}

		function load_virtual_tickets_elements() {
			$dir = $this->plugin_dir . 'includes/ticket-elements/';

			$forms			 = new TC_Forms();
			$both_form_types = $forms->get_forms( 'both', -1, '' );

			if ( count( $both_form_types ) >= 1 ) {
				global $wpdb;


				foreach ( $both_form_types as $form ) {
					$fields		 = array();
					$form_type	 = get_post_meta( $form->ID, 'form_type', true );

					for ( $i = 1; $i <= apply_filters( 'tc_form_row_number', 20 ); $i++ ) {
						$results = $wpdb->get_results(
						$wpdb->prepare(
						"SELECT *, pm2.meta_value as ord FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
											WHERE p.ID = pm.post_id 
											AND p.ID = pm2.post_id
											AND	p.post_parent = %d
											AND (pm.meta_key = 'row' AND pm.meta_value = %d)
											AND (pm2.meta_key = 'order')
											ORDER BY ord ASC"
						, $form->ID, $i ), OBJECT
						);

						if ( !empty( $results ) ) {

							foreach ( $results as $result ) {
								$post_meta			 = get_post_meta( $result->ID );
								$element_class_name	 = $post_meta[ 'field_type' ][ 0 ];
								if ( class_exists( $element_class_name ) ) {
									$element = new $element_class_name( $result->ID );

//if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
									if ( $element->standard_field_as_ticket_template( $element->element_name, true ) ) {
										$fields[] = $element->admin_order_details_page_value();
									}
//}
								}
							}
						}
					}

					foreach ( $fields as $field ) {

						if ( $form_type == 'owner' ) {
							$class_name		 = $field[ 'id' ];
							$element_name	 = $field[ 'id' ];
							$element_title	 = $field[ 'field_title' ];
//$value			 = '"' . $ticket_instance->details->$field[ 'id' ] . '"';
							$default_value	 = '"' . $field[ 'field_title' ] . '"';
						}

						if ( $form_type == 'buyer' ) {

							$class_name		 = $field[ 'id' ];
							$element_name	 = $field[ 'id' ];
							$element_title	 = $field[ 'field_title' ];
//$value			 = '"' . $order->details->tc_cart_info[ 'buyer_data' ][ $field[ 'id' ] . '_post_meta' ] . '"';
							$default_value	 = '"' . $field[ 'field_title' ] . '"';
						}

						include($dir . 'virtual-ticket-element.php');
					}
				}
			}
		}

		function load_form_elements() {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

//get form elements dir
			$dir = $this->plugin_dir . 'includes/form-elements/';

			$form_elements = array();

			if ( !is_dir( $dir ) )
				return;
			if ( !$dh		 = opendir( $dir ) )
				return;
			while ( ( $plugin	 = readdir( $dh ) ) !== false ) {
				if ( substr( $plugin, -4 ) == '.php' )
					$form_elements[] = $dir . '/' . $plugin;
			}
			closedir( $dh );
			sort( $form_elements );

			foreach ( $form_elements as $file )
				include( $file );

			do_action( 'tc_load_additional_elements' );
		}

		function front_header() {
			wp_enqueue_style( $this->name . '-fields-front', $this->plugin_url . 'css/front.css', array(), $this->version );
		}

		function admin_header() {//Add scripts and CSS for the plugin
			wp_enqueue_script( $this->name . '-admin', $this->plugin_url . 'js/admin.js', array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'jquery-ui-droppable',
				'jquery-ui-accordion',
				'wp-color-picker',
				'thickbox',
				'media-upload'
			), $this->version );

			wp_localize_script( $this->name . '-admin', 'tc_custom_fields_vars', array(
				'max_elements_message'	 => sprintf( __( 'Only %s elements per row are allowed', 'tc' ), apply_filters( 'tc_custom_form_elements_count_per_row', 3 ) ),
				'max_elements'			 => apply_filters( 'tc_custom_form_elements_count_per_row', 3 ),
			) );

			wp_enqueue_style( $this->name . '-admin', $this->plugin_url . 'css/admin.css', array(), $this->version );
			wp_enqueue_style( $this->name . '-fontawesome', $this->plugin_url . 'css/font-awesome.min.css', array(), $this->version );
		}

		function register_custom_posts() {
			$args = array(
				'labels'			 => array( 'name'				 => __( 'Forms', 'tc' ),
					'singular_name'		 => __( 'Forms', 'tc' ),
					'add_new'			 => __( 'Create New', 'tc' ),
					'add_new_item'		 => __( 'Create New Form', 'tc' ),
					'edit_item'			 => __( 'Edit Form', 'tc' ),
					'edit'				 => __( 'Edit', 'tc' ),
					'new_item'			 => __( 'New Form', 'tc' ),
					'view_item'			 => __( 'View Form', 'tc' ),
					'search_items'		 => __( 'Search Forms', 'tc' ),
					'not_found'			 => __( 'No Forms Found', 'tc' ),
					'not_found_in_trash' => __( 'No Forms found in Trash', 'tc' ),
					'view'				 => __( 'View Form', 'tc' )
				),
				'public'			 => true,
				'show_ui'			 => false,
				'publicly_queryable' => true,
				'capability_type'	 => 'post',
				'hierarchical'		 => false,
				'query_var'			 => true,
			);

			register_post_type( 'tc_forms', $args );

			$args = array(
				'labels'			 => array( 'name'				 => __( 'Custom Forms', 'tc' ),
					'singular_name'		 => __( 'Custom Forms', 'tc' ),
					'add_new'			 => __( 'Create New', 'tc' ),
					'add_new_item'		 => __( 'Create New Custom Field', 'tc' ),
					'edit_item'			 => __( 'Edit Custom Field', 'tc' ),
					'edit'				 => __( 'Edit', 'tc' ),
					'new_item'			 => __( 'New Custom Field', 'tc' ),
					'view_item'			 => __( 'View Custom Field', 'tc' ),
					'search_items'		 => __( 'Search Custom Forms', 'tc' ),
					'not_found'			 => __( 'No Custom Forms Found', 'tc' ),
					'not_found_in_trash' => __( 'No Custom Forms found in Trash', 'tc' ),
					'view'				 => __( 'View Custom Field', 'tc' )
				),
				'public'			 => true,
				'show_ui'			 => false,
				'publicly_queryable' => true,
				'capability_type'	 => 'post',
				'hierarchical'		 => false,
				'query_var'			 => true,
			);

			register_post_type( 'tc_form_fields', $args );
		}

	}

}

$tc_custom_fields = new TC_Custom_Fields();
?>