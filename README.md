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
git submodule add https://github.com/eddypvz/wp_code_custom.git
```

### Use Zip

1. Download the plugin project: <br/>
    <https://github.com/eddypvz/wp_code_custom/archive/master.zip>
2. Extract in your plugin folder.


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
$entityObject = $cdc->CreateEntity()->Postype("post");
```

#### `GetEntity`
The `GetEntity` method return an object .


### Methods of `CreateEntity`

#### `Postype`
The `Postype` this method create a new **_custom postype_**. This method return an `Entity` object.

- `slug` _(String)_: Slug name for postype. This has been unique.
- `label` _(String)_: Label for display.
- `args` _(Array)_: This param contents multiple arguments.
    - `public` _(Bool)_: Set the postype public.
    - `show_in_menu` _(Bool)_: Show the postype in menu. 
    - `menu_order` _(Int)_: Position for menu. 
    - `icon` _(String)_: Dashicon icon name. 
    - `disable_editor` _(Bool)_: Disable the main wp_editor in the postype.
    - `disable_title` _(Bool)_: Disable the title in the postype.

#### `OptionPage`
The `OptionPage` this method create a new **_options page_**. This method return an `Entity` object.

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
        For filter by fields, we need append the slug of the group parent for field next to underscore and the field slug. 
        The slug structure is: **metabox_slug / group_slug / field_slug**
        ```php
          // Filter by fields
          $args["filter"] = [
              ["field", "metabox_slug/group_slug/field_slug", "=", "value to find"], // Filter single value
              ["field", "metabox_slug/group_slug/field_slug", "=", ["value to find", "value 2 to find"], // Filter many values
          ];
        ```
        
        ##### See taxonomy and fields filters together
        For filter by fields, we need append the slug of the group parent for field next to underscore and the field slug. 
        The slug structure is: **metabox_slug / group_slug / field_slug**
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
        
        ##### The comparition operator: 
        For filter fields, you can send in the third position the "compare" parameter used by wordpress in
        [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query) class.
        
        
 


## License

[The MIT License](http://piecioshka.mit-license.org) @ 2017
