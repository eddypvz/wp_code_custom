# WP Code Custom 
WP Code Custom is a plugin that allow administrate wordpress postypes, taxonomies, fields and more.
All configs can be make through the code in functions.php or anywhere. 

**Supported functionalities**

- Custom postypes.
- Custom metabox.
- Custom fields (Text, Date, Media, Text Editor, Checkbox, Select).

## Installing plugin
Install the plugin following the next methods:

### Use Git
Add the plugin like submodule in your plugins folder.
```bash
git submodule add https://github.com/eddypvz/wp_code_custom.git wp-content/plugins/wp_code_custom
```

**For init the submodules when the project has been cloned:**

```shell
git submodule update --init --recursive
```

**For update the submodules whe the project has been initialized:**

```shell
git submodule update --recursive --remote
```

## Activate plugin
You can activate the plugin using the next methods:

### Activate in code (Recommended)
Add this code in the `functions.php` file of your theme.
```php
// <-- WP_CODE_CUSTOM ACTIVATION -->
(function() {
    $plugin = "wp_code_custom/wp_code_custom.php";
    $current = get_option( 'active_plugins' );
    $plugin = plugin_basename( trim( $plugin ) );
    if ( !in_array( $plugin, $current ) ) {
        $current[] = $plugin;
        sort( $current );
        do_action( 'activate_plugin', trim( $plugin ) );
        update_option( 'active_plugins', $current );
        do_action( 'activate_' . trim( $plugin ) );
        do_action( 'activated_plugin', trim( $plugin) );
    }
})();
// <--  WP_CODE_CUSTOM ACTIVATION -->
```

### Activate in Wordpress dashboard
Go to the plugins page in the Wordpress Dashboard, find `WP Code Custom` and activate.

## Using the plugin
WP Code Custom work with configuration code in theme, for build something, you need create an instance of WP Code Custom.
This instance follow one `singleton` pattern. All instances of the plugin are the same object. 

You can create an instance calling a static method `Instance()` of the class `wp_code_custom`.
```php
$wpcc = wp_code_custom::Instance();
```

### Entities
An "Entity" is the representation of the wordpress "objects or concepts" like metaboxes, postypes or options pages.
The entities can been created by the method `CreateEntity` or can been getted by `GetEntity` method.


### Methods of `wp_code_custom`

#### `CreateEntity`
The `CreateEntity` method return an object that define the entity to be create.

```php
$entityObject = $wpcc->CreateEntity()->Postype("post");
```

#### `GetEntity`
The `GetEntity` method return an object .


### Methods of `CreateEntity`

#### `Postype`
The `Postype` method create a new **_custom postype_**. This method return an `Entity` object.

- `from` _(String, Array or Integer)_: If is string, contains the "postype" for get posts. If is integer, contains the ID for get only this post. If is array, contains an array with multiple ID for make all posts for this list.
- `label` _(String)_: Label for display.
- `args` _(Array)_: This param contents multiple arguments.
    - `public` _(Bool)_: Set the postype public.
    - `show_in_menu` _(Bool)_: Show the postype in menu. 
    - `menu_order` _(Int)_: Position for menu. 
    - `icon` _(String)_: Dashicon icon name. 
    - `disable_editor` _(Bool)_: Disable the main wp_editor in the postype.
    - `disable_title` _(Bool)_: Disable the title in the postype.
    - `disable_thumbnail` _(Bool)_: Disable the thumbnail in the postype.
    - `enable_categories` _(Bool)_: Enable the default category taxonomy.
    - `skip_post_type_register` _(Bool)_: Skip the call of "register_post_type", this work with postypes that already are defined or start before the WPCC entity has been created.

#### `CreatedPostype`
The `CreatedPostype` method create an entity from another postype created for wpcc or other plugins. This method return an `Entity` object.

- `slug` _(String)_: Slug name for postype. This has been unique and belonging of a already created postype.
- `args` _(Array)_: This param contents multiple arguments. (Same args of registration postypes), if you send any arg for label or icon, this has been replaced for the new in postype.

#### `OptionPage`
The `OptionPage` method create a new **_options page_**. This method return an `Entity` object.

- `slug` _(String)_: Slug name for postype. This has been unique.
- `label` _(String)_: Label for display.
- `args` _(Array)_: This param contents multiple arguments.
    - `icon` _(String)_: Dashicon icon name.



## Class `WPCC_DataRetriever` 
The `WPCC_DataRetriever` class provide an API to get data from a Wordpress entities. This is an static class.

### Method `posts`
This method get data from any postype.

**Params:**

