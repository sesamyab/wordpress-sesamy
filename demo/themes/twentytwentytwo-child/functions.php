<?php

// Examples on how to create a custom paywall design with Sesamy

function show_paywall( $default_paywall, $post, $post_settings){

    ob_start();


    ?>
    <div class="sesamy-paywall" data-sesamy-paywall data-sesamy-item-src="<?php echo get_the_permalink( $post->ID ); ?>" data-sesamy-passes="<?php  sesamy_get_passes( $post->ID ) ?>">


        <div class="sesamy-login">
            <h3 class="sesamy-login-header">Already a customer?</h3>
            <p>Press login to access your articles and benefits.</p>
            <sesamy-login></sesamy-login>
        </div>

        <?php

            if( count($post_settings['passes']) > 0 ){
                        
                            
                foreach($post_settings['passes'] as $pass){ ?>

                    

                    <div class="sesamy-pass">

                        <h3 class="pass-name"><?php echo $pass["title"]; ?></h3>

                        <?php if(!empty( $pass["description"])): ?>
                            <p><?php echo $pass["description"]; ?></p>
                        <?php endif;  ?>

                        <?php
                        $button_args = [
                            'text' 		            => $pass['title'], 
                            'price' 	            => $pass['price'], 
                            'currency' 	            => $pass['currency'],
                            'item_src' 	            => $pass['url'],
                            'publisher_content_id'  => $pass['id'],
                        ];
                        echo sesamy_button($button_args, '');
                        ?>
                    </div>
                    <?php
                }
            }


            if ($post_settings['enable_single_purchase']){?>

                <div class="sesamy-single-purchase">

                    <h3 class="single-purchase-name">Unlock article</h3>
                    <p>Just buy access to read this article without starting a subscription.</p>
                    <?php
                    $button_args = [
                        'price' 		=> $post_settings['price'], 
                        'currency' 		=> $post_settings['currency'],
                        'item_src'		=> get_the_permalink($post->ID)
                    ];
                    echo sesamy_button($button_args, '');

                    ?>
                </div>
            <?php
            }

           
           
        ?>
    </div>

    <style>
        .sesamy-paywall {
            display: grid;
            text-align: center;
            background: #e1e1e1;
            box-shadow: 0 0.5rem 1rem rgb(0 0 0 / 15%) !important;
            border-radius: 1rem;
            gap: 1rem;
            padding: 1rem;
        }

        .sesamy-paywall p {
            margin: 0.25rem 0 1rem 0;
        }

        .sesamy-paywall > div {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fff;
            border-radius: 1rem;
        }


        .single-purchase-name, .pass-name, .sesamy-login-header {
            font-size: 1.5rem;
            margin: 0.25rem;
        }

        
    </style>
    <?php

    return ob_get_clean();

}


add_filter('sesamy_paywall', 'show_paywall', 11, 3);