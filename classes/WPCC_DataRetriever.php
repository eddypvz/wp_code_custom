<?php
use wp_code_custom\entity_get;

class WPCC_DataRetriever {

    static function fields($postOrTermID, $from = "post") {

        $treeFields = entity_get::instance()->getTree();

        $searcFieldName = function($keyField) use ($treeFields) {
            $nameField = false;
            if (array_key_exists($keyField, $treeFields)) {
                $nameField = $treeFields[$keyField]["name"] ?? false;
            }
            return $nameField;
        };

        // Fields to return
        $fields = [];

        // Get post meta
        if ($from === "post") {
            $metadata = get_post_meta($postOrTermID, '', true);
        }
        else {
            $metadata = get_term_meta($postOrTermID, '', true);
        }

        // If the post meta it's ok
        if ($metadata) {
            foreach ($metadata as $key => $item) {

                // If is an array, get the first
                $item = $item[0] ?? $item;

                // If the name field exist in tree
                if ($nameField = $searcFieldName($key)) {

                    // If is serialized
                    if (is_serialized($item)) {

                    	// Data repeatable
                        $dataRepeatable = [];
                        $repeatableData = @unserialize($item);

                        // If the unserialize it's ok
                        if (is_array($repeatableData)) {
                            // Fix the name for childrens
                            foreach ($repeatableData as $keyData => $valueData) {
                                foreach ($valueData as $keyField => $valueField) {
                                    if ($nameFieldChild = $searcFieldName($keyField)) {
                                        $dataRepeatable[$keyData][$nameFieldChild] = $valueField;
                                    }
                                    else {
                                        $dataRepeatable[$keyData][$keyField] = $valueField;
                                    };
                                }
                            }
                        }
                        $fields[$nameField] = $dataRepeatable;
                    }
                    else {
                        $fields[$nameField] = $item;
                    }
                }
            }
        }
        return $fields;
    }

    static function post_taxonomy($post_id, $taxonomy_slug) {
        $taxonomyGet = wp_get_post_terms($post_id, $taxonomy_slug);
        $taxonomies = [];

        if(count($taxonomyGet) > 0){
            foreach ($taxonomyGet as $valueTax){
                $taxonomies[$valueTax->slug] = $valueTax;

            }
        }
        return $taxonomies;
    }

    /**
     * @param string $slug, Slug for postype to retrive posts.
     * @param integer $rows, Rows for retrive, 0 is unlimited.
     * @param array $args
     * @return array
     */
    static function posts($from = null, $rows = 20, $args = []) {

    	// Defaults from
	    $postype = null;
	    $postID = null;
	    $postIDS = null;

	    if (is_string($from)) {
		    $postype = $from;
	    }
	    else if (is_integer($from)) {
		    $postID = $from;
	    }
	    else if (is_array($from)) {
		    $postIDS = $from;
	    }

        // Default params
        $args["rows"] = $rows;
        $args["unique_display"] = $args["unique_display"] ?? true;

        // Supports ["fields", "permalink", "thumbnail"]
        $args["include"] = $args["include"] ?? ["fields", "permalink"]; // Defaults includes

        // Supports ["category", "custom_tax"]
        $args["include_taxonomies"] = $args["include_taxonomies"] ?? []; // Defaults includes

        // Filters
        $args["filters"] = $args["filters"] ?? []; // Defaults includes

	    $params = [
		    'posts_per_page' => $args["rows"],
		    'no_found_rows' => true,
	    ];

        // Params for wp query
	    if ($postype !== null) {
		    $params['post_type'] = $postype;
	    }
	    else if($postID !== null) {
		    $params['p'] = $postID;
		    $params['post_type'] = 'any';
		    $args["unique_display"] = true;
	    }
	    else if($postIDS !== null) {
		    $params['post__in'] = $postIDS;
		    $params['post_type'] = 'any';
	    }

        // Apply filters
        foreach ($args["filters"] as $valueFilter) {

            $type = $valueFilter[0] ?? false;
            $slug = $valueFilter[1] ?? false;
            $compare = $valueFilter[2] ?? "=";
            $value = $valueFilter[3] ?? false;

            if ($type && $slug && $compare) {

                if ($type === "taxonomy") {
                    $params["tax_query"][] = [
                        'taxonomy' => $slug,
                        'field' => 'slug',
                        'terms' => $value
                    ];
                }
                else if ($type === "field") {
                    $slug = "WPCC_".str_replace("/", "_", $slug);
                    $compare = is_array($value) ? "IN" : $compare;

                    if ($compare === "EXISTS" || $compare === "NOT EXISTS"){
                        $params["meta_query"][] = [
                            'key' => $slug,
                            'compare' => $compare,
                        ];
                    }
                    else{
                        $params["meta_query"][] = [
                            'key' => $slug,
                            'value' => $value,
                            'compare' => $compare,
                        ];
                    }
                }
            }
            else{
                WPCC_message("DataRetriver", "Bad filter in query to '{$slug} postype'", true);
            }
        }

        // Make wp query
        $query = new WP_Query( $params );

        if (!empty($query->posts)) {
            // If have posts
            foreach($query->posts as $post) {

                // Get fields
                if (in_array("fields", $args["include"])) {
                    $post->wpcc_fields = WPCC_DataRetriever::fields($post->ID);
                }

                // Get taxonomies
                if (count($args["include_taxonomies"]) > 0) {
                    $taxonomies = [];
                    foreach ($args["include_taxonomies"] as $taxonomy_slug) {
                        $taxonomy = WPCC_DataRetriever::post_taxonomy($post->ID, $taxonomy_slug);
                        $taxonomies[$taxonomy_slug] = $taxonomy;
                    }
                    $post->taxonomies = $taxonomies;
                }

                // Get permalink
                if (in_array("permalink", $args["include"])) {
                    $post->permalink = get_permalink($post->ID);
                }

                // Get thumbnail
                if (in_array("thumbnail", $args["include"])) {
                    $thumb_id = get_post_thumbnail_id($post);
                    $post->thumbnail = new \stdClass();
                    $post->thumbnail->thumbnail = wp_get_attachment_image_src($thumb_id,'thumbnail', false)[0];
                    $post->thumbnail->small = wp_get_attachment_image_src($thumb_id,'medium', false)[0];
                    $post->thumbnail->medium = wp_get_attachment_image_src($thumb_id,'medium_large', false)[0];
                    $post->thumbnail->full = wp_get_attachment_image_src($thumb_id,'full', false)[0];
                }
            }
        }

        // Unique display
        if ($rows === 1 && $args["unique_display"] === true) {
            return $query->posts[0] ?? [];
        }

        // Return posts
        return $query->posts;
    }

