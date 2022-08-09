# Development

Dependencies for signature validation are implemented with libraries using composer. 

Run the following command to install the dependencies:

    cd src && composer update


# Filters and actions

In the main loop the wrapping of sesamy container is hooked into two filters, the_content and sesamy_content.

You can apply the sesamy_content to custom templates, shortcodes and others like this:

    apply_filters( 'sesamy_content', $post, $content );

To modify the data before returned you can add a filter. The default priority for the built in main content is 999.

    add_filter( 'sesamy_content', 'my_callback', 10, 2 );

    function my_callback( $post, $content)

