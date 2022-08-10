<?php

class Sesamy_Post_Properties {


    function register_post_meta(){


        $post_types = sesamy_get_enabled_post_types();

        foreach($post_types as $post_type){

            register_post_meta( $post_type, '_sesamy_locked', [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'boolean',
                'auth_callback' => '__return_true'
            ] );

            // Allowed options: "tier" or "custom"
            register_post_meta( $post_type, '_sesamy_payment_type', [
                'show_in_rest' => true,
                'single' => true,
                'default' => 'tier',
                'type' => 'string',
                'auth_callback' => '__return_true'
            ] );

            register_post_meta( $post_type, '_sesamy_price', [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string', // string to allow empty
                'auth_callback' => '__return_true'
            ] );

            register_post_meta( $post_type, '_sesamy_currency', [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'auth_callback' => '__return_true'
            ] );

         
          
        }

        

    }


    public static function get_tier_price_info( $tier) {

        return [
            'type'     => get_term_meta( $tier->term_id, 'type', true ),
            'price'    => get_term_meta( $tier->term_id, 'price', true ),
            'currency' => get_term_meta( $tier->term_id, 'currency', true )
        ];


    }


    public static function is_locked( $post ) {

        return get_post_meta( $post->ID, '_sesamy_locked', true );
    }

    /**
     * Returns price info with applied ruleset and logic
     */
    public static function get_post_price_info( $post ){


        $info = [
            'price' =>  null,
            'currency' => null
        ];

        $payment_type = get_metadata ( $post->post_type, $post->ID, '_sesamy_payment_type', true );


        if( $payment_type == 'tier' ) {

            $tiers = get_the_terms( $post->ID, 'sesamy_tiers' );

            if( !isset($tiers[0]) ){
                // TODO: Handle more gracefully
                wp_die('No tiers configured for post.');
            }

            $tier = $tiers[0];

            $tier_meta = self::get_tier_price_info( $tier );


            $info['price'] = $tier_meta['price'];
            $info['currency'] = $tier_meta['currency'];


        }else{


            $info['price'] = get_post_meta( $post->ID, '_sesamy_price', true );
            $info['currency'] = get_post_meta( $post->ID, '_sesamy_currency', true );

        }


        return $info;

    }

}