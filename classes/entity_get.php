<?php
namespace wp_code_custom;

class entity_get {

    private $tree;
    private $definition_tree;

    private function __construct() {

    }

    private function getEntity($slug, $type, $args) {
        // If context is in tree, return this
        if (isset($this->tree->{$slug})) {
            return $this->tree->{$slug};
        }
        else {
            $context = new Entity($slug, $type, $args);
            $this->toTree($context);
            return $context;
        }
    }

    private function toTree(Entity $entity) {
        $this->tree->{$entity->GetSlug()} = $entity;
    }

    private function processArgs($args) {

        // Quit some args
        unset($args["entity_parent"]);
        unset($args["callback"]);
        unset($args["childs"]);

        return $args;
    }

    private function exploreTreeChildrens(Entity $entity) {

        $childrens = $entity->GetChildren();

        foreach ($childrens as $child) {
            $this->definition_tree[$child["slug"]] = $this->processArgs($child);
        }
    }

    public function getTree() {

        foreach ($this->tree as $entity) {

            $args = $this->processArgs($entity->GetArgs());

            // If the slug is ok
            if(!empty($args["slug"])){
                $this->definition_tree[$args["slug"]] = $args;
            }

            // Get childs
            $this->exploreTreeChildrens($entity);
        }
        return $this->definition_tree;
    }

    public function fromPostype($slug = "", $args = []) {
        return $this->getEntity($slug, 'postype', $args);
    }

    public function fromMetabox($slug = "", $args = []) {
        return $this->getEntity($slug, 'metabox', $args);
    }

    public function fromMetaboxGroup($slug = "", $args = []) {
        return $this->getEntity($slug, 'group', $args);
    }

    public function fromTerm($slug = "", $args = []) {
        return $this->getEntity($slug, 'term', $args);
    }

    public function fromMenu($slug = "", $args = []) {
        return $this->getEntity($slug, 'menu', $args);
    }

    public function fromUser($slug = "", $args = []) {
        return $this->getEntity($slug, 'user', $args);
    }

    //singleton instance
    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new entity_get();
            $instance->tree = new \stdClass();
            $instance->definition_tree = [];
        }
        return $instance;
    }
}