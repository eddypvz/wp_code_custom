<?php
namespace wp_code_custom;

class Entity {

    // Public attributes
    private $slug;
    private $type;
    private $args;

    public function __construct($slug, $type, $args = []) {
        $this->slug = $slug;
        $this->type = $type;
        $this->args = $args;
    }

    /**
     * @param $callback Callback for build content inside entity. The closure receive an "Entity" object for father in param.
     */
    public function Build($callback) {

        // Set the arg for callback
        $this->args["callback"] = $callback;

        // Call the callbacks
        if (is_callable($callback)) {
            call_user_func($callback, $this);
        }
    }

    public function SetChildren($value) {
        $this->args["childs"][] = $value;
    }

    public function GetChildren() {
        return $this->args["childs"] ?? [];
    }

    public function GetSlug() {
        return $this->slug;
    }

    public function GetArgs() {
        return $this->args;
    }

    public function GetType() {
    	return $this->type;
    }

    public function GetPostypeParent() {
	    return $this->args["postype_parent"] ?? false;
    }
}