    static function taxonomy($taxonomy = "", $rows = 20, $args  = []) {

        // Defaults
        $args["rows"] = $rows;

        // Filters
        $args["filters"] = $args["filters"] ?? []; // Defaults includes
        $args["hide_empty"] = $args["hide_empty"] ?? false; // Defaults includes
        $args["unique_display"] = $args["unique_display"] ?? true;

        // Supports ["fields"]
        $args["include"] = $args["include"] ?? ["fields"]; // Defaults includes

        $params = array(
            'hide_empty' => $args["hide_empty"], // also retrieve terms which are not used yet
            'number' => $args["rows"],
            'taxonomy'  => $taxonomy,
        );

        // Apply filters
        foreach ($args["filters"] as $valueFilter) {

            $type = $valueFilter[0] ?? false;
            $slug = $valueFilter[1] ?? false;
            $compare = $valueFilter[2] ?? "=";
            $value = $valueFilter[3] ?? false;

            if ($type && $slug && $compare) {

                if ($type === "field") {
                    $slug = str_replace("/", "_", $slug);
                    $compare = is_array($value) ? "IN" : $compare;

                    if ($compare === "EXISTS" || $compare === "NOT EXISTS"){
                        $params["meta_query"][] = [
                            'key' => $slug,
                            'compare' => $compare,
                        ];
                    }
                    else{
                        $params["meta_query"][] = [
                            'key' => $slug,
                            'value' => $value,
                            'compare' => $compare,
                        ];
                    }
                }
            }
            else{
                WPCC_message("DataRetriver", "Bad filter in query to '{$slug} postype'", true);
            }
        }

        $terms = get_terms( $params );

        //
        foreach($terms as $term) {
            // Get fields
            if (in_array("fields", $args["include"])) {

                /*$term_vals = get_term_meta($term->term_id);
                dd($term_vals);*/

                $term->wpcc_fields = WPCC_DataRetriever::fields($term->term_id, "term");
            }
        }

        // Unique display
        if ($terms === 1 && $args["unique_display"] === true) {
            return $terms[0] ?? [];
        }

        return $terms;
    }

    static function page_options($slug = "") {

        $arrData = [];

        // Draw the childrens
        foreach (entity_get::instance()->fromOptionsPage($slug)->GetChildren() as $child) {
            $optionData = get_option($child["slug"]);

            if (isset($child["repeatable"]) && $child["repeatable"]) {
                $arrData[$child["slug"]] = $optionData;
            }
            else {
                $arrData[$child["slug"]] = $optionData[0] ?? [];
            }
        }
        return $arrData;
    }
}