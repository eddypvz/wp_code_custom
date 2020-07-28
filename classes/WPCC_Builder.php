<?php

use wp_code_custom\entity;
use wp_code_custom\entity_get;

class WPCC_Builder {

    public static function Add_Taxonomy($slug, $label, $items = [], Entity $entity, $editable = true) {

        // Set the args for entity
        $args["slug"] = $slug;
        $args["label"] = $label;
        $args["entity_parent"] = $entity;
        $args["repeatable"] = $args["repeatable"] ?? false;

	    // Validation if the entity is an postype
	    if ($entity->GetType() !== "postype") {
		    WPCC_message("WPCC_Builder", "Trying to add '{$slug}' taxonomy to non post type identity.", true);
	    }

	    // Get entity
        $entityTaxonomy = entity_get::instance()->fromTaxonomy($args["slug"], $args);

        $postype_slug = $entity->GetSlug();

	    register_taxonomy(
		    $slug, // Taxonomy slug
		    $postype_slug, // Postype slug
		    array(
			    'label' => $label,
			    'rewrite' => array('slug' => $slug),
			    'hierarchical' =>  true,
			    'public' =>  true,
			    'has_archive' =>  true,
			    'show_ui' =>  true,
			    'show_in_menu' =>  $editable,
			    'show_in_rest' =>  true,
			    'capabilities' => array (
				    'manage_terms' => 'manage_categories',
				    'edit_terms' => 'manage_categories',
				    'delete_terms' => 'manage_categories',
				    'assign_terms' => 'edit_posts',
			    )
		    )
	    );

	    // Insert the structure
	    foreach($items as $keyTax => $itemTax) {
		    if(is_integer($keyTax)){
			    wp_insert_term($itemTax, $slug, [
				    "slug"=>"tax_{$postype_slug}_{$itemTax}"
			    ]);
		    }
		    else{
			    wp_insert_term($itemTax, $slug, [
				    "slug"=>$keyTax
			    ]);
		    }
	    }

        // On draw and edit fields for taxonomy
	    $draw_childs = function($term) use ($entityTaxonomy, $args) {
            // Include media
            wp_enqueue_media();
            // Create nonce
            wp_nonce_field( "{$args["slug"]}_termmeta", "{$args["slug"]}_termmeta_nonce" );
            // Draw cards for groups
            foreach ($entityTaxonomy->GetChildren() as $child) {
                do_action($child["slug"], $term);
            }
        };
	    add_action( "{$args["slug"]}_add_form_fields", $draw_childs);
	    add_action( "{$args["slug"]}_edit_form_fields", $draw_childs);

	    // Save terms
        $saveTerm = function( $term_id ) use ($args) {

            // Comprobamos si se ha definido el nonce.
            $nonce = $_POST["{$args["slug"]}_termmeta_nonce"] ?? "";
            if($nonce === "") return $term_id;

            // Verificamos que el nonce es válido.
            if ( !wp_verify_nonce( $nonce, "{$args["slug"]}_termmeta" ) ) {
                return $term_id;
            }

            // Get tree fields
            $wpcc = entity_get::instance();
            $treeFields = $wpcc->getTree();

            //Each to post and validate fields
            foreach ($_POST as $key => $value) {

                // If the field exists in register for wpcc
                if (array_key_exists($key, $treeFields)) {

                    // If is not a repeatable field
                    if (!$treeFields[$key]["repeatable"]) {

                        // Get only first
                        if (!empty($value[0])) {
                            foreach ($value[0] as $fieldKey => $fieldValue) {
                                $fieldSlug = "{$key}_{$fieldKey}";
                                // Process data to field
                                $old_value = get_term_meta( $term_id, $fieldSlug, true);
                                update_term_meta( $term_id, $fieldSlug, $fieldValue, $old_value );
                            }
                        }
                    }
                    else {
                        $old_value = get_term_meta( $term_id, $key, true);
                        update_term_meta( $term_id, $key, $value, $old_value );
                    }
                }
            }
        };
        add_action( "edited_{$args["slug"]}", $saveTerm );
        add_action( "create_{$args["slug"]}", $saveTerm );

	    return $entityTaxonomy;
    }

