<?php
use Jose\Component\Core\JWK;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\RS256;


class Sesamy_Signed_Url {

    /**
     * Main function to test if a signed link is valid
     */
    public function is_valid_link( $url ){

        $params = $this->get_request_parameters( $url );

        $expected_keys = ['si', 'se', 'ss'];
 
        if ( count( array_intersect( array_keys( $params),  $expected_keys ) ) !== count( $expected_keys )) {
            return new WP_Error(404, 'Missing request parameters');
        }
       
        $post = get_post( $params['si'] );

        if( $post == null) {
          return new WP_Error(404, 'Item not found');
        }

        // Check if post is locked, if not, just return content
        $is_locked = get_post_meta( $post->ID, '_sesamy_locked', true);

        if ( !$is_locked ){
          return true;
        }

        // Verify expiration
        if ( $params['se'] < time() ) {
          return new WP_Error(404, 'Link is expired');
        }

        // Verify signature
        if ( $this->verify_signature( $params['signed_url'], $params['ss'] ) ) {
          return true;
        }else{
          return new WP_Error(404, 'Invalid signature.');
        }
    }

      /**
       * Get parameters for the request. 
       */
      function get_request_parameters( $url ) {

        $query_string = parse_url( $url, PHP_URL_QUERY );
        parse_str($query_string, $parts );
        array_flip( $parts ); 

        // Get the url part without signature part
        $split_url = explode('&ss=', $url);
        
        return array_merge( ['signed_url' => $split_url[0]], $parts );
      }

      /**
       * Validate signature with 
       */
      function verify_signature($url, $signature){

        $algorithm_manager = new AlgorithmManager([
          new RS256()
        ]);

        $rs256 = $algorithm_manager->get('RS256');
        $jwk = $this->get_public_key();

        return  $rs256->verify($jwk, $url, $signature);
        
      }



      /**
       * Get the public key
       */
      function get_public_key(){

        $jwk = get_transient( 'sesamy_public_key' );

        // Use transient to avoid calling api more than needed
        if ( $jwk === false) { 

          $c = curl_init( Sesamy::$instance->get_assets_url() . '/vault-jwks.json'); 
          curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); 
          curl_setopt($c, CURLOPT_USERPWD, 'david:hax0r'); 
          $json = curl_exec($c); 
          curl_close($c);

          $jwk = JWK::createFromJson($json);
          set_transient( 'sesamy_public_key', $jwk, 3600);

        }   
       
        return $jwk;
      }

}