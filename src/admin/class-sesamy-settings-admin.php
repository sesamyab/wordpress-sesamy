<?php

class Sesamy_Settings_Admin {


    function admin_menu(){

        add_menu_page(
            'Sesamy',
            'Sesamy',
            'manage_options',
            'sesamy',
            [$this, 'admin_menu_html']
        );

    }


    function admin_menu_html(){
        require(plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sesamy-admin-display.php');
    }


    function add_settings(){
        

        register_setting( 'sesamy', 'sesamy_content_types' );
    
        add_settings_section(
            'sesamy_section_general',
            __( 'General', 'sesamy' ),
            [$this, 'sesamy_section_general_callback'],
            'sesamy'
        );
    
        add_settings_field(
            'sesamy_content_types',
            __( 'Content types', 'sesamy' ),
            [$this, 'settings_render_checkboxlist'],
            'sesamy',
            'sesamy_section_general',
            array(
                'name'                  => 'sesamy_content_types',
                'options'               => get_post_types(['public' => true]),
                'label_for'             => 'sesamy_content_types',
            )
        );

    }

    function sesamy_section_general_callback(){
        // Add if needed
    }

    function settings_render_checkboxlist($args){

        $options = get_option( $args['name'] );

        if( !is_array( $options) ){
            $options = [];
        }

        foreach($args['options'] as $key => $value)
        {
            $checked = in_array($key, $options) ? 'checked' : '';
            echo "<label><input type=\"checkbox\" name=\"{$args['name']}[]\" value=\"$key\" $checked>$value</label><br>";
        }
    }


}

