<?php

class Sesamy_Passes {

    private static $instance;

    public static function getInstance() {
        if( empty($instance) ) {
            self::$instance = new Sesamy_Passes();
        }

        return self::$instance;
    }

    public function register_taxonomy(){

        $post_types = sesamy_get_enabled_post_types();

        register_taxonomy( 'sesamy_passes', $post_types, [
            "label" => "Sesamy Passes",
            "singular_label" => "Sesamy Pass",
            'query_var' => true,            
            'public' => false,
            'show_ui' => true,
            'show_tagcloud' => false,
            'show_in_nav_menus' => false,
            'rewrite' => false,
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_in_quick_edit' => false,
            'meta_box_cb' => false,
            'capabilities' => [
                'manage_terms'  => 'manage_options',
                'edit_terms'    => 'manage_options',
                'delete_terms'  => 'manage_options',
                'assign_terms'  => 'edit_posts'
            ]
        ] );


        register_term_meta( 'sesamy_passes', 'price', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'number',
            'default' => 0,
            'auth_callback' => '__return_true'
        ] );


        // NOTE: Different form templates are used in add and edit because of WordPress is not streamlined :(

        add_action( 'sesamy_passes_add_form_fields', [ $this, 'add_taxonomy_form_fields' ] );
        add_action( 'sesamy_passes_edit_form_fields', [ $this, 'edit_taxonomy_form_fields' ] );

        add_action( 'created_sesamy_passes', [ $this, 'save_fields' ] );
        add_action( 'edited_sesamy_passes', [ $this, 'save_fields' ] );

    }

    public function admin_init(){
        add_action( 'admin_enqueue_scripts', [$this, 'load_scripts'] );
    }

    public function load_scripts() {
        if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'sesamy_passes' ) {
          return;
        }

        // Add wp media support
        wp_enqueue_media();
        
        // Load our custom image trigger
		wp_enqueue_script( 'sesamy-passes-admin', SESAMY_PLUGIN_URL. '/admin/js/sesamy-passes-admin.js', array( 'jquery' ), '1.0', false );

      }

    

    public function save_fields( $term_id ) {

        // update_term_meta( $term_id, 'type', sanitize_text_field( $_POST[ 'type' ] ) );
        update_term_meta( $term_id, 'price', sanitize_text_field( $_POST[ 'price' ] ) );
        update_term_meta( $term_id, 'currency', sanitize_text_field( $_POST[ 'currency' ] ) );
        update_term_meta( $term_id, 'url', sanitize_text_field( $_POST[ 'url' ] ) );
        update_term_meta( $term_id, 'image_id', sanitize_text_field( $_POST[ 'image_id' ] ) );
    }


    private function render_select($name, $options, $field_value = null){

        ?>
        <select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
        <?php
        foreach($options as $key => $value){
            echo '<option value="' . $key . '"'. ( $field_value == $key ? 'selected' : '' ) . ">$value</option>";
        }
        ?>
        </select>
        <?php
    }


    public function add_taxonomy_form_fields( $taxonomy ) {

        ?>

        <style>
            .term-parent-wrap {display: none;}
        </style>
       
        <div class="form-field term-group">
            <label for="category-image-id"><?php _e('Image', 'sesamy'); ?></label>
            <input type="hidden" id="taxonomy-image-id" name="image_id" class="custom_media_url" value="">
            <div id="category-image-wrapper"></div>
            <p>
            <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add Image', 'sesamy' ); ?>" />
            <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php _e( 'Remove Image', 'sesamy' ); ?>" />
            </p>
        </div>

       
        <div class="form-field">
            <label for="price">Price</label>
            <input type="text" name="price" />
            <p>Price for this pass</p>
        </div>
        <div class="form-field">
            <label for="currency">Currency</label>
            <?php $this->render_select( 'currency', Sesamy_Currencies::get_currencies() ) ?>
            <p>Currency for this pass</p>
        </div>
        <div class="form-field">
            <label for="cb_custom_meta_data_url">Public URL</label>
            <input type="text" name="url" id="cb_custom_meta_data_url" />
            <p>Url where a visitor can read more about the pass. Also used as the item-src identifier with Sesamy.</p>
        </div>
        <?php
    }

    public function edit_taxonomy_form_fields( $term ) {

        // $type = get_term_meta( $term->term_id, 'type', true );
        $price = get_term_meta( $term->term_id, 'price', true );
        $currency = get_term_meta( $term->term_id, 'currency', true );
        $url = get_term_meta( $term->term_id, 'url', true );
        $image_id = get_term_meta( $term->term_id, 'image_id', true );

        ?>

        <style>
            .term-parent-wrap {display: none;}
        </style>

        <tr class="form-field term-group-wrap">
            <th scope="row">
            <label for="taxonomy-image-id"><?php _e( 'Image', 'showcase' ); ?></label>
            </th>
            <td>
            <input type="hidden" id="taxonomy-image-id" name="image_id" value="<?php echo esc_attr( $image_id ); ?>">
            <div id="category-image-wrapper">
                <?php if( $image_id ) { ?>
                <?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
                <?php } ?>
            </div>
            <p>
                <input type="button" class="button button-secondary ct_tax_media_button" id="showcase_tax_media_button" name="showcase_tax_media_button" value="<?php _e( 'Add Image', 'showcase' ); ?>" />
                <input type="button" class="button button-secondary ct_tax_media_remove" id="showcase_tax_media_remove" name="showcase_tax_media_remove" value="<?php _e( 'Remove Image', 'showcase' ); ?>" />
            </p>
            </td>
        </tr>

        <tr class="form-field">
        <th><label for="price">Price</label></th>
        <td><input type="text" name="price" id="price" value="<?php echo $price; ?>" />
            <p>Price for this tier</p></td>
        </div>
        <tr class="form-field">
        <th><label for="currency">Currency</label></th>
        <td>
            <?php $this->render_select( 'currency', Sesamy_Currencies::get_currencies(), $currency) ?>
            <p>Currency for this tier</p></td>
        </tr>
        <th><label for="url">Public URL</label></th>
        <td><input type="text" name="url" id="url" value="<?php echo $url; ?>" />
            <p>Url where a visitor can read more about the pass. Also used as the item-src identifier with Sesamy.</p></td>
        </div>
        <?php
    }


   

}