- `$slug` _(String)_: Slug name for postype. This has been unique.
- `$rows` _(Int)_: Number of rows for retrive, 0 is unlimited.
- `$args` _(Array)_: This param contents multiple arguments.
    - `unique_display` _(Bool)_: Default `true`. This disable the unique display that show an associative array when the query only retrive one row.
    - `include` _(Array)_: Determine data for retrive per post.
        - `fields`
        - `permalink`
        - `thumbnail`
    - `include_taxonomies` _(Array)_: Determines the taxonomies data for retrieve per post.
        - `category`
        - *any custom taxonomy slug for postype*
    - `filters` _(Array)_: This params configure the filters for query. The structure for filters is the same for filter from taxonomies and filter for custom fields.
        ##### Filter by taxonomy  
        ```php
          // Filter by taxonomy
          $args["filter"] = [
              ["taxonomy", "slug_taxonomy", "=", ["slug term to find", "slug term 2 to find", ...]]
          ];
        ```
        >> Compare operator "=" uses the WP_Query/tax_query param.
        ##### Filter by fields
        
        >> **Non repeteable fields:**
        For non repeatable fields, we need append the slug of the group parent for the field that we need filter. Next, add the group slug and field slug. For example: **metabox_slug / group_slug / field_slug**
                
        >> **Repeteable fields (Groups):**
        For non repeatable fields, we need append the slug of the group that we need filter. Next, add the group slug and field slug. For example: **metabox_slug / group_slug**. For repeatable groups, you only can uses operator "EXISTS" and "NOT EXISTS".
                 
        ```php
          // Filter by fields
          $args["filter"] = [
              ["field", "metabox_slug/group_slug/field_slug", "=", "value to find"], // Filter single value
              ["field", "metabox_slug/group_slug/field_slug", "=", ["value to find", "value 2 to find"], // Filter many values
          ];
        ```
        
        ##### Compare param
        Operator to test. Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' (only in WP >= 3.5), and 'NOT EXISTS' (also only in WP >= 3.5). Values 'REGEXP', 'NOT REGEXP' and 'RLIKE' were added in WordPress 3.7. Default value is '='.
        
        If you uses "EXISTS" or "NOT EXISTS" operator, you need send the "value" in array. 
        
        For example:
        ```php
          // Filter by fields
          $query = [
                      "filters" => [
                          ["field", "general/group_one/field_text_one", "EXISTS"], // Filter by field
                          ["field", "general/group_one", "EXISTS"], // Filter by repeatable group
                      ],
                   ];
        ``` 
        
        ##### See taxonomy and fields filters together
        ```php
          // Filter by fields
          $query = [
                      "filters" => [
                          ["taxonomy", "category", "=", "category_slug_term"],
                          ["taxonomy", "custom_taxonomy_slug", "=", ["term_value_1", "term_value_2"]],
                          ["field", "general/group_one/field_text_one", "=", "value to find"],
                          ["field", "general/group_one/field_text_two", "=", ["value to find", "value 2 to find"]],
                      ],
                   ];
        ```
        
        This filters uses the native [WP_Meta_Query](https://codex.wordpress.org/Class_Reference/WP_Meta_Query), see this class for more information.
        ##### The comparition operator: 
        For filter fields, you can send in the third position the "compare" parameter used by wordpress in
        [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query) class.
        
### Method `taxonomy`
This method get data from any postype.

**Params:**

- `$slug` _(String)_: Slug name for postype. This has been unique.
- `$rows` _(Int)_: Number of rows for retrive, 0 is unlimited.
- `$args` _(Array)_: This param contents multiple arguments.
    - `unique_display` _(Bool)_: Default `true`. This disable the unique display that show an associative array when the query only retrive one row.
    - `include` _(Array)_: Determine data for retrive per post.
        - `fields`
    - `filters` _(Array)_: This params configure the filters for query. The structure for filters is the same for filter from taxonomies and filter for custom fields.
       
        ##### Filter by fields
        
        >> This use the same structure for filters from method `WPCC_DataRetriever::Post`


## Class `WPCC_Builder`
The `WPCC_Builder` class provide components available to add in a entity of WPCC.
 

## Examples 
There is an examples for use

#### Adding custom tax to "post" postype. 
```php
    // Creating the entity for WPCC
    $wpcc->CreateEntity()->Postype("post", "General content", ["public" => false, "disable_editor" => false, "icon" => "dashicons-admin-site"])->Build(function ($e) {
      
        // Adding category tax
        WPCC_Builder::Add_Taxonomy("category", "Categories Label", [], $e, true)->Build(function ($e) {
            
            // Adding a custom fields group to category taxonomy 
            WPCC_Builder::Add_Group("informacion", "Information Label", $e)->Build(function ($e) {
                WPCC_Builder::Add_Field_Color_Picker("color", "Color Label", $e, ["description" => "empty = #00000"]);
                WPCC_Builder::Add_Field_Media("imagen", "Image Label", $e);
            });
            WPCC_Builder::Add_Group("content_group", "Content Label", $e,["repeatable"=>true])->Build(function ($e) {
                WPCC_Builder::Add_Field_Text("title", "Title Label", $e);
                WPCC_Builder::Add_Field_Plain_Text("content_text", "Text Label", $e,["description"=>"This is a description"]);
            });
        });
        // Adding metaboxes to "post" postype
        WPCC_Builder::Add_Metabox("info", "Información", $e)->Build(function ($e) {
            // Adding a custom group for "info" metabox
            WPCC_Builder::Add_Group("slider", "Slider principal", $e, ["repeatable" => true])->Build(function ($e) {
                WPCC_Builder::Add_Field_Text("titulo", "Título", $e);
                WPCC_Builder::Add_Field_Text("descripcion", "Descripción", $e);
                WPCC_Builder::Add_Field_Media("imagen", "Imágen", $e, ["description" => "Tamaño: 1920x750px"]);
                WPCC_Builder::Add_Field_Media("imagen_movil", "Imágen Móvil", $e, ["description" => "Tamaño: 360x640px"]);
                
                // source for selects or 
                $data = new WPCC_DataRetrieverSource();
                $source = $data->posts('lalalala');
                                
                WPCC_Builder::Add_Field_Select('testchosen2', 'TEST CHOSEN', $e, [
                'options' => $source,
                'source_field_to_show' => '[post_title] - [ID]',
                'source_field_key' => '[ID]'
            ]);
            WPCC_Builder::Add_Field_Autocomplete('testchosen', 'TEST CHOSEN', $e, [
                'options' => $source,
                'source_field_to_show' => '[post_title] - [ID]',
                'source_field_key' => '[ID]'
                //'options' => WPCC_DataRetriever::posts('lalalala')
            ]);
            });
        });
    });

```


## License

[The MIT License](http://piecioshka.mit-license.org) @ 2017
