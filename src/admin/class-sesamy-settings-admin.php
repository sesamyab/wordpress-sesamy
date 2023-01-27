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
        
        add_settings_section(
            'sesamy_section_general',
            __( 'General', 'sesamy' ),
            [$this, 'sesamy_section_general_callback'],
            'sesamy'
        );


        add_settings_field(
            'sesamy_client_id',
            __( 'Client ID', 'sesamy' ),
            [$this, 'settings_render_input'],
            'sesamy',
            'sesamy_section_general',
            array(
                'name'                  => 'sesamy_client_id',
                'label_for'             => 'sesamy_client_id',
            )
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

        
        add_settings_field(
            'sesamy_lock_mode',
            __( 'Lock mode', 'sesamy' ),
            [$this, 'settings_render_select'],
            'sesamy',
            'sesamy_section_general',
            array(
                'name'                  => 'sesamy_lock_mode',
                'options'               => ['embed' => 'Embed', "signedUrl" => "Signed URL", 'event' => "Event", 'none' => "None", ],
                'label_for'             => 'sesamy_content_types',
            )
        );

        
        add_settings_field(
            'sesamy_api_endpoint',
            __( 'API Endpoint', 'sesamy' ),
            [$this, 'settings_render_select'],
            'sesamy',
            'sesamy_section_general',
            array(
                'name'                  => 'sesamy_api_endpoint',
                'options'               => ['production' => 'Production', "develop" => "Develop" ],
                'label_for'             => 'sesamy_api_endpoint',
            )
        );

    }


    function sesamy_section_general_callback(){
        // Add if needed
    }

    function settings_render_select($args){

        $settings_value = get_option( $args['name'] );

        echo "<select name=\"{$args['name']}\">";
        foreach($args['options'] as $key => $value)
        {
            $selected = $key == $settings_value ? 'selected' : '';
            echo "<option value=\"$key\" $selected>$value</option>";
        }
        echo "</select>";
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


    function settings_render_input($args) {

        $settings_value = get_option( $args['name'] );

        echo '<input type="text" name="' . $args['name'] . '" value="' . $settings_value . '">';
    }


}

