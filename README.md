# Acquia Site Studio

[![Build Status](https://core.cloudbees.ais.acquia.io/devops-pipeline-jenkins/buildStatus/icon?job=SITESTUDIO-Cohesion-Dev-PIPELINE%2Fdevelop)](https://core.cloudbees.ais.acquia.io/devops-pipeline-jenkins/job/SITESTUDIO-Cohesion-Dev-PIPELINE/job/develop/)

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
drush pm-enable cohesion cohesion_base_styles cohesion_custom_styles cohesion_elements cohesion_style_helpers cohesion_sync cohesion_templates cohesion_website_settings sitestudio_page_builder -y
```

## Upgrading Site Studio

When upgrading to a newer version of Site Studio, the following series of commands will need to be run in this order:

```
drush cr
drush updb -y
drush cohesion:import
drush cohesion:rebuild
```

## Drush integration

The `cohesion` drush command has the following operations:

### cohesion:rebuild

Re-save and run pending updates on all Site Studio config entities.

```
drush cohesion:rebuild
```

### cohesion:import

Import assets and rebuild element styles (replacement for the CRON).

```
drush cohesion:import
```

## Hooks

Several hooks are provided and documented in ./cohesion.api.php.

All hooks are in the `sitestudio` group, so can be implemented in a
MODULE.sitestudio.inc file under your module's root if you wish.

_note: the previous `dx8` group is also included for backwards compatibility._


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

Utilise the database as a scratch directory during rebuild operations:

```
$settings['stylesheet_json_storage_keyvalue'] = TRUE;
```

Set the sync directory that Site studio should use:

```
$settings['site_studio_sync'] = '../config/sync';
```

Set the max number of entities to import via sync in the batch process:

```
$settings['sync_max_entity'] = 10;
```

Set the max number of entities to rebuild at one time in the rebuild batch process:

```
$settings['rebuild_max_entity'] = 10;
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

Show legacy sync options in the UI:

```
$config['cohesion.settings']['sync_legacy_visibility'] = TRUE;
```

### Site Studio Events

When certain Site Studio operations are taking place events are dispatched so that you can interact with it, but you must write your own event subscribers to subscribe to the relevant events.
The main Site Studio module's events can be found within the ``\Drupal\cohesion\SiteStudioEvents`` class.

Pre Site Studio Rebuild

When a Site Studio rebuild operation is started the PreRebuildEvent will be dispatched.

To subscribe: ``Drupal\cohesion\SiteStudioEvents::PRE_REBUILD``

The dispatched event is ``Drupal\cohesion\Event\PreRebuildEvent``.

Post Site Studio Rebuild

When a Site Studio rebuild operation is complete the PostRebuildEvent will be dispatched.

To subscribe: ``Drupal\cohesion\SiteStudioEvents::POST_REBUILD``

The dispatched event is ``Drupal\cohesion\Event\PostRebuildEvent``.

## Tests

Run something like: `vendor/bin/phpunit -c docroot/core/phpunit.xml.dist --testsuite=unit --group Cohesion`

## License

Copyright (C) 2024 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
