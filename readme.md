Registry JSON should be in theme, fallback to plugin
$flag_json = file_get_contents( get_template_directory() . '/beta-flags.json' );
$flag_json = file_get_contents( FF_PLUGIN_PATH . 'config/beta-flags.json' );

A/B test by hooking permalink

# Version 1.3.0
# Author Charles Jaimet @cmjaimet
How to Use
1. Write your new code
2. Register your beta flag
3. Wrap your new code in the flag conditional
4. Test!
5. Deploy your code
6. Enable your code in the Admin

Best Practices:
1. Group new code together so a single conditional can enable/disable it
2. Follow the beta flag key naming convention: [repo_slug]-[descriptor]-[version]
3. Complete all fields in beta flag definition

# Beta flags for WordPress themes

Add Beta Flags to conceal new code from execution until activated in the admin.
Beta Flags can also permit A/B Testing.

## Usage

### Register a flag.
Add your flag to this plugin (configuration/beta-flags.json)
	2. In the theme (functions.php)
	3. In a plugin in the method triggered by the 'init' or 'plugins_loaded' hook

```php
pm_betaflag_register(
 [
	 'key' => 'theme-theprovince-election_widget-v101',
	 'title' => 'Election Widget',
	 'description' => 'Add a widget so we can show the CP election coverage',
	 'author' => 'Charles Jaimet',
	 'ab_label' => 'ab',
	 'enforced' => false,
 ]
);
```

'key' is the unique identifier for your hook, and the value you will pass to use it.
DO NOT reuse keys. Duplicate keys will be blocked.
Only lowercase letters, numbers, hyphens (-), and underscores (_) are allowed.
Other characters will cause the flag to be blocked.

Follow this pattern for keys:
[repo slug (minus 'postmedia')]-[descriptor]-[version]
e.g.
'key' => 'plugin-layouts-new_outfits-v512'
or
'key' => 'theme-theprovince-election_widget-v101'

'title' is what you and others see in the admin to identify this flag. Make it clear and intelligible.

'description' is also only visible in the admin. It should concisely describe what functionality is managed with this flag. Include JIRA ticket IDs if you like but keep it short. If you have a lot to write, point to a Confluence or JIRA page.

'author' is so we all know who to go to with questions. Make sure to include this.

'ab_label' is a key that can be used in URL query strings on the front end to cause the functionality to operate on 50% of page views. If the key is in the QS, then the functionality will be suppressed. Only works on enabled, but not enforced, flags.

'enforced' is a boolean that forces execution of the flagged code (i.e. pm_betaflag_is_active( $key ) returns true always for $key (see below).

### Check the beta flag status.

```php
$active = pm_betaflag_is_active( 'beta-key' );
```
Replace `beta-key` with the key used in the register function to check if it is enabled.

## Examples
```php
if ( true === pm_betaflag_is_active( 'theme-theprovince-election_widget-v101' ) ) {
	$widget = new ElectionWidget2018();
}
```

```php
if ( true === pm_betaflag_is_active( 'theme-theprovince-election_widget-v101' ) ) {
	$widget = new ElectionWidget2018();
} else {
	$widget = new ElectionWidget2017();
}
```

Replace elements in a theme selectively
```php
if ( true === pm_betaflag_is_active( 'theme-npfp-logo-v2' ) ) {
	echo '<img src="images/logo_v2.png" />';
} else {
	echo '<img src="images/logo_v1.png" />';
}
```

CSS cascades, so later loaded styles override earlier ones
```php
wp_enqueue_style( 'npfp-styles' );
if ( true === pm_betaflag_is_active( 'theme-npfp-reskin-v239' ) ) {
	wp_enqueue_style( 'npfp-styles-v239' );
}
```

Test full version upgrades of plugins
```php
function __construct() {
	add_action( 'init', array( $this, 'init' ) );
}
function init() {
	if ( true === pm_betaflag_is_active( 'plugin-layouts-logo-v4' ) ) {
		include_once LAYOUTS_PLUGIN_PATH . 'v4/Layouts.php';
	} else {
		include_once LAYOUTS_PLUGIN_PATH . 'v3/Layouts.php';
	}
}
```

## Screenshots
1. The admin screen
2. Registering a flag in this plugin
3. Registering a flag in a theme
4. Registering a flag in a plugin

## Flag states
enforced: If true then the flag is always on
enabled: Admin has turned the flag on
active: True if enforced or enabled + abtest

## Schema
flag_settings:
Array
(
    [widget-election-2018] => 1
    [theme-show-sidebar] => 1
    [draft-manager-web] => 1
)
