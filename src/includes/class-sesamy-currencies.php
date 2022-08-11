<?php

class Sesamy_Currencies {


     /**
       * Get the public key
       */
      public static function get_currencies(){

        $currencies = get_transient( 'sesamy_currencies' );

        // Use transient to avoid calling api more than needed
        if ($currencies === false) { 

          $c = curl_init( Sesamy::$instance->get_assets_url() . '/markets.json'); 
          curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); 
          $json = curl_exec($c); 
          curl_close($c);

          $data = json_decode( $json );

          $currencies = [];

          foreach( $data as $item) {
            $currencies[$item->currency] = $item->currency;
          }
      
          set_transient( 'sesamy_currencies', $currencies, 60);

        }   
       
        return $currencies;
      }

      public function register_route(){

        register_rest_route( 'sesamy/v1', '/currencies', array(
            'methods' => 'GET',
            'callback' => [$this, 'sesamy_currencies_ep'],
            'permission_callback' => '__return_true',
            'args' => [],
          ) );

    }


    /**
     * Endpoint for returning currencies
     */
    public function sesamy_currencies_ep( $request ) {       
      
        return new WP_REST_Response( self::get_currencies() );
    
    }
}