<?php
/**
 * Plugin Name: WP Code Custom
 * Description: WP Code Custom is a plugin that allow administrate wordpress postypes, taxonomies, fields and more. All configs can be make through the code in functions.php or anywhere.
 * Version: 1.0
 * Author: Eddy Pérez
 * License: e.g
 *
 * @package WP Code custom
 */

if(!defined( 'ABSPATH' ))exit;

const WPCC_VERSION = '1.0.0';

// Load includes
require_once("includes.php");

use wp_code_custom\entity_get;
use wp_code_custom\entity_create;


final class wp_code_custom {

	public function CreateEntity() {
		return entity_create::instance();
	}

    public function GetEntity() {
        return entity_get::instance();
    }

    public function EnableDebug() {
	    define("WPCC_DEBUG_MODE", true);
    }

    //singleton instance
    public static function Instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new wp_code_custom();
        }
        return $instance;
    }
}