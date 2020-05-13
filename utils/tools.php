<?php
if (!function_exists("dd")) {
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

function WPCC_message($from = "", $message = "", $boolDie = false) {
	$strMsg = "WPCC -> {$from} Says: {$message}";
    if ($boolDie) {
		die($strMsg);
    }
    else {
		print $strMsg;
    }
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

/**
 * Disable Gutenberg editor.
 * @return bool
 */
function WPCC_disable_gutenberg() {
    // Disable Gutenberg
    if (version_compare($GLOBALS['wp_version'], '5.0-beta', '>')) {
        add_filter('use_block_editor_for_post_type', '__return_false', 100);
    } else {
        add_filter('gutenberg_can_edit_post_type', '__return_false');
    }
}

function WPCC_Debug_Field($arrDebug = []) {
    if (defined("WPCC_DEBUG_MODE")) {
        ?>
        <div class="WPCC_Debug_Msg">
            <?php
            foreach ($arrDebug as $key => $item) {
                ?>
                <div>
                    <?php
                    if(!is_int($key)) {
                        print "{$key}: ";
                    }
                    print "{$item}";
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
}