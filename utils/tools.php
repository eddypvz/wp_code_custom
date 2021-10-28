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

    $action = "wp_enqueue_scripts";

    if($boolIsAdmin){
        if(!is_admin())return false;
        $action = "admin_enqueue_scripts";
    }

    add_action( $action , function() use ($files, $from_dir) {
        foreach ($files as $folder => $filesDetail) {
            foreach ($filesDetail as $indexArray => $value) {

                $file = '';
                $in_footer = true;

                if (is_integer($indexArray)) {
                    $file = $value;
                }
                else {
                    if ($value === 'header') {
                        $file = $indexArray;
                        $in_footer = false;
                    }
                }

                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $name = "wpcc_{$folder}_{$file}";

                if($ext == "css"){
                    wp_enqueue_style($name, WP_CODE_CUSTOM_DIR."{$from_dir}/{$folder}/{$file}?wpccv=".WPCC_VERSION);
                }
                else if($ext == "js"){
                    wp_enqueue_script($name, WP_CODE_CUSTOM_DIR."{$from_dir}/{$folder}/{$file}?wpccv=".WPCC_VERSION, [], 1, $in_footer);
                }
            }
        }
    });
}

function WPCC_load_public_script($name) {
    add_action( 'wp_enqueue_scripts', function() use ($name) {
        wp_enqueue_script($name, WP_CODE_CUSTOM_DIR."assets/public/js/{$name}");
    });
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

function WPCC_Filetype($fileName = "") {
    $fileName = strtolower($fileName);

    $fileType = [];
    $fileType["type"] = "no_supported";
    $fileType["ext"] = pathinfo($fileName, PATHINFO_EXTENSION);
    $fileType["name"] = pathinfo($fileName, PATHINFO_FILENAME).$fileType["ext"];

    $imageExtensions = [
        "jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "webp", "bmp", "dib","jp2", "svg", "gif", "tiff", "tif", "raw","svgz"
    ];

    $videoExtensions = [
        "mp4", "webm", "ogg", "avi"
    ];

    $audioExtensions = [
        "mp3", "aac", "midi"
    ];

    $fileExtensions = [
        "pdf", "txt", "eps", "ai", "txt", "doc", "zip",
    ];

    if (in_array($fileType["ext"], $imageExtensions)) {
        $fileType["type"] = "image";
    }
    else if (in_array($fileType["ext"], $videoExtensions)) {
        $fileType["type"] = "video";
    }
    else if (in_array($fileType["ext"], $audioExtensions)) {
        $fileType["type"] = "audio";
    }
    else if (in_array($fileType["ext"], $fileExtensions)) {
        $fileType["type"] = "file";
    }

    return $fileType;
}


// add modal tools
add_action('admin_footer', function () {
    echo '<div class="wpcc_supermodal">
            <div class="wpcc_supermodal_content">
                <div class="wpcc_supermodal_close_bar">
                    <i id="supermodal_close_btn" class="dashicons-before dashicons-no"></i>
                </div>
                <div id="loadingMessage">Cargando...</div>
            </div>
          </div>
          <div id="wpcc_loading" class="wpcc_loading"></div>';
});
