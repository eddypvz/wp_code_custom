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




## License

[The MIT License](http://piecioshka.mit-license.org) @ 2017
