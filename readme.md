# Feature flags for WordPress themes

This plugin is for developers. The aim is to simplify/speed up the process of working with feature flags.

## Features

- Adds an admin UI where users can enable/disable features for testing.
- Can enforce flags once you are happy for them to be deployed (allows for staged removal in source).

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

## Usage

Register a flag in functions.php

```php
register_feature_flag(
 [
	 'key' => 'widget-election-2018',
	 'title' => 'Election Widget',
	 'description' => 'Add a widget so we can show the CP election coverage',
	 'author' => 'Charles Jaimet',
	 'ab_label' => 'abew',
	 'enforced' => false,
 ]
);
```

In template you can then check the feature status using:

```php
is_active_feature_flag('feature-key');
```
Replace `feature-key` with the key used in the register function to check if it is enabled.

**Example**

```php
register_feature_flag([

  'title' => 'My awesome new feature',
  'key' => 'correct-horse-battery-staple',
  'enforced' => false,
  'description' => 'An example feature definition'

]);
```

### Options

**key** (required) - `string`

The unique key used in the template to check if a feature is enabled.

**title** (required) - `string`

The human readable feature name.

**enforced** - `boolean` - Default: `false`

Setting this to true will override any user specific settings and will enforce the flag to be true for every user. Useful for deploying a flag before removing it from the codebase.

**description** - `string` - Default: ``

A description displayed in the admin screen. Use to tell users what they are enabling and other information.
