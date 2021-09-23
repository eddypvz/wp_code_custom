<?php
// Define the plugin path
define( 'WP_CODE_CUSTOM_DIR', plugin_dir_url(__FILE__));
define( 'WP_CODE_CUSTOM_PATH', plugin_dir_path(__FILE__));

// Load langs
add_action('plugins_loaded', function(){
    load_plugin_textdomain( 'wp_code_custom', false, basename( dirname( __FILE__ ) ) . '/languajes');
});

// Add editor
add_action( "admin_enqueue_scripts" , function() {
    wp_enqueue_editor();
});



// load utils
require_once("utils/tools.php");

// Load builder
require_once("classes/WPCC_Response.php");
require_once("classes/WPCC_Builder_Ajax.php");
require_once("classes/WPCC_Builder.php");
require_once("classes/WPCC_Save.php");
require_once("classes/WPCC_Repeater.php");

// Load entity
require_once("classes/entity_get.php");
require_once("classes/entity_create.php");
require_once("classes/entity.php");

// Data retriever
require_once("classes/WPCC_DataRetriever.php");
require_once("classes/WPCC_DataRetrieverSource.php");

// thumbnails support
add_theme_support("post-thumbnails");

// load vendor
$assets = [
    "chosen" => [
        "chosen.jquery.min.js",
        "chosen.min.css",
    ],
    "autocomplete" => [
        "autoComplete.min.js",
        "autoComplete.min.css",
    ],
    "pickr-master" => [
        "classic.min.css",
        "pickr.min.js",
    ],
];
WPCC_load_scripts_folder($assets, "vendor", true);


// Builder
$assets = [
    "css" => [
        "builder.css",
        "wpcc-modal-tools.css",
    ],
    "js" => [
        "builder.js",
        "wpcc-modal-tools.js" => 'header',
    ],
];
WPCC_load_scripts_folder($assets, "assets/private", true);