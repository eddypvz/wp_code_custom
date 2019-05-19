<?php
use wp_code_custom\entity_get;

class WPCC_Save {

    static function save($post_id) {
        global $wpdb;

        // $postype = (!empty($_POST["post_type"])) ? $_POST["post_type"] : false;

        //si se guardo automaticamente
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)return $post_id;

        //verifica permisos para editar
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']){
            if (!current_user_can('edit_page', $post_id))return $post_id;
        }
        else if (!current_user_can('edit_post', $post_id)){
            return $post_id;
        }

        $wpcc = entity_get::instance();
        $treeFields = $wpcc->getTree();

        //Each to post and validate fields
        foreach ($_POST as $key => $value) {

            // If the field exists in register for wpcc
            if (array_key_exists($key, $treeFields)) {

                // If is not a repeatable field
                if (!$treeFields[$key]["repeatable"]) {

                    // Get only first
                    if (!empty($value[0])) {
                        foreach ($value[0] as $fieldKey => $fieldValue) {
                            $fieldSlug = "{$key}_{$fieldKey}";
                            update_post_meta($post_id, $fieldSlug, $fieldValue);
                        }
                    }
                }
                else{
                    update_post_meta($post_id, $key, $value);
                }
            }
        }
    }
}

//Action for save post
add_action('save_post', function($post_id = 0){
    WPCC_Save::save($post_id);
});
