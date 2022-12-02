<?php


class Sesamy_Utils {

    
    /**
     * Remove empty values and convert array into html attributes 
     */
    public static function html_attributes(array $array) {
        
        return implode(' ', array_map(function ($key, $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            return str_replace( '_', '-', $key ) . '="' . htmlspecialchars($value) . '"';
        }, array_keys($array), $array));
    }

    /*
    * Remove empty values to avoid creating empty attributes
    */
    public static function remove_empty_values(array $array) {
        return array_filter($array, function($value) {
            return !empty($value);
        });
    }

    /**
     * Generate tag, empty content will generate a self-closing tag unless $self_close is false
     */
    public static function make_tag( $name, $atts, $content, $self_close = true){

        $a = self::html_attributes(self::remove_empty_values($atts));

        $tag = "<$name" . (!empty($a) ? " $a" : "");
        $tag .= (empty($content) && $self_close) ? "/>" : ">$content</$name>";

        return $tag;
    }

}