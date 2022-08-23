<?php


function sesamy_content_container($atts, $content){
	

    $atts = shortcode_atts( array(
        'pid'               => '',
        'preview'           => get_the_excerpt(),
        'item_src'          => get_permalink(),
        'show_childs_count' => '',
        'gradient'          => '',
        'lock_mode'         => get_option( 'sesamy_lock_mode' ),
        'access_url'        => get_site_url() . '/wp-json/sesamy/v1/posts/' . $atts['pid']
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
        'pid'               => '',
        'item_src'          => '',
        'description '      => '',
    ), $atts, 'sesamy_button_container' );


    return Sesamy_Utils::make_tag( 'sesamy-button-container', $atts, $content );
}

function sesamy_button($atts, $content){

    $atts = shortcode_atts( array(
        'text' => '',
        'price' => '',
        'currency' => ''        
    ), $atts, 'sesamy_button' );

    return Sesamy_Utils::make_tag( 'sesamy-button', $atts, $content );
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

