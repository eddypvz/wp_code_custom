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
	    $args["enable_categories"] = $args["enable_categories"] ?? false;

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

	    // Enable categories
	    if ($args["enable_categories"]) {
            add_action('init', function() use ($slug) {
                register_taxonomy_for_object_type('category', $slug);
            });
        }

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

	    //merge $colums array. this add the colums to headers in grid
	    add_filter("manage_{$slug}_posts_columns", function($columns) use ($slug) {
		    $arrToMerge = [];
		    foreach(entity_get::instance()->getTree() as $slug_field => $field){
		        // if is child of postype
			    if(isset($field["postype_parent"]) && isset($field["show_in_grid"])) {
				    if ( $field["postype_parent"] === $slug && $field["show_in_grid"] == true ) {
					    $arrToMerge[ $field["name"] ] = $field["label"];
				    }
			    }
		    }
		    return array_merge($columns, $arrToMerge);
	    });

	    // Get values for grid
	    add_action("manage_{$slug}_posts_custom_column", function($column_slug, $post_id) {
		    $postFields = \WPCC_DataRetriever::fields($post_id);
		    if (!empty($postFields[$column_slug])) {
		        print $postFields[$column_slug];
		    }
	    }, 10, 2);

	    // Join for custom filter
	    add_filter( 'posts_join', function ($join) use ($slug) {
		    global $pagenow, $wpdb;

		    // Only for postype and grid
		    if ( is_admin() && 'edit.php' === $pagenow && (isset($_GET['post_type']) && $slug === $_GET['post_type']) && get_query_var("s") !== "" ) {
			    $join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND (1=1 ";

			    foreach(entity_get::instance()->getTree() as $slug_field => $field) {
				    if(isset($field["postype_parent"]) && isset($field["show_in_grid"])) {
					    if ( $field["postype_parent"] === $slug && $field["show_in_grid"] == true ) {
						    $join .= " OR {$wpdb->postmeta}.meta_key = '{$slug_field}' ";
					    }
				    }
			    }
			    $join .= " )";
		    }
		    return $join;
	    });

	    // Where for custom filter
	    add_filter( 'posts_where', function ($where) use ($slug) {
		    global $pagenow, $wpdb;

		    // Only for postype and grid
		    if ( is_admin() && 'edit.php' === $pagenow && (isset($_GET['post_type']) && $slug === $_GET['post_type']) && get_query_var("s") !== "" ) {
		        $search = esc_sql(get_query_var("s"));
			    foreach(entity_get::instance()->getTree() as $slug_field => $field) {
			        if(isset($field["postype_parent"]) && isset($field["show_in_grid"])) {
				        if ($field["postype_parent"] === $slug && $field["show_in_grid"] == true) {
					        $where .= " OR ({$wpdb->postmeta}.meta_value LIKE '%{$search}%') ";
				        }
                    }
			    }
                // Filter by postype
                $where .= " AND ({$wpdb->posts}.post_type = '{$slug}') ";
		    }
		    return $where;
	    } );

	    // Search disctinct for avoid duplicates
        add_filter( 'posts_distinct', function($where) use ($slug) {
            global $pagenow;

            if ( is_admin() && 'edit.php' === $pagenow && (isset($_GET['post_type']) && $slug === $_GET['post_type']) && get_query_var("s") !== "" ) {
                return "DISTINCT";
            }
            return $where;
        });

	    // Return entity
	    return entity_get::instance()->fromPostype($slug);
    }

    public function OptionPage($slug = "", $label = "", $args = []) {

        // Defaults
        $args["slug"] = $slug;
        $args["slug_for_register"] = "WPCC_CP_{$args["slug"]}";
        $args["label"] = $label;
        $args["icon"] = $args["icon"] ?? "dashicons-arrow-right-alt2";
        $args["menu_order"] = $args["menu_order"] ?? 5;
        $args["enable_button_save"] = $args["enable_button_save"] ?? true;
        $args["capability"] = $args["capability"] ?? 'manage_options';

        // create custom plugin settings menu
        add_action('admin_menu', function() use ($args) {
            //create new top-level menu
            add_menu_page($args["label"], $args["label"], $args["capability"], "option_page_{$args["slug"]}", function() use ($args) {
                // Include media
                wp_enqueue_media();
                ?>
                <div class="WPCC_Option_page">
                    <h1 class="WPCC_Option_page_title"><?= $args["label"] ?></h1>
                    <?php settings_errors(); ?>
                    <form method="post" action="options.php">
                        <input type="hidden" name="option_page" value="<?= $args["slug_for_register"] ?>">
                        <?php
                            // Register group settings
                            settings_fields( $args["slug_for_register"] );

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
                    register_setting( $args["slug_for_register"], $child["slug"] );
                }
            });
        });

        // Enable capabilities for custom option page
        add_filter( "option_page_capability_{$args["slug_for_register"]}", function( $capability ) use ($args){
            return $args["capability"];
        } );

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
