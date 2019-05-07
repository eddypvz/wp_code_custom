<?php

use wp_code_custom\entity;
use wp_code_custom\entity_create;

class WPCC_Builder {

    public static function Add_Metabox($slug, $label, Entity $entity, $args = []) {

        // Set the args for entity
        $args["entity_parent"] = $entity;
        $args["slug"] = "WPCC_{$slug}";
        $args["label"] = $label;
        $args["repeatable"] = $args["repeatable"] ?? false;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["value"] = $args["value"] ?? "";

        $entityMetabox = entity_create::instance()->fromMetabox($args["slug"], $args);

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
        $args["label"] = $label;
        $args["entity_parent"] = $entity;
        $args["repeatable"] = $args["repeatable"] ?? false;

        // Save definition
        $entity->SetChildren($args);

        // Entity for group
        $entityGroup = entity_create::instance()->fromMetaboxGroup($args["slug"], $args);

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
                    <div class="WPCC_repeater">
                        <span class="repeater_add" data-slug="<?= $args["slug"] ?>" data-type="card"><i class="dashicons-before dashicons-plus"></i></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        });

        // Action for card
        add_action("{$args["slug"]}_card", function($post) use ($args, $entityGroup) {

            $groupArgs = [];
            $groupArgs["post_id"] = $post->ID ?? 0;
            $groupArgs["card_values"] = [];

            // Default values for group
            $groupValues = [];
            $groupValues[] = false;

            // Get the meta for fields that are not repeatable
            if ($args["repeatable"] === true) {
                // If the group are repeatable, get only by slug of group
                if ($postMeta = get_post_meta($groupArgs["post_id"], $args["slug"], true)) {
                    $groupValues = $postMeta;
                };
            }
            else {
                $postMeta = get_post_meta($groupArgs["post_id"]);
            }

            // Each to groups
            foreach ($groupValues as $countRepeater => $cardValue) {

                // Use the repeat number
                $groupArgs["repeat_number"] = $post->repeat_number ?? $countRepeater ?? 0;

                // If the field not are repeatable, process the metafields
                if (!$args["repeatable"]) {
                    if (is_array($postMeta)) {
                        foreach ($postMeta as $postMetaKey => $postMetaValue) {
                            $groupArgs["card_values"][$postMetaKey] = $postMetaValue[0] ?? "";
                        }
                    }
                }
                else{
                    // Set the values for actual card
                    $groupArgs["card_values"] = $cardValue;
                }
                ?>
                <div class="column2 WPCC_group_item">
                    <div class="card">
                        <?php
                        foreach ($entityGroup->GetChildren() as $child) {
                            do_action($child["slug"], $groupArgs);
                        }
                        ?>
                        <?php if ($args["repeatable"]): ?>
                            <div class="WPCC_repeater_delete">
                                <span class="repeater_delete"><i class="dashicons-before dashicons-dismiss"></i></span>
                            </div>
                        <?php endif; ?>
                        <div class="clear"></div>
                    </div>
                </div>
                <?php
            }
        });

        return $entityGroup;
    }

    public static function Add_Field_Text($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
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
                    <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="text" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>">
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Media($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
        $args["name"] = $slug;
        $args["label"] = $label;
        $args["placeholder"] = $args["placeholder"] ?? "";
        $args["size"] = $args["size"] ?? 50;
        $args["show_in_grid"] = $args["show_in_grid"] ?? false;
        $args["description"] = $args["description"] ?? "";
        $args["image_none"] = WP_CODE_CUSTOM_DIR."/assets/private/img/noimage.png" ?? "";

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
                    <div class="WPCC_Field_Media">
                        <img src="<?= (!empty($args["value"]))?$args["value"]:$args["image_none"] ?>" data-none="<?= $args["image_none"] ?>">
                        <input type="hidden" name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" value="<?= $args["value"] ?>">
                        <div class="btn-media">
                            <a class="link link-default WPCC_Field_Media_Action" data-action="select">Seleccionar</a>
                            <a class="link link-danger WPCC_Field_Media_Action" data-action="delete">Quitar</a>
                        </div>
                    </div>
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Date($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
        $args["name"] = $args["slug"];
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
                    <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="date" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>">
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Number($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
        $args["name"] = $args["slug"];
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
                    <input name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" type="number" class="form-control" aria-describedby="wpcc_aria_<?= $args["label"] ?>" placeholder="<?= $args["placeholder"] ?>" value="<?= $args["value"] ?>">
                    <small id="wpcc_aria_<?= $args["label"] ?>" class="field_description"><?= $args["description"] ?></small>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Select($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
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
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Checkbox($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
        $args["name"] = $args["slug"];
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
                        <input type="checkbox" name="<?= $args["slug_parent"] ?>[<?= $repeater ?>][<?= $args["name"] ?>]" <?= $checked ?> value="1">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <?php
        });
    }

    public static function Add_Field_Editor($slug, $label, Entity $entity, $args = []) {

        $args["entity_parent"] = $entity;
        $args["slug_parent"] = $entity->GetSlug();
        $args["slug"] = "{$args["slug_parent"]}_{$slug}";
        $args["name"] = $args["slug"];
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
            $args["value"] = $groupArgs["card_values"][$slug] ?? wpautop($groupArgs["card_values"][$args["slug"]]) ?? "";
            $repeater = $groupArgs["repeat_number"] ?? 0;
            ?>
            <div class="clear"></div>
            <div class="form-group">
                <label><?= $args["label"] ?></label>
                <div class="WPCC_Field_Editor" data-slug="<?= $newSlug ?>">
                    <?php
                    // If gutenberg is active, print the wp_editor
                    if ( WPCC_gutenberg_active() ) {

                        wp_editor($args["value"], $newSlug, [
                            'wpautop' => false,
                            'forced_root_block' => false,
                            'force_br_newlines' => true,
                            'force_p_newlines' => false,
                            "editor_height" => $args["height"],
                            "textarea_name" => "{$args["slug_parent"]}[{$repeater}][{$args["name"]}]"
                        ]);
                    }
                    else {
                        ?>
                        <textarea id="<?= $newSlug ?>" name="<?= $args["slug_parent"] ?>[<?= $args["name"] ?>][]"><?= $args["value"] ?></textarea>
                        <?php
                    }
                    ?>
                </div>
                <small class="field_description"><?= $args["description"] ?></small>
            </div>
            <?php
        });
    }
}