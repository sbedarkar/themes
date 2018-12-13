<?php

function tc_get_order_details_owner_form_fields_values( $field_name, $post_id, $field_id ) {
	$ticket_type_id	 = get_post_meta( $field_id, 'ticket_type_id', true );
	$order_id		 = $_GET[ 'ID' ];
	$fields = array();
	$forms		 = new TC_Forms();
	$owner_form	 = $forms->get_forms( 'owner', -1, $ticket_type_id );

	if ( count( $owner_form ) >= 1 && (isset($owner_form[ 0 ]) && !is_null($owner_form[ 0 ])) ) {
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

						if ( $element->standard_field_admin_order_details( $element->element_name, true ) ) {
							$fields[] = $element->admin_order_details_page_value();
						}
					}
				}
			}
		}
	}

	foreach ( $fields as $field ) {
		?>
		<div class="tc_custom_field_record_order_details">
			<?php
			echo $field[ 'field_title' ] . ': ';
			eval( $field[ 'function' ] . "('" . $field[ 'id' ] . "', " . $field_id . ", '', 'owner_data');" );
			?>
		</div>
		<?php
	}
}

function tc_custom_form_fields_owner_form_template_select( $field_name, $event_id = 0 ) {
	$forms	 = new TC_Forms();
	$forms	 = $forms->get_forms( 'owner' );

	if ( $event_id !== 0 ) {
		$selected_option = get_post_meta( $event_id, $field_name, true );
		if ( isset( $selected_option ) && !empty( $selected_option ) ) {
			
		} else {
			$selected_option = 0;
		}
	} else {
		$selected_option = 0;
	}
	?>
	<select name="<?php echo $field_name; ?>_post_meta">
		<option value="0" <?php selected( '0', $selected_option, true ); ?>><?php _e( 'Default', 'tc' ); ?></option>
		<?php
		foreach ( $forms as $form ) {
			?>
			<option value="<?php echo $form->ID; ?>" <?php selected( $form->ID, $selected_option, true ); ?>><?php echo $form->post_title; ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

function tc_get_input_admin_order_details_page_value( $field_name, $post_id, $field_id, $field_type = 'buyer_data' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	if ( $field_type == 'buyer_data' ) {
		echo isset( $value[ $field_type ][ $field_id . '_post_meta' ] ) && !empty( $value[ $field_type ][ $field_id . '_post_meta' ] ) ? $value[ $field_type ][ $field_id . '_post_meta' ] : '-';
	} else {
		echo isset( $value ) && $value !== '' ? $value : '-';
	}
}

function tc_get_textarea_admin_order_details_page_value( $field_name, $post_id, $field_id, $field_type = 'buyer_data' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	if ( $field_type == 'buyer_data' ) {
		echo isset( $value[ $field_type ][ $field_id . '_post_meta' ] ) && !empty( $value[ $field_type ][ $field_id . '_post_meta' ] ) ? $value[ $field_type ][ $field_id . '_post_meta' ] : '-';
	} else {
		echo isset( $value ) && $value !== '' ? $value : '-';
	}
}

function tc_get_radio_admin_order_details_page_value( $field_name, $post_id, $field_id, $field_type = 'buyer_data' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	if ( $field_type == 'buyer_data' ) {
		echo isset( $value[ $field_type ][ $field_id . '_post_meta' ] ) && !empty( $value[ $field_type ][ $field_id . '_post_meta' ] ) ? $value[ $field_type ][ $field_id . '_post_meta' ] : '-';
	} else {
		echo isset( $value ) && $value !== '' ? $value : '-';
	}
}

function tc_get_select_admin_order_details_page_value( $field_name, $post_id, $field_id, $field_type = 'buyer_data' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	if ( $field_type == 'buyer_data' ) {
		echo isset( $value[ $field_type ][ $field_id . '_post_meta' ] ) && !empty( $value[ $field_type ][ $field_id . '_post_meta' ] ) ? $value[ $field_type ][ $field_id . '_post_meta' ] : '-';
	} else {
		echo isset( $value ) && $value !== '' ? $value : '-';
	}
}

function tc_get_checkbox_admin_order_details_page_value( $field_name, $post_id, $field_id, $field_type = 'buyer_data' ) {
	$value = get_post_meta( $post_id, $field_name, true );
	if ( $field_type == 'buyer_data' ) {
		echo isset( $value[ $field_type ][ $field_id . '_post_meta' ] ) ? $value[ $field_type ][ $field_id . '_post_meta' ] : '-';
	} else {
		echo isset( $value ) && $value !== '' ? $value : '-';
	}
}

function tc_save_eval_strings($string){
	$string = sanitize_title($string);
	$string = str_replace(array('-'), array('_'), $string);
	return $string;
}
