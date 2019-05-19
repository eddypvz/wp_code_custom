<?php
namespace wp_code_custom;

use wp_code_custom\entity_get;

class entity_create {

    private function __construct() {

    }

    public function Postype($slug = "", $label = "", $args = []) {

    	// Default params
	    $args["public"] = $args["public"] ?? false;
	    $args["show_in_menu"] = $args["show_in_menu"] ?? true;
	    $args["menu_order"] = $args["menu_order"] ?? 5;
	    $args["icon"] = $args["icon"] ?? "dashicons-arrow-right-alt2";
	    $args["disable_editor"] = $args["disable_editor"] ?? false;
	    $args["disable_title"] = $args["disable_title"] ?? false;

	    //Build the custom postypes
	    register_post_type( $slug,
		    array(
			    'labels' => array(
				    'name' => $label,
				    'singular_name' => $label
			    ),
			    'public' => $args["public"],
			    'has_archive' => true,
			    //'rewrite' => array('slug' => strtolower($this->namePostype)."s", 'with_front' => true),
			    'hierarchical' => false,
			    'show_ui' => true,
			    'show_in_menu' => $args["show_in_menu"],
			    'menu_position' => $args["menu_order"],
			    'show_in_admin_bar' => true,
			    'show_in_nav_menus' => true,
			    'show_in_rest' => true,
			    'query_var' => true,
			    'can_export' => true,
			    'exclude_from_search' => false,
			    'publicly_queryable' => true,
			    'capability_type' => 'post',
			    'menu_icon' => $args["icon"],
		    )
	    );
	    // Disable editor
	    if ($args["disable_editor"]) {
		    remove_post_type_support($slug, 'editor');
	    }
	    // Disable title
	    if ($args["disable_title"]) {
		    remove_post_type_support($slug, 'title');
	    }
	    // Return entity
	    return entity_get::instance()->fromPostype($slug);
    }

    public function Taxonomy($slug = "", $label = "", $args = []) {

    }

    //singleton instance
    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new entity_create();
        }
        return $instance;
    }
}