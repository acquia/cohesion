# Acquia Site Studio

[![Coverage Status](https://coveralls.io/repos/github/acquia/cohesion-dev/badge.svg?branch=&t=UOU34W)](https://coveralls.io/github/acquia/cohesion-dev?branch=develop)

## Installation with composer

Using composer is the preferred way of managing your modules and themes as composer handles dependencies automatically and there is less margin for error. You can find out more about composer and how to install it here: https://getcomposer.org/. It is not recommended to edit your composer.json file manually.

Open up your terminal and navigate to your project root directory.

Run the following commands to require the module and minimal theme:

```
composer require acquia/cohesion
composer require acquia/cohesion-theme
```

Site Studio will install along with several module dependencies from drupal.org.

You can now enable the modules via drush with the following commands: 

```
drush cr
drush pm-enable cohesion cohesion_base_styles cohesion_custom_styles cohesion_elements cohesion_style_helpers cohesion_sync cohesion_templates cohesion_website_settings -y
```  

## Upgrading Site Studio

When upgrading to a newer version of Site Studio, the following series of commands will need to be run in this order:

```
drush cr 
drush updb -y 
drush cohesion:import / drush cohesion-import
drush cohesion:rebuild / drush cohesion-rebuild
``` 

## Drush integration (supports ^9)

The `cohesion` drush command has the following operations:

### cohesion:rebuild

Re-save and run pending updates on all Site Studio config entities.

Drush 9 format: 

```
drush cohesion:rebuild
```

Drush 8 format: 

```
drush cohesion-rebuild
```

### cohesion:import 

Import assets and rebuild element styles (replacement for the CRON).

Drush 9 format:

```
drush cohesion:import
```

Drush 8 format:

```
drush cohesion-import
```
 
## Hooks

Several hooks are provided and documented in ./cohesion.api.php.

All hooks are in the `dx8` group, so can be implemented in a 
MODULE.dx8.inc file under your module's root if you wish.


## Global $settings options

Show the JSON fields for debugging:

```
$settings['dx8_json_fields'] = TRUE;    
```

Allow the API URL field on the account settings page to be editable:

```
$settings['dx8_editable_api_url'] = TRUE;
```

Expose a version number field on the account settings page (for development):

```
$settings['dx8_editable_version_number'] = TRUE;
```

Don't show the API key field on the account settings page:

```
$settings['dx8_no_api_keys'] = TRUE;
```

Don't show the Google API key page:

```
$settings['dx8_no_google_keys'] = TRUE;
```

Set the temporary stream wrapper that cohesion should use:

```
$settings['coh_temporary_stream_wrapper'] = 'mytemp://';
```

Set the sync directory that Site studio should use:

```
$settings['site_studio_sync'] = '../config/sync';
```

## Global $config options

Set API key:

```
$config['cohesion.settings']['api_key'] = 'api-key';
```

Set organization key:

```
$config['cohesion.settings']['organization_key'] = 'org-key';
```

## Using contextual links with component content

Component content may render the same content multiple times on the same page which makes in context 
editing not working. In order to have in context editing with component content you need to apply this core patch:

https://www.drupal.org/project/drupal/issues/2891603

## Using entity clone module

In order to be able to clone Site Studio layout fields when cloning a content entity, you need to apply this `entity_clone` module patch 

https://www.drupal.org/project/entity_clone/issues/3013286

## Tests

Run something like: `vendor/bin/phpunit -c docroot/core/phpunit.xml.dist --testsuite=unit --group Cohesion`

## License

Copyright (C) 2020 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
