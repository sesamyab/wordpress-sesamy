<?php



class Sesamy_Content_Endpoint {


    public function register_route(){

        register_rest_route( 'sesamy/v1', '/post/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => [$this, 'sesamy_post_ep'],
            'permission_callback' => '__return_true',
            'args' => [
                'se' => [
                  'validate_callback' => [$this, 'validate_numeric_param']
                ],
                'si' => [
                  'validate_callback' => [$this, 'validate_numeric_param']
                ],
                'ss' => [],
            ],
          ) );

    }

    /**
     * Validation callback for arguments
     */
    function validate_numeric_param($param, $request, $key){
      return is_numeric( $param );
    }

    /**
     * Endpoint for validating request and returning the content
     */
    public function sesamy_post_ep( $request ) {
       
      
        $signed_url = new Sesamy_Signed_Url();

        $public_signed_url = $_SERVER['HTTP_X_SESAMY_SIGNED_URL']; // ?? get_the_permalink( $data['si'] ) . "&se=". $data['se'] . "&si=" . $data['si'] . "&ss=" . $data['ss'];

        $result = $signed_url->is_valid_link( $public_signed_url );

        if( is_bool($result) && TRUE == $result ){
          $params = $signed_url->get_request_parameters($public_signed_url);
          $post = get_post( $params['si']);
          return new WP_REST_Response( ['data' => apply_filters( 'the_content', $post->post_content ) ]);
        }else{

          return new WP_Error(400, 'The link is incorrect or no longer valid.');
        }


      }

      /**
       * Return content with applied filters
       */
      function get_response( $post ){

        return new WP_REST_Response( ['data' => apply_filters( 'the_content', $post->post_content ) ]);

      }


      /**
       * Format response based on Accept header
       */
      public function format_response($served, $result, $request, $server) {


        if ( preg_match('/^\/sesamy\/v1\/post\/[0-9]+$/m', $request->get_route() ) && isset( $result->data['data'] )){

          switch ( $_SERVER['HTTP_ACCEPT'] ) {

            case 'text/html':
              header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

              echo $result->data['data'];
              $served = true;
              break;
        
            case 'application/xml':
              header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' )  );
        
              $xmlDoc = new DOMDocument();
              $response = $xmlDoc->appendChild( $xmlDoc->createElement( 'Response' ) );
              $response->appendChild( $xmlDoc->createElement( 'Data', $result->data->my_text_data ) );
        
              echo $xmlDoc->saveXML();
              $served = true;
              break;
        
          }
        
          return $served;

        }
      
      }
      

}