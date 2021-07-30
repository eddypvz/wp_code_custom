<?php

class WPCC_DataRetrieverSource {

    private $config;

    public function fields($postOrTermID, $from = "post", $groupByParentSlug = false): WPCC_DataRetrieverSource  {
        $this->config = [
            'type' => 'fields',
            'postOrTermID' => $postOrTermID,
            'from' => $from,
            'groupByParentSlug' => $groupByParentSlug,
        ];
        return $this;
    }

    public function post_taxonomy($post_id, $taxonomy_slug): WPCC_DataRetrieverSource  {
        $this->config = [
            'type' => 'post_taxonomy',
            'post_id' => $post_id,
            'taxonomy_slug' => $taxonomy_slug,
        ];
        return $this;
    }

    /**
     * @param string $slug, Slug for postype to retrive posts.
     * @param integer $rows, Rows for retrive, 0 is unlimited.
     * @param array $args
     * @return WPCC_DataRetrieverSource
     */
    public function posts($from = null, $args = []): WPCC_DataRetrieverSource {
        $this->config = [
            'type' => 'posts',
            'from' => $from,
            'args' => $args,
        ];
        return $this;
    }

    public function taxonomy($taxonomy = "", $rows = 20, $args  = []): WPCC_DataRetrieverSource  {
        $this->config = [
            'type' => 'taxonomy',
            'taxonomy' => $taxonomy,
            'rows' => $rows,
            'args' => $args,
        ];
        return $this;
    }

    public function page_options($slug = ""): WPCC_DataRetrieverSource  {
        $this->config = [
            'type' => 'page_options',
            'taxonomy' => $slug,
        ];
        return $this;
    }

    public function get($fieldKey = "", $fieldValue = "", $overrideArgs = []) {

        if (empty($fieldKey) || empty($fieldValue)) {
            WPCC_message("WPCC_Builder_Ajax", "Invalid field configuration", true);
        }

        $dataSourceTMP = [];
        $dataSource = [];

        if ($this->config['type'] === 'posts') {
            $args = array_merge($this->config['args'], $overrideArgs);
            $dataSourceTMP = WPCC_DataRetriever::posts($this->config['from'], $this->config['rows'] ?? 20, $args);
        }

        foreach ($dataSourceTMP as $item) {
            $item = (array) $item; // cast to array

            // replace keys for [key]
            foreach ($item as $key => $val) {
                $item["[{$key}]"] = $val;
                unset($item[$key]);
            }

            $valueToShow = str_replace(array_keys($item), array_values($item), $fieldValue);
            $valueToKey = str_replace(array_keys($item), array_values($item), $fieldKey);

            if (!empty($valueToShow) && !empty($valueToKey)) {
                $dataSource[$valueToKey] = $valueToShow;
            }
        }

        return $dataSource;
    }
}
