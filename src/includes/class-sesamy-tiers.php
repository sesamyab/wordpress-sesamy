<?php

class Sesamy_Tiers {


    public function register_taxonomy(){

        $post_types = sesamy_get_enabled_post_types();

        register_taxonomy( 'sesamy_tiers', $post_types, [
            "label" => "Sesamy Tiers",
            "singular_label" => "Sesamy Tier",
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
        ] );

        // NOTE: Different form templates are used in add and edit because of WordPress is not streamlined :(

        add_action( 'sesamy_tiers_add_form_fields', [ $this, 'add_taxonomy_form_fields' ] );
        add_action( 'sesamy_tiers_edit_form_fields', [ $this, 'edit_taxonomy_form_fields' ] );

        add_action( 'created_sesamy_tiers', [ $this, 'save_fields' ] );
        add_action( 'edited_sesamy_tiers', [ $this, 'save_fields' ] );

    }

    public function save_fields( $term_id ) {

        update_term_meta( $term_id, 'type', sanitize_text_field( $_POST[ 'type' ] ) );
        update_term_meta( $term_id, 'price', sanitize_text_field( $_POST[ 'price' ] ) );
        update_term_meta( $term_id, 'currency', sanitize_text_field( $_POST[ 'currency' ] ) );

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
        <div class="form-field">
            <label for="type">Type</label>
            <?php $this->render_select( 'type', ['single' => 'Single Purchase', 'subscription' => "Subscription"] ) ?>
            <p>Price for this tier</p>
        </div>
        <div class="form-field">
            <label for="price">Price</label>
            <input type="text" name="price" id="cb_custom_meta_data" />
            <p>Price for this tier</p>
        </div>
        <div class="form-field">
            <label for="currency">Currency</label>
            <?php $this->render_select( 'currency', ['SEK' => 'SEK', 'NOK' => "NOK", "DKK" => "DKK"] ) ?>
            <p>Currency for this tier</p>
        </div>
        <?php
    }

    public function edit_taxonomy_form_fields( $term ) {

        $type = get_term_meta( $term->term_id, 'type', true );
        $price = get_term_meta( $term->term_id, 'price', true );
        $currency = get_term_meta( $term->term_id, 'currency', true );

        ?>
        <tr class="form-field">
        <th><label for="type">Type</label></th>
        <td>
            <?php $this->render_select( 'type', ['single' => 'Single Purchase', 'subscription' => "Subscription"], $type) ?>    
            <p>Type for this tier</p></td>
        </tr>
        <tr class="form-field">
        <th><label for="price">Price</label></th>
        <td><input type="text" name="price" id="price" value="<?php echo $price; ?>" />
            <p>Price for this tier</p></td>
        </div>
        <tr class="form-field">
        <th><label for="currency">Currency</label></th>
        <td>
            <?php $this->render_select( 'currency', ['SEK' => 'SEK', 'NOK' => "NOK", "DKK" => "DKK"], $currency) ?>
            <p>Currency for this tier</p></td>
        </tr>
        <?php
    }



}