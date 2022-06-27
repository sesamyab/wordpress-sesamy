<?php


function sesamy_content_container($atts, $content){
	
    $atts = shortcode_atts( array(
        'preview' => get_the_excerpt(),
        'item_src' => get_permalink(),
        'show_childs_count' => '',
        'gradient' => ''
    ), $atts, 'sesamy_content_container' );


    $tag_content = '<div slot="preview">' . $atts['preview'] . '</div><div slot="content">' .  $content . '</div>';
    

    // Exclude attributes used by WordPress when making sesamy tag
    $non_display_atts =  [ 'preview' ];
    $html_attributes = array_filter(  $atts, function( $key ) use ( $non_display_atts ) { 
        return !in_array($key, $non_display_atts); 
    } , ARRAY_FILTER_USE_KEY);


    return Sesamy_Utils::make_tag( 'sesamy-content-container', $html_attributes, $tag_content );
}

function sesamy_button_container($atts, $content){

    $atts = shortcode_atts( array(
        'item_src' => '',
        'description ' => ''
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
    ), $atts, 'sesamy-login' );

    return Sesamy_Utils::make_tag( 'sesamy-profile', $atts, $content );
}


