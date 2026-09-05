
# Sync

## Using Sync for site deployment

Sync allows you to export all of your Site Studio configuration including styles, templates and components along with all file entity asset dependencies as a single package.
You export this package to a directory you define in `settings.php`. You could set this directory to the config export directory `sync` and commit to your repository for later deployment in a similar way to performing a core config export/import.   

It is designed to be used alongside core config export/import. A typical workflow would involve using core config export/import to move all configuration except for Site Studio entities and using Sync to move the rest.  

**Configuring your sync directory**

In `settings.php` you will need to add `$settings['site_studio_sync']` to set the sync directory.

It's possible to set the `site_studio_sync` directory to the sync directory as core (which is `/sync` by default) or you could export to the filesystem in `sites/default/files` for example. 

**Configuring entity types to include in the export**

With Site Studio installed and activated with your license key, you'll be able to access this page:
`/admin/cohesion/sync/export_settings`

You can use this page to select which entity types will be scanned for the export.

**Export**

To export all Site Studio packages and dependencies to your sync directory, use the following drush command:

```
drush sync:export
``` 

This will create a file called: `[your-site--name].package.yml_` in your `site_studio_sync` directory.

If you wish to specify the filename of the exported file instead of it using the site name as a prefix, you can use the following option:

```
drush sync:export --filename-prefix=something
``` 

which will output a file called: `something.package.yml_`


**Import**

To import all previously exported `*.package.yml_` files from your `site_studio_sync` directory into the current site, use the following drush command: 

```
drush sync:import
```

Note that you will need to use one of the following options to tell Drupal what to do when it finds differences between the entities in the `*.package.yml_` files and your site. 

```
drush sync:import --overwrite-all
drush sync:import --keep-all
```

While the import is running, your site will be placed into maintenance mode.

## Deploying a specific package via drush by specifying a path 

To deploy a specific `*.package.yml` file via drush, you can specify a local or remote file path like this:

```
drush sync:import --path=/path/to/local.package.yml
```  

```
drush sync:import --path=http://domain.com/remote.package.yml
```  

Sync will detect if the path you provided is a local or remote file and handle streaming automatically.

You can also use the `--overwrite-all` and `--keep-all` options when specifying a path. 

## Deploying a list of packages on module install 

Module developers can export Site Studio Sync packages and include as part of their module. When set up correctly, the packages will automatically be deploy when the module is enabled. 

To set this up, create a new Yaml reference file inside your module: `config/dx8/packages.yml`

This file should only contain a simple array which references paths to local or remote files. For example:

```yml
# This is my package reference file. 

- config/dx8/my.package.yml
- http://mydomain.com/my.package.yml
```

When including local files, the path should be relative to the module root directory. 

## Sync packages

List available sync packages:

```
drush sitestudio:package:list
```  