    public static function Add_Metabox($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an postype
        if ($entity->GetType() !== "postype") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' metabox to non post type identity.", true);
        }

        // Set the args for entity
        $args["entity_parent"] = $entity;
        $args["slug"] = "WPCC_{$slug}";
        $args["postype_parent"] = $entity->GetSlug();
        $args["label"] = $label;
        $args["repeatable"] = $args["repeatable"] ?? false;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["value"] = $args["value"] ?? "";

        $entityMetabox = entity_get::instance()->fromMetabox($args["slug"], $args);

        // Add metaboxes action
        add_action('add_meta_boxes', function () use ($args, $entityMetabox) {

            // Add metabox
            add_meta_box(
                $args["slug"],
                $args["label"],
                function ($post) use ($entityMetabox) {
                    foreach ($entityMetabox->GetChildren() as $child) {
                        do_action($child["slug"], $post);
                    }
                },
                $args["entity_parent"]->getSlug(),
                'normal',
                'high'
            );
        });

        return $entityMetabox;
    }

    public static function Add_Group($slug, $label, Entity $entity, $args = []) {

        // Set the args for entity
        $args["slug"] = "{$entity->GetSlug()}_{$slug}";
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["entity_parent"] = $entity;
        $args["repeatable"] = $args["repeatable"] ?? false;
	    $args["postype_parent"] = $entity->GetPostypeParent();

        // Save definition
        $entity->SetChildren($args);

        // Entity for group
        $entityGroup = entity_get::instance()->fromMetaboxGroup($args["slug"], $args);

        // Action for group
        add_action($args["slug"], function ($post) use ($args, $entityGroup) {
            ?>
            <div class="WPCC_group">
                <div class="title">
                    <?= $args["label"] ?>
                </div>
                <div class="WPCC_group_content">
                    <?php do_action("{$args["slug"]}_card", $post); ?>
                </div>
                <?php if ($args["repeatable"]): ?>
                    <div class="text-right">
                        <div class="WPCC_repeater_add" data-slug="<?= $args["slug"] ?>" data-type="card">
                            <span class="repeater_add"><i class="dashicons-before dashicons-plus"></i></span>
                            &nbsp; Agregar nuevo elemento
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        });

        // Action for card
        add_action("{$args["slug"]}_card", function($post) use ($args, $entityGroup) {

            $groupArgs = [];
            $groupArgs["post_id"] = $post->ID ?? 0;
            $groupArgs["term_id"] = $post->term_id ?? 0;
            $groupArgs["card_values"] = [];

            // Default values for group
            $groups = [];
            $groups[0] = false;
            $groupData = [];

            // If is not repeater draw by ajax, get the values
            if (empty($post->repeat_number)) {

                // If is an option page
                if ($args["entity_parent"]->GetType() === "options_page") {
                    if ($args["repeatable"] === true) {
                        if ($groupData = get_option($entityGroup->GetSlug())) {
                            $groups = $groupData;
                        };
                    }
                    else {
                        if ($groupData = get_option($entityGroup->GetSlug())) {
                            $groupData = $groupData[0];
                        };
                    }
                }
                // If the entity parent is an taxonomy
                else if ($args["entity_parent"]->GetType() === "taxonomy") {
                    if ($args["repeatable"] === true) {
                        if ($groupData = get_term_meta( $groupArgs["term_id"], $entityGroup->GetSlug(), true)) {
                            $groups = $groupData;
                        };
                    }
                    else {
                        $groupData = get_term_meta( $groupArgs["term_id"] );
                    }
                }
                // If the entity parent is a post
                else {
                    // Get the meta for fields that are not repeatable
                    if ($args["repeatable"] === true) {
                        // If the group are repeatable, get only by slug of group
                        if ($groupData = get_post_meta($groupArgs["post_id"], $args["slug"], true)) {
                            $groups = $groupData;
                        };
                    }
                    else {
                        $groupData = get_post_meta($groupArgs["post_id"]);
                    }
                }
            }

            // Each to groups
	        $countRepeater = 0;
            foreach ($groups as $cardValue) {

                // Use the repeat number
                $groupArgs["repeat_number"] = $post->repeat_number ?? $countRepeater ?? 0;

                // If the field not are repeatable, process the metafields
                if (!$args["repeatable"]) {
                    if (is_array($groupData)) {
                        foreach ($groupData as $fieldKey => $dataValue) {
                            if ($args["entity_parent"]->GetType() === "options_page") {
                                $groupArgs["card_values"][$fieldKey] = $dataValue ?? "";
                            }
                            else{
                                $groupArgs["card_values"][$fieldKey] = $dataValue[0] ?? "";
                            }
                        }
                    }
                }
                else{
                    // Set the values for actual card
                    $groupArgs["card_values"] = $cardValue;
                }
                ?>
                <div class="column WPCC_group_item">
                    <div class="WPCC_card">
                        <?php
                        foreach ($entityGroup->GetChildren() as $child) {
                            do_action($child["slug"], $groupArgs);
                        }
                        ?>
                        <?php if ($args["repeatable"]): ?>
                            <div class="WPCC_repeater_delete">
                                <span class="repeater_delete">
                                    <i class="dashicons-before dashicons-no"></i>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="clear"></div>
                    </div>
                </div>
                <?php
	            $countRepeater++;
            }
        });

        return $entityGroup;
    }

    public static function Add_Field_Text($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["locked"] = $args["locked"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="column">
                <div class="form-group">
                    <label><?= $args["label"] ?></label>
                    <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="text" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>" <?= print ($args["locked"])?"disabled='disabled'":"" ?> />
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Plain_Text($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["rows"] = $args["rows"] ?? 6;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["locked"] = $args["locked"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="column">
                <div class="form-group">
                    <label><?= $args["label"] ?></label>
                    <textarea name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" rows="<?= $args["rows"] ?>" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" <?= print ($args["locked"])?"disabled='disabled'":"" ?> ><?= $args["value"] ?></textarea>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Media($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["image_path"] = WP_CODE_CUSTOM_DIR."/assets/private/img/";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;

            $filePreviewUri = (!empty($args["value"]))?$args["value"]:"{$args["image_path"]}/noimage.png";
            $fileType = WPCC_Filetype($filePreviewUri);

            $filePreview = $filePreviewUri;
            if ($fileType["type"] == "file") {
               $filePreview = WP_CODE_CUSTOM_DIR."/assets/private/img/file-extensions/{$fileType["ext"]}.png";
            }
            ?>
            <div class="column">
                <div class="form-group">
                    <label><?= $args["label"] ?></label>
                    <div class="WPCC_Field_Media">
                        <div>
                            <div class="preview_image" style="display: <?= ($fileType["type"] == "image" || $fileType["type"] == "file") ? "block" : "none" ?>">
                                <img src="<?= $filePreview ?>" data-images="<?= WP_CODE_CUSTOM_DIR."/assets/private/img/" ?>" />
                            </div>
                            <div class="preview_video" style="display: <?= ($fileType["type"] == "video") ? "block" : "none" ?>">
                                <video controls="controls" preload="metadata" style="max-width: 100%;/>
                                <source src="<?= $filePreview ?>#t=0.5" type="video/mp4">
                                </video>
                            </div>
                            <div class="preview_file" style="display: <?= ($fileType["type"] == "file") ? "block" : "none" ?>">
                                <a class="filenamePreviewLink" href="<?= $filePreviewUri ?>" target="_blank"><?= $fileType["name"] ?></a>
                            </div>
                        </div>
                        <input type="hidden" name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" value="<?= $args["value"] ?>"/>
                        <div class="btn-media">
                            <a class="link link-default WPCC_Field_Media_Action" data-action="select">Seleccionar</a>
                            <a class="link link-danger WPCC_Field_Media_Action" data-action="delete">Quitar</a>
                        </div>
                    </div>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Date($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="column">
                <div class="form-group">
                    <label><?= $args["label"] ?></label>
                    <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="date" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>"/>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Number($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="column">
                <div class="form-group">
                    <label><?= $args["label"] ?></label>
                    <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="number" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>"/>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Select($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $args["slug"];
        $args["label"] = $label;
        $args["repeatable"] = $args["repeatable"] ?? false;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["options"] = $args["options"] ?? [];

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="column">
                <div class="form-group WPCC_Field_Select">
                    <label><?= $args["label"] ?></label>
                    <select name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" data-placeholder="<?= $args["placeholder"] ?>"
                            class="chosen-select">
                        <?php foreach ($args["options"] as $key => $value): ?>
                            <?php $selected = ($args["value"] == $key) ? "selected='selected'" : ""; ?>
                            <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                        <?php endforeach; ?>
                        ?>
                    </select>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Checkbox($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["repeatable"] = $args["repeatable"] ?? false;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="column">
                <div class="form-group WPCC_Field_Checkbox">
                    <?php $checked = ($args["value"] == 1) ? "checked='checked'" : ""; ?>

                    <label><?= $args["label"] ?></label>
                    <input type="hidden" name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" value="0"/>

                    <label class="switch">
                        <input type="checkbox" name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" <?= $checked ?> value="1"/>
                        <span class="slider round"></span>
                    </label>
                </div>
                <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
            </div>
            <?php
        });
    }

    public static function Add_Field_Editor($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["repeatable"] = $args["repeatable"] ?? false;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["height"] = $args["height"] ?? 30;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Editor slug
            $newSlug = $args["slug"]."_".uniqid();
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$args["name"]] ?? wpautop($groupArgs["card_values"][$args["name"]]) ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="clear"></div>
            <div class="form-group">
                <label><?= $args["label"] ?></label>
                <div class="WPCC_Field_Editor" data-slug="<?= $newSlug ?>">
                    <textarea id="<?= $newSlug ?>" name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]"><?= $args["value"] ?></textarea>
                </div>
                <small class="field_description"><?= $args["description"] ?></small>
                <?php WPCC_Debug_Field(["Slug"=> $args["slug"], "Slug System" => $args["slug"]]) ?>
            </div>
            <?php
        });
    }

    public static function Add_Field_Color_Picker($slug, $label, Entity $entity, $args = []) {

        // Validation if the entity is an group
        if ($entity->GetType() !== "group") {
            WPCC_message("WPCC_Builder", "Trying to add '{$slug}' field to non group identity.", true);
        }

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
        $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["locked"] = $args["locked"] ?? "";

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function ($groupArgs) use ($args, $slug) {
            // Value and repeater
            $args["value"] = $groupArgs["card_values"][$slug] ?? $groupArgs["card_values"][$args["slug"]] ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            $pickerID = "{$args["slug_parent"]}_{$repeater}_{$args["name"]}";
            ?>
            <div class="column">
                <div class="form-group">
                    <label><?= $args["label"] ?></label>
                    <div class="WPCC_color_picker" data-picker="<?= $pickerID ?>">
                        <div id="<?= $pickerID ?>"></div>
                        <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="hidden" class="form-control picker-value" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>" />
                    </div>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                    <?php WPCC_Debug_Field(["Slug"=> $args["name"], "Slug System" => $args["slug"]]) ?>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_HTML(Entity $entity, $callback) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "custom_html_".uniqid();
	    $args["postype_parent"] = $entity->GetPostypeParent();
        $args["name"] = $args["slug"];

        // Save definition
        $entity->SetChildren($args);

        add_action($args["slug"], function () use ($callback) {
            if(is_callable($callback)){
                call_user_func($callback);
            }
        });
    }
}