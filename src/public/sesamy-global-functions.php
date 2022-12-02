<?php


function sesamy_content_container($atts, $content){
	

    $post_settings = sesamy_get_post_settings(get_the_ID());

    $atts = shortcode_atts( array(
        'publisher_content_id'  => '',
        'preview'               => get_the_excerpt(),
        'item_src'              => get_permalink(),
        'show_childs_count'     => '',
        'gradient'              => '',
        'lock_mode'             => get_option( 'sesamy_lock_mode' ),
        'access_url'            => get_site_url() . '/wp-json/sesamy/v1/posts/' . $atts['publisher_content_id'],
        'pass'                  => ''
    ), $atts, 'sesamy_content_container' );


    $tag_content = '<div slot="preview">' . $atts['preview'] . '</div>';
    
    if ( $atts['lock_mode'] == 'embed' ) {
        $tag_content .= '<div slot="content">' .  $content . '</div>';
    }
    
    if( $atts['lock_mode'] !== 'signedUrl' ){
        unset( $atts['access_url'] );
    }

    // Exclude attributes used by WordPress when making sesamy tag
    $non_display_atts =  [ 'preview' ];
    $html_attributes = array_filter(  $atts, function( $key ) use ( $non_display_atts ) { 
        return !in_array($key, $non_display_atts); 
    } , ARRAY_FILTER_USE_KEY);


    return Sesamy_Utils::make_tag( 'sesamy-content-container', $html_attributes, $tag_content );
}

function sesamy_button_container($atts, $content){

    $atts = shortcode_atts( array(
        'publisher_content_id'  => '',
        'item_src'              => '',
        'description '          => '',
    ), $atts, 'sesamy_button_container' );


    return Sesamy_Utils::make_tag( 'sesamy-button-container', $atts, $content );
}

function sesamy_button($atts, $content){

    $atts = shortcode_atts( array(
        'text'                  => '',
        'price'                 => '',
        'currency'              => '',
        'item_src'              => '',
        'publisher_content_id'  => ''
    ), $atts, 'sesamy_button' );

    return Sesamy_Utils::make_tag( 'sesamy-button', $atts, $content, false );
}

function sesamy_login($atts, $content){

    $atts = shortcode_atts( array(
        'client_id' => '',
        'login_text' => '',
        'logout_text' => ''
    ), $atts, 'sesamy_login' );

    return Sesamy_Utils::make_tag( 'sesamy-login', $atts, $content );
}

function sesamy_profile($atts, $content){

    $atts = shortcode_atts( array(
        // Add here if needed
    ), $atts, 'sesamy_profile' );

    return Sesamy_Utils::make_tag( 'sesamy-profile', $atts, $content );
}

/**
 * Render the config tag
 */
function sesamy_config($atts, $content){

    $atts = shortcode_atts( array(
        'client_id' => '',
        'currency' => ''
    ), $atts, 'sesamy_config' );

    return Sesamy_Utils::make_tag( 'sesamy-config', $atts, $content );
}

function sesamy_get_enabled_post_types(){

    return get_option( 'sesamy_content_types', [] );

}


/**
 * Return information about a pass in sesamy in a easily accessible way
 */
function sesamy_get_pass_info($term){

    $image_id =  get_term_meta($term->term_id, 'image_id', true);

    return [
        'id'            => $term->term_id,
        'slug'          => $term->slug,
        'title'         => $term->name,        
        'description'   => $term->description,
        'price'         => get_term_meta($term->term_id, 'price', true),
        'currency'      => get_term_meta($term->term_id, 'currency', true),
        'image'         => !empty($image_id) ? wp_get_attachment_image_url(  $image_id , 'full' ) : null,
        'url'           => get_term_meta($term->term_id, 'url', true),
        'item_src'      => get_site_url() . '/wp-json/sesamy/v1/passes/' . $term->term_id
    ];

}

/**
 * Return information about sesamy settings for a post in an easy accessible way
 */
function sesamy_get_post_settings( $post_id ){

    $post = get_post( $post_id );
    $meta = get_post_meta( $post_id );
    $passes = get_the_terms($post->ID, 'sesamy_passes');

    return [
        'locked'                 => $meta['_sesamy_locked'],
        'enable_single_purchase' => $meta['_sesamy_enable_single_purchase'],
        'price'                  => $meta['_sesamy_price'],
        'currency'               => $meta['_sesamy_currency'],
        'passes'                 => array_map( 'sesamy_get_pass_info' , $passes)
    ];

}
