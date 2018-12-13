<?php

$class_name		 = tc_save_eval_strings( $class_name );
$original_element_name = sanitize_title(str_replace('-', "-", $element_name));
$element_name	 = tc_save_eval_strings( $element_name );

//echo $element_name;
//exit;
//$element_title	 = tc_save_eval_strings( $element_title );

eval( "
class $class_name extends TC_Ticket_Template_Elements {

	var \$element_name	 = '$element_name';
	var \$element_title	 = '$element_title';

	function on_creation() {
		\$this->element_title = apply_filters( 'tc_" . $element_name . "_title', '$element_title' );
	}
	
	function admin_content(){
		parent::admin_content();
		echo \$this->field_label();
	}
	
    function field_label(){
	?>
		<label><?php _e( 'Field Label', 'tc' ); ?>
			<input class=\"ticket_element_field_label\" type=\"text\" name=\"<?php echo \$this->element_name; ?>_field_label_post_meta\" value=\"<?php echo esc_attr( isset( \$this->template_metas[ \$this->element_name . '_field_label' ] ) ? \$this->template_metas[ \$this->element_name . '_field_label' ] : '$element_title'  ); ?>\" />
		</label>
		<?php
	}

function ticket_content( \$ticket_instance_id = false, \$ticket_type_id = false ) {
if ( \$ticket_instance_id ) {

\$ticket_instance = new TC_Ticket_Instance( (int) \$ticket_instance_id );
\$order = new TC_Order(\$ticket_instance->details->post_parent);

if($form_type == 'buyer'){
\$field_value = \$order->details->tc_cart_info[ 'buyer_data' ][ '" . $original_element_name . "_post_meta' ];
}

if($form_type == 'owner'){
\$field_value = \$ticket_instance->details->$element_name;
}

if(isset( \$this->template_metas[ \$this->element_name . '_field_label' ] ) && !empty(\$this->template_metas[ \$this->element_name . '_field_label' ])){
	\$field_label = \$this->template_metas[ \$this->element_name . '_field_label' ].'<br />';
}else{
	\$field_label = '';
}

return apply_filters( 'tc_" . $element_name . "_ticket_type', \$field_label.' '.\$field_value );
} else {
return $default_value;
}
}

}

tc_register_template_element( '$element_name', __( '$element_title', 'tc' ) );
" );
