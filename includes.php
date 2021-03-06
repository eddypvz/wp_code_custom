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
require_once("classes/WPCC_Builder.php");
require_once("classes/WPCC_Save.php");
require_once("classes/WPCC_Repeater.php");

// Load entity
require_once("classes/entity_get.php");
require_once("classes/entity_create.php");
require_once("classes/entity.php");

// Data retriever
require_once("classes/WPCC_DataRetriever.php");

// thumbnails support
add_theme_support("post-thumbnails");

//Vendor
$vendor = [
    "jquery" => [
        "jquery-3.3.1.min.js"
    ],
    "fontawesome" => [
        "fontawesome-all.min.css",
    ],
    "chosen" => [
        "chosen.jquery.js",
        "chosen.css",
    ],
    "bootstrap4" => [
        "bootstrap-grid.min.css",
    ],
    "trumbowyg" => [
        "trumbowyg.js",
        "ui/trumbowyg.css",
    ]
];

// Picker
$assets = [
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
    ],
    "js" => [
        "builder.js",
    ],
];
WPCC_load_scripts_folder($assets, "assets/private", true);