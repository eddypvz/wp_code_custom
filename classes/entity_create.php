<?php
namespace wp_code_custom;

use wp_code_custom\entity_get;

class entity_create {

    private function __construct() {}

    public function Postype($slug = "", $label = "", $args = []) {

    	// Default params
	    $args["public"] = $args["public"] ?? true;
	    $args["show_in_menu"] = $args["show_in_menu"] ?? true;
	    $args["menu_order"] = $args["menu_order"] ?? 5;
	    $args["icon"] = $args["icon"] ?? "dashicons-arrow-right-alt2";
	    $args["disable_editor"] = $args["disable_editor"] ?? false;
	    $args["disable_title"] = $args["disable_title"] ?? false;
	    $args["disable_thumbnail"] = $args["disable_thumbnail"] ?? false;
	    $args["taxonomies"] = $args["taxonomies"] ?? [];

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
                'taxonomies' => $args["taxonomies"],
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

        // Disable thumbnail support
        if(!$args["disable_thumbnail"]){
            add_post_type_support($slug, 'thumbnail');
        }

	    // Return entity
	    return entity_get::instance()->fromPostype($slug);
    }

    public function OptionPage($slug = "", $label = "", $args = []) {

        // Defaults
        $args["slug"] = $slug;
        $args["label"] = $label;
        $args["icon"] = $args["icon"] ?? "dashicons-arrow-right-alt2";
        $args["menu_order"] = $args["menu_order"] ?? 5;
        $args["enable_button_save"] = $args["enable_button_save"] ?? true;

        // create custom plugin settings menu
        add_action('admin_menu', function() use ($args) {
            //create new top-level menu
            add_menu_page($args["label"], $args["label"], 'manage_options', __FILE__, function() use ($args) {
                // Include media
                wp_enqueue_media();
                ?>
                <div class="WPCC_Option_page">
                    <h2 class="WPCC_Option_page_title"><?= $args["label"] ?></h2>
                    <form method="post" action="options.php">
                        <?php
                            // Register group settings
                            settings_fields( "WPCC_CP_{$args["slug"]}" );

                            // Draw the childrens
                            foreach (entity_get::instance()->fromOptionsPage($args["slug"])->GetChildren() as $child) {
                                do_action($child["slug"]);
                            }
                            if ($args["enable_button_save"]) {
                                ?>
                                <div class="buttonSave">
                                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                                </div>
                                <?php
                            }
                        ?>
                    </form>
                </div>
                <?php
            }, $args["icon"], $args["menu_order"]);

            // Register the options childrens
            add_action( 'admin_init', function() use ($args) {
                foreach (entity_get::instance()->fromOptionsPage($args["slug"])->GetChildren() as $child) {
                    register_setting( "WPCC_CP_{$args["slug"]}", $child["slug"] );
                }
            });
        });

        // Return entity
        return entity_get::instance()->fromOptionsPage($slug);
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