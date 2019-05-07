<?php
use canary\canary;

function dd($var){

    print "<pre>";
    if($var == "" || !$var){
        var_dump($var);
    }
    else{
        print_r($var);
    }
    print "</pre>";
}

function WPCC_load_scripts_folder($files = [], $from_dir = "", $boolIsAdmin = false) {

    if($boolIsAdmin){
        if(!is_admin())return false;
    }

    foreach ($files as $folder => $filesDetail) {
        foreach ($filesDetail as $file){
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $name = "wpcc_{$folder}_{$file}";

            if($ext == "css"){
                wp_enqueue_style($name, WP_CODE_CUSTOM_DIR."{$from_dir}/{$folder}/{$file}");
            }
            else if($ext == "js"){
                wp_enqueue_script($name, WP_CODE_CUSTOM_DIR."{$from_dir}/{$folder}/{$file}", [], 1, true);
            }
        }
    }
}

function WPCC_load_public_script($name) {
    wp_enqueue_script($name, WP_CODE_CUSTOM_DIR."assets/public/js/{$name}");
}

/**
 * Check if Gutenberg is active.
 * Must be used not earlier than plugins_loaded action fired.
 *
 * @return bool
 */
function WPCC_gutenberg_active() {
    $gutenberg    = false;
    $block_editor = false;

    if ( has_filter( 'replace_editor', 'gutenberg_init' ) ) {
        // Gutenberg is installed and activated.
        $gutenberg = true;
    }

    if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
        // Block editor.
        $block_editor = true;
    }

    if ( ! $gutenberg && ! $block_editor ) {
        return false;
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
        return true;
    }

    $use_block_editor = ( get_option( 'classic-editor-replace' ) === 'no-replace' );

    return $use_block_editor;
}