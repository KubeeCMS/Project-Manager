<?php
/**
 * Adds a custom field type for select multiples.
 *
 * @param  object $field The CMB2_Field type object.
 * @param  string $escaped_value The saved (and escaped) value.
 * @param  int    $object_id The current post ID.
 * @param  string $object_type The current object type.
 * @param  object $field_type_object The CMB2_Types object.
 *
 * @return void
 */
function cmb2_render_select_multiple_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

	if ( is_string( $escaped_value ) ) {
		$escaped_value = array( $escaped_value );
	}

	$select_multiple = '<select class="widefat" multiple name="' . $field->args['_name'] . '[]" id="' . $field->args['_id'] . '"';
	foreach ( $field->args['attributes'] as $attribute => $value ) {
		$select_multiple .= " $attribute=\"$value\"";
	}
	$select_multiple .= ' />';

	foreach ( $field->options() as $value => $name ) {
		$selected        = ( $escaped_value && in_array( $value, $escaped_value ) ) ? 'selected="selected"' : '';
		$select_multiple .= '<option class="cmb2-option" value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$select_multiple .= '</select>';
	$select_multiple .= $field_type_object->_desc( true );

	echo $select_multiple; // WPCS: XSS ok.
}
add_action( 'cmb2_render_select_multiple', 'cmb2_render_select_multiple_field_type', 10, 5 );

/**
 * Sanitize the selected value.
 */
function cmb2_sanitize_select_multiple_callback( $override_value, $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $saved_value ) {
			$value[ $key ] = sanitize_text_field( $saved_value );
		}

		return $value;
	}

	return;
}
add_filter( 'cmb2_sanitize_select_multiple', 'cmb2_sanitize_select_multiple_callback', 10, 2 );

/**
 * Adds a custom field type for client uploads.
 *
 * @since 4.8
 *
 * @param  object $field The CMB2_Field type object.
 * @param  array  $escaped_value The saved (and escaped) value.
 * @param  int    $object_id The current post ID.
 * @param  string $object_type The current object type.
 * @param  object $field_type_object The CMB2_Types object.
 *
 * @return void
 */
function cmb2_render_client_uploads_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
	$client_uploads = '';

	if ( ! empty( $escaped_value ) ) {
		$client_uploads .= '<ul class="cmb-attach-list">';
		foreach ( $escaped_value as $k => $file ) {
			$file_url        = trailingslashit( get_permalink( $object_id ) . 'file/' . $k );
			$client_uploads .= '<li><span>File: </span> <strong>' . $file['name'] . '</strong> ';
			$client_uploads .= '(<a href="' . $file_url . '" download>Download</a>)</li>';
		}

		$escaped_value = serialize( $escaped_value );
		$client_uploads .= '</ul>';
	} else {
		$escaped_value = '';
		$client_uploads .= '<p class="cmb2-metabox-description">You\'ll see the file list once your client has uploaded some.</p>';
	}

	// store the real value in a hidden field.
	$client_uploads .= '<input type="hidden" name="' . $field->args['_name'] . '" id="' . $field->args['_id'] . '" value="' . esc_attr( $escaped_value ) . '" />';

	echo $client_uploads; // WPCS: XSS ok.
}
add_action( 'cmb2_render_client_uploads', 'cmb2_render_client_uploads_field_type', 10, 5 );

/**
 * Replace the escaped value of client uploads.
 *
 * @since 4.8
 *
 * @param bool|mixed $override_value Escaping override value to return.
 *                                   Default: null. false to skip it.
 * @param mixed      $meta_value The value to be output.
 * @param array      $field_args The current field's arguments.
 * @param object     $field      This `CMB2_Field` object.
 *
 * @return mixed
 */
function cmb2_types_esc_client_uploads_callback( $override_value, $meta_value, $field_args, $field ) {
	if ( ! empty( $meta_value ) ) {
		// return the unescaped array value when rendering the field.
		return $field->value();
	}

	return $meta_value;
}
add_action( 'cmb2_types_esc_client_uploads', 'cmb2_types_esc_client_uploads_callback', 10, 4 );

/**
 * Sanitize the client uploads value.
 *
 * @since 4.8
 *
 * @param array  $override_value The value we are going to store in the database.
 * @param string $value The raw value we get from $_POST input.
 *
 * @return array
 */
function cmb2_sanitize_client_uploads_callback( $override_value, $value ) {
	$override_value = unserialize( wp_unslash( $value ) );

	return $override_value;
}
add_filter( 'cmb2_sanitize_client_uploads', 'cmb2_sanitize_client_uploads_callback', 10, 2 );
