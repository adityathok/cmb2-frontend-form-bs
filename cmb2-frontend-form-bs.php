<?php
/**
 * CMB2 Front-end Shortcode Bootstrap 5 
 *
 * Author: Adityathok
 * Version: 1.0.0
 * Author URI: https://github.com/adityathok
 * GitHub URI: https://github.com/adityathok/cmb2-frontend-form-bs
*/

/**
 * Generate the frontend form shortcode for CMB2.
 * 
 * [cmb-form id="id_metabox" post_id=94]
 * 
 * @param array $atts Shortcode attributes.
 * @return string The output of the frontend form shortcode.
 */
add_shortcode( 'cmb-form', 'cmb2_do_frontend_form_shortcode' );
function cmb2_do_frontend_form_shortcode( $atts = array() ) {

    // Current user
    $user_id = get_current_user_id();

    // Use ID of metabox in wds_frontend_form_register
    $metabox_id = esc_attr( $atts['id'] );

    // since post ID will not exist yet, just need to pass it something
    $object_id  = isset($atts['post_id'])?absint( $atts['post_id'] ):'new-oject-id';

    // Get CMB2 metabox object
    $cmb = cmb2_get_metabox( $metabox_id, $object_id );

    if(empty($cmb))
        return 'Metabox ID not found';
    return false;
    
    // Get $cmb object_types
    $post_types = $cmb->prop( 'object_types' );

    // Parse attributes. These shortcode attributes can be optionally overridden.
    $atts = shortcode_atts( array(
        'ID'            =>isset($atts['post_id'])?absint( $atts['post_id'] ):0,
        'post_author'   => $user_id ? $user_id : 1,
        'post_status'   => 'pending',
        'post_type'     => reset( $post_types ),
    ), $atts, 'cmb-frontend-form' );

    // Initiate our output variable
    $output = '';
    
    $new_id = handle_frontend_cmb2_form_submission( $cmb, $atts );
    if ( $new_id ) {

        if ( is_wp_error( $new_id ) ) {

            // If there was an error with the submission, add it to our ouput.
            $output .= '<div class="alert alert-warning">' . sprintf( __( 'There was an error in the submission: %s', 'cmb2-post-submit' ), '<strong>'. $new_id->get_error_message() .'</strong>' ) . '</div>';

        } else {

            // Add notice of submission
            $output .= '<div class="alert alert-success">' . sprintf( __( 'Produk %s, berhasil di submit.', 'cmb2-post-submit' ), esc_html( get_the_title($new_id) ) ) . '</div>';
        }

    }

    // Get our form
    $form = cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Submit Produk', 'cmb2-post-submit' ) ) );
    
    // Format our form use Bootstrap 5
    $styling = [
        'regular-text'              => 'regular-text form-control',
        'cmb2-text-small'           => 'cmb2-text-small form-control',
        'cmb2-text-medium'          => 'cmb2-text-medium form-control',
        'cmb2-timepicker'           => 'cmb2-timepicker form-control d-inline-block',
        'cmb2-datepicker'           => 'cmb2-datepicker d-inline-block',
        'cmb2-text-money'           => 'cmb2-text-money form-control d-inline-block',
        'cmb2_textarea'             => 'cmb2_textarea form-control',
        'cmb2-textarea-small'       => 'cmb2-textarea-small form-control d-inline-block',
        'cmb2_select'               => 'cmb2_select form-select',
        'cmb2-upload-file regular-text'         => 'cmb2-upload-file regular-text d-inline-block',
        'type="radio" class="cmb2-option"'      => 'type="radio" class="cmb2-option form-check-input"',
        'type="checkbox" class="cmb2-option"'   => 'type="checkbox" class="cmb2-option form-check-input"',
        'button-secondary'          => 'button-secondary btn-sm btn btn-outline-secondary',
        'button-secondary btn-sm btn btn-outline-secondary cmb-remove-row-button'   => 'button-secondary btn btn-danger cmb-remove-row-button',
        'class="button-primary"'    => 'class="button-primary btn btn-primary"',
        'class="cmb-th"'            => 'class="cmb-th w-100 p-0"',
        'class="cmb-td"'            => 'class="cmb-th w-100 p-0 pb-2"',
        'class="cmb-add-row"'       => 'class="cmb-add-row text-end"',
    ];
    foreach ($styling as $std => $newf) {
        $form = str_replace($std, $newf, $form);
    }

    $output .= $form;

    return $output;
}

/**
 * Handles form submission on save
 *
 * @param  CMB2  $cmb       The CMB2 object
 * @param  array $post_data Array of post-data for new post
 * @return mixed New post ID if successful
 */
function handle_frontend_cmb2_form_submission( $cmb, $post_data = array() ) {
    
    // If no form submission, bail
    if ( empty( $_POST ) ) {
        return false;
    }
    // Fetch sanitized values
    $sanitized_values = $cmb->get_sanitized_values( $_POST );

    // Set our post data arguments
    $post_data['post_title']   = $sanitized_values['post_title'];
    $post_data['post_content'] = $sanitized_values['post_content'];

    // Create the new post
    $new_submission_id = wp_insert_post( $post_data, true );

    if(is_wp_error($new_submission_id)){
        return $new_submission_id;
    }    

    //thumbnail
    if(isset($sanitized_values['post_thumbnail_id']) && $sanitized_values['post_thumbnail_id']){    
        set_post_thumbnail( $new_submission_id, $sanitized_values['post_thumbnail_id'] );
    }

    unset( $post_data['post_type'] );
    unset( $post_data['post_status'] );

    // Loop through remaining (sanitized) data, and save to post-meta
    foreach ( $sanitized_values as $key => $value ) {
        update_post_meta( $new_submission_id, $key, $value );
    }

    return $new_submission_id;
}
