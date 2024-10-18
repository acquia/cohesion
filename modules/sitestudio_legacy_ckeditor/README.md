# Acquia Site Studio Legacy CKEditor

Acquia Site Studio Legacy CKEditor provides integration between [Acquia Site Studio](https://www.acquia.com/products/drupal-cloud/site-studio) and the deprecated CKEditor 4 module. This module provides the Site Studio CKEditor 4 plugins and Site Studio theme CSS needed for CKEditor 4.

If a site is running Drupal 10, the [CKEditor 4 contrib module](https://www.drupal.org/project/ckeditor) is required to use the Site Studio Legacy CKEditor module. Using Site Studio Legacy CKEditor and CKEditor 4 should be a temporary solution, and it is recommended that sites are upgraded to use CKEditor 5.

The core Acquia Site Studio module can be found at [https://github.com/acquia/cohesion](https://github.com/acquia/cohesion) - for full details on product setup and installation please refer to the [documentation](https://sitestudiodocs.acquia.com/).
CKEditor 4 contrib module can be found at [https://www.drupal.org/project/ckeditor](https://www.drupal.org/project/ckeditor) - required for Drupal 10.

## Installation with composer

Using composer is the preferred way of managing your modules and themes as composer handles dependencies automatically and there is less margin for error. You can find out more about composer and how to install it here: [https://getcomposer.org/](https://getcomposer.org/). It is not recommended to edit your composer.json file manually.

Open up your terminal and navigate to your project root directory.

Run the following commands to require the module:

```
composer require acquia/sitestudio_legacy_ckeditor
```

Site Studio Legacy CKEditor will install along with the CKEditor contrib module from drupal.org.

You can now enable the module via drush with the following commands:

```
drush cr
drush pm-enable sitestudio_legacy_ckeditor -y
```

## Documentation

Further documentation for Site Studio Legacy CKEditor is available [here](https://sitestudiodocs.acquia.com/user-guide/using-ckeditor-legacy-module).

## License

Copyright (C) 2021 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
