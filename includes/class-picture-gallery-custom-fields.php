<?php

	function be_attachment_field_credit( $form_fields, $post ) {

      	$form_fields['Category'] = array(

          	'label' => 'Image Category',

          	'input' => 'text',

          	'value' => get_post_meta( $post->ID, 'Category', true ),

          	'helps' => 'Will be displayed under appropriate gallery category if selected');

      	return $form_fields;
	}

	add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );

	function be_attachment_field_credit_save( $post, $attachment ) {
	    if( isset( $attachment['Category'] ) )
	        update_post_meta( $post['ID'], 'Category', $attachment['Category'] );
	   
	    return $post;
	}


	add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );


}
