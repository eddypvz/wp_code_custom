<?php
use wp_code_custom\entity_get;

class WPCC_Repeater {

    public static function add($add_from, $type, $repeater_number = 0) {

        $wpcc = entity_get::instance();
        $treeFields = $wpcc->getTree();

        // If the field exists
        if(array_key_exists($add_from, $treeFields)) {
            $args = $treeFields[$add_from];

            // If type is card, add _card to slug for action
            $repeater = new stdClass();
            $repeater->repeat_number = intval($repeater_number);

            if($type == "card") {
                do_action("{$args["slug"]}_card", $repeater);
            }
        }
    }
}

//Canary Ajax
add_action('wp_ajax_wpcc_repeat_add', function() {

    $add_from = !empty($_GET["add_from"])?$_GET["add_from"]:false;
    $type = !empty($_GET["type"])?$_GET["type"]:"";
    $repeater_number = !empty($_GET["repeat"])?$_GET["repeat"]:0;

    WPCC_Repeater::add($add_from, $type, $repeater_number);


    // dd($_GET);

    die;
});