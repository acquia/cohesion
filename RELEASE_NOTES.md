# Release notes

## 8.0.3

## Highlights

### Added Drupal 11.1 Support

#### What is it?

This update adds support for Drupal 11.1.

#### What impact will there be?

You can now run Site Studio on Drupal 11.1 sites, taking advantage of new features and improvements in Drupal 11.1.

#### What actions do I need to take?

Ensure your site is running Drupal 11.1 to utilize the latest features and improvements.

#### Are there any risks I should be aware of?

None.

## Bug Fixes

### Multiple WYSIWYG editors used with layout canvas resulted in undefined values while saving the node

#### What is it?

The content types with layout canvas and multiple WYSIWYG editors resulted in "undefined" values being saved right inside the CKEditor body instead of the values added by the user. The version fixes that problem.

#### What impact will there be?

The correct values will be preserved while saving the node when there are single or multiple WYSIWYG editors present in the content type along with layout canvas.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### WYSIWYG editor doesn't preserve the text format value when used with repeater fields

#### What is it?

Ensures that the text format value of WYSIWYG editor which was set during component creation is respected when user adds more rows via repeater field

#### What impact will there be?

The text format setting will be respected in conjunction with repeater field.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Image styles are not getting applied for DAM images

#### What is it?

The image styles not being applied for DAM images integrated with Site Studio components.

#### What impact will there be?

The image styles will be applied for DAM images.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Color palette/picker not preserving the value once style guide settings are changed.

#### What is it?

When a style guide settings are set and color information is saved, further changes in style guide flushes the color information and doesn't load correct tab: picker/palette.

#### What impact will there be?

When a style guide settings are set, color information will always be preserved and accurate picker/palette tab will be opened as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 8.0.2

## Bug Fixes

### Support SVG images

#### What is it?

Ensures that SVG images are not passed through the image style processor.

#### What impact will there be?

SVG images will be displayed as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Sliders not working responsively

#### What is it?

Fixes an issue where sliders were not working responsively.

#### What impact will there be?

Sliders will now work as expected when specific breakpoint settings are set.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Access denied error when flushing cache with BigPipe module enabled

#### What is it?

Fixes an issue when flushing cache on a Site Studio with the BigPipe module enabled would result in access denied error.

#### What impact will there be?

The BigPipe module can be enabled and flushing the caches on a Site Studio page should no longer results in an access denied error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Drupal error when viewing rendered page and BigPipe module enabled

#### What is it?

Fixes an issue where the Drupal "This website has encountered an error" message would appear when trying to view the rendered output of a website with the BigPipe Module enabled.

#### What impact will there be?

The BigPipe module can be enabled and the rendered output of a website should not produce an error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Style guide preview breaking after updating values

#### What is it?

Fixes an issue where updating values in the style guide preview would break the preview styles.

#### What impact will there be?

Updating values in the style guide, the preview will no longer break and the updated styles are reflected in the preview.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 8.0.1

## Bug Fixes

### Matching heights issue when browser width is adjusted

#### What is it?

Fixes an issue of matching height among the DIVs when match height property is attached on the column.

#### What impact will there be?

The DIVs of same column will carry same height by abiding match height property especially when browser width is adjusted.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Color variables can be altered when in-use

#### What is it?

Fixes an issue where color variables could be altered when in-use resulting in a mismatch between the stored variable and colors utilised in WYSIWYG editors.

#### What impact will there be?

Colors variables can no longer be altered when in-use.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Reordering custom styles in the listing page is not reflected in the generated CSS

#### What is it?

Fixes an issue where saving the order of custom styles in the listing page was not reflected in the generated CSS on save.

#### What impact will there be?

If custom styles ordering is not respected you will need to resave or run a Site Studio Rebuild.

#### What actions do I need to take?

You may need to resave the custom styles to ensure the order is reflected in the generated CSS or run a Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

### Video elements do not reflect accessibility standards

#### What is it?

Components that utilise the video element did not meet accessibility standards and featured an unnecessary tabbable element.

#### What impact will there be?

The video element will now meet accessibility standards.

#### What actions do I need to take?

Resave any components that utilise the video element or run a Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

### Warning text is not visible when displayed along site component fields

#### What is it?

Under some circumstances, the warning text was not visible when displayed along site component fields.

#### What impact will there be?

The warning text will now be visible when displayed along site component fields.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Users are unable to use entity browser field and WYSIWYG media button on the same component

#### What is it?

Resolves an issue where users were unable to open the entity browser from the entity browser field and then use the CKEditor WYSIWYG media button. This would cause overrides to incorrect fields.

#### What impact will there be?

All instances of media library will open and apply correctly to the appropriate fields.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Issue with ssaShifted not working correctly when opening and closing the sidebar browser

#### What is it?

Resolves an issue in the back-end when editing the layout canvas and opening and closing the sidebar browser would have an issue with ssaShifted class and cause a page breaking error.

#### What impact will there be?

This issue will no longer occur due to further guard rails in the code.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Visual page builder edit in place not limiting number fields

#### What is it?

Resolves an issue when using the Visual page builder that a number input field is not respected. This could lead to non-numerical letters being entered into number fields.

#### What impact will there be?

You can no longer use non-numerical fields in number fields.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Visual page builder while dragging can cause an error

#### What is it?

Resolves an issue when dragging a component and pressing the escape key can cause the user interface to be broken and stop users from performing any more actions.

#### What impact will there be?

You can no longer press escape to close the sidebar browser when dragging in the user interface.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Visual page builder text can be highlighted when dragging components

#### What is it?

Resolves an issue where text could be highlighted when dragging a component on the page.

#### What impact will there be?

No text will be highlighted when dragging in the VPB user interface.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Range slider component form preview would behaving erratically

#### What is it?

Resolves an issue where range sliders in the component form preview would behave erratically when it had a default value. This default value would loop and show the default value as well as 'Undefined'.

#### What impact will there be?

There will be no switching between the two values in the form preview.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Field repeater minimums do not trigger form validation

#### What is it?

Resolves an issue where repeater minimum rows was not triggering the form validation if there were not sufficient rows in the repeater. It also solves an issue where the increment could allow for more rows than the max value.

#### What impact will there be?

Now there is validation for minimum repeater fields.

If there is an increment that would cause your rows to go above the max repeatable fields, it will add the max amount of rows until the max repeatable fields value.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

If your forms are breaking the validation, you may need to resave/alter your components to pass the correct validation.

### Tokenising styles on a custom element within a component

#### What is it?

Tokenising styles on a custom element within a component was earlier not applying, component is missing the generated {{ coh_instance_class }} value on the custom element styles.

#### What impact will there be?

Tokenising styles on a custom element within a component will start rendering the values.

#### What actions do I need to take?

Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

## 8.0.0

## Highlights

### Removed Drupal 9 Support

#### What is it?

This update removes support for Drupal 9, it supports a minimum Drupal core version of 10.2.2.

#### What impact will there be?

Sites running on Drupal 9 will need to be upgraded to a supported version to continue receiving updates and support.

#### What actions do I need to take?

Ensure that Drupal core is on version 10.2.2 or later.

#### Are there any risks I should be aware of?

If a website is using Drupal 9, it can not use this version of Site Studio until Drupal core is updated to at least 10.2.2.

### Added Drupal 11 Support

#### What is it?

This update adds support for Drupal 11.

#### What impact will there be?

You can now run Site Studio on Drupal 11 sites, taking advantage of new features and improvements in Drupal 11.

#### What actions do I need to take?

Ensure your site is running Drupal 11 to utilize the latest features and improvements.

When upgrading a site and attempting to use a WYSIWYG editor with the Site Studio text format you may encounter a console error relating to the "image_upload" plugin.
If this is the case the text editor configuration will need to be updated, and it likely caused by the "image_upload" plugin is set to "false" then all other settings
for that plugin should be removed.

#### Are there any risks I should be aware of?

None.

### JSON:API element support

#### What is it?

Adds support for elements that have been added to a Node Layout canvas to be available in the JSON API output.

#### What impact will there be?

If a Node Layout canvas contains elements these will be available in the JSON API output.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Toggling Site Studio default elements

#### What is it?

A new feature that allows users with the correct permission to toggle Site Studio default elements that are not in use on the site.
This feature is ideal when starting a website build and "out-of-the-box" elements are not required, however, it can be used at any point where an element is not being used on the site.

#### What impact will there be?

By default, all Site Studio elements are enabled on new and existing Site Studio installs.
Elements can be enabled/disabled from the admin UI - `/admin/cohesion/configuration/elements-toggle-settings` and each element can only be disabled, if that element is not in use.
When an element is disabled, it will no longer be available to be used when building out components, templates & helpers.
If a user attempts to import a Site Studio package where a disabled element is used, the import process will fail with a message.

The element usage needed for toggling the elements can be generated using batch or cron on the admin UI page or run using the follow drush command: `drush sitestudio:element-usage`.

#### What actions do I need to take?

Run a cache clear, drush updb / database updates.
Navigate to `/admin/cohesion/configuration/elements-toggle-settings` to enable or disable elements once the element usage data has been generated via batch or cron.

#### Are there any risks I should be aware of?

None.

### Layout Canvas Governance module and permissions

#### What is it?

A new module, that when enabled will provide granular control over what a user can do with the layout canvas.

This will add the following options:
- "administer layout canvas add" - This will remove the add button in the layout canvas and visual page builder, remove it from the layout canvas and visual page builder node dropdown, and remove single clicking a layout canvas node to alter your insert position and opening the sidebar browser
- "administer layout canvas configure" - This will remove the configure button in the layout canvas and visual page builder node dropdown (provided a user has the ability to configure components)
- "administer layout canvas delete" - This will remove the delete button in the layout canvas node dropdown and visual page builder component toolbar dropdown, it will remove the keyboard commands to delete a layout canvas node
- "administer layout canvas drag" - This will remove drag and drop on the layout canvas and visual page builder
- "administer layout canvas duplicate" - This will remove the duplicate button on the layout canvas node dropdown and visual page builder component toolbar dropdown, it will also remove the keyboard shortcut to duplicate.
- "administer layout canvas edit" - This will remove the edit button on the layout canvas dropdown and visual page builder component toolbar, it will also remove being able to double click a layout canvas node.
- "administer layout canvas save" - This will remove the save button for the layout canvas node dropdown and visual page builder component toolbar dropdown, and it will remove the save button in the layout canvas and visual page builder canvas dropdown

#### What impact will there be?

When the module has been enabled and if a user does not have the appropriate permissions, certain parts of the UI will be disabled for them. Only enable this module if you require these granular permissions.

#### What actions do I need to take?

Install the new governance module and run a Site Studio import.

#### Are there any risks I should be aware of?

That by enabling this module you may lock out users unintentionally.

## Bug Fixes

### Users are able to use empty spaces to get around required validation

#### What is it?

Resolves an issue where users were able to get around any required validation by using empty spaces

#### What impact will there be?

Now empty spaces will not usable for validation, and will still show a required message to users.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

If you have purposefully avoided validation, this will now fail when you try to resave.

### Users are unable to use media browser field and WYSIWYG media button on the same component

#### What is it?

Resolves an issue where users were unable to open the media browser from the media browser field and then use the CKEditor WYSIWYG media button. This would cause overrides to incorrect fields.

#### What impact will there be?

All instances of media library will open and apply correctly to the appropriate fields.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

## 7.5.2

## Bug Fixes

### Drupal 10.3.0 support

#### What is it?

Some changes introduced in Drupal 10.3.0 resulted in Site Studio incompatibilities.

#### What impact will there be?

Upgrading to this version of Site Studio will address incompatibilities with Drupal 10.3.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Wysiwyg link modal hidden behind sidebar editor when using Page Builder

#### What is it?

Fixes an issue when using page builder and a wysiwyg form element with a link button.

#### What impact will there be?

The link modal is now visible and functions as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Accordion component items not saving in expected order

#### What is it?

Fixes an issue whereby accordion component ordering is not being saved as expected under some circumstances.

#### What impact will there be?

Accordion items will save and order as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Resolves an issue when editing a component content that was created from a custom component

#### What is it?

Fixes a bug when editing a component content that was created from a custom component the "component" field and link to the component was removed.
This produced various errors messages around the UI.

#### What impact will there be?

When editing a component content created from a custom component and saving the reference is retained as expected.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

Component contents that have a missing component reference won't be updated as there is no way to determine what the reference should be.

### Details accordion styling not applying in Acquia Claro theme

#### What is it?

Resolves a bug where Site Studio detail accordions in Acquia Claro theme are not styled as expected.

#### What impact will there be?

Site Studio details accordions will now be styled when using the Acquia Claro theme.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Resolve incompatibility with Mautic placeholders when syndicating content

#### What is it?

Mautic uses similar placeholders to Site Studio which causes an issue when syndicating content with Content Hub.

#### What impact will there be?

Content Syndication will now complete as expected when Mautic placeholders are in use.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Setting a context to negate a condition not working as expected

#### What is it?

Resolves an issue where negating a condition of a context, that was applied to a Site Studio element was not working as expected.

#### What impact will there be?

The negate functionality of context conditions now works as expected with Site Studio elements.

#### What actions do I need to take?

Site Studio rebuild or re-save the affected component/s.

#### Are there any risks I should be aware of?

None.

## 7.5.1

## Bug Fixes

### CSS styles missing when using the picture element

#### What is it?

Fixes an issue where CSS styles on classes applied to the picture element were missing as a result of some of the front end performance improvements.
This meant that the picture element was not styled/positioned as expected.

#### What impact will there be?

The library that implements these styles has now been added to the picture element's template and the styles will now be applied as expected.

#### What actions do I need to take?

Site Studio rebuild or re-save the affected component/s.

#### Are there any risks I should be aware of?

None.

### Style from sitestudio_claro module overrides Drupal dialog style

#### What is it?

Fixes an issue where a style from the sitestudio_claro module was overriding a Drupal dialog style and affecting other dialog modals that should not have been affected.

#### What impact will there be?

The style has been updated to be more specific and only affect the media library dialog.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Custom pager template overridden when sitestudio_claro module enabled

#### What is it?

Fixes an issue where custom pager templates used in the front-end theme, were overridden when the sitestudio_claro module was enabled.

#### What impact will there be?

Custom pagers should no longer be overridden, code updated to be more specific and only affect the media library pager.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

None.

### Last saved content date is not updating, while saving through visual page builder

#### What is it?

Fixes an issue when updating content through the visual page builder and saving, the last saved date of the node was not updating.

#### What impact will there be?

When making changes to the layout canvas through visual page builder and saving, the last saved date of the node will update as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### CKEditor style dropdown previews were not displaying when multiple WYSIWYG elements/fields are used

#### What is it?

Fixes a bug where the CKEditor style dropdown previews did not work for all instances of WYSIWYGs in a component.

#### What impact will there be?

The CKEditor style dropdown previews will now be styled as expected.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

## 7.5.0

## Highlights

### Advanced Select field capabilities

#### What is it?

[Advanced select field capabilities](https://sitestudiodocs.acquia.com/7.5/user-guide/advanced-select-field-capabilities) have been added to provide additional custom sources for select values.

Two new options have been added for the Select form field in the form builder. The previous two options have been renamed as described:

- Manually set options - _previously "Custom select field"_
- Options from existing select field - _previously "Existing select field"_
- Options from external data source - _new_
- Options from custom function - _new_

Selecting "Options from external data source" will display a new field "External URL". You must enter a url endpoint that returns JSON data in the correct format.

Selecting "Options from custom function" will display a new field "Function name". You must enter the name of a function that is globally available that returns the data in the correct format (or a promise that resolves to the correct data).

An [example module](modules/example_custom_select) has been included with further [instructions](modules/example_custom_select/README.md) to outline this feature.

#### What impact will there be?

The new settings give greater control over the options available in a select in the form builder by allowing the user to dynamically load in options via either a URL or a custom JS function.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Improved error handling for invalid CSS selectors in the Style builder.

#### What is it?

Implemented stricter validation and more detailed error handling for invalid CSS selectors.

The updated validation aligns with the specifications outlined in [CSS Selectors Level 4](https://www.w3.org/TR/selectors-4/) and accommodates unrecognized pseudo-classes, pseudo-elements, and attribute case sensitivity modifiers.

#### What impact will there be?

Upon detection of an invalid CSS selector, Drupal will now provide a comprehensive error message, including the invalid selector, Entity type and Entity ID.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

Existing invalid CSS selectors not adhering to the revised validation may trigger errors during rebuild or save processes.

### Front end performance improvements

#### What is it?

Introducing a number of front end performance improvements:

- Default Site Studio element styles are only loaded if that element is used on the page
- Toggle only loading custom styles on the page when needed
- Toggle only loading component & template styles on the page when needed

These options can be configured under /admin/cohesion/developer/front-end-settings and are disabled by default.

When these options are enabled, only the required styles that are used on the page will be loaded. When this is disabled, all styles will be loaded.
By enabling these options it will reduce the amount of CSS being loaded on each page and therefore less unused CSS, but it will make each page less cachable as each page will have a different set of CSS.

Enabling to only load custom styles when needed will break any Site Studio custom styles used in custom code, unless the library is attached in the custom code.

#### What impact will there be?

If the options are enabled there will be no unused CSS loaded on the page, but could make the pages less cachable as the CSS for each page may be different.
If a user decides to enable the "only load custom styles on the page when needed" option and have custom styles used in custom code, each library will need to be attached for the CSS to be loaded.

#### What actions do I need to take?

A Site Studio rebuild. Enable the options if required.

#### Are there any risks I should be aware of?

Enabling "only load custom styles on the page when needed" could break existing styling on sites where custom styles maybe used in custom code.
If custom styles have been used in custom code either keep this option disabled or attach the appropriate library generated by Site Studio.

Enabling these options can have a positive impact on the page speed by rendering less unused CSS on the page, however, pages may be less cachable as the CSS for each page of the site could be unique.

### Gin dark mode support

#### What is it?

Added support for dark mode within the Site Studio interface when using the Gin admin theme.

#### What impact will there be?

Interfaces will look better on dark backgrounds when the Gin admin theme has been set to dark mode, or if auto mode is set and your system preferences are set to use dark mode.

#### What actions do I need to take?

A Site Studio import.

#### Are there any risks I should be aware of?

None.

### Font display for icon libraries

A font display configuration for icon libraries has been added inline with that of font libraries.

#### What impact will there be?

Font display (eg. auto, swap, block etc.) is now configurable in when creating/editing icon libraries in the UI. By default auto is used.

#### What actions do I need to take?

To leverage one of the display options beyond the default you will need to edit and re-save the relevant icon library.

#### Are there any risks I should be aware of?

None.

## Bug Fixes

### Improved UI for handling transitions between workflow states in Visual Page Builder.

#### What is it?

Updated the moderation state drop down on the Visual Page Builder to better handle situations where a transition from the current state to the same state is not possible. E.g. a Published page must be transitioned back to a Draft if any changes are made.

#### What impact will there be?

The UI should now better reflect the moderation/workflow state of the page.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

## 7.4.3

## Bug Fixes

### Fixed Exception thrown in relation to nested repeaters

#### What is it?

Fixes an issue where nested pattern repeaters could result in Twig syntax exception when adding items to the outter pattern repeater.

#### What impact will there be?

Pattern repeater field should now work more reliably.

#### What actions do I need to take?

Site Studio rebuild or individual affected node resave, if you were affected by this issue.

#### Are there any risks I should be aware of?

None.

### Warning message during database update 9004

#### What is it?

Fixes an issue where running database update `cohesion_update_9004` would trigger warning message like so:
```ssh
[warning] foreach() argument must be of type array|object, null given cohesion.install:1372
```

#### What impact will there be?

The warning message will no longer be displayed.

#### What actions do I need to take?

Drupal cache clear if the `cohesion_update_9004`  has not been performed yet.

#### Are there any risks I should be aware of?

None.

### TMGMT support for custom components

#### What is it?

Adds support for custom component field content to be translated using TMGMT.

#### What impact will there be?

Custom component fields can be translated using TMGMT and toggled as they can for UI components in the custom component builder.
If these settings are changed the form JSON will need to be re-downloaded and added to the custom component module.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Acquia DAM pagination un-styled while using the page builder

#### What is it?

When using Acquia DAM through the media library in page builder, the pagination was un-styled.

#### What impact will there be?

The pagination is now styled when using the page builder.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Layout canvas appearing as a reference option when creating new fields

#### What is it?

Stops the layout canvas appearing as a reference option when creating a new reference field on a content type in Drupal 10.2 and Entity Reference Revisions 1.11.
This option has been removed as layout canvases shouldn't be referenced this way.

#### What impact will there be?

The layout canvas will no longer appear as an option when creating a new Drupal reference field on a content type.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Exception thrown when attempting to render certain types of links using the link to page field

#### What is it?

Fixed an exception that was thrown when attempting to render external links that contain colons.

#### What impact will there be?

These types of links should now render as expected and no exception is thrown.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Editing translations in page builder result in wrong translation being updated

#### What is it?

When editing a multilingual website using the page builder; with specific language detection and selection setup, saving changes would sometimes apply to the incorrect translation.

#### What impact will there be?

Editing a multilingual website using the page builder with any language detection and selection setup should result in saving the correct translation.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Resolved issues around behaviour of field repeaters and conditional fields

#### What is it?

Fixed several issues where in certain circumstances a Component form could be configured in such a way that the values of form fields inside a field repeater would be mixed up or set incorrectly when re-arranging or removing rows from the form.

#### What impact will there be?

Field repeaters should behave in a more consistent way in these cases.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Improved Entity Browser field stability when on slow internet connections

#### What is it?

Fixed an issue where selecting an entity using the Typeahead Entity Browser would sometimes appear to select the wrong entity as a result of a race condition triggered by requests to the server taking longer than expected.

#### What impact will there be?

The Typeahead Entity Browser should now work more consistently.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Fixed Typeahead Entity Browser field displaying incorrect value when inside a Field Repeater

#### What is it?

Fixed an issue where Typeahead Entity Browser's inside Field Repeaters could get out of sync when changing the order of or removing rows from the Field Repeater.

#### What impact will there be?

The Typeahead Entity Browser should now work more consistently.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

## 7.4.2

## Bug Fixes

### Unable to pick color from the style guide

#### What is it?

When editing the typography settings within a theme in some instances it was not possible to select a color from the
color palette due to an unexpected entity state.

#### What impact will there be?

An entity update will be run ensuring color entities are in the correct state.

#### What actions do I need to take

Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

### Package imports via UI will use dedicated temp directory.

#### What is it?

Fixes an issue where Site Studio package import via UI would use the same
sync directory as Drush command. This could result in unexpected behaviour,
such as orphan files imported and changes detected when there are not
supposed to be any.

#### What impact will there be?

Packages imported via UI (in `*.tar.gz` format) will be extracted to
and imported from `temporary://cohesion-sync-{site_id}/` URI.
Here `{site_id}` refers to 8 characters postfix generated based on site hash.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Non-config files incorrectly marked as changed during package imports.

#### What is it?

Fixes an issue where Site Studio non-config files (images, fonts, etc.) would
be detected as changed even though that would not be the case.

#### What impact will there be?

Non-config files will no longer be marked as changed when they are not.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Package import triggering TypeError in UsageUpdateManager::buildRequires() fix.

#### What is it?

Fixes an issue where Site Studio entities that are in-use and have different UUIDs to the ones in database would result
in the following error:
```php
TypeError: Drupal\cohesion\UsageUpdateManager::buildRequires(): Argument #1 ($entity) must be of type Drupal\Core\Entity\EntityInterface, bool given
```
There is also now a "Status" column when importing packages via Drupal UI to display which entities
are being recreated due to UUID differences.

#### What impact will there be?

This error no longer will appear and the entity "re-import" will be handled correctly.
Entities that are being "reimported" due to difference in UUID values will be marked as such in Drupal UI.

#### What actions do I need to take?

Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

### Component content not appearing as in-use when used within an Entity browser field

#### What is it?

Fixes an issue where component content entities referenced in an Entity browser field would not show as in-use.

#### What impact will there be?

Component content entities should now appear as in-use when referenced within an Entity browser field.

#### What actions do I need to take?

Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

### Ckeditor styles dropdown not displaying style preview in Drupal 10

#### What is it?

Fixes a bug where the styles dropdown for Ckeditor was not displaying the styles preview in Drupal 10.

#### What impact will there be?

The styles can now be previewed from the Ckeditor styles dropdown.

#### What actions do I need to take?

Site Studio import and cache clear.

#### Are there any risks I should be aware of?

None.

### HTML attribute not rendering as expected

#### What is it?

Fixes a bug where tokenizing a markup attribute such as class was resulting in the attribute not being added to the HTML element as expected.

#### What impact will there be?

The HTML attribute should render in the HTML as expected if tokenized.

#### What actions do I need to take?

Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

## 7.4.1

## Bug Fixes

### Fixing Deprecated function: str_replace(): Passing null to parameter when adding content templates

#### What is it?

Fixes the following error when creating a new content template:

``Deprecated function: str_replace(): Passing null to parameter #3 ($subject) of type array|string is deprecated in Drupal\cohesion_templates\Form\ContentTemplatesForm->form() (line 113 of modules/contrib/cohesion/modules/cohesion_templates/src/Form/ContentTemplatesForm.php).``

#### What impact will there be?

The deprecated function: str_replace(): Passing null to parameter will no longer appear when creating a new content template.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Preview for entity browser doesn't reflect the correct preview image

#### What is it?

Fixes a problem where the preview image wasn't working correctly for the entity browser when used in a repeater.

#### What impact will there be?

The entity browser will work correctly when used within a repeater.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

### Fixed tab fields in tabs showing incorrect values

#### What is it?

Fixes an issue where in some cases the value shown in a field inside a tab in a component form would be out of sync compared to the stored data of that component.

#### What impact will there be?

Fields will now correctly show the correct data.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

## 7.4.0

## Highlights

### JSON:API support

#### What is it?

There is a new separately available submodule for Site Studio called "Site Studio JSON:API".
It's available via Github repository https://github.com/acquia/sitestudio_jsonapi
and can be installed via composer by running `composer require acquia/sitestudio_jsonapi`.

_Note: This feature is compatible with Drupal 10 only._

Site Studio JSON:API has dependency on [JSON:API Extras](https://www.drupal.org/project/jsonapi_extras)
and it ships pre-configured with Field Enhancer that improves JSON:API output for Site Studio Layout canvas.

More information is available on https://sitestudiodocs.acquia.com/7.4/user-guide/site-studio-jsonapi

#### What impact will there be?

It will be possible to read content stored within Site Studio Layout Canvas on Nodes in JSON:API format via the
"Site Studio JSON:API" submodule.

If the new submodule is not downloaded and enabled - no changes will occur.

#### What actions do I need to take?

In order to get familiar with updated JSON:API integration, please refer to [documentation](https://sitestudiodocs.acquia.com/7.4/user-guide/site-studio-jsonapi).

In order to enable updated JSON:API integration:
1. Download "Site Studio JSON:API" submodule: `composer require acquia/sitestudio`
2. Enable "Site Studio JSON:API" submodule: `drush en sitestudio_jsonapi -y`

#### Are there any risks I should be aware of?

Some known issues with JSON:API integration are listed [here](https://sitestudiodocs.acquia.com/7.4/user-guide/known-issues)

### CSS Variables

#### What is it?

This feature introduces the availability of CSS variables from Site Studio website settings pages. These variables are now accessible for utilization in Custom Elements/Components and Custom Drupal themes.

Detailed instructions can be found at https://sitestudiodocs.acquia.com/7.4/user-guide/css-variables

#### What impact will there be?

CSS variables will be included in the Site Studio theme stylesheet.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

## Bug fixes

### Fixed regression where CKEditor did not work inside a field repeater inside another field repeater

#### What is it?

Nested field repeaters caused a number of issues. CKEditor's initialisation code was behaving incorrectly when the editor instance was inside multiple levels of repeater.

#### What impact will there be?

Users can now use WYSIWYG fields inside repeaters inside repeaters.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Fixed data corrucption when using nested field repeaters

#### What is it?

Nested field repeaters caused a number of issues. Dragging and dropping fields within nested repeaters was incorrectly changing the form data and resulting in data being lost or appearing in the incorrect row.

#### What impact will there be?

Fields can now be used inside repeaters inside repeaters.

#### What actions do I need to take?

Site Studio Import

#### Are there any risks I should be aware of?

There is still an outstanding known issue where using Tab Items and Tab Containers inside a repeater is not working correctly.

### Fixed issue with deleting a row containing an Entity Browser element from a field repeater

#### What is it?

Nested field repeaters caused a number of issues. Deleting a row in a field repeater that contained an Entity Browser field would, in some instances, fail and the row would be immediately re-added to the repeater resulting in the user being unable to remove rows.

#### What impact will there be?

Entity Browser fields inside repeaters will no longer prevent the row from being deleted.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Updates to various cohesion.settings config schema

#### What is it?

Adds missing schema to the following cohesion.settings config:

- cohesion_sync.cohesion_sync_package.
- image_browser.content.dx8_imce_stream_wrapper
- image_browser.content.cohesion_media_lib_types
- image_browser.config.cohesion_media_lib_types

cohesion_media_lib_types default value has been update from an integer to a string.

#### What impact will there be?

There should no longer be any missing schema for cohesion.settings config.
The cohesion_media_lib_types default value has been update from an integer to a string.

#### What actions do I need to take?

Run database updates.

#### Are there any risks I should be aware of?

None.

### Support for Drush 12.

#### What is it?

This adds support for Drush 12 and introduces minimum Drush version requirement of 11.6.

#### What impact will there be?

You will have to update Drush. This should be handled via composer, as updated version
requirement has been added to Site Studio `composer.json` file.

#### What actions do I need to take?

Update to Drush ^11.6 or Drush 12. If using composer, `composer update` should be able
to handle update, but the results may vary depending on your other third-party
dependencies and how Drush is installed on your environment.

#### Are there any risks I should be aware of?

Verify if other third-party modules are compatible with Drush ^11.6 or Drush 12.

### Media library filter issues

#### What is it?

From version 7.1.0 of Site Studio the media library was loaded within an iframe, which caused multiple issues.
We have reverted this change and media library will be loaded in a Drupal modal.

The previous implementation was to resolve "styling" issues with the media library when using the visual page builder.
There is a new submodule `sitestudio_claro` which can be optionally installed to resolve these styling issues in the visual page builder when using the claro admin theme.
If a website is using a different admin theme, it's not recommended to use the `sitestudio_claro` module.

#### What impact will there be?

The media library should now function as expected, and there should be no issues using filters.
By default, the media library will not look styled when using the visual page builder, unless users install the `sitestudio_claro` submodule.

#### What actions do I need to take?

Site Studio import & Drupal cache clear, enable the `sitestudio_claro` submodule if a website is using visual page builder and the claro admin theme.
If a website is using a different admin theme, it's not recommended to use the `sitestudio_claro` module.
A similar approach/custom module maybe required to style the media library for other admin themes when using the visual page builder.

#### Are there any risks I should be aware of?

None.

### Tokenizing an input to attributes such as ID, in a component with no value resulted in empty attribute

#### What is it?

Fixes an issue where tokenizing an input to attributes such as ID, in a
component and entering no value resulted in empty attribute in the markup.

#### What impact will there be?

If a markup field such as ID has been tokenized in a component and no value is
entered, then the attribute is not rendered in the DOM.

#### What actions do I need to take?

Site studio rebuild.

#### Are there any risks I should be aware of?

None.

### Fix for type ahead validation not working

#### What is it?

Validation, in particular "required",  was not running on type ahead (e.g. link to page) fields.

#### What impact will there be?

The validation will now run before a form can be applied.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Fix instance where certain browser/computer speed configurations could result in data loss in the Style Tree

#### What is it?

Fixed a race condition in which in some browsers on slower computers values entered into one level in the Style Tree could be lost when navigating to a different level.

#### What impact will there be?

The issue is resolved.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Fix for issue where fields in the Style Builder could lose values when navigating the Style Tree.

#### What is it?

In some rare circumstances it was possible for values entered into the CSS fields on the Style Tab of a component or in the Style Builder to be lost when navigating to a different level in the Style Tree. The issue was especially noticible in some browsers, on slower computers and with forms containing many fields.

#### What impact will there be?

Values should no longer be lost.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

## 7.3.2

## Bug Fixes

### Unable to use special characters for colors in the website settings color palette

#### What is it?

Fixes a problem where users that had special characters in the name of their color caused an error when saving, and when trying to use in-use links.

#### What impact will there be?

You can now use special characters in the name of your colors

#### What actions do I need to take?

Site studio import and a cohesion rebuild

#### Are there any risks I should be aware of?

None.

### Unable to save styles when using the CSS `counter increment` property.

#### What is it?

When using the CSS property `counter increment` and the value included a space you would be unable to save the style or rebuild.

#### What impact will there be?

Style can now be saved with a space in the value.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Accordion and tab items not working as expected when used in pattern repeaters

#### What is it?

Fixes a problem when using accordions within a pattern repeater. Opening and closing accordions would no longer work, as well as various configuration options.

#### What impact will there be?

Accordion and tab items will now work as expected inside pattern repeaters.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

### Validation on boolean fields not working correctly

#### What is it?

Fixes a problem where certain boolean field weren't being validated correctly

#### What impact will there be?

Boolean field validation should work as expected now.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

### Unable to access custom styles on components

#### What is it?

Fixes a problem where certain users were unable to access the custom styles for elements.

#### What impact will there be?

Fixes the error that would occur if you were unable to reach the custom styles list.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

### Style preview not showing for Site studio styles in CKEditor

#### What is it?

Fixes a problem where you are unable to preview a style in the style dropdown for CKEditor

#### What impact will there be?

Styles will now be visible when you use the styles dropdown.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

### Fix generic heading styles from leaking into the visual page builder

#### What is it?

Fixes a problem that if a base style targeted the h3 selector, certain styles were overriden in the visual page builder.

#### What impact will there be?

No styles will leak onto the headings in visual page builder.

#### What actions do I need to take?

Site studio import.

#### Are there any risks I should be aware of?

None.

### Error on theme appearance page when Style guide manager enabled and 'base_theme' for active theme is set to false

#### What is it?

Fixes an error that occurs on the theme appearance page when the Style guide
manager module is enabled and 'base_theme' for active theme is set to false.

`TypeError: property_exists(): Argument #1 ($object_or_class) must be of type object|string, null given in property_exists()`

#### What impact will there be?

The error will no longer occur for themes that have 'base_theme' set to "false"
and using the style guide manager.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### When tokenizing context visibility but no contexts are active & no condition set the context would wrongly fail

#### What is it?

Fixes a bug when tokenizing context visibility but no contexts are active and no
condition set, the context would wrongly fail.

#### What impact will there be?

When tokenizing context visibility and no contexts are active and no condition
set, the context will pass as expected.

#### What actions do I need to take?

Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Error in some scenarios when using TMGMT to translate

#### What is it?

Fixes a bug where the following error would occur in some scenarios when using TMGMT to translate components:

`Error: Call to a member function getEntityTypeId() on null in cohesion_tmgmt_source_suggestions()`

#### What impact will there be?

When translating using TMGMT in some scenarios the error will no longer occur.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Entities are not displayed for anonymous users unless default language is published

#### What is it?

This addresses an issue whereby access to a translation is dictated by the default language status.

#### What impact will there be?

Translated entities rendered via Site Studio display independently regardless of default language publish status.

#### What actions do I need to take?

Drupal cache clear

#### Are there any risks I should be aware of?

None.

## 7.3.1

## Bug Fixes

### Some fields in components will load the default value back in after the user has cleared them

#### What is it?

This fixes an issue where on certain fields (image browser, typeahead, select, color picker) the default value will load when the sidebar is opened if the user has previously removed the value.

#### What impact will there be?

Default values will only load in if the user has not edited the field.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Keyboard shortcuts executing when using Sidebar Editor.

#### What is it?

This fixes an issue where you could envoke a keyboard shortcut when editing content via the Sidebar Editor.

#### What impact will there be?

You can no longer use the layout canvas node shortcuts when using the sidebar editor.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Accordion/Tabs Element Start State option not working correctly

#### What is it?

Accordion/Tabs Elements configured to show in Accordion mode and set to have the Start State of "All Closed" were not loading with all the sections closed as they should.

#### What impact will there be?

Accordions will now behave correctly as per the user settings

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Font files not found when using Youtube video background Element

#### What is it?

When using the Youtube video background Element several font files were requested by the browser, but not found.

#### What impact will there be?

The font files will no longer be requested by the browser.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Color palette field showing both tabs

#### What is it?

Fixes a problem with the color palette field where both tabs would appear on top of each other.

#### What impact will there be?

The tabs will work as expected, and only show one at a time.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Entity browser preview not displaying for non-media or file entities

#### What is it?

When using an Entity browser field to select an entity that was not media or a
file entity no preview was shown to indicate that the entity browser had a
selection.

A 500 error would also appear in the browsers network tab.

#### What impact will there be?

When selecting non-media or file entities in the Entity browser field the
preview will now show the entities title. This indicates to the user that a
selection has been made on the entity browser field.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

None.

### Syntax error occurring with _0042EntityUpdate

#### What is it?

When running a rebuild users would get the following error:

`Unable to decode output into JSON: Syntax error TypeError: Drupal\cohesion\Plugin\EntityUpdate_0042EntityUpdate::updateFormElement(): Argument #1 ($model) must be of type Drupal\cohesion\LayoutCanvas\ElementModel, null given`

#### What impact will there be?

Running a rebuild users should no longer get this error.

#### What actions do I need to take?

Cache clear & rebuild.

#### Are there any risks I should be aware of?

None.

### Entity browser with Typeahead not showing selected entity

#### What is it?

Fixes an issue where if using a Typeahead with the entity browser, no content entity was rendered.

#### What impact will there be?

It will now show your selected entity

#### What actions do I need to take?

Import, Cache clear & rebuild.

#### Are there any risks I should be aware of?

None.

### When setting database transaction isolation level to READ-COMMITTED a warning is displayed

#### What is it?

Drupal provides a mechanism for setting database transaction isolation level (see: https://www.drupal.org/node/3264101).
This requires all tables to have a primary key which is introduced with this fix.

An additional index of source_uuid is also added to optimise database queries.

#### What impact will there be?

Warning messages will no longer display when setting database isolation level to READ-COMMITTED.

#### What actions do I need to take?

Run database updates.

#### Are there any risks I should be aware of?

None.

### Error: Call to undefined function component_contents_mass_update()

#### What is it?

Fixes a bug when canceling a users account that had component content associated
with it, users would receive a Drupal white screen "This website encountered an
error" and the following error in the Drupal logs:
`Error: Call to undefined function component_contents_mass_update() in cohesion_elements_user_cancel() (line 651 of /app/web/modules/contrib/cohesion-dev/modules/cohesion_elements/cohesion_elements.module)`

#### What impact will there be?

When canceling a users account in this scenario the user will no longer get this
error the action will successfully complete.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

None.

### Content moderation, translation & revision issues

#### What is it?

Using Site Studio with content moderation and translations enabled can lead to unexpected behaviour whereby
translated content and moderation states can impact upon each other.

#### What impact will there be?

Issues relating to translations and content moderation are resolved.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Improving package refresh endpoint performance

#### What is it?

Editing, viewing or building a complex package could result in poor performance
when loading or refreshing package contents due to slow response from
`/admin/cohesion/sync/refresh` endpoint in Drupal. This has been improved
and editing, viewing or building complex packages should not result in
timeout errors.

#### What impact will there be?

Faster loading and building packages, no timeout errors.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 7.3.0

## Highlights

### Added the ability to open a style from a style select

#### What is it?

This adds a button to style selects that allows you to open your style in a new tab.

#### What impact will there be?

Makes it easier to access a custom style from a select.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Ability to search for packages on the sync packages list builder

#### What is it?

Adds the ability for a user to search for a sync package via the title or description.

#### What impact will there be?

Makes it easier to search for sync packages on sites with lots of packages.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Toggle match height and parallax scrolling libraries globally

#### What is it?

Adds the ability to toggle off the match height and parallax scrolling libraries which are added to every Site Studio page even if they are not used.
A site editor can now toggle off these libraries in the UI if they are not used on the site.

#### What impact will there be?

Match height and parallax scrolling libraries can be turned off globally and are no longer attached to every page.
This should improve page performance and reduced unused Javascript on the page.

#### What actions do I need to take?

Run a Site Studio import & rebuild, Drupal cache clear.
Navigate to Site Studio > Developer > Front-end settings to toggle these libraries.

#### Are there any risks I should be aware of?

It is recommended that these libraries only be turned off, if the website is not using these libraries as it may result in broken page layouts.

### Categories in the sidebar browser can now be expanded/collapsed

#### What is it?

Users can expand and collapsed categories in the sidebar browser.

#### What impact will there be?

Categories in the sidebar browser can be expanded/collapsed.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

## Bug Fixes

### Nested components were not editable using in place editing

#### What is it?

Fixes an issue where nested components were not editable when using the in place editor.

#### What impact will there be?

Nested components should now be editable when using in place editing.

#### What actions do I need to take?

Site Studio import & rebuild.

#### Are there any risks I should be aware of?

None.

### Components using pattern repeaters were not editable using in place editing

#### What is it?

Fixes an issue where components using pattern repeaters were not editable when using the in place editor.

#### What impact will there be?

Components using pattern repeaters should now be editable when using in place editing.

#### What actions do I need to take?

Site Studio import & rebuild.

#### Are there any risks I should be aware of?

None.

### Link to page form element encodes URI and handles errors gracefully

#### What is it?

"Link to page" form field makes calls to `/cohesionapi/link-autocomplete` endpoint with encoded argument.
If the endpoint responds with HTTP error, the error message is displayed, but field retains its functionality.

#### What impact will there be?

Unsuccessful calls to autocomplete suggestion endpoint will not prevent user from entering value into "Link to page" form field manually.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Entity browser not opening modal

#### What is it?

Fixes an issue when using the entity browser it was unable to open the appropriate modal.

#### What impact will there be?

The modal will now render as expected.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Resolves TypeError: property_exists() in entity update 0040

### What is it?

Resolves an error which can occur when running a rebuild:

``[error]  TypeError: property_exists(): Argument #1 ($object_or_class) must be of type object|string, null given in property_exists()``

#### What impact will there be?

This error will no longer appear and the rebuild will complete successfully.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Data in conditional fields lost when sorting rows in a pattern repeater

#### What is it?

Fixes an issue where after sorting the rows in a pattern repeater the data entered into fields that were displayed conditionally could be wiped.

#### What impact will there be?

Data should no longer be lost when sorting.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Slider renders incorrectly when inside an Accordion Tabs element

#### What is it?

Fixes an issue where a Slider element would render in a very slender form if it is inside a tab that is not intially shown on page load.

#### What impact will there be?

Sliders should have their correct, fuller, figure when rendered inside tabs.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

### Accordion components do not work correctly when their items are rendered in a pattern repeater

#### What is it?

Fixes a regression introduced in 7.0.0 that prevented accordions from working when their items were rendered via an entity in a pattern repeater.

#### What impact will there be?

Accordions will function correctly as they did in previous versions.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

## 7.2.3

## Bug Fixes

### Accordion start state not working with tokens

#### What is it?

Fixes an issue where the accordion start state wasn't working with token values

#### What impact will there be?

This will now work as expected

#### What actions do I need to take?

Site Studio Import and Site Studio Rebuild.

#### Are there any risks I should be aware of?

None.

### Entity browser not opening modal

#### What is it?

Fixes an issue when using the entity browser it was unable to open the appropriate modal.

#### What impact will there be?

The modal will now render as expected.

#### What actions do I need to take?

Site Studio Import.

#### Are there any risks I should be aware of?

None.

## 7.2.2

## Bug Fixes

### CKEditor modals appear below the Sidebar Editor in the Visual Page Builder

#### What is it?

Fixes an issue where switching a text format on a WYSIWYG caused a modal to show below the sidebar editor.

#### What impact will there be?

The modal will now render as expected, above the Sidebar Editor.

#### What actions do I need to take?

Site Studio Import.

### Error navigating the Style Tree on certain Custom Styles

#### What is it?

Fixes an issue where loading certain Style Tree configurations on Custom Styles would cause errors when navigating through different levels of the Style Tree.

#### What impact will there be?

Users should no longer see this error and be able to edit those Custom Styles.

#### What actions do I need to take?

Run a Site Studio import.

### Default font display causing an error

#### What is it?

Fixes an issue where if you leave the font display when uploading a font and saving your font it causes an API error.

#### What impact will there be?

We now set the default value to auto for font display, this is the default value for font display.

#### What actions do I need to take?

Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Visual UI issues with the component form

#### What is it?

Fixes multiple issues with form styling being incorrect.

#### What impact will there be?

Labels will be shown without being cut off. Columns counts are rendered for field groups.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Content hash appearing when using hide if no data

#### What is it?

Fixes an issue where some components using hide if no data and tokens, the rendered output would show a content hash rather than the content.

#### What impact will there be?

The rendered output will now be correct and the hash is no longer incorrectly rendered.

#### What actions do I need to take?

Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Issue with TwigExtension calling access() on null

#### What is it?

Fixes an issue introduced in **Site Studio 7.2.1** where rendering Twig templates could sometimes trigger uncaught PHP exception with this message:
``Uncaught PHP Exception Error: "Call to a member function access() on null" at cohesion/modules/cohesion_templates/src/TwigExtension/TwigExtension.php line 1890``

#### What impact will there be?

Users should no longer get this exception.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Content entered in WYSIWYG Source Mode is lost if form submitted without switching back out of Source Mode.

#### What is it?

In Site Studio content entered into CKEditor 5 in Source Mode is not stored until the user exits Source Mode. This means that it is possible for users to accidentally lose their changes by pressing Apply or navigating to a different tab within the Sidebar Editor before switching back from Source Editing Mode. This has been mitigated by displaying a warning if a user is going to lose data.

#### What impact will there be?

Users will now see a warning before taking an action that would result in their changes being discarded.

#### What actions do I need to take?

Run a Site Studio import.

#### Are there any risks I should be aware of?

None.

## 7.2.1

## Bug Fixes

### Issue with _0040EntityUpdate failing

#### What is it?

Fixes an issue where _0040EntityUpdate would sometimes error with this message:
``[error]  TypeError: str_replace(): Argument #3 ($subject) must be of type arraystring, stdClass given in str_replace()``

#### What impact will there be?

Running a rebuild users should no longer get this error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Issue with media library filters and DAM

#### What is it?

Fixes a bug when using the media library with Acquia DAM installed, switching the media source and using the filters. Upon attempting to insert resulted in an AJAX error.

#### What impact will there be?

Using the media library in Site Studio with DAM will now work as expected when switching media source.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Multiple Entity Browser fields sharing same configuration

#### What is it?

Fixed a bug where if you have two entity browsers, they share the same configuration.

#### What impact will there be?

Each entity browser configuration is completely separate.

#### What actions do I need to take?

Run a Site Studio import & rebuild.

#### Are there any risks I should be aware of?

None.

### Duplicate inline styles

#### What is it?

Fixed a bug where duplicate inline styles were attached to the page unnecessarily.

#### What impact will there be?

No duplicate inline styles will be attached to the page, this should help improve page performance.

#### What actions do I need to take?

Run a Site Studio import & rebuild.

#### Are there any risks I should be aware of?

None.

### Duplicate Webform submissions

#### What is it?

Fixes a bug when using webforms on a Site Studio layout canvas, and a user submits the webform, duplicate submissions were sent.

#### What impact will there be?

When submitting a webform there should no longer be duplicate submissions.

#### What actions do I need to take?

Run a Site Studio import & rebuild.

#### Are there any risks I should be aware of?

None.

### Issues with file upload AJAX on webforms

#### What is it?

Fixes a bug where using a file upload field on a webform within an entity reference element in Site Studio the file upload would fail with an AJAX error.

#### What impact will there be?

When uploading a file on a webform and rendering using an entity reference element, file uploads should now succeed without AJAX error.

#### What actions do I need to take?

Run a Site Studio import & rebuild.

#### Are there any risks I should be aware of?

None.

### Multiple contexts with 'OR' rule fix

#### What is it?

When adding multiple contexts to a component with the pass condition "Only one criteria must pass", the condition is shown even if none of the criteria options pass.

#### What impact will there be?

Evaluating multiple contexts with 'OR' condition now will work as expected.

#### What actions do I need to take?

Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

## 7.2.0

## Highlights

### In-place Editing feature

#### What is it?

A new way of editing components in the Visual Page Builder has been enabled in this version. In-place Editing allows page editors to make and see their changes directly on the page allowing for a more seamless editing experience.

#### What impact will there be?

In Visual Page Builder a new UI will be shown when hovering over content inside your components that can be edited using the In-place Editing mode.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Added the ability to delete a node on keypress using "backspace" or "delete"

#### What is it?

Adds a feature of deleting nodes on keypress when using "backspace" or "delete" while the node is selected.

#### What impact will there be?

Allows for quick deletion of a layout canvas node.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Added the ability to duplicate nodes on keypress using "Ctrl + d" or "d"

#### What is it?

Adds a feature of duplicating nodes on keypress when using "Ctrl + d" or "d" while the node is selected.

#### What impact will there be?

Allows for quick duplication of a layout canvas node.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Updated Insert Position Logic

#### What is it?

We have updated the insert position logic in the app to match the Visual page builder. This means that when inserting elements or components into your pages, you will now have a consistent and intuitive experience across the app. It also adds a visual indicator of where your insert position is placed relative to the node.

#### What impact will there be?

This update ensures that the insert position logic is aligned with the Visual page builder, making it easier for users to locate and place elements within their pages. It enhances the overall usability and familiarity of the app.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None

## Bug Fixes

### CSS counter-reset breaking rebuilds

#### What is it?

When using the CSS counter-reset property triggering a Site Studio rebuild would fail.

#### What impact will there be?

When triggering a rebuild when a CSS counter-reset will work as expected.

#### What actions do I need to take?

Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Start collapsed property of Accordion tabs container not applying

#### What is it?

This fixes a bug where setting the startCollapsed property on an Accordion tabs container wouldn't apply to the rendered element.

#### What impact will there be?

It will fix the issue with the Accordion tabs container

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Unable to create component content on a site with only custom components

#### What is it?

Fixes a bug where users were unable to create component content from custom components, if a site only had custom components created.
The chosen component did not load correctly on the component content layout canvas, and also resulted in the below warning in the Drupal logs:

`Warning: Undefined variable $element in Drupal\cohesion\Controller\CohesionEndpointController->getElementGroupInfo() (line 422 of /app/web/modules/contrib/cohesion-dev/src/Controller/CohesionEndpointController.php) `

#### What impact will there be?

Sites with only custom components will now be able to create component content and no longer see the warning in the logs.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

None.

### Ensure Layout Canvas field functions as expected with Drupal 10.1

#### What is it

Drupal 10.1 introduces minification on module supplied libraries by default, Site Studio's core JS library should be
flagged as pre-minified to avoid javascript errors.

#### What impact will there be?

This update ensures that Site Studio's core javascript library is flagged as minified avoiding errors when using the
"Layout Canvas" field.

#### What actions do I need to take?

Run a Drupal cache rebuild: `drush cr`

#### Are there any risks I should be aware of?

No.

### Custom component form builder CSS library missing file extension

#### What is it?

Fixes a bug where a library loaded on the custom component form builder was missing the CSS file extension.
This caused some warning in the logs.

#### What impact will there be?

When using the custom component form builder wil no longer result in warnings filling the logs.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 7.1.2

### Unable to submit forms if layout canvas field present in the Gin theme

#### What is it?

Fixes a compatibility issue with the Gin admin theme where the form submit button was outside the form element.

#### What impact will there be?

You can now submit forms if a layout canvas field is present in the Gin theme without error.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Required validation not working for image uploader field

#### What is it?

Fixes a bug where the required validation did not work as expected on the image uploader field.

#### What impact will there be?

The required validation on the image uploader field will now work as expected.

#### What actions do I need to take?

Site Studio import.

#### Are there any risks I should be aware of?

None.

### Support for the Remote Stream Wrapper module

#### What is it?

Adds support for the Remote Stream Wrapper module, previously where an external image was used the image URL domain was removed and the image was not displaying.

#### What impact will there be?

An external image will now render as expected when the Remote Stream Wrapper module is enabled.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Default package import no longer triggers on config sync

#### What is it?

Fixes a bug where default module package import process would trigger during config sync process, such as installing Drupal from configuration or importing configuration. This would result in unexpected exception being thrown and no default module packages being imported.

#### What impact will there be?

Updated default package management mechanism no longer will trigger during Drupal site install from configuration or during config import.

#### What actions do I need to take?

A Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### WYSIWYG field not loading in custom component form builder

#### What is it?

Fixes an issue where the WYSIWYG field was not loading correctly in the custom component form builder.

#### What impact will there be?

The WYSIWYG field will now load correctly in the custom component form builder.

#### What actions do I need to take?

Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Custom selects with options containing empty values were behaving in an unexpected way

#### What is it?

Resolved a regression where select fields created on a component form would behave inconsistently if the select had an option with a blank value.

#### What impact will there be?

Selects should now behave as they did prior to version 7.0.0

#### What actions do I need to take?

None.

### Acquia DAM thumbnail preview not displaying for all asset types

#### What is it?

Fixes a bug where selecting an Acquia DAM asset in the Entity browser that wasn't an image resulted in a broken thumbnail preview.

#### What impact will there be?

The thumbnail preview will now correctly be displayed for all asset types.

#### What actions do I need to take?

A Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Multiple image uploader fields in a component selected image getting replaced

#### What is it?

Fixes a bug where a component configured to have multiple image uploader fields, and that was set to use the media library the first image selection was replaced by the next.
This meant that all fields end up with the same image.

#### What impact will there be?

Each image selected will not be replaced, and each field will have the correct image selected by the user.

#### What actions do I need to take?

Site Studio import & Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Warning error when Better Exposed Filters is not installed

#### What is it?

Fixes an "undefined array key" warning when better_exposed_filters is not installed.

#### What impact will there be?

The error will not be logged when better_exposed_filters is not installed.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 7.1.1

### Icon picker would not load in form preview and style guides

#### What is it?

Fixes a bug where the icon picker would get an error in the form preview or on style guides.

#### What impact will there be?

The icon picker will work in all areas of the application.

#### What actions do I need to take?

Run a Site Studio import and clear Drupal caches.

#### Are there any risks I should be aware of?

None.

### Media library modal window displaying behind the sidebar browser in visual page builder

#### What is it?

Fixes a bug where the media library modal window was un-styled and displaying behind the side browser when using the visual page builder.

#### What impact will there be?

The media library modal window will now appear on top of the sidebar browser and styled as expected.

#### What actions do I need to take?

Run a Site Studio import and clear Drupal caches.

#### Are there any risks I should be aware of?

None.

### hook_requirements blocking install

#### What is it?

7.1 introduced a hook_requirements implementation to verify CkEditor 5 was enabled, this was blocking fresh install
processes as the requirement is called too early in the process.

#### What impact will there be?

The requirement is now checked at runtime only so the error will not block the install or update process.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Style builder color values disappearing when changing to another item in the style tree

#### What is it?

Fixes an issue where selecting a color value in the default style tree selector, and then navigate to the hover styles. The color value in the default style tree had been unset.

#### What impact will there be?

The color value in the style tree will retain its value.

#### What actions do I need to take?

Run a Site Studio import.

#### Are there any risks I should be aware of?

None.

### Color picker in components now functions correctly when Hide Picker Tab is enabled

#### What is it?

Resolved an issue where the color picker field in component forms was not behaving correctly when the Hide Picker Tab options was enabled.

#### What impact will there be?

The Hide Picker Tab option now correctly hides the tab on color pickers.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Limit Stream Wrappers to Local

#### What is it?

A change in File Usage plugin that limits stream wrappers used by Site Studio to local stream wrappers only.

#### What impact will there be?

Prevents errors when used with remote stream wrapper module `remote_stream_wrapper`.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Templated Drupal multi fields not rendering

#### What is it?

When templating a Drupal multi field with Site Studio the values and HTML would not appear.

#### What impact will there be?

When templating a Drupal multi field with Site Studio the HTML and values will render as expected.

#### What actions do I need to take?

Site Studio rebuild or `drush cohesion:rebuild`

#### What impact will there be?

None.

### Icon picker not displaying in form preview

#### What is it?

When creating or editing a component that utilises the icon picker the form preview was not displaying as expected.

#### What impact will there be?

Icon picker will display in form preview.

#### What actions do I need to take?

A Site Studio import or `drush cohesion:import`.

#### Are there any risks I should be aware of?

None.

## 7.1.0

### Adding CKEditor 4 support via additional "legacy" module

#### What is it?

Support for CKEditor 4 is now available via the [Site Studio Legacy CkEditor module](https://github.com/acquia/sitestudio_legacy_ckeditor).

#### What impact will there be?

CKEditor 4 based text formats and plugins can be utilised in the latest version of Site Studio.

_Note: The module has a dependency on the contrib version of [CKEditor](https://www.drupal.org/project/ckeditor) to
ensure support for Drupal 10 is maintained._

#### What actions do I need to take?

When upgrading to Site Studio 7.1 the following module and it's dependencies should be included and enabled to support
CKEditor 4.

- `composer require acquia/sitestudio_legacy_ckeditor:^7.1`
- `drush en sitestudio_legacy_ckeditor -y`

_Note: As of version 7.0 Site Studio requires the CKEditor 5 Drupal core module to be enabled and at least 1 CKEditor 5
text format to be configured. This is to ensure compatability with Drupal 10+._

#### Are there any risks I should be aware of?

None.

### Overflow issue when inserting an image into the WYSIWYG field

#### What is it?

Fixed an issue that could arise with certain form layouts when inserting high resolution images into CKEditor causing
the form to stretch wider than the screen.

#### What impact will there be?

Image embeds via the wysiwyg interface will adjust to fit the viewport ensuring buttons are accessible.

#### What actions do I need to take?

A Site Studio import or `drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Link to page entity type restriction not applying

#### What is it?

Fixes a bug where the "link to page" component field had been configured to be restricted to specific entity types, but was showing all entity type results regardless.

#### What impact will there be?

The "link to page" component field results will be restricted as configured.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 7.0.5

### Templated Drupal multi fields not rendering

#### What is it?

When templating a Drupal multi field with Site Studio the values and HTML would not appear.

#### What impact will there be?

When templating a Drupal multi field with Site Studio the HTML and values will render as expected.

#### What actions do I need to take?

Site Studio rebuild or `drush cohesion:rebuild`

#### What impact will there be?

None.

### Visual page builder no height components flickering

#### What is it?

When using components with no height or with images that aren't defined, they cause a flickering when using visual page builder. This fix stops the flickering issue.

#### What impact will there be?

Visual page builder will not have any component flickering issues.

#### What actions do I need to take?

A Site Studio import or `drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Cannot read properties of undefined (reading 'uuid') error on page builder

#### What is it?

Fixes an issue when a component displaying a Drupal view, which in turn rendered content and potentially the same content entity a user was viewing.
When attempting to use the page builder in this scenario the error "Cannot read properties of undefined (reading 'uuid')" was displayed.

#### What impact will there be?

The page builder will now work as expected in this scenario as the page builder does not attempt to add markup to items in the Drupal view.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 7.0.4

### Color palette performance

#### What is it?

The color palette had performance issues when using a lot of different colors. Now there are no issues when using a large number of colors on the color palette page.

#### What impact will there be?

The performance on the website settings color palette will be improved.

#### What actions do I need to take?

A Site Studio import or `drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Component dropzone content in component content

#### What is it?

There was an issue when saving a component as component content when it had a nested component in a component dropzone. When saving it didn’t include the component in the dropzone.

#### What impact will there be?

You will now be able to save component with nested components as component content.

#### What actions do I need to take?

A Site Studio import or `drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Config entities no longer regenerate IDs

#### What is it?

When a config entity was saved via UI, Site Studio regenerated ID based on a hash of UUID. If entity was created via UI and UUID would match ID, this would not be noticeable.
However, in the instances when entity ID or UUID was manually edited or the entity was not created via UI this would result in entity ID being changed on save.

#### What impact will there be?

Site Studio will only generate IDs for new entities or entities that don't have ID yet.

#### What actions do I need to take?

Entities re-saved via UI will retain their IDs, even if their ID is not based on UUID.

#### Are there any risks I should be aware of?

None.

### Fixed logic on conditional form fields applying incorrectly in field repeater

#### What is it?

A regression in 7.0.x meant that component form fields inside field repeaters were no longer correctly showing/hiding based on other fields within the same repeated row.

#### What impact will there be?

Conditional display of fields will now behave as they did in versions prior to 7.0.x

#### What actions do I need to take?

A Site Studio import or `drush cohesion:import`

#### Are there any risks I should be aware of?

None.

## 7.0.3

### Invalid CSS generated when Background images left unset

#### What is it?

When a background image was left unset on Style and a Gradient was added to the next Breakpoint, invalid CSS was generated.

#### What impact will there be?

When left unset the CSS `background-image` property will no longer be returned.

#### What actions do I need to take?

A Site Studio rebuild or `drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Menu tokens available in menu templates

#### What is it?

Menu tokens such as [menu:name] were not being replaced in Site Studio menu
templates.

#### What impact will there be?

Menu tokens such as [menu:name] can now be used within Site Studio menu
templates.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### URLs that are already encoded are being encoded again

#### What is it?

Fixes a bug where URLs that are encoded, are then encoded again.

#### What impact will there be?

If a URL is already encoded, it won't be encoded again.

#### What actions do I need to take?

A Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Sanitize links whilst supporting all possible link types

#### What is it?

Allow links to be sanitized whilst providing support for all possible link types that could be input.

#### What impact will there be?

Links are sanitized correctly and all link types should be accepted.

#### What actions do I need to take?

A Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Entity reference fields break on entity types with no bundles

#### What is it?

Fixes a bug where autocomplete endpoint would fail with exception on entity types with no bundles.

#### What impact will there be?

The endpoint will work as intended and entity reference form field autocomplete functionality will behave correctly.

#### What actions do I need to take?

A Drupal cache clear.

#### Are there any risks I should be aware of?

None.

## 7.0.2

### The icon picker in Site Studio does not display any icons that are NOT the default Icomoon

#### What is it?

Fixes an issue where icons aren't loaded in the icon picker due to a hardcoded reference to Icomoon.

#### What impact will there be?

When using icon font that is not from Icomoon, you should now be able to see your icons.

#### What actions do I need to take?

A Site Studio import and rebuild.

#### Are there any risks I should be aware of?

None.

### Hidden items on Visual Page Builder no longer have incorrect styling when made visible

#### What is it?

This fixes an issue that occurs when an item goes from being hidden to visible. It used to apply the visually hidden styling, now it removes this appropriately when the DOM updates.

#### What impact will there be?

Dynamically hidden and visible items on the Visual Page Builder will behave as expected.

#### What actions do I need to take?

A Site Studio import and rebuild.

#### Are there any risks I should be aware of?

None.

### YouTube video embed element URLs generated not always valid

#### What is it?

Fixes an issue where the YouTube video embed element URL generated when using
autoplay and loop options were enabled.

#### What impact will there be?

When using the YouTube video embed element, with the autoplay and loop options
the URL will be correct.

If the autoplay option is enabled, the mute parameter is also applied, so that
the video autoplays as expected. This is due to web browser restrictions.

#### What actions do I need to take?

A Site Studio import and rebuild.

#### Are there any risks I should be aware of?

None.

### Error being thrown when a Color has not been set in Default font settings

#### What is it?
When saving the Default font settings or running a Site Studio rebuild the API would
return an error if the Default font color was not defined.

#### What impact will there be?
Saving the Default font settings or running a Site Studio rebuild will now result
in a successful save / rebuild even when the Default font color is not defined.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 7.0.1

### Using view variable element to display a views exposed form caused duplicate IDs

#### What is it?

Fixes a bug when using the view variable element to display a views exposed form
caused duplicate IDs in the DOM. This was due to the Site Studio view template
also rendering the exposed form.

The exposed form in the view template is now only inserted into the DOM, if a
view variable element for "exposed" isn't used and the view has an exposed form.

#### What impact will there be?

If the view has an exposed form and the view variable element is used to render
it the exposed form will only appear in the DOM once and no duplicate IDs will
be present.

#### What actions do I need to take?

A Site Studio import and rebuild.

#### Are there any risks I should be aware of?

None.

### Adding compatibility for better exposed filters combined option

#### What is it?

Provides compatibility for using the Better Exposed Filters "Combine sort order
with sort by" for view filters.

#### What impact will there be?

When using the Better Exposed Filters "Combine sort order with sort by" option
in a view, the Site Studio view filter element will render this as expected,
combining sort order and sort by options.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Twig templates handling Drupal Fields fix

#### What is it?

Site Studio v7.0.0 introduced a bug when handling templates with Drupal multi-value fields. Templates would contain syntax errors and would cause PHP exceptions when rendered. This fix resolves this regression.

#### What impact will there be?

Site Studio templates utilising Drupal fields will no longer cause exceptions.

#### What actions do I need to take?

Run a full rebuild.

#### Are there any risks I should be aware of?

None.

## 7.0.0

### Drupal 10 compatibility

#### What is it?

Site Studio v7.0 is compatible with Drupal 9.4, 9.5 and 10. A number of changes have been implemented due to Drupal 10
requirements:

- CKEditor 5 support added
- jQuery UI and Stable theme contrib dependencies added
- PHP 8.1 support added
- Drupal 8 support removed

#### What impact will there be?

Site Studio will no longer be installable on Drupal 8, however, it can now optionally be installed on Drupal 10
with support for PHP 8.1

#### What actions do I need to take?

Site Studio v7.0 supports CKEditor 5 **only**; you are required to manually migrate text formats from CKEditor 4 to
CKEditor 5, this should be completed on Drupal 9.x before upgrading to 10.x

For details on migration steps [visit](https://sitestudiodocs.acquia.com/7.0/user-guide/CKEditor-4-CKEditor-5).

#### Are there any risks I should be aware of?

Some CKEditor plugins do not currently have support for CKEditor 5.

For more details please refer to the following: https://www.drupal.org/docs/core-modules-and-themes/core-modules/CKEditor-5-module/upgrade-coordination-for-modules-providing-CKEditor-4-plugins

### CKEditor 4 support removed

#### What is it?

Site Studio v7.0 only supports CKEditor 5, support for the now deprecated CKEditor 4 has been removed.

_Note: A fresh install of Site Studio will provide a pre-configured CKEditor 5 text editor/format._

#### What impact will there be?

CKEditor 4 based text formats are not supported in Site Studio v7.0.

#### What actions do I need to take?

You are required to manually migrate text formats from CKEditor 4 to CKEditor 5, this should be completed on
Drupal 9.x before upgrading to 10.x.

For details on migration steps [visit](https://sitestudiodocs.acquia.com/7.0/user-guide/CKEditor-4-CKEditor-5).

#### Are there any risks I should be aware of?

Some CKEditor plugins do not currently have support for CKEditor 5.

For more details please refer to the following: https://www.drupal.org/docs/core-modules-and-themes/core-modules/CKEditor-5-module/upgrade-coordination-for-modules-providing-CKEditor-4-plugins

### Page builder button not appearing in some admin themes

#### What is it?

Fixes a bug where the Visual page builder was not appearing in other admin
themes such as Gin.

#### What impact will there be?

The Visual page builder button will now appear as expected when using the Gin
theme & toolbar.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Legacy Site Studio Sync user interface now hidden by default

#### What is it?

The legacy Site Studio Sync user interface options are only shown if a site
builder enables "Show legacy sync in UI" within the Site Studio system settings.
Legacy drush command are not affected.

#### What impact will there be?

The legacy sync options for importing and exporting Site Studio config will only
appear in the UI if the "Show legacy sync in UI" has been enabled within the
Site Studio system settings.

#### What actions do I need to take?

Toggling on "Show legacy sync in UI" in the Site Studio system settings is
required to show the legacy options in the UI. We recommend for users to use
the updated sync system, and only toggle this option on if required.

To enable this by default add the following to your settings.php file:

`$config['cohesion.settings']['sync_legacy_visibility'] = TRUE;`

#### Are there any risks I should be aware of?

None.

### Module API hooks namespace update

#### What is it?

Module API hooks previously had the namespace of dx8, this has been updated to
sitestudio. Previous hooks have been marked as deprecated.

#### What impact will there be?

The deprecated hooks will continue to work for the time being until removed, it
is recommended to update any API hooks that your websites uses to the new
namespace sitestudio.

#### What actions do I need to take?

If a website is using one of the API hooks, the namespace should be updated from
dx8 to sitestudio.

#### Are there any risks I should be aware of?

If the API hooks namespace is not updated, then they will not work in a future
version of Site Studio where the previous namespaces are used.

### Custom element form HTML class change

#### What is it?

The format of the HTML classes used to layout custom element forms has been
updated. Previously 'col-xs-12' is now 'ssa-grid-col-xs-12'.

#### What impact will there be?

If a site uses custom elements, the elements form will need the htmlClass in the
form element array updated.

#### What actions do I need to take?

Custom elements forms will need the htmlClass in the form element array updated
to the new format, or the form will not be laid out as expected.
See 'example_element' module for examples.

#### Are there any risks I should be aware of?

If no action is taken, then the custom elements form will not be laid out as
expected.

### stable contrib module added as a dependency of Site Studio

#### What is it?

To provide support for both Drupal 9 & 10 a dependency on the [stable](https://www.drupal.org/project/stable)
module has been introduced to Site Studio.

Site Studio uses on `stable` theme as base theme for `cohesion_theme` theme, however, the `stable` theme this has been
removed in Drupal core 10.x see
[https://www.drupal.org/node/3309392](https://www.drupal.org/node/3309392) for details.

#### What impact will there be?

Site Studio frontend features will continue to function as expected.

#### What actions do I need to take?

Download and enable the [stable](https://www.drupal.org/project/stable) module and install `stable` theme.

#### Are there any risks I should be aware of?

None.

### jquery_ui contrib module added as a dependency of Site Studio

#### What is it?

To provide support for both Drupal 9 & 10 a dependency on the [jquery_ui](https://www.drupal.org/project/jquery_ui)
module has been introduced to Site Studio.

Site Studio relies on jQuery UI for a number of frontend features, however, the library definition for this has been
removed in Drupal core 10.x see
[drupal.org/project/drupal/issues/3277744](https://www.drupal.org/project/drupal/issues/3277744) for details.

#### What impact will there be?

Site Studio frontend features will continue to function as expected.

#### What actions do I need to take?

Download and enable the [jquery_ui](https://www.drupal.org/project/jquery_ui) module.

#### Are there any risks I should be aware of?

None.

### Custom components can be ordered by a defined weight

#### What is it?

Custom components can now have a weight defined within the yml file, which allows them to be ordered with other custom
components in the same category.

#### What impact will there be?

Custom components can now be ordered by weight, but only with other custom components in the same category. They will
not be ordered within all components in the same category.

#### What actions do I need to take?

When adding a weight to the yml file the Drupal cache should be cleared for the ordering to take effect.

#### Are there any risks I should be aware of?

None.

### Form helpers can be used in the custom component builder

#### What is it?

When building a custom component using the custom component builder, developers can now use form helpers to help with
the development of their custom component forms.

#### What impact will there be?

Form helpers are now available from the sidebar browser on the custom component builder page and can be used to help build a custom components form.

#### What actions do I need to take?

A Site Studio import & Drupal cache clear.

#### Are there any risks I should be aware of?

None.

### Custom components in-use tracking

#### What is it?

Custom component's usage can now be tracked via the Site Studio in-use system. Within the components list builder page,
next to a custom component it will now display either an "in-use" link, so users can see where a custom component has
been used or be shown as "Not in use".

#### What impact will there be?

Users can see where custom components have been placed.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Removing the "clone" option from Site Studio list builders

#### What is it?

Removing the "clone" option that the Entity Clone module adds, from Site Studio configuration list builders as Site
Studio provides its own "duplicate" functionality. The "clone" functionality did not work as expected for Site Studio
configuration entities.

#### What impact will there be?

If the Entity clone module is installed, the "clone" option will not appear in the operation list on Site Studio list
builders.

#### What actions do I need to take?

If users want to "clone" a Site Studio configuration entity, use the "duplicate" operation.

#### Are there any risks I should be aware of?

None.

### Create a component content from a custom component via the UI

#### What is it?

The ability to create a component content from a custom component direct in the UI
at `Site Studio > Components > Component Content > Create Component Content` rather than from the Layout Canvas.

#### What impact will there be?

Component contents can be created from a custom component directly in the UI as is possible with components and no
longer has to be created from a Layout Canvas.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Menu template loses menu link context after rendering menu links

#### What is it?

Fixes a bug where menu templates with multiple levels were losing token context depending on where the elements were
placed within the menu template.

#### What impact will there be?

The token context will not be lost and the token content will now render as expected within the menu.

#### What actions do I need to take?

A Site Studio import & rebuild.

#### Are there any risks I should be aware of?

We recommend visually testing menu layout of a rendered pages for sites that have multi-level menus and are using tokens
to display content for specific links.

### Custom menu link attributes were not being added to menu links as expected

#### What is it?

Fixes an issue were when using the menu link attributes module, and adding custom attributes they were not being rendered as expected.

#### What impact will there be?

Custom menu link attributes added to menu items will now be rendered on menu links.

#### What actions do I need to take?

Perform a Site Studio import & rebuild as per the upgrade steps.

#### Are there any risks I should be aware of?

If custom attributes were added to menu links in the past they will now render.

### Limit allowed HTML tags filter is now enabled by default on Site Studio text format

#### What is it?

The `Limit allowed HTML tags and correct faulty HTML` filter is now enabled by default on the Site Studio text format.

#### What impact will there be?

New Site Studio module installations will have the`Limit allowed HTML tags and correct faulty HTML` filter enabled on
the Site Studio text format.

#### What actions do I need to take?

Existing sites are not affected by this change, but it is recommended to enable
the `Limit allowed HTML tags and correct faulty HTML` filter on the Site Studio text format.

#### Are there any risks I should be aware of?

New Site Studio module installs or reinstalls, the "Site Studio" text format will now limit the use of HTML tags within
WYSIWYGs to the specific HTML tags configured in the text format.

On existing Site Studio installs no change will occur and user action is required to limit the allowed HTML tags in the
text format by enabling the `Limit allowed HTML tags and correct faulty HTML` filter in the "Site Studio" text format.

### The menu-link tokens not working in menu templates

#### What is it?

The menu-link tokens were not rendering any values within menu templates.

#### What impact will there be?

The menu-link tokens will now work as expected in menu templates.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### View filter labels

#### What is it?

The View filter element has a new toggle option, "Show filter label", which when set will render the filter or sort
label set within the Drupal view. By default, this option is toggled off for backward compatibility.

#### What impact will there be?

View filters can have labels that have been set within the Drupal view.

#### What actions do I need to take?

If a view filter should display a label, the "Show filter label" option should be toggled on within the view filter
element.

#### Are there any risks I should be aware of?

None.

## 6.9.5

### Content template url is malformed when used in a multisite/subdir context

#### What is it?

Fixes a bug when adding a Drupal field to a content template under multisite in a subdirectory context.

#### What impact will there be?

The underlying API call is correctly formed and the field edit form loads as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### PHP 8.1 Deprecation warning when an icon library contains a null value

#### What is it?

Fixes a PHP 8.1 deprecation warning when an icon library contains a null value.

#### What impact will there be?

Logs will be silent when an icon value is null in PHP 8.1.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.9.4

### Layout canvas changes are lost when server side validation and refresh takes place

#### What is it?

When using a required checkbox or radio button field in a content type, form validation will trigger a page refresh
causing layout canvas changes to be lost if the required field is not populated.

#### What impact will there be?

Form validation will retain layout canvas changes regardless of page refresh.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Visual page builder initialized incorrectly

#### What is it?

Fixes a bug where the visual page builder would initialize when a Layout canvas
field was used in a block. This would result in an error.

#### What impact will there be?

The visual page builder will only be initialized if the entity type has a layout
canvas field. Nested entities that contain layout canvas fields will not enable
the visual page builder.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

None.

### PHP 8.1 support added

#### What is it?

Ensures php code meets php 8.1 standards and removes any errors or warnings from logs.

#### What impact will there be?

Site Studio 6.9.4 and above can be used without warnings or errors being logged.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Updating multiple twig functions to respect Drupal entity permissions

#### What is it?

Unpublished referenced entities sometimes would be still rendered for users
without permissions to view such entities. This now has been resolved.

#### What impact will there be?

When rendering referenced entities Drupal permissions and entity status will be
respected.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

If the website used this bug as a feature where unpublished entities were still
displayed when used as referenced entities via Layout Canvas, this will need to
be addressed.

### Updating Image Browser Update manager to use ModuleHandlerInterface

#### What is it?

Updates the image browser update manager to use ModuleHandlerInterface rather
than ModuleHandler.
This caused some errors when contrib modules overwrote the default
ModuleHandler.

#### What impact will there be?

If a site uses a module that overrides the default ModuleHandler, no errors
relating to ModuleHandler should occur.

#### What actions do I need to take?

Cache clear.

#### Are there any risks I should be aware of?

None.

### Page builder incorrectly displaying drop zones from components that contained a nested component with a drop zone

#### What is it?

Fixes a bug where drop zones were appearing on the page builder from nested
components, causing JS errors such as "Cannot read properties of undefined (
reading 'parentUid')" when attempting to insert new components.
Only drop zones from components on a node layout canvas should appear in the
page builder.

#### What impact will there be?

Only drop zones from components on a node layout canvas will appear. Drop zones
from a component in a component will not appear on the page builder, reflecting
the node layout canvas.

Users should not experience JS errors when attempting to insert components onto
the layout canvas in the page builder.

#### What actions do I need to take?

Cache clear and Site Studio rebuild.

#### Are there any risks I should be aware of?

None.

### Nested accordion tabs components navigation links incorrect

#### What is it?

Fixes a bug where nested accordion tabs components navigation links were all the
same. This meant users would not be able to navigate between the accordion
items.

#### What impact will there be?

The navigation links will be correct and clicking on them will show the correct
accordion item content.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Nested components in a pattern repeater resulted in same styling

#### What is it?

Fixes a bug where nested components in a pattern repeater resulted in the same
styling when it should be different for each repeated item.

#### What impact will there be?

The styling will appear as expected and will be different for each repeated item
if set in the component.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Error appeared on the page builder when editing a translated node that contained a component in a component

#### What is it?

Fixes a bug where the error "Cannot read properties of undefined (reading '
uuid')" appeared whilst editing a translated node that contained a component in
a component on the layout canvas.

This error specifically appeared when editing using the Visual Page Builder.

#### What impact will there be?

The error "Cannot read properties of undefined (reading 'uuid')", will no longer
appear when editing a translated node that contains a component in a component.
Users will be able to edit the translated node as expected using the Visual Page
Builder.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.9.3

### Any new component created by saving from the layout canvas cannot be used.

#### What is it?

Creating a component from within the layout canvas ellipsis menu results in a broken component that cannot be used.

#### What impact will there be?

Components can successfully be created from within the layout canvas ellipsis menu.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Resolves package export crashing when handling recursive config dependencies

#### What is it?

Fixes a bug where the package export process would crash if a recursive config dependency was encountered.

#### What impact will there be?

Recursive config dependencies will be handled without issues

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Adds support for relative paths to sitestudio:package:multi-import

#### What is it?

Fixes a bug where the `sitestudio:package:multi-import` would only work with absolute paths. After this change both absolute and relative paths are supported.

#### What impact will there be?

Relative paths will be allowed to be used both as argument to the `sitestudio:package:multi-import` command and as package definitions in multi-package manifest file.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.9.2

### Duplicate styles on same component

#### What is it?

Fixes a bug where the same styles or no styles were applied when the same type of component
was placed on the page

#### What impact will there be?

All styles will now come through without any duplicate

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Exported packages files store metadata in individual files

#### What is it?

Packages exported in new format no longer store all of exported file entity metadata in a single
`sitestudio_package_files.json` file. Instead each file entity metadata is exported as individual file prefixed with
`sitestudio_file_metadata` and using `.yml` extension.

#### What impact will there be?

All new package exports will no longer contain `sitestudio_package_files.json` file. Individual metadata files using
filename format of `sitestudio_file_metadata.<uuid>.yml` will be exported instead.

Any packages that already use `sitestudio_package_files.json` will continue to work as before. If such package is
exported, metadata from `sitestudio_package_files.json` will be split into individual files after this update.

#### What actions do I need to take?

Export your packages via Drush or Drupal UI and make sure to commit all of the additionally created metadata files, if
git is used.

#### Are there any risks I should be aware of?

None.

## 6.9.1

### Creating a component content from a custom component on a layout canvas

#### What is it?

Fixes a bug when creating a component content from a custom component on a layout canvas the newly created component
content would not appear in the sidebar browser.

#### What impact will there be?

Component contents can be created from a custom component on a layout canvas and they will appear in the sidebar
browser as expected.

Any existing component contents created from a custom component via the layout canvas will now appear in the
sidebar browser after running the database update.

#### What actions do I need to take?

Run Drupal update via drush or UI.

#### Are there any risks I should be aware of?

None.

### Create a component content from a custom component via the UI

#### What is it?

The ability to create a component content from a custom component direct in the UI
at `Site Studio > Components > Component Content > Create Component Content` rather than from the Layout Canvas.

#### What impact will there be?

Component contents can be created from a custom component directly in the UI as is possible with components and no
longer has to be created from a Layout Canvas.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Deleting an item from a Pattern repeater that contains a Link field the last item is always deleted

#### What is it?
Fixes a bug when a Component has a Pattern repeater with a Link field inside the last item would always be deleted.

#### What impact will there be?
The correct item will now be deleted.

#### What actions do I need to take?
Run an API import `drush cohesion:import` when upgrading.

#### Are there any risks I should be aware of?

None.

### Resolves fatal error when using Site Studio and the Component modules together

#### What is it?

Fixes the fatal error `Fatal error: Cannot redeclare _component_build_library()` when both Site Studio and the Component
modules are installed together.

#### What impact will there be?

Both Site Studio and the Component modules can be installed together.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### A nested component with multiple instances, with inline styles always using the first components styles

#### What is it?

Fixes a bug where inline styles of a nested component with multiple instances of the same component were present. The
styles are configurable in the component form, but would always use the first instances styles, rather than the styles
set for that component instance.

#### What impact will there be?

If the component has a configurable form that applies inline styles, the correct styles will be applied for that
component instance, rather than using the first instances styles.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Transitioning content between moderation states on the Visual page builder resulting in an error

#### What is it?

Fixes a bug when attempting to transition content between moderation states, on the Visual page builder, the user
receives the error `Invaild state transition from..` although the state transition should be valid. This appeared to
only occur on content types configured to use revisions.

Transitioning between these states worked as expected on the backend edit page.

#### What impact will there be?

The error `Invaild state transition from..` should only appear when attempting to transition to an invalid state.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Includes "indirect" dependencies when exporting new format packages

#### What is it?
Fixes a bug where only direct dependencies would only be included in package export.

#### What impact will there be?
Additional files will be present in new format package exports.

#### What actions do I need to take?
Run a `sitestudio:package:export` command or use UI to export new format package complete with
previously missing dependencies.

#### Are there any risks I should be aware of?

None.

## 6.9.0

### Machine names are displayed in categories list

#### What is it?

Adds a new column to the "Component categories" entity list named "Machine names (id)" that contains each category "machine name" (id) used by Drupal.

#### What impact will there be?

It is possible now to quickly find category machine name without having to edit it.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Site studio custom components

#### What is it?

Adds a new feature to Site studio where developers can create their own components by coding them with Javascript/HTML/CSS or Twig/Javascript/CSS.
Refer to the [user guide](https://sitestudiodocs.acquia.com/user-guide/custom-component)
for more detailed documentation on how to create and use custom components.

Custom component examples can be found in a submodule (example_custom_component). This module is intended for demonstration
purposes only and should not be enabled on production.

#### What impact will there be?

Developers will be able to extend Site studio capabilities by defining their own custom components. This will enable Site Studio to have functionality that
previously could not be built with Site Studio elements.

Javascript developers with limited knowledge of Drupal will be able to add custom component to Site Studio
without the need for a Drupal developer. A Drupal developer will only be required for creating
a module and file structure. Refer to the [user guide](https://sitestudiodocs.acquia.com/user-guide/custom-component)
for more information.


#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Site Studio package multi-import command for Drush

#### What is it?

A new drush command that takes package list file as an argument and imports multiple packages at once with a combined rebuild process based on imported changes.

#### What impact will there be?

This will allow specifying multiple packages to be imported into a site in a specific order with a single optimised rebuild process.
There is also a `PackageImportHandler` (`cohesion_sync.package_import_handler`) service available to access such functionality via PHP and Drupal services.

#### What actions do I need to take?

The new command is `sitestudio:package:multi-import --path=<path/to/package_list.yml`.
Package list needs to follow specific format for the command to work, more information is available [here](https://sitestudiodocs.acquia.com/6.9/user-guide/package-import-using-drush#multi-package).

#### Are there any risks I should be aware of?

Only new format packages (exports containing multiple yaml files, rather than single aggregated yaml file) are supported.
Strict format of package list file needs to be followed.
Import happens in the same order as listed in a package list. If multiple package import the same entity, only the last import will be taken into consideration.
Reading [documentation](https://sitestudiodocs.acquia.com/6.9/user-guide/package-import-using-drush#multi-package) before attempting to use the new command is strongly recommended

### Next generation images

#### What is it?

Adds the ability to use Next generation images such as `.avif` and `.webp` images when using the Picture Element (subject to Drupal supporting these image formats).

#### What impact will there be?

This will fix an issue where the fallback image was the first image added to a Picture Element. The last image added to the Picture Element will now be used as the fallback.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Missing permission check for saving components/helpers from a layout canvas

#### What is it?

The "Save" option allowed a user to save the layout canvas (or an item on it) both as Helper and as Component even if the user did not have permission to do so.

#### What impact will there be?

Users will now need the 'administer helpers' and/or 'administer components' permissions to use the Save functionality on layout canvases.

#### What actions do I need to take?

You may need to update user permissions if you were relying on this functionality.

#### Are there any risks I should be aware of?

None.

### Site Studio generated CSS will now be aggregated

#### What is it?

Any stylesheet generated by Site Studio will now be aggregated with the rest of the website styles

#### What impact will there be?

All styles will now be aggregated increasing frontend performance.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Component contents not rendering

#### What is it?

Fixes an issue where component contents were not rendering after reverting to a previous revision or publishing a draft.

#### What impact will there be?

Component contents will now render as expected.

#### What actions do I need to take?

Run a rebuild as part of upgrading to this version.

#### Are there any risks I should be aware of?

None.

### Inline css media queries will not render if all component fields are empty

#### What is it?

In the event where one or multiple fields attached to a style on a specific media query are not populated when the component is used, the media query will not render at all.

#### What impact will there be?

This will fix an issue where empty media query css would render, having no effect on the page

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Improvements to the link auto complete endpoint

#### What is it?

Some improvements made to the link auto complete endpoint used in the link element, which should improvement performance and use less memory.
When inserting an external link the endpoint won't continue to look for content.

#### What impact will there be?

The auto complete functionality used in the link element should use less memory. When using the entity type selection
within the link element, it now correctly displays the supported types. These types include: nodes, views, taxonomy
terms, users, media and files.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Uncaught ReferenceError: _ is not defined console error

#### What is it?

Fixes a bug when editing a component on a layout canvas, the sidebar editor would throw a console error.
If aggregation was enabled the sidebar browser did not load as expected.

#### What impact will there be?

This error will no longer appear and the sidebar editor will load as expected if aggregation is enabled.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.8.3

### Slider and Accordion elements behave incorrectly in Visual Page Builder

#### What is it?

The slider should NOT autoplay when VPB is enabled even if it has been configured to do so.

Accordion tabs should not append a hash to the URL when VPB is enabled even if they have been configured to do so.

#### What impact will there be?

User experience editing these elements in the VPB should be improved.

#### What actions do I need to take?

Run an API import `drush cohesion:import` when upgrading.

#### Are there any risks I should be aware of?

None.

## 6.8.2

### WYSIWYG used with hide if no data adds extra <br>

#### What is it?

When using WYSIWYG field on a component and linking to both a WYSIWYG element and a hide if not data field, it would add extra <br> for each line break

#### What impact will there be?

Rendered WYSIWYG will not longer output extra <br>

#### What actions do I need to take?

You need to re-save effected entities or perform a rebuild

#### Are there any risks I should be aware of?

None.

### Compatibility with the Select2 module

#### What is it?

When exposing filters on a view and utilising a module/library such as [Select2](https://www.drupal.org/project/select2) the select2 library was not attaching as expected on page load

#### What impact will there be?

Following a cache rebuild the select2 library will attach to select elements as expected

#### What actions do I need to take?

Cache rebuild

#### Are there any risks I should be aware of?

None.

### Modal element autoload functionality not working

#### What is it?

Fixes a bug where a modal that had the autoload functionality enabled was not autoloading as expected and a browser console error was displayed.

#### What impact will there be?

Modals that should autoload will now automatically load as expected.

#### What actions do I need to take?

Run an API import `drush cohesion:import` when upgrading.

#### Are there any risks I should be aware of?

None.

### Visual page builder edit controls not showing on translated page

#### What is it?

When editing a translated page, the edit buttons would not show when using the Visual page builder

#### What impact will there be?

You can now edit component on translated pages

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Single entity exports fix

#### What is it?

When exporting single entities (like components) via UI, full package settings for entity type restrictions were applied. This has been fixed and full package export settings now only apply to full package exports.

#### What impact will there be?

Individually exported entities will no longer follow full package export settings. This will resolve some unexpected behaviour, such as files with zero bytes content being exported when single entity packages export would contain items restricted by full packace export settings.

#### What actions do I need to take?

If you have single entity packages exported with zero bytes content, exporting the same entities again

#### Are there any risks I should be aware of?

None.

### Unable to upload fonts on a path based multi-site setup

#### What is it?

Fixes a bug when attempting to upload font and icon font files would produce an error.

#### What impact will there be?

Fonts and icon fonts can be uploaded correctly without error on a path based multi-site.

#### What actions do I need to take?

Run a cohesion API import `drush cohesion:import` when upgrading.

#### Are there any risks I should be aware of?

None.

### Enabled template generation during drupal config import

#### What is it?

When importing Drupal config, if the Site Studio module has been initialised, templates generation will occur if relevant content types are imported.

#### What impact will there be?

Templates will be generated when Drupal config is imported.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

If templates are generated during deployments, CLI output will have additional output - we recommend additional testing of automation tools that rely on such output.

### Add a drush command that manually triggers regeneration of templates

#### What is it?

We have added new drush command `sitestudio:templates:regenerate` that will trigger templates regeneration manually. This is useful in case a non-standard workflow was used to introduce content type changes (for example direct database manipulation) or if Drupal is in a state of templates misalignment due to config import completed prior to Site Studio 6.8.2.

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

If templates are generated during deployments, there will be additional CLI output - we recommend additional testing of automation tools that rely on such output.

### New Package Management permissions fix

#### What is it?

When exporting packages via UI using the new package management non super-admin role users would encounter "access denied" screen when trying to download generated package, even if they would have "access coheison_sync" permission assigned to their role.

#### What impact will there be?

Only users with "access cohesion_sync" permissions will be able to export packages via UI.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.8.1

### Field Repeater - Deleting an item in the Component form deletes the wrong item

#### What is it?

When deleting an item with a WYSIWYG in a Field repeater deletes the wrong item.

#### What impact will there be?

Components that have a Field repeater with a WYSIWYG, will now delete the correct item.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Field Repeater - When Minimum repeatable fields is set it doesn't show the Minimum number of pattern repeats

#### What is it?

When creating a Component with the Minimum repeatable fields set to more than 1 the minimum number is not being rendered.

#### What impact will there be?

When using a Component the Minimum repeatable fields will match what is rendered.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Field repeater - number of fields getting out of sync

#### What is it?

When first adding a Component to the Layout canvas and not changing any settings before saving the page, when you then come back to the Component on the Layout canvas and Edit the Component the default Minimum number of fields is incorrect.

#### What impact will there be?

The minimum number of fields will now be shown correct when saving the page and then editing the Component settings.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Unable to use the Visual page builder with nested components

#### What is it?

Fixes a bug where a component dropped on the Visual page builder that would also contain a component would return an error ("Cannot read property of undefined (reading 'uuid'))

#### What impact will there be?

Components containing components can now be used in the Visual page builder

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Visual page builder warning "no image browser defined"

#### What is it?

Fixes a bug where "no image browser defined" warning message would appear on the visual page builder, although an image browser had been defined.

#### What impact will there be?

This will resolve the warning appearing incorrectly, allowing the user to select an image as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Unable to add components to a layout canvas on path based multi-site

#### What is it?

Fixes a bug where users were experiencing errors when attempting to add components to a layout canvas on a path base multi-site.

#### What impact will there be?

Components can now be added the to layout canvas without error.

#### What actions do I need to take?

A Site Studio API import should be run as part of the upgrade: `drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Prevent Package entities from being processed during rebuild

#### What is it?

Fixes a bug where Package entities would be processed during partial rebuild after package import.

#### What impact will there be?

This will resolve errors that were happening during package imports, if Package entities would be imported and partial rebuild triggered.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.8.0

### Layout canvas undo and redo

#### What is it?

Adds a new feature to the layout canvas to allow you to undo changes to the layout canvas and redo. Two buttons are added to the top of a layout canvas allowing you to go back and go forward with your changes.

#### What impact will there be?

Two new buttons added to the layout canvas.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

There is the risk of manually deleting data by using the buttons inadvertently.

### Copy token button

#### What is it?

Adds a new feature to the layout canvas to allow you to copy the token value of the layout canvas node. This allows you to easily copy and paste tokens without having to type them manually any more.

#### What impact will there be?

A new button on layout canvas nodes. Improved usability.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Updated Package entity definition

#### What is it?

We have updated the entity definition of Package config entities - `config_prefix` now matches entity `id`. Where previously entity `id` was `cohesion_sync_package` and `config_prefix` was `package`, now both are `cohesion_sync_package`.

#### What impact will there be?

There is no impact to packages exported via Site Studio package management mechanisms. Package entity definitions exported via Drupal should be renamed to use updated prefix.

#### What actions do I need to take?

If package entity definition was exported as an individual file via Drupal config, the file should be renamed in order for it to work correctly. For example, file exported as `cohesion_sync.package.pack_test_package.yml` should be renamed to `cohesion_sync.cohesion_sync_package.pack_test_package.yml`.

#### Are there any risks I should be aware of?

If Site Studio Package entity was exported via Drupal configuration management and package file is not renamed, importing such file will prevent the entity from behaving as expected, for example it will not appear in Package list.

### Undefined breakpoint on menu template

#### What is it?

Fixes a bug where an error would be thrown in the console when using menu template `Uncaught TypeError: Cannot read property 'xl' of undefined on menu template`.

#### What impact will there be?

Using template will not throw that error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Some content not rendering when inside of a pattern repeater

#### What is it?

Fixes a bug where some components content was not rendering when within a patter repeater.

#### What impact will there be?

Components that use pattern repeaters and are not rendering content will start rendering as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### In context editing of Components has been removed

#### What is it?

In context editing functionality is being deprecated in Drupal core, therefore support for in context editing on Components and Component content on the front-end has been removed in line with this.

#### What impact will there be?

Components and Component content in context editing will no longer be available.
The Visual page builder can be used to edit a Site Studio layout canvas on the front end - please see [using the visual page builder for building pages and editing content](https://sitestudiodocs.acquia.com/user-guide/using-visual-page-builder-building-pages-and-editing-content).

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Permission "access contextual links" is no longer required for using Visual page builder

#### What is it?

The permission "access contextual links" is no longer needed to use the Visual page builder.

#### What impact will there be?

Access contextual links permission is no longer needed to use the Visual page builder.

To use the Visual page builder these permissions are needed:

- Use toolbar
- Site Studio Visual page builder
- Site Studio Access components group

#### What actions do I need to take?

If the permission "access contextual links" was applied to a role specifically to allow access to the Visual page builder this can be removed.

#### Are there any risks I should be aware of?

None.

### Multilingual letters in a paragraph element don't create line breaks

#### What is it?

Fixes a bug when using a paragraph and entering some multilingual letters which included line breaks, were not breaking onto separate lines as expected.

#### What impact will there be?

Multilingual letters will now line break as expected within paragraph elements.

#### What actions do I need to take?

You should perform a `drush cohesion:import` & `drush cohesion:rebuild`.

#### Are there any risks I should be aware of?

None.

### Custom elements can be containers

#### What is it?

Custom elements can now be containers, this allows other elements to be placed inside them.
This gives developers more flexibility when creating custom elements.

#### What impact will there be?

There is no impact on existing custom elements.

#### What actions do I need to take?

To make a custom element a container you need to implement a few extra bits of code.
Please see an example in the example element module of how to implement: `example_element/src/Plugin/CustomElement/ContainerExample.php`.

#### Are there any risks I should be aware of?

None.

### Rebuild events

#### What is it?

When a rebuild operation is run, an event is dispatched at the time the operation starts and completes.
This allows developers to write event subscribers to listen for these events and trigger further actions if required.

#### What impact will there be?

Events will be dispatched during a Site Studio rebuild.

#### What actions do I need to take?

To take advantage of these events, event subscribers will need to be created in custom code.

Events that can be subscribed to:

Pre Rebuild: `Drupal\cohesion\SiteStudioEvents::PRE_REBUILD`
Post Rebuild: `Drupal\cohesion\SiteStudioEvents::POST_REBUILD`

See module readme for more information on these events.

#### Are there any risks I should be aware of?

None.

### Visual page builder throws an error in the page contains a block with a layout canvas

#### What is it?

The visual page builder would not work and return an error if the page contains a block that has a layout canvas. This could be a custom block or a view rendering entities with layout canvases.

#### What impact will there be?

No impact but it is worth noting that only the layout canvas of the page can be edited with the Visual page builder. Any entity rendered by a layout canvas that itself contains a layout canvas would need to be edited in the admin.

#### What actions do I need to take?

You should clear cache: `drush cr`.

#### Are there any risks I should be aware of?

None.

### Expose component content to views

#### What is it?

Adds the relevant data handler to enable component_content entities to be utilised with the views module.

#### What impact will there be?

It will now be possible to create custom views listing component_content entities.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Node revision delete module now a soft dependency

#### What is it?

A message will appear when installing Site Studio via composer suggesting to install the node revision delete module.
If the node revision module isn't installed a message will also appear on the Drupal status page.

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Modals not working on AJAX enabled views when an AJAX action occurs

#### What is it?

Fixes a bug where modals used on an Ajax enabled view, would not open after the Ajax action had been triggered.

#### What impact will there be?

Modals loaded with Ajax will now work as expected.

#### What actions do I need to take?

You should perform a `drush cohesion:import`.

#### Are there any risks I should be aware of?

None.

### Youtube no cookie URLs not working correctly with the video element

#### What is it?

Fixes a bug where inputting a Youtube no cookie URL was still using the normal embed URL.

#### What impact will there be?

Youtube no cookie URLs will now work as expected.

#### What actions do I need to take?

You should perform a `drush cohesion:import`.

#### Are there any risks I should be aware of?

None.

### Theme specific templates not properly deleted

#### What is it?

Fixes a bug where templates that are more specific would not be deleted and still be selected as the template to render.
Only deleting all templates and run a `drush cohesion:rebuild` would fix the issue.

#### What impact will there be?

There is not impact, follow the instructions below to resolve the issue.

#### What actions do I need to take?

You should perform a `drush cohesion:rebuild`.

#### Are there any risks I should be aware of?

None.

### Changing the "Base Unit Font Size" causes the media queries to be incorrectly calculated

#### What is it?

Fixes a bug when changing the Base font size is Base unit settings from 16px, CSS media queries were being incorrectly calculated into `rem` units.

#### What impact will there be?

All Site studio generated CSS styles will now use pixel (`px`) units on CSS media queries.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### New sync command to list out sync packages

#### What is it?

A new drush sync command `drush sitestudio:package:list` which lists available sync packages created using the package manager.

#### What impact will there be?

Running the command will list out available sync packages names and IDs in the CLI.

#### What actions do I need to take?

None.

### PHP notices appearing when using Better exposed filters module

#### What is it?

This fixes a bug where some PHP notices were appearing when using Better exposed filters on a view.

#### What impact will there be?

No PHP notices will appear relating to Better exposed filters.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### "Package" entity appearing in "exclude entity types" package UI list

#### What is it?

The package entity was appearing within the exclude entity types list when creating a package in the UI, this could produce a blank package export if "packages" was selected to be excluded.

#### What impact will there be?

The package entity can no longer be excluded from packages.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### New Drush command for exporting and importing Site Studio Packages

#### What is it?

We are adding new Drush command that allows Site Studio Package export and import via individual files. This command no longer encodes Images and keeps Config files individually, allowing for better Developer experience.
Config files are exported with pretty-printed Json, allowing for human readability and review of exported config. Images and other files exported with the package are now exported in their respective formats.
Both individual files and full package export options are available.

#### What impact will there be?

The new commands will not be compatible with config exported by using old commands. Old commands are still available for use.
The new Package export and import will behave in a way that is very similar to the Drupal config export and import via Drush.

#### What actions do I need to take?

We encourage trying new Import and Export commands as they significantly reduce the time required for Package import and export.
Additional information about the new commands is available [here](https://sitestudiodocs.acquia.com/user-guide/sync-package-export-and-import).

#### Are there any risks I should be aware of?

Mysql settings for `max_allowed_packet` on the environment needs to be large enough to be able to handle whole package in a single Database transaction.

## 6.7.0

### PHP 8.0 compatibility

#### What is it?

PHP 8 has added supported for named arguments. This can cause problems when using call_user_func_array() as is the case
with batch operations - see https://wiki.php.net/rfc/named_params for the changes in question.

#### What impact will there be?

Site Studio batch operations will operate as expected on php 8.0 based environments as of 6.7.0.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Non serialised JSON when exporting site studio packages

#### What is it?

When exporting Site Studio assets you can now set a setting to export all json values non serialised.
This is to increase the readability of sync packages when doing diffs as it will output json values in multiple lines.

#### What impact will there be?

None.

#### What actions do I need to take?

To enable this functionality add `$settings['site_studio_package_multiline'] = TRUE` to your settings.php.

#### Are there any risks I should be aware of?

None.

### Loaded config before bootstrap

#### What is it?

This fixes a bug where config would be loaded before bootstrap causing side effects on other services.

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Entity reference fields not working after upgrading to 6.6.0

#### What is it?

This fixes a bug where entity references inside field repeater would not be updated properly resulting in a broken reference.

#### What impact will there be?

There is no impact, entity references should now be linked to the correct entities.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Integration with Acquia ContentHub

#### What is it?

We are moving default functionality when using Acquia ContentHub into a new contrib module. The new module is called
"Sitestudio Contenthub" ([sitestudio_contenthub](https://github.com/acquia/sitestudio_contenthub)) and contains two
new submodules `sitestudio_contenthub_publisher` and `sitestudio_contenthub_subscriber`.
They add additional Site Studio support for Content publishing and importing via ContentHub.

#### What impact will there be?

Customers leveraging Content hub for [Personalization/Lift](https://www.acquia.com/products/marketing-cloud/personalization) integration with Site Studio will be required to take manual action to ensure continued operation,
Site Studio related entities will no longer be published and imported until the new "Sitestudio Contenthub" ([sitestudio_contenthub](https://github.com/acquia/sitestudio_contenthub))
is downloaded and appropriate submodule is enabled. Once the submodules are enabled, the functionality will resume, but integration behaviour might be different, depending on how it was used until 6.7.0.
Please refer to the [documentation](https://sitestudiodocs.acquia.com/6.7/user-guide/using-acquia-cohesion-acquia-content-hub) to find out more.

#### What actions do I need to take?

If integration with Acquia ContentHub is required, please read updated [documentation](https://sitestudiodocs.acquia.com/6.7/user-guide/using-acquia-cohesion-acquia-content-hub),
download the new "Sitestudio Contenthub" ([sitestudio_contenthub](https://github.com/acquia/sitestudio_contenthub))
and enable submodules relevant to the Publisher and the Subscriber sites.

#### Are there any risks I should be aware of?

This update removes default integration with ContentHub. If no actions are taken, Layout Canvas entities will no longer be published and imported via ContentHub.
Once the new module is downloaded relevant submodules are enabled, ContentHub integration with Site Studio will resume,
but will behave differently compared to 6.6.0 and earlier versions.
More information is available [here](https://sitestudiodocs.acquia.com/6.7/user-guide/using-acquia-cohesion-acquia-content-hub).

### Nested components in pattern repeaters not rendering

#### What is it?

Fixes a bug where nested components inside pattern repeaters with tokenized fields where not rendering.

#### What impact will there be?

Components that are nested inside another component and use a pattern repeater, that contain tokenized values will now render as expected.

#### What actions do I need to take?

Run a rebuild.

#### Are there any risks I should be aware of?

None.

### Component Configure links not working correctly on sub-directory multi-site setups

#### What is it?

Fixes a bug when using a components configure links on a layout canvas on a sub-directory multi-site setup, the URL returned was incorrect.

#### What impact will there be?

The component layout canvas configure links will now work for multi-sites that are configured in a sub-directory setup.
For example: www.domain.com/site1 www.domain.com/site2

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Component content throwing an error on Drupal 9 when moderation is enabled

#### What is it?

Fixes a bug where the edit page of a component content would throw a `Call to undefined method isReusable()` error when moderation is enabled.

#### What impact will there be?

Editing component content while having content moderation enabled will work as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### References to Component Content on the Layout canvas have changed from IDs to UUIDs

#### What is it?

Component content that is placed on a layout canvas is now referenced by its UUID rather than ID.
This makes it compatible with modules such as contenthub and default content.

#### What impact will there be?

There should be no impact from a users perspective.
Any existing component contents on layout canvases will be updated to reference by UUUID, and new ones will be referenced by UUIDs.

#### What actions do I need to take?

When upgrading be sure to follow the upgrade guide and run a rebuild when stated.

#### Are there any risks I should be aware of?

None.

### When using the template selector field a warning appears on the Drupal status page

#### What is it?

Fixes a bug where the warning "Tokens or token types missing name property." appears on the Drupal status page when using the template selector field.

#### What impact will there be?

This warning will no longer appear.

#### What actions do I need to take?

Run `drush updb` as usual when upgrading.

#### Are there any risks I should be aware of?

None.

### Missing contexts no longer show as "undefined" in the context visibility select

#### What is it?

Fixes an issue where if a context is missing it would appear as "undefined" in the context visibility select where it had been used.

#### What impact will there be?

If a context has been selected in the context visibility select and is subsequently then removed, it will now show "Selected context is missing."

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### When cancelling a user that had created component content the uid was not updated correctly

#### What is it?

Fixes a bug where a users account is cancelled and component content that they had created should be reassigned, unpublished or deleted, was not being re-assigned or deleted correctly.

#### What impact will there be?

When cancelling a user account that has created component content it will be re-assigned, unpublished or deleted correctly based on what the user selects when cancelling the users account.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Installing Site Studio via a profile resulted in 403

#### What is it?

Fixes a bug when installing Site Studio through a profile, would result in a 403 rather than being logged in as user 1.

#### What impact will there be?

Installing Site Studio through a profile will now log the user in after installation as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Entity browser URLs not respecting sub-directory sites

#### What is it?

Fixes a bug where Entity browser URLs don't respect sub-directory multi-site setup and the URL returned was incorrect for this setup.

#### What impact will there be?

The URLs are now correct for both sub-directory multi-site setup and standard Drupal setups.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Component builder elements not always appearing in the sidebar browser

#### What is it?

Fixes a bug where the component builder elements category did not show in the sidebar browser as expected on component creation and edit forms, if certain referer policies were in place.

#### What impact will there be?

The component builder elements category will now appear as expected even if certain referer policies are in place.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Sync import through UI not working when on a sub-directory multi-site

#### What is it?

Fixes a bug where the sync file chunk URL was hardcoded rather than generated through Drupal.

#### What impact will there be?

No error will be encountered when importing a sync file through the UI on a sub-directory site.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Error: Call to undefined method isPublished()

#### What is it?

Fixes a bug where a call to an undefined method for entity types that do not use the trait EntityPublishedTrait and as such do not have a isPublished() method.

#### What impact will there be?

The error will no longer appear.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.6.1

### Menu templates are generated with incorrect filename

#### What is it?

Fixes a bug that causes menu templates to be generated with an incorrect filename and therefore results in not rendering the template selected.

#### What impact will there be?

Menu templates are now generated with the correct filename and will start to render as expected.

#### What actions do I need to take?

A Site Studio rebuild or re-save the affected menu templates.

#### Are there any risks I should be aware of?

None.

## 6.6.0

### Slider - accessibility improvements

#### What is it?

Accessibility of the Site Studio Slider has been enhanced to include features from Accessible360's Slick replacement.
These include:

- Users can define an `aria-label` for the Slider container, which also has a `role="region"` attribute/value.
- Instructions can be provided for screen reader users on the Slider container. This is added as a visually-hidden paragraph within the Slider container but before the slides.
- Each Slide item has a `role="group"` attribute/value with an `aria-label` value of `slide x`, where x is the numerical index in total number of slides.
- When `Autoplay` is enabled the user can add a play/pause button to the Slider, add visually-hidden state labels and customise its appearance and position.
- When `Center mode` is enabled, the `aria-label` of the centered slide will be appended with the text `(centered)`.
- The ARIA roles `tablist`, `tab`, `presentation` and `tabpanel` have been removed from pagination HTML and slide items.
- Keyboard navigation (using left and right arrow keys to traverse slides) was originally enabled programmatically. Whilst on by default for backwards compatibility, there is now a toggle option in the Slider container settings form to disable this option.

#### What impact will there be?

Customers using screen reader technology on your websites should have a better experience, providing configurable options are completed by site builder.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

Pagination or Slide item styles could be be broken if a site builder has used the now-removed tab roles as attribute css selectors.

### Components that rely on entity IDs not rendering after upgrade

#### What is it?

Fixes a bug where components that rely on entity IDs where not rendering the referenced content as expected.

#### What impact will there be?

Components can rely on either entity UUIDs or IDs for rendering referenced content.
If your components rely on entity IDs, upgrading will resolve referenced content not rendering.

#### What actions do i need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

## Wire mode is now available for the Visual page builder.

#### What is it?

Adds the ability to toggle on borders for components and borders for component drop zones.

#### What impact will there be?

Users will now be able to view borders on their components and drop zones. They can use the visual page builder menu, or toggle with (Alt + b).

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Content titles with brackets not appearing in Link typeahead

#### What is is?

Fixes a bug where content titles that contained brackets weren't appearing in the link typeahead.

#### What impact will there be?

When using the link typeahead content titles now appear as expected if they have brackets or not.

#### What actions do i need to take?

None.

#### Are there any risks I should be aware of?

None.

### KeyValue template storage fails on Drupal 9

#### What is is?

Fixes a bug where KeyValue storage would fail to locate templates with specific filenames.

#### What impact will there be?

KeyValue template storage will work correctly on Drupal 9.

#### What actions do i need to take?

None.

#### Are there any risks I should be aware of?

None.

### Pattern Repeater and Hide if No data results in data "bleeding" between repeated elements

#### What is is?

Fixes a bug where by having a component with multiple element having no data on different component field token, the data would "bleed" between elements

#### What impact will there be?

Hide if not data will now be respected on all elements within pattern repeaters

#### What actions do i need to take?

If affected by this issue, do a cache clear

#### Are there any risks I should be aware of?

None.

## 6.5.1

### Media library modal not always closing upon creating new media

#### What is is?

Fixes a bug where when creating a new piece of image media through the media library modal in Site Studio the modal failed to close when attempting to insert.

#### What impact will there be?

Media can be created through the media library modal and it closes as expected when clicking insert.

#### What actions do i need to take?

None.

#### Are there any risks I should be aware of?

None.

### Unable to import multiple files from the site_studio_sync folder

#### What is is?

Fixes an error where the drush sync:import command would not import all packages in the site_studio_sync folder but only one

#### What impact will there be?

All packages will be imported from the site_studio_sync folder

#### What actions do i need to take?

None.

#### Are there any risks I should be aware of?

None.

### Error being thrown in the console when accessing Base and Custom styles.

#### What is it?

Some Site Studio assets were being loaded from `/site/default/files` when this directory may not exist.

#### What impact will there be?

The horizontal and vertical rulers in Base and Custom styles have now been removed from the style preview.

#### What actions do i need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### When using a Field repeater the default minimum number of fields not working as expected

#### What is it?

When setting the minimum number of repeatable fields this was not being set as a default when first using the form.

#### What impact will there be?

When setting a minimum number of repeatable fields this will now be used as the default number of fields shown.

#### What actions do i need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Error when enabling custom module containing default_content and Site Studio components

#### What is is?

Fixes an error thrown when installing a custom module that includes Site Studio components exported with the default_content module.

#### What impact will there be?

The custom module will install successfully.

#### What actions do i need to take?

None.

#### Are there any risks I should be aware of?

None.

### Default colors are no longer included by default on new installs

#### What is it?

Removes the default gray, white and black colors from the color palette on a clean install of Site Studio.

#### What impact will there be?

When installing Site Studio on a site there will be no colors in the color palette. Existing sites won't be effected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Default component and helper categories no longer included by default on new installs

#### What is it?

Removes the default component and helper categories on a clean install of Site Studio.

#### What impact will there be?

When installing Site Studio on a site there will be no helper or component categories by default. Existing sites won't be effected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Using newly created media in Site Studio Media library not always selecting correct media item

#### What is it?

Fixes a bug where selecting newly created media in Site Studio media library was not always selecting the media item the user selected.

#### What impact will there be?

When selecting newly created media the correct item is now selected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Accordion tab items are not linkable

#### What is it?

Fixes a bug when creating a Hash URL for an Accordion item the page would not scroll to the open Accordion.

#### What impact will there be?

When creating a Hash URL for an Accordion item the page will automatically scroll to the open Accordion when the page first loads.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Untrusted callback exception on Drupal 9 sites

#### What is it?

Fixes a bug where viewing content in the view mode "search result" resulted in an untrusted callback exception on Drupal 9.

`Drupal\Core\Security\UntrustedCallbackException: Render #post_render callbacks must be methods of a class that implements \Drupal\Core\Security\TrustedCallbackInterface or be an anonymous function. The callback was _cohesion_entity_clean_css. See https://www.drupal.org/node/2966725 in Drupal\Core\Render\Renderer->doTrustedCallback()`

#### What impact will there be?

This error should no longer occur.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Fatal error when clicking "translate" on a content template

#### What is it?

Fixes a bug where if you have the Configuration translation module enabled and clicked the "translate" link a fatal error occurred.

#### What impact will there be?

The fatal error should no longer happen.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

The fatal error has been resolved, but further research is required to make Site Studio and Configuration translation modules fully compatible.

### Improving speed building package from the UI

#### What is it?

When building a package from manage package, it would take a significant amount of time to return the list of entities. This has now been improved.

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Drupal preview on existing nodes looses unsaved changes

#### What is it?

Fixes a bug when adding new components to a node layout canvas, then using the preview and going back to the node edit the most recent changes/additions were lost.

#### What impact will there be?

When using the Drupal preview on a node unsaved changes to the layout canvas are no longer lost.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Drupal block cache issue in Site Studio

#### What is it?

Fixes a bug when a non-accessible version of Drupal block used via Site Studio Block Element would be cached and cache would not refresh if block settings were updated making it accessible.

#### What impact will there be?

When updating block visibility settings, Block Element in Site Studio will have identical caching behaviour to a regular Drupal block.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.5.0

### Site Studio Visual Page Builder Module

#### What is it?

A new module available as part of Site Studio that provides a front-end page building and content authoring experience using Site Studio drag and drop components. The visual page builder is a companion to the Layout canvas that is used in the back-end Drupal user interface.

#### What impact will there be?

When installed, the module will add a new ‘Page builder’ button to pages that have a Site Studio layout canvas. When the button is clicked, users enter the new page builder mode.

#### What actions do I need to take?

To use the Page Builder you will need to install the Visual Page Builder module.

#### Are there any risks I should be aware of?

There are no known risks.

### Bugfix: Sync package entity dependencies not being removed if no longer used on the entity

#### What is it?

Fixes a bug where a sync package contains entities that then have their dependencies updated, but the sync package contained both the original and new dependency.
For example your component exists in a package, you then update that components default image, both images files were included in the sync package rather than the latest one.

#### What impact will there be?

Old entity dependencies should no longer appear in your sync package. Sync package files may also be smaller in size if there were multiple "old" dependencies.

#### What actions do I need to take?

You may need to re-save your sync package.

#### Are there any risks I should be aware of?

None.

### Provide an override for sync:import batch limit

#### What is it?

By default the sync:import process will handle 10 items at a time to reduce the memory required to run this operation.
This feature exposes a method for increasing that number via Drupal settings.

#### What impact will there be?

Where more memory is available the `sync_max_entity` can be set to a number greater than 10 to process the import faster.

#### What actions do I need to take?

Add a settings value such as:

    `$settings['sync_max_entity'] = 20;`

#### Are there any risks I should be aware of?

Increasing the `sync_max_entity` value will require more memory to process each sync:import batch.

### Link elements - support for `rel` attribute

#### What is it?

The `rel` attribute defines the relationship between a linked resource and the current document. Support has been added for three of the most important values:

- `nofollow` - prevents backlink endorsement, so that search engines don't pass page rank to the linked resource.
- `noopener` - prevents linked resource getting partial access to linking page, something that is otherwise exploitable by malicious websites.
- `noreferrer` - similar to `noopener` (especially for older browsers), but also prevents the browser sending the referring webpage's address.

#### What impact will there be?

For each of the following Site Studio elements:

- `Link`
- `Container`
- `Slide item`
- `Column`

Checkbox toggles for `No follow`, `No opener` and `No referrer` will appear when the following conditions are met:

- Link `Type` is set to `URL`
- Link `Target` is set to `New window`

When checked, they will be added to the created link HTML in the format `rel="nofollow noopener noreferrer"` (if all are enabled).

#### What actions do I need to take?

Existing components that you would like to use this feature on need to be updated, as these toggles are `OFF` by default.

#### Are there any risks I should be aware of?

- Use of the `No follow` toggle will have an impact on SEO, given that it stops search engines passing page rank endorsement to the linked resource.
  This is often used in blog comments or forums, as these can be a source of spam or low-quality links.
  Google and other search engines require `nofollow` to be added to sponsored links and advertisements.

- Use of the `No referrer` toggle will affect analytics, as it will report traffic as direct instead of referred.

### Menu templates - support for Drupal `<nolink>` token

#### What is it?

When creating Drupal menus, it's possible to use a `<nolink>` token to render the link text only, which outputs as a `span` instead of an `a` tag.

This can be useful for creating headings for menu sub-levels.

#### What impact will there be?

Previously Site Studio menu templates would ignore the `<nolink>` token and still render an `a` tag with an empty `href` attribute.

Now as per Drupal behaviour, these are rendered as `span` tags.

If a different HTML element has been specified in the Site Studio menu template (`Menu link` settings), this setting will take priority.

This is recommended if you are using `<nolink>` for creating menu sub-level headings.

#### What actions do I need to take?

1. Update your Drupal menu with the `<nolink>` token where needed.
2. Re-save the relevant Site Studio menu template, or run a site rebuild. Both of these actions will refresh the menu template code.

#### Are there any risks I should be aware of?

You many need to create additional menu template styles to account for the tag change from `a` to `span`, depending on how current styles are being applied.

### Accordion accessibility enhancements

#### What is it?

The accessibility of `Accordion tabs` elements has been improved, when in the accordion display mode.

#### What impact will there be?

- Accordion header links now have the `aria-expanded` attribute, which toggles between `true` and `false` when expanded and collapsed, respectively.
- Accordion header links now have `aria-disabled="true"` set if the parent `Accordion tabs container` has the `Collapsible` setting toggled `OFF`. This is only applied when the item is expanded, to indicate to a screen reader that the panel cannot be collapsed manually.

  When the panel is collapsed because a sibling accordion item is expanded, the `aria-disabled` attribute is removed.

- Accordion header links now have `aria-disabled="true"` permanently set if the accordion item has been disabled through `Navigation link` settings.

### Option to add `font-display` on Font libraries settings page

#### What is it?

Adds the ability to set the `font-display` CSS property when uploading a new web font.

#### What impact will there be?

None.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

---

## 6.4.3

### Bugfix: Child elements not rendering in layout canvas

#### What is it?

Fixes bug where children of elements would not render in the layout canvas

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.4.2

### Bugfix: Custom element has no context of what element it belongs to

#### What is it?

Fixes bug where custon elements had no context of what element it belongs to

#### What impact will there be?

None

#### What actions do I need to take?

Re-save component using custom elements. It can also be done as part of a `cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Bugfix: Typo/Incorrect permission for 'Add component content' route

#### What is it?

Fixes a permissions issue limiting access to the `add component content` page.

#### What impact will there be?

Users with `administer component content` can access Site Studio > Components > Component content > Add component content
as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Unable to create a translation of component content

#### What is it?

Fixes an issue where creating a translation of component content could result an error and it was not appearing the sidebar browser.

`Error: Call to a member function getCategoryEntity() on null in Drupal\cohesion\Services\CohesionUtils->getPayloadForLayoutCanvasDataMerge()`

#### What impact will there be?

No further error messages should occur when creating a translation of component content, they should also now appear as expected within the sidebar browser.

#### What actions do I need to take?

As part of the upgrade make sure to run `drush updb`.

#### Are there any risks I should be aware of?

None.

### Bugfix: Can't tokenize styles in the style builder on a component when TMGMT module is enabled

#### What is it?

When the TMGMT module is enabled users are unable to tokenize Styles in Style builder.

#### What impact will there be?

None.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None

### Compatibility with the Webprofiler module

#### What is it?

Fixes an issue where enabling the webprofiler module prevents some actions (eg. drush commands) from completing properly.

#### What impact will there be?

No code related issues will be observed relating to the webprofiler module being enabled.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Empty styles in the head

#### What is it?

Fixes an issue where if you have a component which applies styles to an element but the editor does not specify any value when using the component, an empty style in the head would be generated

#### What impact will there be?

No empty css selector will be printed on the page

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Component content edit links containing two language prefixes

#### What is it?

Fixes an issue where if you have a default language prefix, the component content edit link from the layout canvas contained two language prefixes and therefore resulted in an invalid link.

#### What impact will there be?

If the default language has a prefix, the component content edit links from the layout canvas are now correct.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Unpublished content entities appearing in typeaheads

#### What is it?

Fixes an issue where unpublished content entities were appearing in typeaheads when searching for content.
This meant that site editors could link to content that was not yet published and could lead to 404s.

#### What impact will there be?

Unpublished content entities no longer appear in typeaheads when searching for content in the Entity browser, Entity reference and link elements.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Can't insert media from media library after using the filters

#### What is it?

Fixes an issue where after using the filters in the Media Library you were not able to insert media, this resulted in an ajax error.

#### What impact will there be?

You will now be able to use the media library filters and insert media without error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

## 6.4.1

### Provide optional flag for sync:import to prevent calls to the API

#### What is it?

Adds new optional argument `--no-rebuild` to the `sync:import` drush command. When using the command with the argument, no entity rebuilds are triggered
reducing calls to the API. This is useful when a rebuild is likely to be run in a subsequent script during a deployment for example.

#### What impact will there be?

Reduce the time and memory required to run the `sync:import` process.

#### What actions do I need to take?

Usage:

`drush sync:import --no-rebuild`

#### Are there any risks I should be aware of?

Ensure a rebuild step is included as part of your scripts when employing this flag.

### Option to add `font-display` on Font libraries settings page

#### What is it?

Adds the ability to set the `font-display` CSS property when uploading a new web font.

#### What impact will there be?

None.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Entity browser element error appearing unexpectedly

#### What is it?

Fixes an issue where "This element requires the Entity browser or Media library modules enabled" error appeared even though users has either installed.
Also resolves another issue where you could not select "Typeahead" without "Entity browser" module being installed.

When adding a new Entity browser element to a Layout canvas the default option is now set to Typeahead as this is not module dependent.

#### What impact will there be?

The Typeahead option in the Entity browser element can be used without Entity browser or Media library modules installed and users should no longer experience the any error messages appearing unexpected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Compatibility with Link attributes module

#### What is it?

Fixes an issue when using the Link attributes module with Site studio.
This error would appear in the logs: `User error: "0" is an invalid render array key in Drupal\Core\Render\Element::children() (line 97 of /var/www/drupal/standard/web/core/lib/Drupal/Core/Render/Element.php)`

#### What impact will there be?

If using the Link attributes module and Site studio you should no longer see any errors on your site.

#### What actions do I need to take?

When upgrading an Site Studio import and rebuild should be run.

#### Are there any risks I should be aware of?

None.

### Provide optional flag for sync:import to prevent the site going in maintenance mode

#### What is it?

By default the `sync:import` cli command will put the site into maintenance mode, this feature provides a `--no-maintenance` flag to optionally disable this action.
This is useful on platforms such as ACSF where this behaviour is handled by the internal update hooks.

#### What impact will there be?

More granular control on how/when a site enters and leaves maintenance mode.

#### What actions do I need to take?

Usage:

`drush sync:import --no-maintenance`

#### Are there any risks I should be aware of?

A site _should_ be in maintenance mode during the sync:import procedure to avoid cache issues, ensure this is handled by either the platform or additional scripts
when employing the `--no-maintenance` option.

### Compressing data to the Site studio API

#### What is it?

By default Site Studio will send data to its API using gzencode to reduce the traffic. It can be turned off by settings the config `cohesion.settings compress_outbound_request` to 0

#### What impact will there be?

If enabled, when saving an entity through the UI or via drush cohesion:rebuild, the process will be faster as less traffic will be necessary.

#### What actions do I need to take?

If your server does not have zlib you might want to turn this off by doing `drush config-set cohesion.settings compress_outbound_request 0` or install zlib.

#### Are there any risks I should be aware of?

This is now turned on by default, make sure zlib is installed on your server or you might see some failures. See `What actions do I need to take?`

### Bugfix: IMCE & Media library not loading on sub-directory multi-site setups

#### What is it?

Fixes a bug when using IMCE and Media library integration with Site studio used relative URLS, where in a sub-directory multi-site setup, the URL returned was incorrect.

#### What impact will there be?

IMCE and Media library will now work for multi-sites that are configured in a sub-directory setup.
For example: www.domain.com/site1 www.domain.com/site2

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Deleting a component removes it's styles from stylesheet

#### What is it?

Deleting Components with inline styles in Drupal now removes the styles for such components from the stylesheet. Previously this would be done only during rebuilds.

#### What impact will there be?

Deleting Components with inline styles will reduce size of stylesheets immediately, without having to run rebuild.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Limiting the number of twig cache file created when using inline styles

#### What is it?

A large number of twig cache file were created when rendering inline styles, causing the twig cache folder to grow overtime very quickly

#### What impact will there be?

The number of twig cache file will stop growing when using inline styles

#### What actions do I need to take?

If you want to clear the twig cache folder you should clear the cache

#### Are there any risks I should be aware of?

None.

### Added the ability to not run a cache clear on drush cohesion:rebuild

#### What is it?

The drush command for rebuild now has a new option `--no-cache-clear` which removes the cache clear from the batch it performs.

#### What impact will there be?

If the option is specified the rebuild will take less memory to run.

#### What actions do I need to take?

Usage

`drush cohesion:rebuild --no-cache-clear`

#### Are there any risks I should be aware of?

You will still need to run a `drush cr` after the rebuild

### Reducing new lines within the inline CSS

#### What is it?

Reducing new lines within inline CSS style blocks that are generated by Site Studio.

#### What impact will there be?

Websites that use inline CSS styles generated by Site Studio, will now take up less space within the head of the page.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Inline editing pencil not always appearing

#### What is it?

Fixed a bug where in certain situations where the browser was zoomed in and specific widths, the Site Studio inline editing pencil did not always appear.

#### What impact will there be?

The inline editing pencils should now always show.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Translating with TMGMT, translated content sometimes resulting in "null"

#### What is it?

Fixes a bug where an error appeared when attempting to translate content, that had a Layout canvas field and other Drupal fields through TMGMT.
This resulted in some translated content values being returned as "null".

#### What impact will there be?

Translating content using TMGMT in this scenario will no longer return an error and return the translated content.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Saving translated nodes could result in stream wrapper exceptions in Drupal logs

#### What is it?

Fixes a bug where the `translated://` stream wrapper and other hidden stream wrappers were being incorrectly passed into a Twig extensions function.

#### What impact will there be?

When saving a translated node you should no longer see these errors and it has been handled correctly.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Sync import batch to consume less memory

#### What is it?

The batch process has been reworked to hold less in memory

#### What impact will there be?

Large sync package will use less memory when imported via the UI and therefore will be less prone to out of memory errors

#### What actions do I need to take?

You might be able to decrease the memory limit needed to run a sync import via the UI if you had to increase it to overcome this issue

#### Are there any risks I should be aware of?

None.

---

## 6.4.0

### Provide an override for rebuild batch limit

#### What is it?

By default the rebuild process will handle 10 items at a time to reduce the memory required to run this operation. This feature exposes a method for increasing that number via Drupal settings.

#### What impact will there be?

Where more memory is available the `rebuild_max_entity` can be set to a number greater than 10 to process the rebuild faster.

#### What actions do I need to take?

Add a settings value such as:

`$settings['rebuild_max_entity'] = 20;`

#### Are there any risks I should be aware of?

Increasing the `rebuild_max_entity` value will require more memory to process each rebuild batch.

### Style builder - field tokenization

#### What is it?

The ability to expose style properties as component form options.

#### What impact will there be?

The following style properties are no longer flagged and can now be tokenized:

- `Text shadow > Horizontal`
- `Text shadow > Vertical`
- `Text shadow > Blur`
- `Text shadow > Color`
- `Position > Position`
- `Display > Display`
- `Box shadow > Horizontal`
- `Box shadow > Vertical`
- `Box shadow > Blur`
- `Box shadow > Spread`
- `Box shadow > Color`
- `Transition > Duration`
- `Transition > Delay`

#### What actions do I need to take?

Create component fields that are connected to relevant style properties in your layout canvas elements, which will enable configurable styles in your component forms.

#### Are there any risks I should be aware of?

`text-shadow` and `box-shadow` are both shorthand properties and support multiple values.

This means values for all fields should be entered, to ensure styles are built correctly. You can do this in a number of ways:

1. Make all relevant component fields required.
2. Use component fields that provide set options that can't be cleared (eg `Range` and `Select`).
3. Hardcode values for style properties that have a fixed value and don't need to be configurable in your component form.

### Button element - layout canvas child support

#### What is it?

You can now add child elements to the `Button` element.

#### What impact will there be?

It will enable more advanced button styles, such as:

- Animated menu buttons
- Buttons that look like on/off toggle switches

#### What actions do I need to take?

To update existing link elements, you will need to do a rebuild.

Both new and existing `Button` elements on the layout canvas will be collapsed by default.

#### Are there any risks I should be aware of?

None.

### Component content with Dropzones

#### What is it?

Adds the ability to save Component content with a Dropzone.

#### What impact will there be?

This will expose the Layout canvas when editing a Component content.
Editors will not be able to edit / add to the top level Layout canvas only the Dropzone.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### Component content can be created directly

#### What is it?

Component content can now be created directly through the Site Studio menu `Site Studio > Components > Component content > Add component content` rather than having to save a component from a node.

#### What impact will there be?

Component content doesn't have to be created from a node and can now be created separately just like you would with other content entities.
The ability to create a component content from a node is still available

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Two Google Map API keys can be defined to follow Google's best practices

#### What is it?

Two Google Map API keys can now be defined through the Site studio UI. One for the Google Maps Javascript API which is used for rendering maps and previews, another for the Geocoding API which is used to fetch the coordinates.

#### What impact will there be?

This allows for restrictions to be applied to API keys as explained by in Google's best practice document: https://developers.google.com/maps/api-key-best-practices#best_practice_list
If a second API key for the Geocoding is not defined, it will continue to use the first one.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Entity browser element can be used used for content entities

#### What is it?

The Entity browser element now has the ability for more entity types to be used with the element.
This allows more flexibility for site builders to create their own Entity browsers for other entities, and for contributed modules, such as content browser to be used with the Entity browser element.

Note that when using the contributed module content browser the browser modal uses the active front-end theme for styling.

#### What impact will there be?

The ability for the content or other entities to be selected within the Entity browser element.

---

## 6.3.5

### Bugfix: Unable to upload icon and font library files on Drupal 9

#### What is it?

Fixes a bug where attempting to upload an icon or font library file through the UI resulted in the error: `Error occurred while uploading the file.` and the file not being saved.

#### What impact will there be?

Icon and font library files can now be uploaded through the UI on Drupal 9 sites without error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Ability to execute javascript into the link element url

#### What is it?

Fixes a bug where site editors could inject javascript and potentially other harmful markup, into the link to page field on the link element and link to page component form field.

#### What impact will there be?

A user without the `Bypass XSS validation in element forms` permission will not be able to inject Javascript or HTML into the link to page field on a link element.
If the value is not a valid link it will go through the XSS filter.

Where a link to page field on a component has been used and the value input is not a valid link the value will not render out.

Valid links values include: Using the autocomplete (node::1), /my-page, external links and mailto:.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

Any value that is not a valid link and been input into a component link field will no longer render out that value in the href.

### Feature: Save template in the database

#### What is it?

Adds the ability to switch a template storage from the file system to the key value storage

#### What impact will there be?

If enabled Site Studio will be using the database more often as it will save all config templates in the database

#### What actions do I need to take?

To enable this feature you need to change the alias of the `cohesion.template_storage` services to point to `cohesion.template_storage.key_value`

You can do so by providing a site specific services.yml and provide the path to it in your settings.php

Ex:

`env.services.yml`

> services:
> cohesion.template_storage:

     alias: cohesion.template_storage.key_value

`settings.php`

`$settings['container_yamls'][] = '/path/to/env.services.yml'`

#### Are there any risks I should be aware of?

Database utilisation will increase especially during rebuild and sync operations, be aware that this will also increase the size of your database.

### Bugfix: IMCE & Media library not loading on sub-directory multi-site setups

#### What is it?

Fixes a bug when using IMCE and Media library integration with Site studio used relative URLS, where in a sub-directory multi-site setup, the URL returned was incorrect.

#### What impact will there be?

IMCE and Media library will now work for multi-sites that are configured in a sub-directory setup.
For example: www.domain.com/site1 www.domain.com/site2

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Cache context on menu items preventing inline style from rendering

#### What is it?

Fixes a bug where adding cache context to link inside a menu would prevent inline style on components living alonside the menu to render

#### What impact will there be?

Adding cache context should not impact the rendering of styles

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

---

## 6.3.4

### Bugfix: Views and image styles can't be excluded from full sync export

#### What is it?

Fixes a bug where Views and Image styles were not available to be excluded from the full sync package export.

#### What impact will there be?

Views and Image styles can be excluded from the full sync export, through the export settings page: `admin/cohesion/sync/export_settings`.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Styles only on a pseudo elements do not apply

#### What is it?

When adding a style with only a pseudo element the styles would not apply.

#### What impact will there be?

None.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Bugfix: Google Analytics events not firing and resulting in a JS error

#### What is it?

Fixes a bug where the Google Analytics events integration only supported the classic analytics and didn't support universal analytics.

#### What impact will there be?

Both classic and universal analytics are now supported by the element analytics tab.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Accordions in UI when clicking open/closing multiple times when clicked once

#### What is it?

Fixes a bug where in some cases clicking an accordion in the Site studio UI open/closes multiple times before applying the correct state.

#### What impact will there be?

None

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

---

## 6.3.3

### Bugfix: Repeater fields not updating in Component content instances

#### What is it?

Fixes a bug when using Repeater fields in a Component content the content was not updating.

#### What impact will there be?

None

#### What actions do I need to take?

A Site studio import should be run after upgrading.

#### Are there any risks I should be aware of?

None.

### Bugfix: Component gets stuck in "loading" state when created from the layout canvas

#### What is it?

Fixed a bug where components created from a layout canvas would fail to work when added to another layout canvas before their component form had been configured.

#### What impact will there be?

None

#### What actions do I need to take?

A Site studio import should be run after upgrading.

#### Are there any risks I should be aware of?

None.

### Bugfix: Conditional Fields not working on Style Guide Forms

#### What is it?

Fixed a bug where Conditional Fields were always set to hidden on Style Guide Forms.

#### What impact will there be?

You can now use Conditional Fields on Style Guide Forms and they will show/hide correctly.

#### What actions do I need to take?

When implementing Conditional Fields on Style Guide Forms the syntax should be the same as on Components. E.g. use `[field.select]` and not `[style-guide:style-guide-name:select]`

#### Are there any risks I should be aware of?

None.

### Bugfix: Setting a custom sync directory in settings.php was ignored

#### What is it?

Fixed a bug where configuring the sync directory in the settings.php was using `$config_directories` which is now deprecated in Drupal 8.8 and above.
This has now been updated inline with Drupal core.

#### What impact will there be?

You can now set a custom sync directory by setting `$settings['site_studio_sync']` in your settings.php.
For example `$settings["site_studio_sync"] = "../config"`

#### What actions do I need to take?

We recommend that you update your site to use `$settings["site_studio_sync"]` rather than `$config_directories`, if you don't update the fallback of `sites/default/files/sync` will be used.

#### Are there any risks I should be aware of?

None.

---

## 6.3.2

### Bugfix: Tokenised Drupal fields in view are displaying incorrect translation

#### What is it?

Fixed a bug where using tokens in content template and rendered in a view would retrun the default language instead of the current content language.

#### What impact will there be?

If a token is used in a content template and this content dipplayed in a view the correct translation will be displayed

#### What actions do I need to take?

Clear the cache of the affected templates for the tokens to be processed. You can do it by saving each individual template or a global `drush cr`

#### Are there any risks I should be aware of?

None.

### Bugfix: Translating multiple of the same Component on a node using TMGMT resulted in incorrect translations

#### What is it?

Fixed a bug where translating a node using TMGMT that had multiple of the same Component resulted in incorrect translations being applied to components.

#### What impact will there be?

Translating a node with multiple of the same component using TMGMT will now apply the correct translation to the component.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Toggle values on a component within a template return a number even if the toggle is set to string

#### What is it?

Fixes a bug whereby a toggle value added to a template is not rendered correctly in a child node.

#### What impact will there be?

Toggle values will now be rendered correctly in all use cases.

#### What action do i need to take?

None.

#### Are there any risks I should be aware of?

None.

---

## 6.3.1

### Bugfix: Inputting an incorrect token in a component causing twig errors

#### What is it?

Fixed a bug where inputting an incorrect token within a nested component with a dropzone caused twig errors.

#### What impact will there be?

If a token is input incorrectly it will be output as a string rather than displaying twig errors and stopping the page from rendering.

#### What actions do I need to take?

A Site studio import and rebuild should be run after upgrading.

#### Are there any risks I should be aware of?

None.

---

## 6.3.0

### Update to content template machine name prefix

#### What is it?

The content template machine name prefix has been shorten to `ctn_tpl`, so when creating additional full content templates the site builder has more flexibility.
Previously if your content bundle had a long name this could produce an error when attempting to create multiple full templates as the maximum characters for a machine name is 32 as enforced by Drupal core.

#### What impact will there be?

The ability to create content templates for bundles with long names and no get any errors about the length of the machine name.
Existing templates will not be affected and continue to work as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Feature: Component field repeater

#### What is it?

Components can now include repeating sets of fields within the component form. A new form element called a Field repeater allows the form builder to wrap one or multiple fields within a repeatable group. The field repeater includes a button which allows the content author to add another group of fields as an array. The field repeater can be limited to maximum number of repeats.

The field repeater form element is used with another new component layout element called a Pattern repeater. This allows a section of layout on the layout canvas to be repeated. When linked together, the field repeater will repeat the layout within the pattern repeater. The result is a component that can include a repeating set of fields that automatically repeats a corresponding layout.

There are many use cases for this functionality. Some examples include:

- A gallery component where the content author can add multiple images to a single component.
- A map component where the content author can add multiple map pins to a single component.
- A slider where the content author can add multiple slides to a single component.
- A list where the content author can add multiple list items to a single component.
- A table where the content author can add multiple rows to the table..

#### What impact will there be?

None.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

None.

### TMGMT compatible

#### What is it?

You can now use Site Studio with TMGMT and translate content for each field of your components. By default all text based field in your components will be translatable, you can exclude fields from being translatable in a new section on the component form page named `Translation settings`. Suggestions have also been implemented, if you add a component content or have a component with a entity reference field or a entity browser field, TMGMT will suggest to translate the referenced entity if relevant

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### File Sync error message updated

#### What is it?

When importing a file through Site Studio Sync where the UUID already exists, but the URI doesn't match an error message is displayed. The error message now displays more information to help debug which file is causing the error.

#### What impact will there be?

The error message will display some more useful information to help debug the file causing the error to display.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: WYSIWYG element textarea width

#### What is it?

Fixes a bug where sites on Drupal 8.9.3 and 9.0.3 the WYSIWYG elements textarea width was incorrect.

#### What impact will there be?

The WYSIWYG elements textarea is now the correct width.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Using inline editing to edit components loaded the incorrect translation

#### What is it?

Fixes a bug where on a multilingual site, using the inline edit functionality to edit components on the frontend of a website was loading the incorrect translation.

#### What impact will there be?

Using the inline edit functionality now loads the correct translation.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Components not always being correctly categorized

#### What is it?

Fixed a bug where importing a sync package containing components would not always categorize them correctly.

#### What impact will there be?

Importing components should now be categorized correctly.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Using Drupal preview to preview a node that not yet been saved produced an error

#### What is it?

Fixes a bug where an unsaved node containing components on a layout canvas, then previewing using Drupal preview caused the below error.

`Symfony\Component\Routing\Exception\InvalidParameterException: Parameter "revision_id" for route "cohesion_elements.components.settings_tray_iframe" must match "[^/]++" ("" given) to generate a corresponding URL. in Drupal\Core\Routing\UrlGenerator->doGenerate()`

#### What impact will there be?

When previewing an unsaved node containing a layout canvas, you should no longer get this error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Custom block usage plugin

#### What is it?

If a component is used on a custom block layout canvas field, the component will now show as in-use by the custom block.

#### What impact will there be?

Allows for components in-use on a custom block to be tracked.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Error when cloning a node using quick clone module

#### What is it?

Fixes an issue where attempting to clone a node with a Layout canvas field and component containing a file produced an error.

#### What impact will there be?

You can now use the quick clone module to clone nodes with components containing files without error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Support for focal point module

#### What is it?

Fixes an issue where the focal point module image widget did not load as expected on Drupal image fields.

#### What impact will there be?

You can now use the focal image widget on Drupal fields.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

---

## 6.2.0

### Bugfix: Using a Range slider on a component the default value unit is not applied correctly

#### What is it?

Fixes a bug when adding a Component to the Layout canvas which has a Range slider with a default value, the values were not being applied correctly with the correct unit.

#### What impact will there be?

None.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Nodes with parentheses in titles do not work in entity reference elements

#### What is it?

Fixes a bug where linking an entity with parentheses in the title to an entity reference element did not render in the frontend.

#### What impact will there be?

You can now link an entity that has parentheses in its title in an entity reference element and it will render.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Updated IMCE module dependency to version 2.2 or higher.

#### What is it?

We changed the IMCE module version dependency from `^1.x` to `^2.2` to solve a Drupal 9 incompatibility issues within IMCE.

#### What impact will there be?

Sites running Drupal 9 and Site Studio will use the latest version of IMCE which is fully Drupal 9 compatible.

#### What actions do I need to take?

While Site Studio will continue to work with the `1.x` branch of IMCE, it's worth upgrading existing sites.

`composer update drupal/imce:^2.2`

From your `docroot` you can run the following to upgrade existing sites. New installations will install `^2.2` of IMCE automatically as part of the Site Studio dependencies.

#### Are there any risks I should be aware of?

None.

### Improved RTL language support - Slider and style builder

#### What is it?

The `Slider` interactive element now works correctly when a RTL language is active. If enabled, the `Previous` and `Next` button positions will be reversed.

Logical CSS properties make directional styles easier to maintain, such as right-to-left (RTL) content.

To support this, some style builder properties now have extra options or values:

- **Text-align:** Two new values (`Start` and `End`)
- **Padding:** Four new options that can be enabled through the `Padding` ellipsis menu (`Padding block-start`, `Padding block-end`, `Padding inline-start`, `Padding inline-end`)
- **Margin:** Four new options that can be enabled through the `Margin` ellipsis menu (`Margin block-start`, `Margin block-end`, `Margin inline-start`, `Margin inline-end`)
- **Border width:** Four new options that can be enabled through the `Border width` ellipsis menu (`Block-start`, `Block-end`, `Inline-start`, `Inline-end`)
- **Border style:** Four new options that can be enabled through the `Border style` ellipsis menu (`Block-start`, `Block-end`, `Inline-start`, `Inline-end`)
- **Border color:** Four new options that can be enabled through the `Border color` ellipsis menu (`Block-start`, `Block-end`, `Inline-start`, `Inline-end`)

#### What impact will there be?

**Slider:** When an RTL language is active it will show the first slide as expected, instead of an empty slide.

**Style builder:** None, as existing styles will not be effected.

#### What actions do I need to take?

**Slider:** If you are using icons for your `Previous` and `Next` buttons, you will need to add styles to mirror these when RTL is active. You can do this by using a `[dir="rtl"]` prefix in your style.

**Style builder:** To access the new options for the `Padding`, `Margin` and `Border` properties, you will need to enable them through the respective properties' ellipsis menu on the right-hand side of the property group in the style builder.

#### Are there any risks I should be aware of?

The new style builder options and values are not supported in Internet Explorer 11 and below.

### Drupal core config entities can now be included in Site Studio Sync packages

#### What is it?

The following Drupal core config entities can now be included in your Site Studio Sync package. On the Sync package editor page, there is a new set of tabs in the package requirements accordion called “Site Studio entities” and “Drupal entities”.

- Content types including fields, field form display and field display.
- Media types including fields, field form display and field display.
- Taxonomy vocabularies including fields, field form display and field display.
- Menu definitions.
- Module dependencies.

Drupal core entities have been separated in the UI as they do not have any dependencies calculated for Site Studio entities. For example, if you export a content template for a content type, the Drupal content type is not included in the export automatically. This is a change to the way Drupal views will be included with Site Studio view templates. Views now need to be selected separately as they are now no longer a dependency of the view template.

When exporting a Drupal core entity using Site Studio sync, the associated fields with that entity can also be exported. For example, exporting a content type will also export all the fields attached to that entity. If the content type has an entity reference field referencing either media types or taxonomy vocabularies, the allowed types set on the field can also be exported.

This new functionality allows for "features" to be created as a Sync packages that contain both Drupal core and Site Studio configuration entities.

#### What impact will there be?

Site Studio sync packages can now include both Drupal and Site Studio configuration entities.

### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Themes that are not Site Studio enabled can now be set to generate Site Studio templates

#### What is it?

If you have a theme not set to use Site Studio (ie: the theme does not specify `cohesion:true` in its info.yml) you can now generate templates for it.

This is useful for AMP themes where there is the requirement to render the Layout Canvas field and supporting components.

#### What impact will there be?

You can now render Site Studio templates and layout canvas with any theme, making it compatible with AMP themes.

### What actions do I need to take?

If you want a theme not using Site Studio like an AMP theme to generate and render the templates only, you need to enable it by going to the appearance page of the theme and check `Generate templates only` under the `Site Studio` section and perfom a rebuild to generate the templates for this theme.

#### Are there any risks I should be aware of?

You should be aware that this functionality will only build templates for Site Studio entities and Layout Canvas field, no styles will be generated and rendered. You should define your own stylesheet for these themes

### Conditional Component Form Fields

#### What is it?

Form elements on Site Studio Components can now be conditionally displayed based on user specified logic. The new "Show field if" field in the sidebar editor allows the user to enter custom conditional logic.

Conditional logic is evaluated in real time as users fill in the form which allows for fields to be shown or hidden based on the values of other fields within the form.

Conditional fields that are not shown by default will only have any default values assigned to them set when they are show (because the condition required to show them is met). Further to this, conditional fields can be set to be shown by default based on default values of other fields in the form.

In addition, to further leverage this new functionality, a new Hidden Input field has been added that allows users to enter static values that are only set when the field is conditionally shown.

#### What impact will there be?

Component Form Fields now have an addition setting in the sidebar editor that is used to set the conditional logic described above.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None

### Site Studio UI accessibility improvements

#### What is it?

Keyboard accessibility has been significantly improved in several areas:

- When a layout canvas/form builder item that can have children is focused, the sidebar browser can be opened with keypress of `enter` or `space` to add child elements.
- When a layout canvas/form builder item that cannot have children is focused, the elements settings can be opened with keypress of `enter` or `space`.
- When a layout canvas/form builder item toolbar is focused, the elements settings can be opened with keypress of `enter` or `space`.
- On focus, the color picker can now be opened with keypress of `enter` or `space`.
- The style tree is now fully navigable with `tab` key
- Style tree levels can be expanded, collapsed and selected with `enter` or `space` key
- When a blade menu is open the `escape` key will close the current blade menu.
- When a blade menu is launched, the back button is focused. This also happens when navigating between blade levels so that focus stays within the blade.
- When the sidebar editor is launched, the close button is focused.
- All focusable elements have a consistent yellow outline applied when focused.

#### What impact will there be?

Visual cue improvements when using keyboard to navigate through the Site Studio UI.

### Bugfix: Removing an image from a style guide results in broken image in component

#### What is it?

When creating components with `Picture` and `Image`elements, but without specifying images, the HTML for these elements were still being generated on the front end.

If the `Title` and `Alt text` element settings were completed, this resulted in a broken image being displayed on the front end, alongside the image alt text.

If either `Title` or `Alt text` were left blank, there would be no visual indication of the `<picture>` or `<img>` HTML being generated.

#### What impact will there be?

`Picture` and `Image` elements will only generate HTML if an image is specified and therefore a `src` generated.

#### What actions do I need to take?

A rebuild will update the relevant twig templates and apply these conditions.

#### Are there any risks I should be aware of?

Whilst images that have no `src` specified usually have no size on the page, there is a chance that base styles that you've created for your site could generate whitespace for these empty elements.

Now that the elements will no longer be generated, this could cause some reflow of content.

### Bugfix: Using integers as values for select options in custom element throwing an error

#### What is it?

Fixed a bug where select option values for a custom element could only be a string.

#### What impact will there be?

When creating a select list in a custom element you can now use both strings or numbers as values.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Bugfix: Component templates not always being generated

#### What is is it?

When saving as a component from the layout canvas, but not ever editing the component through the UI the components template wasn't generating as expected on rebuild.
This resulted in error message like this appearing on the site: `Unable to find a template for suggestion matching component__cohesion_cpt_component_machine_name`.

#### What impact will there be?

Saving a component in this way should now generate the component templates as expected.

#### Are there any risks I should be aware of?

None.

#### What actions do I need to take?

Components that have been previously created this way and never edited through the UI, need to be saved through the UI.

### Bugfix: Error when saving a helper that contains files and has a long name

#### What is is it?

Fixed an issue where saving a helper with a long name from a layout canvas that contained files, caused a MYSQL error when trying to insert into the file usage table.
If a helper already existed on the site, this also caused an error when running a site rebuild.

#### What impact will there be?

Customers should no longer see the error if they were previously experiencing it.
If a helper is saved from the layout canvas with a long name, the machine name is created with a max length of 32 characters.

#### Are there any risks I should be aware of?

None.

#### What actions do I need to take?

None.

---

## 6.1.3

### Bugfix: Style tree child or pseudo selector CSS values not saving after initial save and enabling extra breakpoints

#### What is is it?

There was an issue when adding additional Breakpoints to an existing style the values were not being saved correctly.

#### What impact will there be?

Users should now be able to add additional Breakpoints and the values saved correctly.

#### Are there any risks I should be aware of?

None.

#### What actions do I need to take?

`drush cohesion:import`

### Bugfix: Incorrectly reporting import error after upgrading to 6.1.x

#### What is is it?

After upgrading to `6.1.2` some customers reported that they were seeing the following error when importing Sync packages containing files such as icon font files:

> An entity with theh UUID already exists but the URI does not match.

This error was incorrectly reported, blocking the import.

#### What impact will there be?

Customers will now be able to import packages that were previously throwing this error.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Error: Call to a member function toUrl() on null

#### What is it?

Fixes a bug where a URL tries to be generated in the in-use modal when an entity might not be available, stopping the in-use modal from opening.

#### What impact will there be?

You should no longer see the error "Call to a member function toUrl() on null" in your logs and the in-use modal should open as expected.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

---

## 6.1.2

### Bugfix: Twig error when rendering a component with a link field

#### What is is it?

Fixed a bug where using a value that would return a string (usually a token) the pathRenderer twig function would return an error

#### What impact will there be?

The error message should no longer appear and you will be able to use any value in a component link field

#### Are there any risks I should be aware of?

None.

#### What actions do I need to take?

None.

---

## 6.1.1

### Bugfix: Server error appearing when creating an existing select of Drupal blocks

#### What is is it?

Fixes a bug when creating an existing select of Drupal blocks in the component form builder a server error message appeared.

#### What impact will there be?

The error message should no longer appear and you will be able to create an existing select of Drupal blocks.

#### Are there any risks I should be aware of?

None.

#### What actions do I need to take?

None.

### Themes with Site Studio enabled and not set as default will no longer generate assets automatically

#### What is is it?

When enabling a theme which is using Site Studio (ie: it has `cohesion: true` in its `info.yml` file or the theme it inherits from has), assets such as styles and templates for this theme will not be generated unless you check the checkbox labeled `Build Site Studio assets` in the appearance settings of the theme

#### What impact will there be?

If you have any enabled themes using Site Studio that are not set as default, it will cease to generate the assets for these themes unless you explicitly check the `Build Site Studio assets` in the appearance settings of the theme under the `Site Studio` section

#### What actions do I need to take?

If you want Site Studio to generate assets such as styles and templates for any non default theme you might already have enabled, you need to check the the checkbox labeled `Build Site Studio assets` in the appearance settings of the theme under the `Site Studio` section

#### Are there any risks I should be aware of?

Your site might not render correctly unless you do what is describe in the `What actions do I need to take?` section above for any non default theme you might have enabled that is using Site Studio

### Bugfix: When tokenising a link to anything other than a link field in a component, the link is the internal reference

#### What is is it?

When attaching a component link field token to a field that is not a link field (ex: to the analytics data layer or a data attribute in the markup tab) the rendered output would be the internal reference (ex: node::1) rather then the link to the page

#### What impact will there be?

Tokenising a link field to any type of field in a component will now render the link rather than the internal reference

### Bugfix: Warning when doing a rebuild when updating to 6.1

#### What is is it?

A warning `First parameter must either be an object or the name of an existing class _0027EntityUpdate.php:50` is thrown on certain cases when upgrading a site to 6.1 on the \_0027EntityUpdate

#### What impact will there be?

The warning should not be thrown anymore

#### What actions do I need to take?

None.

### Site Studio now requires Drupal core 8.8.0

#### What is it?

Site Studio now requires Drupal core 8.8.0 as a minimum, due to some Drupal 8.8 specific functions being used.

#### What impact will there be?

This version of Site Studio and future versions can only be installed on Drupal core 8.8.0 and above.

#### What actions do I need to take?

If your website is running on an older version of Drupal core you will need to upgrade to 8.8.0 before upgrading to this version of Site Studio.

#### Are there any risks I should be aware of?

None.

### Bugfix: When the lazy-loading setting is set on the picture element, images are not loaded

#### What is is it?

Resolved issue when using a Picture element and enabling Lazy loading the image was not loading.

#### What impact will there be?

Users that reported this issue will see it has been resolved.

#### What actions do I need to take?

`drush cohesion:import`

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Error whilst rebuilding custom style after upgrading to 6.1.0

#### What is it?

Fixes a regression introduced into 6.1.0 where a user could see an error like `Error: Unable to build styles. StatusCodeError: 400` when saving or rebuilding styles. This was blocking some Site Studio rebuilds from completing.

#### What impact will there be?

Users will be able to save styles and rebuild successfully without this error.

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

---

## 6.1.0

### Navigating between Style Guide Manager tabs causes them to move around

#### What is it?

When selecting a different sibling `Tab item` in a Style Guide Manager form, it causes the selected tab to move to the first position.

This should only happen when `Responsive mode` is enabled on the parent `Tab container` (on by default).

#### What impact will there be?

Tab items will stay in place if responsive mode is disabled.

**Note: Tabs will not stack if responsive mode is disabled.**

### Component machine name support

#### What is it?

When adding form fields to Components you will now be required to specify a machine name for them. When entering a title/label for your field, the machine name will be auto-generated but can be edited manually if you prefer.

This machine name will then be used to link the field to elements in your component.

The syntax for tokenizing fields has changed slightly. Previously your input would be linked by entering `[Field 1]`, `[Field 2]` etc. The new syntax is `[field.` followed by the machine name you specify e.g. `[field.my-machine-name]`.

As a result of this change it is now possible to add the token to fields on the layout canvas before you create the fields on the component form. Previously you had to add fields first.

#### What impact will there be?

Workflow for the creation of components has changed. You must set/use a unique machine name for every field you add. Existing components will be updated with an auto-generated machine name based on the field title.

As an example a field with the label "Paragraph padding top" would be assigned a machine name of `paragraph-padding-top`.

#### What actions do I need to take?

**A rebuild is required to ensure existing form fields are updated.**

#### Are there any risks I should be aware of?

None.

### Prefixes in the Style tree now support all selector types

#### What is it?

Adds the ability to use all CSS selectors types in the style tree when adding a Prefix selector.

This includes Class, ID and Attribute CSS selectors.

#### What impact will there be?

Any prefix CSS Class selectors already added in the Style tree without the CSS class selector (`.`) will automatically be prefixed with a `.`

#### What actions do I need to take?

`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

None.

### Drupal core media library compatibility

#### What is it?

Adds the ability to use the core media library to browse for media entities on the image, picture and entity browser elements.

A new option for media library is available in the image browser system settings and within the entity browser element and field settings.

#### What impact will there be?

You can now use the core media library within supported Site Studio elements.

#### What actions do I need to take?

The media library module will need to be installed to use within Site Studio.

#### Are there any risks I should be aware of?

None.

### Site Studio Import batch failing when used inside hook_update

#### What is it?

Fixes a bug where `cohesion_base_styles_process_batch` $context param was typed as array which prevented using Object extending ArrayObject, more specifically DrushBatchContext

#### What impact will there be?

You can now use the Site Studio import batch process inside an hook_update

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None

### Blocks in the block element select list are now alphabetical

#### What is it?

The blocks within the block element select list are now alphabetical based on the block label.

#### What impact will there be?

The blocks will now be ordered alphabetically and easier to find in the select list.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None

### Site Studio "Component content" entity now implements a view mode

#### What is it?

Previously, the Site Studio component content entity did not implement a Drupal core view mode. This caused some issues with modules attempting to render this entity programmatically.

This issue has been resolved by implementing a default view mode for this entity.

#### What impact will there be?

Third party modules that attempt to render the Site Studio component content entity programmatically will be able to do so via its default view mode.
This includes Acquia Lift via Acquia Content Hub 2+

The display settings for this entity have not been enabled, so it will not be possible to access the display settings or add additional view modes for this entity. This fix is hidden from the point of view of the site administrator.

#### What actions do I need to take?

Performing a `drush updb` as part of the standard upgrade will deploy this fix.

The fix ID `8800` and will appear as "Add Site Studio view mode to Component Content entity type"

#### Are there any risks I should be aware of?

None

### Tokenizing Opacity in Styles result in an invalid value

#### What is it?

When tokenizing Opacity on Base, Custom or Element styles it would return an incorrect value.

#### What impact will there be?

You can now tokenie Opacity in Base, Custom or Element styles

#### What actions do I need to take?

If you have already tokenized Opacity on a Base, Custom or Element style you will need to run `drush cohesion:rebuild` or resave the style.

#### Are there any risks I should be aware of?

No.

### Entity browser Element and Component now includes the ability to browser with a Typeahead

#### What is it?

Adds the ability when using the Entity browser element or Entity browser component field to browse Entities using a Typeahead.

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

No.

### Component/SGM forms - breakpoint icon support

#### What is it?

When adding breakpoint-specific fields to component or SGM forms you can now display breakpoint icons in the form, on field groups or tabs.

#### What impact will there be?

- The `Field group` and `Tab item` form elements have a new setting called `Breakpoint icon`, a select field that provides options for multiple breakpoint options. The default setting is `None`.

- The `Tab container` form element has a new setting called `Responsive mode`, a toggle which when enabled causes tabs to collapse into a dropdown menu with an ellipsis button (below a screen width of `768px`). The default setting is `ON`.

#### What actions do I need to take?

When enabling breakpoint icons on tab items:

- Tab text is visually hidden. Make sure it is still meaningful for screen reader accessibility.
- It is recommended you disable responsive mode on the tab container when four or less breakpoint tabs are defined, as these tabs are smaller and don't need to stack.

**Note: A rebuild is required to ensure existing form fields are updated.**

#### Are there any risks I should be aware of?

Existing tab items will not be responsive until a rebuild is completed.

### Stream wrapper cohesion:// deprecated

#### What is it?

We have deprecated the use of the cohesion:// stream wrapper in all Site Studio modules

#### What impact will there be?

There will be no part of Site Studio using the cohesion:// stream wrapper and it will now use the public:// one, storing everything in public://cohesion
There is no impact on existing site or sync package from previous version on Site Studio

#### What actions do I need to take?

`drush updb`
`drush cohesion:rebuild`

#### Are there any risks I should be aware of?

If you were using the cohesion:// stream wrapper in a custom module you should plan to use public://cohesion instead as it will be removed in future release

### Component content throwing an error when translating

#### What is it?

Fixes a bug when saving a new translation of a component content the page was returning an error

#### What impact will there be?

You can now use component content with translations

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

No

### Blocks in the block element can now be filtered by theme

#### What is it?

A new select option is now available in the block element that allows a site builder to filter blocks by theme. The blocks select list is then filtered to show only blocks in the selected theme.
By default, the "All themes" option will be set, and existing blocks will continue to work.

#### What impact will there be?

Blocks can be filtered by theme and easier to find in the block select list.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

No

### Bugfix: Content moderation for Site Studio component in-context editing

#### What is it?

Previously, when editing a component on a content entity via the front end in-context editor, Site Studio was applying changes to the component to the latest revision regardless of which entity was being viewed and edited.

#### What impact will there be?

It's now possible to switch between moderation states on the front end (using moderation toolbar for example) and edit Site Studio component field data for that specific revision.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

### Content templates list, only show view modes that have been enabled in "manage display"

#### What is it?

In the Site Studio content templates list only view modes that have been enabled for that bundle in the "manage display" will appear in the list.

#### What impact will there be?

If a view mode has been enabled for a bundle in the "manage display" a content template can be created for that view mode.

On existing sites where the view mode might not be enabled, but a template was created, the existing template will appear in the list.

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

No

### `Field group` form element - new `Enable padding` toggle setting

#### What is it?

When adding a `Field group` field to a component or SGM form, there is now a new toggle option called `Enable padding`. This option removes the padding and background color of the field group.

Previously, if the `Show heading` toggle was off, the field group heading, padding and background color were all removed together.

With some form designs it's desirable to remove the heading or padding separately.

#### What impact will there be?

Greater flexibility with component and SGM form design.

#### What actions do I need to take?

A rebuild is required so that existing field groups are updated with the new settings, to ensure no visual change to existing forms.

#### Are there any risks I should be aware of?

If a rebuild is not completed, forms that have field groups with `Show heading` disabled will be padded and have a background color, as per a default field group added to a form. The heading will still be disabled.

### `Help text` form field improvements

#### What is it?

You can now customise your component and SGM help text in the following ways:

- set a type (help or warning style)
- set/unset close button
- add formatting to help text to output headings, ordered lists, unordered lists and links.

When enabled, the close button will show in the top-right corner of the help dialog. Clicking this will remove the help text and set a cookie to stop the help text showing when the component or SGM form is opened again.

**Note: the browser cache will need to be cleared to clear the cookie and restore the help text.**

Help text formatting supports all [CommonMark](https://commonmark.org/) markdown syntax.

#### What impact will there be?

Greater flexibility with help text on component and style guide forms.

#### What actions do I need to take?

None - existing help text will only need to be updated if the new options are needed.

#### Are there any risks I should be aware of?

No

### Disabled styles no longer appear as a blank option in the Custom styles select

#### What is it?

Fixes an issue when disabling a Custom style it would appear blank in the Custom select where it had been used.

#### What impact will there be?

If a style has been selected in the Custom styles list and is subsequently then disabled, it will now show _"Selected style is disabled."_

#### What actions do I need to take?

`drush cohesion:import`

#### Are there any risks I should be aware of?

No

### Menu button element - button text

#### What is it?

A new setting on the `Menu button` element that allows button text to be specified.

This generates a child `<span>` element for the button with a class of `coh-menu-button-link-text`. The span has been added around the text to provide flexibility with CSS styling.

#### What impact will there be?

The form interface for the `Menu button` element will have an extra section called `Button text` with a text input.

#### What actions do I need to take?

For existing `Menu button` elements that have been added to a menu template, the new setting will need to be enabled in `Properties > Settings > Button text`.

New elements added will have the setting in the form by default.

#### Are there any risks I should be aware of?

No, as a new option existing settings will be unaffected.

### Site Studio no longer supports Drush 8

#### What is is?

To support the upcoming release of Drupal 9, Site Studio has dropped support from Drush version 8.

#### What impact will there be?

If you use Drush 8 and Site Studio together, the Site Studio drush commands will no longer work.

#### What actions do I need to take?

Upgrade to Drush version 9 or higher wherever you use Site Studio Drush commands including `cohesion:import` `cohesion:rebuild` and `sync:import`

#### Are there any risks I should be aware of?

Failing to upgrade could result in your deployments or CI failing.

---

## 6.0.3

### Fix warnings on style guide manager preview

#### What is is it?

Resolved an issue where PHP warnings were thrown when changing and previewing style guide manager values

#### What impact will there be?

Removes all warnings when previewing style guide manager values

#### What actions do I need to take?

None

#### Are there any risks I should be aware of?

None.

### Improve caching of endpoint when using a component with existing selects

#### What is is it?

Resolved an issue where multiple requests to the `/select-options` endpoint were not correctly cached when creating content entities. This led to the endpoint being hit an excessive number of times.

#### What impact will there be?

In-browser performance and server load will be improved when placing and editing components on content entities.

#### What actions do I need to take?

A Site Studio import via the UI or `drush cohesion:import` is required on existing websites.

#### Are there any risks I should be aware of?

None.

---

## 6.0.2

### Improve Dropzone UI on layout canvas to provide more room and a simpler appearance.

#### What is is it?

Resolved issue with drop-zones on layout canvas which was preventing them displaying as columns. This fix also includes improvements to the drop-zone layout, making them take up less space so that the layout canvas is easier to use for content editors.

#### What actions do I need to take?

A Site Studio import via the UI or `drush cohesion:import` is required on existing websites.

#### Are there any risks I should be aware of?

None.

### Generic styles not showing in the WYSIWYG

#### What is is it?

Fixes an issue when using Generic custom styles in the WYSIWYG not showing the correct label.

#### What actions do I need to take?

Clear Drupal caches

#### Are there any risks I should be aware of?

No.

### Sync package doesn't find file entity

#### What is it?

In some rare cases, a file entity's ID may contain a space at the start. When importing a Sync package containing this file, no changes were detected as the file could not be found.

#### What impact will there be?

If a file entity has a space in the ID, it will remove the space and find the correct file.

#### What actions do I need to take?

None.

#### Are there any risks I should be aware of?

None.

---

## 6.0.1

### Site Studio no longer switches the site into and out of maintenance mode when rebuilding via drush

#### What is it?

In certain circumstances, users were seeing the error "MySQL has gone away" after running `drush cohesion:rebuild`

This was caused by a combination of MySQL configuration and/or a long running CRON or drush command process that running `cohesion:rebuild` command contributed to.

More information: https://www.drupal.org/node/259580

It was decided that developers working with Site Studio should be responsible for bringing the site into and out of maintenance mode if required and that Site Studio should not enforce this.

#### What impact will there be?

When running `drush cohesion:rebuild` note that your site will no longer go into and out of maintenance mode when running this command.

#### What actions do I need to take?

If you require this functionality, you will need to implement it yourself inside your build scripts.

More information: https://www.drupal.org/docs/user_guide/en/extend-maintenance.html

#### Are there any risks I should be aware of?

See above.

### Fixed an issue where icon and font files were not showing

#### What is it?

In certain configurations of Acquia Cloud and Acquia Cloud Site Factory hosting, it was possible that font and icon files were not being referenced on the front end correctly. This resulted in missing font and icons on the front end of the website.

#### What impact will there be?

Users that reported this issue will see it has been resolved.

#### What actions do I need to take?

A Site Studio import and rebuild is required after upgrading to the latest version of the module.

#### Are there any risks I should be aware of?

No

### Initial page rendering performance improvements

#### What is it?

We've improved the time to build Site Studio layout canvas enabled pages for the first paint (before the cache has been built).

#### What impact will there be?

Users making changes to Site Studio powered content entities will see their page rendering more quickly.

#### What actions do I need to take?

No actions are required.

#### Are there any risks I should be aware of?

No

---

## 6.0.0

### Import and Rebuild drush commands are now prefixed with "cohesion" instead of "dx8"

#### What is it?

As part of the rebrand to "Site Studio", the existing drush commands have also been renamed.

`drush dx8:import` becomes `drush cohesion:import`

`drush dx8:rebuild` becomes `drush cohesion:rebuild`

#### What impact will there be?

Note that the `dx8:import` and `dx8:rebuild` commands are still in place, so your existing deployments will continue to work with no changes.

However, you should plan to update your usage of these commands as they will be deprecated and removed in a future version.

#### What actions do I need to take?

Change your drush commands to the new ones asap.

#### Are there any risks I should be aware of?

No

### Component/Style guide builder - tab fields are now responsive

#### Impact

When you add a `Tab container` and at least one child `Tab item` to your component or style guide forms, these will collapse into a dropdown menu with an ellipsis button.

This is now consistent with how Drupal renders tabs responsively.

#### Conditions

- The screen width is less than `768px` when completing a component form.
- A style guide form is being completed in `Appearance settings` and the preview is active (no breakpoint condition).
- A style guide form is being completed in `Appearance settings`, the preview is hidden and screen width is less than `768px`.

#### Features

- The selected tab always appears at the top of the dropdown, next to the ellipsis button that opens the menu.
- To improve keyboard navigation, focus is returned to the ellipsis button when a new tab is selected.

#### Action required

None - this behaviour happens automatically and is not configurable.

#### Risks

There are no backwards compatibility or upgrade requirements.

### Optional background image on component renders invalid css

#### What is it?

Fixed a bug where setting a image field to a background image on a lower breakpoint would render invalid CSS if this image was not populated instead of inheriting for the higher breakpoint.

### Modal element - jQuery animation settings

#### What is it?

Fixes a bug where a scale animation was being applied to a triggered modal that was set to have no animation (default setting).

Now the modal will instantly appear when triggered, if `jQuery animation` is set to `None`.

### Machine name are now locked after saving

#### What is it?

Machine name of Site Studio entities will now be not editable after you save even if they are not in use.

#### What impact will there be?

the `dx8:import` and `dx8:rebuild` commands are still in place, so your existing deployments will continue to work with no changes. However, you should plan to update your usages of these commands as they will be deprecated and removed in a future version.

### Prevent and warn when importing component or style guide with broken linkage to populated content

#### What is it?

If you import a package through the UI that contains a component and/or a style guide with missing fields that previously had content populated against it, you will see a system warning.

You can choose to override, in which case you would eventually lose the content, or keep the existing entity.

On `drush sync:import` (when using the option `--overwrite-all`) if you import a package with missing fields that previously had content populated against it, you will be prevented from importing and be given a summary of affected entities.

You can choose to ignore it by using the `--force` option, which will ignore the validation and overwrite all entities.

#### What impact will there be?

Reduces the opportunity to accidentally overwrite existing fields containing content. This prevents the loss of content on sites using a distributed design system and Site Studio style guide manager.

### Style guide manager real-time preview

#### What is it?

The Style guide manager now includes a real-time preview. Users can browse around the website, moving from page to page to see how their changes will affect the appearance of the site before applying them.

This new feature makes it much quicker and easier to apply styling changes globally to the theme of your website.

If a style guide field cannot show a preview because it is changing something other than a CSS style, an indicator displays next to the form field telling the user.

#### What impact will there be?

Teams using SGM can now see the changes being in realtime. This allows for faster, more accurate customisation of sites across large multi-site estates for non technical users.

### Improvements to API scalability, fault tolerance and error reporting.

#### What is it?

The Site Studio API has been significantly refactored for this release to fix some legacy issues related to deploying and rebuilding sites at scale.

Network fault tolerance is improved, and error reporting back to the client is also now more descriptive and useful.

#### What impact will there be?

Less API errors, better reporting capabilities and faster rebuilding when required

### Bugfix: If no cohesion enabled theme is installed, it returns a js error on component preview

This fixes a javascript error when no theme with cohesion enabled is installed and trying to preview a component.

### Define the temporary:// stream wrapper for cohesion to use

#### What is it?

You can set the stream wrapper of the temporary folder for cohesion to use by setting `$settings['coh_temporary_stream_wrapper'] = 'mytemp://';` in you settings.php file.

#### What impact will there be?

More flexibility for developers and dev ops to control the configuration to meet the needs of their business

### Component/style guide manager fields - tooltip support

#### What is it?

You can now add tooltips to your component and style guide manager fields.

When a field has tooltip text specified, a blue help icon will appear next to the label. Hovering over this icon or the label will display the tooltip.

Placement options `Top`, `Right` and `Bottom` are available.

Note:

- The `Entity browser` and `Entity reference` fields have multiple tooltip options as they have multiple fields.
- The `Google map marker` field has a single tooltip option which is applied to the `Address` field in standard view and the `Latitude, longitude` field when in variable mode.

#### What impact will there be?

Tooltips are available in more places improving UX.

#### What actions do I need to take?

If required these can be added retrospectively

### Component/helper category improvements

#### What is it?

- Fixed a bug where `Category 3` and `Category 5` had the same color. Category 3 now has a different color.
- Added three new categories for a total of `15`
- Improved accessibility of category color selector (visual focus state plus `fieldset` and `legend` HTML)
- Colors now have a tooltip on hover, similar to the color palette
- While most categories have similar colors as before, some have been changed to provide better contrast and a more consistent color story. When used effectively, this can be used to create sub-categories within a specific color group.

#### What impact will there be?

Significant improvements in accessibility. End users will see a different color when first using the updated version.

### Region element has a new option for all themes

#### What is it?

The region element has a new option, "All themes". When selected, all regions in the active themes are shown in the region select list and will render out the region, regardless of what theme is set as default.

#### What impact will there be?

More flexibility in the use of regions in components and templates for sites that use multiple themes and Style Guide Manager.

### Fixed issues around required fields and tokens

#### What is it?

- Fixed a bug where it was possible to circumvent form validation and apply the sidebar editor when in variable Mode.
- Fixed an issue where adding a token to a required field did not count as the field having a value for its validation criteria.

### Additional warnings when deleting a component that is in-use.

#### What is it?

When attempting to delete a component that is in-use, the site builder will now see a list of entities where the component is in-use on the delete confirmation page.

If the component is in-use on component content and the site builder deletes it, the component content entities for the component will also be deleted.

#### What impact will there be?

It will be easier to detect potential data loss issues with package imports.

---

## 5.7.11

### Multiple contexts on single element not applying correctly

Fixed an issue if multiple contexts are set on an element and the pass condition "Only one criteria must pass" was set, the context wasn't showing/hiding the element as expected.

### Styles missing when aggregation turned on

Fixed an issue where in some cases having aggregation turned on would result in some missing styles.

---

## 5.7.10

### Colors in the color palette could not be selected in certain circumstances

Fixed an issue where if you had 2 or more colors only differentiated by their alpha transparency value then clicking any of them in the color picker would always just select the first one.

---

## 5.7.9

### Style guide removed if all values are inheriting from parent theme

If all values of a style guide are set to inherit from the parent (ie: not edited), then the underlying entity will be removed.

Therefore if you do a full export this entity will no longer exists. This prevents overriding a child site style guide values when doing a full export/import.

### View template not rendering correctly when adding component with dropzone

Fixes a bug where adding a component with a dropzone on a view template would break the styles and markup of the view.

### Link element - Open modal type - Trigger ID renders as link text

Fixed an issue where using a link element to trigger a modal was rendering the `Trigger ID` as the link text instead of its `ID` value.

### Reduce the amount of temporary component preview

Improvement has been made to reduce the amount of records saved in the temporary key value storage when previewing a component which was resulting in a large database.

### Component field connected to font weight

Fixes an issue where using a component field with font weight causes incorrect values to be returned.

### Field tokens are not copied when duplicating a parent component

Fixes a bug where duplicating a component with a component inside would not retain its field linkage.

### Style Guide Manager - "0" as value not working

Fixes an issue that prevented using `0` as a value for a style guide field.

---

## 5.7.8

### PHP out of memory on drush dx8:rebuild

Improves memory usage when executing `drush dx8:rebuild` so that less memory is used to run the process.

---

## 5.7.7

### Icon picker not rendering icons

Fixed a bug where uploaded icon libraries would render correctly on the front end but not in the icon picker.

### Style guide values lost

Fixed a bug where some style guide values were lost after saving.

### Custom style ordering

Fixes a bug where ordering custom styles with similar weights were not retained.

### Drupal 8.8.0 compatibility

Fixed a few `dblog` warnings and code style issues related to Drupal `8.8.0`.

---

## 5.7.6

### Nested components not rendering with multiple Cohesion enabled themes enabled

Fixed an edge case where components that were placed inside dropzones that were themselves inside other components were not rendering.

### Improvements to Cohesion sync error reporting

When importing an entity with a UUID / machine name mismatch, the errors provided to the end user were not helpful. This has been fixed and provides enough information for the user to start debugging the issue with their package.

Example:

```
The validation failed with the following message: Custom style with UUID 00000000-0000-0000-0000-0000000000000 already exists but the machine name "coh_existing_machine_name" of the existing entity does not match the machine name "coh_mismatched_machine_name" of the entity being imported.`
```

---

## 5.7.5

### Improved warning message for missing template files.

When a generated component twig template file was missing from the file system, Cohesion was printing "something here" on the front end of the website.

This message was not helpful and has been replaced with a message indicating the missing template suggestion.

### Style guide form - padding missing

Fixed an issue where there was some padding missing from `Group accordion` fields when used in a style guide form.

This only affects the form shown in Drupal `Appearance settings`.

### Existing theme settings not updating when saving a style guide

Fixed an issue where theme settings that are not included in a style guide form (such as logo and favicon) were not being saved when saving theme settings performed a rebuild of Cohesion entities.

### The numerical value of the range slider not always displayed correctly

Fixed an issue where the numerical value of the range slider was not always rendering correctly.

---

## 5.7.4

**Please note, upgrading to this release will require a re-import and rebuild which can be performed with `drush dx8:import` and `drush dx8:rebuild`**

### Bugfix: correctly look up available text formats by account

Fixed an issue where available text formats were being queried by the current user role(s) instead of directly with the user account object.

This meant that if user 1 had no role, they were unable to access any text formats.

### Range slider - thumb positioning

Fixes an issue where the range slider thumb was sometimes positioned incorrectly when loading a page.

### Base and custom styles - pseudo content image

Fixed an issue that prevented rendering of `:before` and `:after` pseudo element images that were specified in base and custom styles.

Content image styles applied directly to elements are unaffected.

You can now also select a `Drupal image style`.

**A rebuild is required to update all existing styles, otherwise re-save base/custom styles on a case-by-case basis.**

### Bugfix: Cohesion layout canvas preview

Fixed an issue where the Cohesion layout canvas field was not rendering correctly on content entity preview pages.

### Component - existing select field

Fixed an issue when tokenizing an existing select field in a component.

This was causing the following validation error when selecting a value on a component instance:

```
Invalid type, expected ["string","number","boolean"]
```

### Fixed an issue where inserting media into the WYSIWYG element could fail to save

Entering an entity via the entity browser and pressing apply without ever focusing into the WYSIWYG could cause the embedded entity data not to be saved and you would end up with an empty WYSIWYG when re-opening.

### Fixed an issue that prevented the order of custom styles changing when they were re-ordered in the UI

This fixes an issue that prevented the re-ordering of the custom styles.

### Fixed an issue with the help text element on style guide forms

Added code to ensure the text in the Help Text element is loaded on the SGM forms.

---

## 5.7.3

### Fix an issue where the master template was not rendering

This fixes an issue where when using style guide manager tokens in master template it would not render the master template.

---

## 5.7.2

### Fixed issue saving master templates with multiple enabled Cohesion themes.

This fixes templates failing in some edge cases and the following `dblog` warnings:

```
Warning: Illegal string offset 'template' in Drupal\cohesion\Plugin\Api\TemplatesApi->send()

Warning: Illegal string offset 'themeName' in Drupal\cohesion\Plugin\Api\TemplatesApi->send()
```

---

## 5.7.1

### Fixed function declaration warning.

Fixed a function declaration that was incompatible with the interface. It was causing this warning:

```
Declaration of Drupal\cohesion\StreamWrapper\CohesionStream::basePath($site_path = NULL) should be compatible with Drupal\Core\StreamWrapper\PublicStream::basePath(?SplString $site_path = NULL)
```

---

## 5.7.0

### Style guide manager

The style guide manager is a new (optional) sub module and will need to be enabled to use it (`Cohesion style guide manager` via the UI or `cohesion_style_guide` via drush).

You can use the style guide manager to create theme-specific overrides for your website's styles and appearance settings.

Theme specific overrides can use theme inheritance. This means a sub-theme will automatically inherit the settings of its parent theme.

Changes made to sub-theme settings will override its parent theme settings. The style guide manager has two main interfaces:

1. Style guide builder - this is an interface for defining theme-specific overrides. The output of the style guide builder is a `Style guide`. This can be accessed at: `/admin/cohesion/style_guides`.

2. Style guides - this is an interface for applying values to your theme-specific overrides. These overrides are theme specific and can be access on the appearance settings form for any Cohesion enabled theme.

The style guide definition entities (1 above) and style guide manager instance entities (2 above) are config entities that work with the Cohesion sync module.

For more information on how to set up and use the style guide manager feature, please refer to the latest Cohesion user guide.

### Bugfix: Video in Modal continues to play

Fixes issues when there is a Video in a Modal and the modal is closed the video continued to play.

Fixed for native HTML5 videos, YouTube and Vimeo.

### Bugfix: Rendering regions of inactive themes

Fixed a bug where regions for inactive themes were being rendered if they had the name machine name as a region in the inactive theme.

For example, adding two `content` regions from different themes would render the `content` region of the active theme twice.

This is now fixed as the system checks if a region belongs to the active theme before rendering it.

### Bugfix: WYSIWYG form fields in custom elements not working

Fixed and issue where tokenizing a custom element WYSIWYG in a component didn't render the WYSIWYG content.

### Lock and unlock the Font stacks

Adds the ability to lock and unlock the Label and Variable fields when adding a Font stack.

### Bugfix: Authentication via settings.php and drush

Fixed an issue where defining the API authentication credentials in settings.php was not working with Cohesion drush commands.

### Toggle parent menu visibility

Added an interaction option to the menu button element to allow site builders to add a `Menu button`, which will toggle the visibility of the parent menu.

### Font picker field added for use on Style guides

Added a new field type that allows for the selection of fonts. The list dynamically updates and will pull newly added/removed fonts.

**Note: this field is only available for use with style guide entities.**

### Bugfix: Fixed API warning

A Drupal warning was being thrown when saving custom styles that had ben upgraded from an earlier version of Cohesion and had an unset image background. This is now fixed.

### Bugfix: empty styles edge case

Fixed an edge case where a malformed response from the API could fail to update the website stylesheet correctly.

### Support for tokens in View item element settings

When building a view template, it is now possible to toggle variable mode in the View item settings form and apply tokens to the view mode settings.

### Component and helper category permission changes

Users with permissions to create, edit and delete component and helper entities will:

- Automatically have permissions to select from any category on the component and helper entity form
- Be able to select from any component or helper in the Cohesion sidebar.

### Element forms UI changes.

The "Toggle variable mode" and "Open token browser" buttons in the element ellipsis menu have been moved to the toolbar containing the title and properties menu.

### Bugfix: Using images and gradients together on an element inline style.

When using a background image and a gradient together on an inline style for an element, the gradient was not being rendered in the CSS. This is now fixed.

### Bugfix: Conflicts with remote stream wrappers

Resolved an issue where Cohesion would conflict with stream wrappers that did not invoke `getDirectoryPath` (remote
stream wrappers for example).

### Support for embed media plugins in Cohesion WYSIWYG

Added support for CKEditor plugins like Drupal 8.8.x "Insert from Media Library" and "Node" that use the "Embed media" or "Display embedded entities" setting in the text format definition.

These plugins can now be used in Cohesion WYSIWYG elements and Cohesion WYSIWYG component form elements.

### Color palette - tagging

Adds the ability to tag colors in `DX8 > Website settings > Color palette`.

Website builders can then group certain colors and then restrict a color picker component field to specific tags.

### Bugfix: Package upload button disabled state

Fixed a bug where the upload button on the sync package upload form `DX8 > Sync packages > Import packages` was always enabled.

This meant it was possible to upload the validation of a package before it was complete by clicking the button prematurely.

This button is now disabled until the validation is complete.

### Bugfix: Saving an element as a helper and then placing the helper on the same layout canvas as the original element.

Fixed an issue where after creating a helper from an element, it would have the same UUID as the original element.

This would clash if you placed that helper back onto the same layout canvas it was saved from, resulting in form data being overwritten with blank values.

---

## 5.6.2

### Bugfix: Elements inside dropzones being lost when importing templates and components.

Fixed an issue where a element inside a dropzone was being removed from a template or component layout canvas when importing an entity that was new to the local site.

### Bugfix: Helpers not showing in the sidebar browser

Fixed a bug where helpers containing components with drop zones were not showing in the list of helper in the sidebar browser.

### Bugfix: Video controls assets not showing

Fixed a bug where the video controls assets were not loading correctly from the right path.

### Setting Cohesion API and organization keys in settings.php

Fixed a bug where Cohesion configuration settings could not be set in environment settings files. See `README.md` for more details.

### Bugfix: XSS validation applying to component fields

Fixed an issue where the XSS validation was sometimes being applied to component form field data.

This is now fixed so XSS validation only applied to elements settings.

### Bugfix: Canvas preview

Fixed an issue that prevented the canvas preview working when opened in a new window.

### Enabling RESTful on update

The `RESTful Web Services` module is now enabled as part of an update script.

This only affects websites being upgraded from versions prior to `5.6.0` and the rest module does not need to be enabled manually before upgrading.

---

## 5.6.1

### Bugfix: composer issue

Removed `Entity reference revisions` patch from Cohesion `composer.json` as version `1.7` of `Entity reference revisions` now includes the patch.

### Custom element fields can now be required

When developing custom elements for Cohesion, developers can now make text inputs, text areas, selects and file browsers required and set a custom validation message.

---

## 5.6.0

### SCSS variables behave in a more predictable way

- `$coh-color-` variables from the color palette can be used within SCSS variables.
- SCSS variables can be used within the value field of other SCSS variables. Example: `calc ($var1 + 10px);`.
- The API now catches syntax errors in SCSS variable values and prints warnings within the generated CSS, making debugging easier.

### Webform usage plugin

If a Cohesion Custom style is used on a Webform entity, the style will now show as in-use by the Webform.

### Block element now available within menu templates

The `Block` element can now be placed within a Cohesion `Menu template`.

### Set the default cohesion sidebar list view

There is a new settings in the global cohesion settings page: `/admin/cohesion/configuration/system-settings` called `Default sidebar view style`.

This allows the site administrator to set the default list view style of cohesion elements, components and helpers in the sidebar. Thumbnails are show by default.

As before, this setting can be changed by the user by clicking the toggle icon at the top of the sidebar and those changes persist across the browser session.

(Existing sites being upgraded will see the original list by default unless the user has already changed this for their session).

### XSS validation in element forms

Cohesion now validates all element settings inputs for script tags and other potentially dangerous markup using the Drupal core `Xss::filterAdmin` utility.

Examples of potentially dangerous markup that are filtered:

- `<script>` tags within markup prefix and suffix fields.
- `<object>` tags within markup prefix and suffix fields.
- `onClick` and other Javascript event attributes.
- Custom `href` attributes that contain values prefixed with: `javascript:`

There is a new permission that can be applied to certain roles to allow users to bypass this check: `Bypass XSS validation in element forms`.

This permission has the `restrict access` flag so will appear in the permissions table with the label `Warning: Give to trusted roles only; this permission has security implications`.

Notes:

- If you're upgrading an existing site, it's important that you review your role permissions after the upgrade and only give this permission to users that absolutely need it (the `user=1` administrator will have this permission by default).
- Bypassing this check to add javascript libraries or snippets in elements is not recommended. Javascript libraries should be added to source control within your theme or a custom module and attached programmatically via the Drupal core library system: `https://www.drupal.org/docs/8/api/javascript-api/add-javascript-to-your-theme-or-module`.

### ‘Elements’ will be disabled by default and not show in the sidebar browser when using the ‘Layout canvas’ field on a content entity.

Using primitive elements to create content means there is no separation between content and design. This approach is discouraged in favor of using `Components` which have a clear separation between content and design.

To discourage page creators from using primitive elements for content, they will now be disabled by default and not show in the sidebar browser.

When using the `Layout canvas` on content entities, components will be shown first for all users.

Site builders can choose to enable primitive elements on the layout canvas field within the field settings (although this is not recommended).

Cohesion `Helpers` will still be available in the sidebar browser unless they include primitive elements. In which case, they will be hidden.

Existing instances of the layout canvas field on sites upgraded to this version will remain unaffected and can continue to use primitive elements on the layout canvas (although this is not recommended).

### Bugfix: Using global tokens in templates

Fixed an issue where global tokens like `[current-page:title]` was breaking the generated twig.

### Bugfix - Tokenizing values in styles on elements when building a component

Fixes a bug when tokenizing values on element styles when switching between levels in the style tree.

### Bugfix - Using images containing ampersands in the filename resulted in a server error

Fixed a bug where selecting an uploaded image where the filename contained an ampersand resulted in a server error.

### New dependency on the core RESTful Web Services module.

Cohesion is now using the `RESTful Web Services` core module for its endpoints. Before upgrading you MUST enable this module.
‌

### Modal trigger elements - `Trigger ID` field

Focus will return to the body when a modal is closed. If you would like focus to return to your trigger element, give it a unique ID.

To make this easier, we've added a `Trigger ID` field to elements that support the `Open modal` interaction type. These are currently:

- Link
- Button
- Container
- Column
- Slide

You can still add an ID through `Markup > Properties > Classes and ID` as with other elements, but this value will override the `Trigger ID` value.

### New component form field - Range slider

The range slider field can be used as an alternate input method for number-based fields in your components.

You can specify `min`, `max` and `step` values, as well as a default value within the specified range.

### Enable Cohesion on a theme

When creating a new theme that is Cohesion enabled, you will need to add `cohesion: true` to the .info.yml of your theme.

**Note: if you extend from the base `cohesion_theme` you do not need to add this flag because the system will detect the flag has been set in the parent theme.**

If you have an existing theme extended from `cohesion_theme`, you do not need to add this flag. Your existing theme will just work without modification.

Because of this change, the selector for the global theme on the System settings configuration page `/admin/cohesion/configuration/system-settings` has been removed.

### "Existing selects" used in components are now dynamic, not hard coded on save.

Selects chosen from the `Existing select` picker on a component will now dynamically load options when used in components.

Previously the options were fetched when the component was created and not updated afterwards.

### Removed experimental layout builder module.

The removes the ability for site builders to see the content templates injected around the layout builder canvas at `node/x/layout`

Other layout builder support is unaffected, including:

- Custom block templates can still be themed with Cohesion (`/admin/cohesion/templates/content_templates/block_content`)
- Tokens can still be used in custom blocks.
- New "Drupal -> Content" element still available in the Cohesion sidebar browser.

**Note: if `cohesion_layout_builder` is enabled on your site, you should uninstall that module before upgrading to this version.**

## 5.5.6

### Using Drupal tokens in slide container, slides to show and scroll does not process the Drupal token

Fixed an issue where using Drupal tokens within Slides to show and slides to scroll did not process the Drupal token.

## 5.5.5

### Editing other fields in sidebar after setting a link to page using typeahead could incorrectly set the link value

Fixed an issue where on load a Typeahead _model_ value could be incorrectly set to the _view_ (label) value.

### Duplicated elements don't display variable mode correctly

Fixed a bug where after being duplicated the fields in an element that have variables in are not correctly displayed as yellow and show the token/variable instead of the preview text.

## 5.5.4

### Moderated translation not returning the correct layout canvas data

Fixed a bug where editing the translation of a node in draft would return the canvas of the published version instead of the draft content.

### Content images on Pseudo element rendering public:// instead of path

Fixed a bug where an image added to a content pseudo style would not convert the public:// path to it's internal path.

### Module compatibility with web profiler

Fixed a bug when the web profiler module was installed on your Cohesion website, which was causing error messages.

## 5.5.3

### External urls on background images looking at current domain

Fixed bug where external urls to background images would see the domain stripped out.

### Menu button elements created before 5.5 can error in certain circumstances

Fixed an issue where menu button element could throw an error when clicked after being upgraded from an older version of Cohesion.

## 5.5.2

### Component and helpers category not selectable after creation on restricted permissions

If a user as admin permission on component or helper category you don't have to enable the permission on each individual ones for the user to be able to select one.

### Font libraries not uploading to the correct folder

Fix a bug where font libraries were not moved from the temporary directory to the cohesion directory therefore not loading when included in the head.

### Cohesion sync packages not accessible unless site admin

Fixed an issue where Cohesion sync packages were only accessible to users with the role of Administrator.

Roles that have the `Access Cohesion Sync` permission will be able to manage sync packages.

### Drupal config import failing on Cohesion entities

Fixes a bug where Cohesion was creating content template config entities on config import of view modes and entity type, which caused the imported content templates to fail.

## 5.5.1

### Background image enabled but not set a top breakpoint

Fix a warning that was thrown ( in `Drupal\cohesion\LayoutCanvas\ElementModel:122`) if you had an element with background image enabled at the top breakpoint bu had not selected any image.

### WYSIWYG element - first line of pasted text cut off

In rare cases, pasting a large portion of text into a WYSIWYG element resulted in the first line of the content being cut off and unscrollable. This has now been fixed.

### Link to page - typeahead search improvements

Fixed an issue where the `Link to page` component field's typeahead search functionality was behaving incorrectly when typing. Characters were being removed when new results were returned making it difficult to use.

## 5.5.0

### New element - Modal

Within the `Interactive elements` section of the sidebar browser, you can now add a `Modal` element to your layout canvas.

This will allow you to display content in an accessible popup dialog and can be triggered by the following elements:

- `Link`
- `Button`
- `Container`\*
- `Column`\*
- `Slide`\*

\*_`Link and interaction` settings need to be enabled through `Properties > Settings`._

To link a trigger to a modal, you need to select `Modal` from the interaction `Type` dropdown and specify the `Modal ID` of the modal to open.

You can also trigger a modal through a `WYSIWYG` link. For optimal accessibility, this link should point to an alternate location where the modal content can be viewed (should JavaScript be disabled).

To connect this with your modal:

1. Create your link in the WYSIWYG as per your preferred method.
2. Toggle into `Source` mode.
3. Add `data-modal-open="modal-id"` as an attribute to your link, where `modal-id` is the id you've given to your modal.
4. Toggle out of `Source` mode and save your changes.

**You may need to add `data-modal-open` as an allowed HTML attribute to your text format settings **for this to work.\*\*\*\*

The modal element has several settings that you can configure:

- Dialog ID, animation, position, custom style and auto open/close
- Close button visibility, text, custom style and position
- Overlay visibility, click to close and custom style
- Generic layout style to be applied on outer container

For optimal accessibility, when the overlay is visible focus is trapped in the modal using the [inert polyfill from Google](https://github.com/GoogleChrome/inert-polyfill).

### Fix revisions not being created on non moderated entities

There is now a patch on the `Entity reference revisions` module on Drupal.org: `https://www.drupal.org/project/entity_reference_revisions/issues/3025709`.

This fixes revisions not being created on non moderated entities and we have added it to our `composer.json` file.

You will need to enable patching according to `https://github.com/cweagans/composer-patches#allowing-patches-to-be-applied-from-dependencies`.

### New element - Read more

A new interactive element called `Read more` is now available to use on the Layout canvas. This allows site builders to show/hide content on click of a button.

The initial expanded/collapsed state of the content can be set per breakpoint, as well as the corresponding button text in each state.

### Use component fields on column width - push - pull and offset

You can now attach a component field to the column width - push - pull and offset fields

### New element - Menu button

In menu templates, you can now add a new element called `Menu button`. This allows you to toggle submenu visibility when you want the sibling `Menu link` to click through to a page.

It has the same click animation settings as the `Menu link` element.

### Lock entities to prevent them being updated by Sync.

It's now possible to decouple / lock a Cohesion entity on a site. This means that this entity will be ignored by Sync when running an import.

For example, if you have a `package.yml` that contains a component and you import that component to your site. Now you lock the component and make some changes.

If you attempt to re-import the same `package.yml` file, Sync will ignore the locked component and report that there are no changes to apply.

An example use case: This feature could be used to make local changes to an entity that is contained inside an external design system that automatically applied to the site on module update or via a CRON process.

To lock/unlock an entity, visit the entity list builder page and under the action menu on the right hand side there will be the option `Lock` or `Unlock`.

### Support for Chosen module

Previously it was not possible to use the `Chosen` module: `https://www.drupal.org/project/chosen` with the layout canvas on the same content entity form. This has now been fixed.

### Entity browser element

You can now add a new element called Entity browser. This element allows you to browse entities using an entity browser and display an entity in a specific view mode.

You also have a component field element that you can attach to this element to give this capability to site editors.

A new dependency on the `Entity browser` module `https://www.drupal.org/project/entity_browser`.

You will need to install and enable this module if upgrading from a previous version.

### Slider container - slide count

You can now add a slide count to your slider container. This is in the format of `current slide / total slides` and is best used when `Slides to show` is blank or set to `1`.

It has the same positioning options as slider pagination, has a helper class of `coh-slide-count` and you can apply a `layout` or `generic` custom style to it.

To enable these options on your slider container form, navigate to `Properties > Navigation > Slide count visibility` and `Properties > Navigation > Slide count style`.

### Link element - layout canvas child support

You can now add child elements to the `Link` element - by default, it will appear on the layout canvas as collapsed.

**_To update existing link elements, you will need to do a rebuild._**

### Link autocomplete field no longer limited to nodes

Previously, the "Link to page" field on the link element and the link component form field only allowed users to search for links to nodes.

This has been extended to allow users to link to views pages.

### Analytics data layer

The analytics tab on elements now has options for adding data layer key and value pairs.

This data can be pushed to Google tag manager when triggered by a selected event.

### New options for deploying Sync packages

- There is a new option to specify a path to a local or remote file when deploying a package using `drush sync:import`.

- Module developers can include a list of `*.package.yml` files that will automatically be installed when their module is enabled.

For information about these new features, see: `modules/cohesion_sync/README.md`.

### Helpers can now be restricted by content entity bundle

Previously, it was ony possible to restrict certain components for use on certain content entity bundles. Helpers were available everywhere.

The same settings that exist for this on the component form have been copied to the helper form so site builders can now restrict helpers for use by content entity bundle.

### Image lazy loading

`Image` and `Picture` elements now have a `Lazy load` option which can be set to make them load only when a user scrolls them into view, deferring load until they are needed.

### Commercial Font License Information

When uploading fonts it is now possible to enter license information which will be displayed as a comment in your generated CSS file.

### Sync packages and updates to existing sync functionality

#### New package entity

The sync module menu items have moved from under `Config > Development > Sync` to `DX8 > Sync`.

To export packages via the UI, you need to create a new package entity at: `/admin/cohesion/sync/packages` and define your package contents.

On the package list page, there will be a new button "Export package as file" to export the package definition and all dependencies that you defined.

There is a new permission for administering the new "package" entities. This is under: "DX8 Sync packages -> Grant access to edit and manage DX8 sync packages."

#### Change to full export behavior

When defining which entity types to exclude from a full export in full export settings (`/admin/cohesion/sync/export_settings`), DX8 Sync will now exclude all entities of those types regardless of their dependencies.

#### Importing custom styles

Fixed a small issue where DX8 Sync always detected custom style entities as changed even if they were identical.

#### Uploading large files via the UI

When uploading package files via the UI at `/admin/cohesion/sync/import`, it's now possible to upload very large files that exceed the PHP `upload_max_filesize` limit.

### Inline element

A new content element called "Inline element" is now available to use on the Layout canvas.

This allows site builders to add an HTML inline element such as subscript from a pre-defined list or use a custom one.

### New system setting to restrict DX8 to a specific theme

In `DX8 > Configuration > System settings configuration` there is a new setting called `DX8 enabled theme`. This restricts DX8 templates and styles from only applying to a specific theme (for use with modules like AMP).

For existing sites, this setting will be set to `All themes` and work as normal.

### Bugfix: Width of content in style preview sometimes exceeded width of preview on slower connections.

Fixed a race condition that meant the width of the content inside the style preview was not set to be constrained by the width of the WYSIWYG.

This meant right-aligned elements could be displayed off the right edge of the preview and therefore appear invisible.

### Bugfix: context pass condition field not tokenizable

Fixed an issue where it wasn't possible to tokenize or map a component field to the context pass condition field. There was no tag or warning on this field to indicate this limitation.

The field can now be tokenized or mapped to a component form text or select field.

### Drupal image style on background images

You can now apply drupal image style to background images in the style builder on styles and elements.

### Layout canvas validation improvements

Certain container elements no longer blindly accept child elements. The list of allowed child/parent pairings are as follows.

```
row-for-columns -> column
google-map -> google-map-marker
accordion-tabs-container -> accordion-tabs-item
slider-container -> slide
menu-list-container -> menu-list-item
list-container -> list-item
form-tab-container -> form-tab-item
```

In addition a number of elements have been designated "universal" which means they can be placed inside any container, regardless of the above.

This includes custom elements and all drupal elements such as drupal blocks, views etc.

### Website settings - Color palette

Adds the ability to link and unlink a color label from the variable name when the color is not in use.

### Component and helper categories

Instead of the previously fixed list, any number of new component and helper categories can be defined by the site builder.

Site builders can access the categories listing pages and create new categories via the menu at `DX8 > Components > Categories` and `DX8 > Helpers > Categories`.

**Note: existing sites will have the previously hardcoded entities converted to editable categories and all references inside components and helpers will link to these new categories.**

- A core permission will be added for each category to: `People > Permissions`. If you have configured these permissions on an existing site, they will need to be configured again for these new permissions.

- New installs of DX8 will come with a set of pre-defined categories that can be edited or removed by the site builder.

- Categories can be re-ordered on their list builder pages and this order will reflect in the sidebar browser when selecting components.

- The category and helper entities within individual components can also be re-ordered on their list page.

### `Video` and `Video URL` component field elements

When you specify a Vimeo or YouTube URL for these elements, the control settings for them will be ignored in favour of their native controls.

This is because they still depend on embedded iframes and can result in double controls being displayed.

#### Exceptions

- **Business/Pro Vimeo account** - native controls can be disabled and HTML5-compliant custom embed URLs can be used so that element control settings can be used.
- **YouTube URL with controls disabled** - this setting will be respected, if present in the URL (`controls=0`).

**For best results a CDN-hosted, HTML5-compliant format (MP4, OGG, WebM, MKV) should be used with these elements.**

### Base styles - Add base styles and edit selector

You can now add new base styles and specify their css selectors. You can also edit the selector of base styles that came from the pre-defined list.

The reset capability has been removed so you can now delete base styles.

### Allow pseudo fields to be used in templates

You can now add pseudo fields in content templates using the field element.

### Picture element - multiple images per breakpoint

You can now add multiple pictures per breakpoint, which is useful for adding multiple image formats.

**Note: the browser will use the first type that it matches, so make sure you specify your image formats in order of preference.**

### Element browser - Drupal block element - styles

You can now add styles to a Drupal `Block` element, either by selecting a `Layout style` in the element settings, or adding properties through the `Styles` tab.

### Style builder - background images/gradients

The background image styles section has been updated to provide more flexibility. Previously, if the background image dropdown option was selected but no image was specified, it would result in `background-image: none` being set in the styles.

#### Improvements:

- New background image dropdown option: `None`
- Background properties are now available on gradients and can be set without an image/gradient being specified
- Background images now inherit across breakpoints

#### Backwards compatibility

Following upgrade and a site rebuild, any breakpoint that had background image selected but no image specified will be converted to the new `None` option.

### Machine name field on DX8 config entity forms.

Previously, the ids for DX8 config entities were generated automatically when saving their forms. There is now a machine name form field on DX8 entity form pages which sets this id.

Note that this will affect the filename of generated `.twig` templates making them easier to manage. All existing entities and generated `.twig` files will be unaffected.

### Canvas preview

You can now preview your layout canvas as you are creating your layout. Clicking `Show preview` will show a live preview as you modify the elements.

- Layout canvas preview is available for Components, Helpers, Content Templates and Master Templates.
- Drupal field, Dropzone placeholder, Drupal field item, Drupal content, and Breadcrumb all show a placeholder (blue/striped box) that is resizable.
- Placeholder size is stored and retains it’s size after page load.
- Other Drupal elements (e.g. blocks) will actually render in the preview.
- You can hover over elements in the preview when the window is popped out and it will highlight those elements in the layout canvas.
- You can hover over elements in the layout canvas when the preview window is popped out and it will highlight those elements in the preview.

#### Features

- Real time preview updates as you edit your component
- Multi screen editing (pop out the preview multiple times to see the preview reload in real time at different browser resolutions)
- Breakpoint selection
- Grid System guide preview display
- Scale to fit for viewing large breakpoints on small screens
- Custom column width preview
- Custom background color preview

### Sidebar browser - components, component content and helpers now have tooltips

Previously when creating components, component content and helpers with long names, it wasn't possible to see the full name in the sidebar browser.

You can now see this when hovering the item in the sidebar browser, in either list or thumbnail view. If you have uploaded a preview image for the component or helper, this will also be visible on hover.

Keyboard focus for the sidebar browser has also been improved, both functionally and visually.

### WYSIWYG form element now fully supports text formats

The WYSIWYG element and component form field now allows to use all text formats, following Drupal standards.

If you have implemented a custom element using a WYSIWYG field you need to change it's default value to an array as follow

```
php
'mywysiwygfield' => [
    'htmlClass' => 'col-xs-12',
    'type' => 'wysiwyg',
    'title' => 'Title of my WYSIWYG field.',
    'defaultValue' => [
        'text' => '<p>This is some default content.</p>',
        'textFormat' => 'cohesion'
    ]
],
```

## 5.4.11

### Bugfix: Master template using default on views with path parameters

Fixed a bug where the selected master template in a view was not rendering and was falling back to the default one if you had a dynamic parameter in your view path on view pages.

Example: `/path_to_view/%id/`

## Animate on view items not appearing on mobile

Fixed a bug when animate on view is disabled for touch devices, the elements set to animate were not always shown.

## 5.4.10

### Bugfix: Tab items with long titles in component form builder losing padding

Fixed a small styling issue where any text in tab items on the component form builder was not correctly formatted if it wrapped to two or more lines.

### Bugfix: Menu is-active class is not added to Home menu links

Fixed a bug where having `<front>` as a url in menus would not add the `is-active` class on front pages.

### Bugfix: It is sometimes not possible to rename selectors in the style tree

Fixed an issue where some items in the style tree were unable to be edited after they were created. These items should now be editable as usual.

### Bugfix: using menu_link_content on menu link config

Fixed a bug where using `menu_link_content` on menu link config would throw a warning.

## 5.4.9

### Bugfix: Tokenizing context on a component

Previously when tokenizing the context field on a component, the front end would throw a Twig runtime error. This is now fixed.

## 5.4.8

### Bugfix: Removing colors in the color palette from the WYSIWYG still show in the 'Inline styles'

Fixed a bug where if a site builder unchecked `Available in WYSIWYG` on a color in the color palette, it would still show in the WYSIWYG `Inline styles`.

## 5.4.7

### Bugfix: Sync attempting to export component preview images that have been removed

Fixed a bug where if a site builder creates a component with a preview image and then uses that component somewhere, the preview image file is tracked as in use even if both the prewview image file entity and file are removed.

Note that element preview images will now be stored locally in public://element-preview-images/ instead of public://cohesion/element-preview-images/

### Bugfix: Hovered menu item behaves erratically if your mouse is over it when the page loads

Fixed a bug where the initial state of hovered menu item can get inverted when mouse leave is fired before mouse enter (e.g. on loading a page with the mouse over the menu item).

**An import via the UI or `drush dx8:import` is required for this change.**

## 5.4.6

### Added in Sync package validation before export

Previously when exporting, packages were streamed directly to files or the browser without testing for export errors.

This meant that any errors with an export would show up in the `package.yml` file as printed error messages, corrupting the Yaml.

The export now streams the export to a temporary file before validating and serving to the user.

Errors with package exports are shown to the user in UI or on screen when using drush.

### Bugfix: Tokenizing accordion tabs container `Start state`

Previously it was not possible to tokenize the `Start state` field in the element. This is now fixed and can be tokenized as expected.

### Bugfix: Website settings admin link text incorrect

When using certain contributed admin themes the admin links on the website settings page were incorrect.

## 5.4.5

### New permission for DX8 Sync

Previously DX8 sync was accessible if the user had access to core config import/export. A new permission has been created called `Access Cohesion sync` which will grant access to the DX8 sync admin interface.

### You can no longer import packages exported from later versions of DX8

DX8 sync only supports importing packages created from the same or older versions of DX8 (there is no way to downgrade a package on import). Importing packages created from newer versions of DX8 resulted in unpredictable behavior and corrupted data.

The importer now tests this and blocks imports created from later versions of DX8. It shows a meaningful error in the UI and drush instead of just crashing.

### Bugfix: Color/Icon picker could appear off the screen

When opening the blade menu with a color/icon picker in it, it was possible for it to load the picker off the top of the screen.

## 5.4.4

### Specify a filename when performing a sync export via drush.

There is a new command line option for setting the filename when performing a DX8 sync:export via Drush.

See: `/modules/cohesion_sync/README.md` for more details.

### Bugfix: default styles not initially set to correct breakpoint when grid is set to mobile first

Fixed a bug where default styles on custom, base and element styles were not initially loading as mobile first when responsive grid settings are set to mobile first.

### Bugfix: DX8 libraries and template not displaying on admin pages for user without permission to see admin theme

Fixed a bug where users without admin theme permission would not see the DX8 theming and templates.

### Bugfix: component value having a token and some text does not render correctly

Fixed an issue where adding a token and some text to a component field (eg. `[node:title]sometext`) was returning the text part as a UUID.

### Alter the list of fields on the Drupal field element by bundle

You can now alter the list of available fields on the Drupal field element by entity type and bundle.

## 5.4.3

### Bugfix: initial DX8 asset import cannot complete on Drupal 8.7-beta

Fixed an issue that made it impossible to set up a new site running DX8 on Drupal core `8.7-beta`.

### Bugfix: validation on responsive grid settings page was failing to update in some cases

It was possible to end up in a situation where the form was valid but would not let you save the page.

## 5.4.2

### Bugfix: default images on components causing Sync import issues

In certain cases, Sync import was failing when an `export.yml` file contained incorrect references to default images on components.

### Bugfix: imported colors missing from color picker

In certain cases, imported colors were missing from the color picker list but were available on the colors website settings page. This is now fixed.

### Bugfix: component context contextual_preprocess Drupal\Core\Template\Attribute warning

Fixed an issue on master template where a `Drupal\Core\Template\Attribute` was passed to `contextual_preprocess()` therefore throwing a notice about `Indirect modification of overloaded element`.

### Bugfix: invalid twig when field element has no field defined.

When adding a Drupal field element to a layout canvas and leaving the field set to `None`, DX8 rendered invalid twig. This is now fixed.

## 5.4.1

### Bugfix: component forms saved as helpers were uneditable

In `5.4.0` we introduce the ability to save component forms as helpers. When editing these helpers via the UI at `/admin/cohesion/helpers/{id}/edit` the layout canvas was unusable. This is now fixed.

There is also a new action button `Add form helper` in the helpers list builder UI: `/admin/cohesion/helpers`.

### Bugfix: 'Breakpoint widths should not intersect.' message shown incorrectly

Fixed an issue where it was impossible to edit minimum widths on the responsive grid settings page.

### Image style usage plugin

Drupal image styles are now exported as part of DX8 Sync packages when they are detected as in use on DX8 entities.

## 5.4.0

### Component content with content moderation

You can now enable content moderation on component content entities.

### Entity update tracking for DX8 Sync

With versions prior to `5.4.0`, exports from DX8 Sync could only be imported to sites with the same version of DX8 that they were exported from.

For example, if you exported a package using DX8 Sync from a site running DX8 version `5.2`, you could not import to a site running DX8 `5.3`.

There is now an automatic entity update tracking system in place, so you will be able to export from sites running older versions of DX8 (starting with `5.4.0`) into sites running later versions.

**Note: it's not possible to export from later versions into previous versions (there will be no way to downgrade a package export). Currently there is no testing or warning when attempting this.**

### Configurable component element dropzone width

You can now control a component element dropzone width when the component is dropped onto the Layout canvas. To access these settings edit the `Dropzone` element in your component and turn on the `Dropzone width` from the `Properties` menu.

### Breakpoint indicator module

You can now enable a new sub module `DX8 breakpoint indicator` (`cohesion_breakpoint_indicator`) which provides an indicator in the bottom left of your browser window to show which breakpoint you are viewing your page at.

### SCSS Variables

It's now possible to define a list of SCSS variables in the UI (under `DX8 > Website Settings > SCSS variables`) that can be used within the style builder.

The CSS `calc()` function can also be used as a SCSS variable value, eg. `calc($var / 2)`. It's not yet possible to use SCSS variables in `Responsive grid settings`.

### Component forms - layout tabs

You can now layout component fields within tabbed sections using `Tab container` and `Tab item` fields.

Like `Accordion tabs`, tab items are nested within the tab container and can be renamed by editing them and changing the label.

The component fields that you would like in your tab should be nested within the respective tab item.

### Button and link elements - jQuery animations

You can now add jQuery UI animation targets to `button`, `link`, `column`,`container` and `slide` elements. To access these settings, make sure the `Interaction` section is enabled and `jQuery animation` selected as the `Type`.

You can also apply animations to multiple targets at the same time.

For the `Scale` animation, `Direction` will only have an effect if a `Scale (%)` value greater than `0` is specified.

**Do not apply multiple animation to the same target as this will create undesirable effects.**

**It is recommended that the desired animations are applied prior to creating CSS styles, as these styles could have a negative impact on the jQuery animation.**

Animation effects: <http://api.jqueryui.com/category/effects>

Easing functions: <http://api.jqueryui.com/easings>

### Video component field - video preview

Allows users to preview videos when specified in component forms.

### Video and Video background elements - video preview

Allows uses to preview videos when specified in `Video` and `Video background` elements.

### Style builder - CSS3 filters

You can now add CSS3 filters to your element styles. Multiple filters can be applied at the same time.

**Note: The `filter` property is not supported in `IE11` but is supported in `Edge` and all other modern browsers.**

### Tidy Form button replaces Expand/Collapse all button

A new button added to the style builder and `Styles` tab on elements allows for easy tidying of your form.

A site builder can now click this button and remove all empty fields, breakpoint rows and sections from their styles form.

The expand/collapse all accordions button has been removed. The new tidy button replaces it on the element `Styles` tab.

### Tokenizing element title field

When you now enter a token in the `Title` field of an element, the title field will not be disabled.

### Sidebar editor - modal/backdrop z-index override

In the latest version of Drupal (`8.7.x-dev`) the `z-index` of native jQuery UI modals/backdrops has been reduced significantly, from ~`10000` to ~`600`.

To ensure no obstruction, we've hardcoded a `z-index` of ~`500` on our sidebar editor modal/backdrop.

### Bugfix: Style helpers entities not calculating dependencies on export

Previously, when exporting style helper entities via the DX8 Sync module, dependencies were not included. This has now been fixed so colors, styles, etc. are exported with style helper entities.

### Video element - Play on hover

You can now set videos to play on hover and pause when hover focus is changed.

A helper class of `coh-video-hover` is applied to the `coh-video` element when hover is active, should you want to apply hover styles to the video.

### Update to icon libraries

`Website settings > Icon libraries` page has been simplified to allow for easier uploading of icon libraries.

### Update to Font libraries

`Website settings > Font libraries` page has been simplified to allow for easier uploading of font libraries.

### Menu link element - jQuery animations

You can now add several jQuery UI animation effects to your menus. This can be changed across different breakpoints. Settings are available on the `Menu link` element.

**It is recommended that the desired animations are applied prior to creating CSS styles, as these styles could have a negative impact on the jQuery animation.**

Animation effects: <http://api.jqueryui.com/category/effects>

Easing functions: <http://api.jqueryui.com/easings>

### Support for media entity browsers

Under `DX8 > Configuration > System Settings` (`/admin/cohesion/configuration/system-settings`), the administrator can specify the type of image browser to use when selecting images for use within DX8.

You can select a different browser for editing config pages (DX8 entities, component, styles etc) and one for content pages (layout canvas fields on a node for example).

This is so you could give site builders access to use the more flexible IMCE browser and content editors access to use the entity browsers for a more simple user experience.

The available options depend on the modules you have installed. DX8 requires the [IMCE file manager](https://www.drupal.org/project/imce) module so that will always be an option.

You can also install the [Entity browser](https://www.drupal.org/project/entity_browser) module and have an image media browser available.

The entity browsers that come with the [Drupal Lighting distribution](https://www.drupal.org/project/lightning) are compatible.

**Note: because IMCE is already installed on your existing site, an update will run as part of `drush updb` to automatically set IMCE as your file browser.**

### Menu content tokens can now be used in menu templates.

Menu link entity tokens are now available in the token browser when editing a DX8 menu template. Note that tokens in menu templates only work as part of element settings and will not render out in menu template inline styles.

To add additional fields to menu links and use their tokens, we recommend using the [Menu Item Extras](https://www.drupal.org/project/menu_item_extras) module. This will expose field tokens to the DX8 token browser automatically.

This module has been added to the recommended section of the `composer.json` file as:

```
drupal/menu_item_extras
```

### Menu link item element

The `Menu link item` element has been converted to a container. If this element is empty it will default to printing the link title, otherwise it will render the child elements you place inside it.

**Note: existing elements on your site will not automatically be converted to containers and will work as normal.**

### Improved validation on responsive grid settings page

Added stricter validation to ensure that the minimum width value on each breakpoint doesn't intersect with any of the others.

### New permission for custom elements group

A new permission has been added: `DX8 Elements - Custom elements group`

This grants access to the DX8 custom elements group within the sidebar browser to the roles you specify.

### dx8-sync drush import/export directory is configurable

It's now possible to specify an import/export directory in `settings.php` when running dx8 sync from the command line.

For example:

```php
  $config_directories = [
    'dx8_sync' => 'sites/default/files/',
  ];
```

See: `modules/cohesion_sync/README.md` for more information.

### Bugfix: Icons as component field not showing

Fixed a bug where icons were not showing if used as component field in style tab.
Requires a cache clear.

### Accessibility updates

#### Keyboard navigation

Menu keyboard navigation has been improved significantly:

- `Space` = toggles child menu visibility. If no child menu, will follow link
- `Return/enter` = Always follows menu link
- `Down/right arrow keys` = opens child menu and/or moves onto next menu item
- `Up/left arrow keys` = closes child menu and/or moves onto previous menu item
- `Escape` = closes child menu and restores focus to parent menu item.

Also menu links that have sub-menus have `aria-haspopup` and `aria-expanded` attributes. The latter will toggle between a value of `true/false` depending on the display state of the child menu.

#### Button element

There is now a `button` element available within the `Interactive` section of the sidebar browser.

This shares the `Back to top` and `Scroll to` functionality of the `link` element and provides two additional `modifier` options:

- `Toggle modifier (as accessible popup - collapsed)` = adds attributes/values `aria-haspop="true"` and `aria-expanded="false"`
- `Toggle modifier (as accessible popup - expanded)` = adds attributes/values `aria-haspop="true"` and `aria-expanded="true"`

**These should be used instead of links for toggling visibility of mobile and other hidden navigation, e.g. search and language selector blocks.**

## 5.3

### Bugfix: Video controls assets not showing

Fixed a bug where the path in the .css to `/assets/video/mejs-controls.svg` was incorrect in generated css resulting in the video controls not showing for the video element.

### Component default content update

When using components that have default values, the default value will be applied at the point that the component is added to the layout canvas.

This means that later updates to default values will not propagate to components saved on nodes or in helpers etc.

Adding new fields or removing fields from a component will propagate to existing components and any new default data associated with those new fields will be added to the node when it's next saved.

### HTML base style

It is now possible to create a `HTML` base style.

This is particularly useful for maintaining `<body>` scroll position when opening a fixed position menu or modal, where the `<html>` and `<body>` elements need to be given a fixed height of `100%`.

See <https://labs.cohesiondx.com/projects/menu-page-scroll> for a demo of this technique in action.

### Layout builder support

You can now enable a new sub module `DX8 layout builder` (`cohesion_layout_builder`) which will give some support from using DX8 with the core Drupal layout builder module.

Note that the layout builder module is experimental, so this support module may break unexpectedly. Consider it experimental until the core layout builder comes out of experimental status.

Support:

- Content templates will render on the layout edit page (`node/x/layout`)
- Custom block templates can be themed with DX8 (`/admin/cohesion/templates/content_templates/block_content`)
- Tokens can be used in custom blocks.
- New "Drupal -> Content" element available in the DX8 sidebar browser (this element prints the `content` variable of the current context which effectively hands rendering the content entity over to Drupal core).

To use core layout builder with DX8, you will need to drop the new "Content" element onto your content template in place of the usual fields you would add if using the DX8 layout builder field to handle your content entity layouts.

## 5.2

### Fixed super user permissions

User #1 is now given all DX8 permissions regardless of their roles (matches how Drupal core behaves for this user).

### Link and interaction -> Modifier scope to parent element

You can now choose `Parent element` as the scope when applying using the modifier interaction type.

Once selected you can enter `Parent` (jQuery selector) which will traverse up the DOM (see `https://api.jquery.com/closest/`) to find a parent element and then looks for your given selector within that parent.

If the parent is not found (e.g. you select a non-existent class) then the modifier class will not be applied to anything.

### Row for columns - markup and style target

The `Row for columns` element consists of a two-level `<div>` container structure (`coh-row` and `coh-row-inner`).

In this update, the way additional markup and styles are added to the element has changed.

**A database update is required for existing elements to be updated.**

#### Previous behaviour

- Additional markup (classes, ids, attributes) are added to the outer container
- Custom style and style tab classes are added to the inner container

**This prevented modifiers from being added to the top-level (default) of the `Row for columns` style tree, and styles from being applied to the outer container.**

#### New behaviour

- Upon running a database update, existing `Row for columns` elements will have their custom style and style tab classes moved to the outer element
- Editing an existing `Row for columns` element or creating a new one will expose a new form section under `Settings` called `Markup and style target`, which will allow the site builder to select whether markup and styles are attached to the outer or inner container.

#### Risk mitigation

This change should pose **minimal risk** providing a database update is done. This is because markup will still be on the outer container and styles on `Row for columns` have only very recently been enabled.

**For new `Row for columns` elements, markup and styles will be added to the inner container by default.**

## 5.1

### DX8 sync

Fixed a bug where imported colors were not showing on the color palette page and would therefore be accidentally deleted.

Fixed a bug where it was possible to import two custom styles with the same class name.

### Scoping added to modifiers in Link and Interaction

When adding toggle/add/remove class modifier to a link you now have a scope option. You can choose page, component or this element. The options will scope the “target” jQuery selector you enter.

1. `Page` - Behaves as before. The Target selector will search for matches on the whole page
2. `Component` - Will traverse up parent elements from the current element until it finds the top of the component it is in. Then it searches the elements within that component for any that match the Target.
3. `This element`- Will search the current element itself and any children for the target selector. If you choose `This element` and don’t specify a target the modifier will be applied to the current element itself.

N.b. If you set the Scope as `Component` but the element is _not in a component_ it will behave as if you had selected `Page`.

### composer.json

Added some additional Drupal module suggestions and updated some dependencies to the latest version.

### Element Browser Thumbnail View

The Element Browser can now be toggled into Thumbnail View.

Thumbnail images can be uploaded for components that you make and show in the Component tab in the Element browser.

A magnified "Loupe" now appears when hovering over Components that have thumbnails set in both List view and Thumbnail view.

### Component forms - column support

You can now arrange component form fields in columns. These columns will also apply to the `Component form builder` canvas.

To use this new feature, your form fields must be inside a `Field group`. Editing this will provide options for `Heading visibility` and `Column count`.

Toggling `Heading visibility` off will hide the field group's title bar and remove left/right padding - this is useful for making fields appear to be within the same form group but arranged with different column widths.

`Column count` will determine how many columns a field group's fields will divide over. By default this is set as `undefined`, meaning the number of columns will match the number of fields. There are additional options of `1`, `2`, `3` and `4`.

You can also nest field groups, though only a single level deep is recommended. This should be sufficient for most component forms.

**All form fields will stack below a display width of 768px.**

### Export packages via DX8 sync from the entity list builder pages

If DX8 Sync is enabled and the current user has the "export configuration" permission, they can now export packages for single entities from the operations dropdowns on all DX8 entity list builder pages.

### Slider container

The slider container now support most of its fields to be tokenize. Posistion outside of navigation and pagination cannot be tokenize as they are dependencies of other fields.
The same applies for autoplay and the easing section

## 5.0

### Added DX8 sync module

A new new deployment module is included in this version. See: `modules/cohesion_sync/README.md` for more details.

If you are using the `module/dx8_deployment` included in earlier releases, you will need to uninstall it before updating
to this release as the module is no longer included in the repository.

### Responsive grid conversion - float to flex

The responsive grid now uses CSS flexbox properties for layout, rather than CSS float. Flex provides advanced layout capabilities and removes the need for an extra `<div>` for vertical alignment.

#### Key changes:

- `Row for columns` and `Column` elements now use CSS flex properties.
- Clearfixes (`:before` and `:after` pseudo elements) have been removed from these elements as they interfere with several flex properties.
- Columns have been removed as target elements from `Row for columns > Match heights of children` settings. Flex does this automatically and using JS match heights with them causes unexpected behaviour.
- Styles can now be applied directly to the `Row for columns` element, either through the `Styles` tab or selecting a custom `Generic` or `Layout` style.
- `Column > Width` and `Accordion tabs container > Width of tab` settings have additional options of `Undefined (expands to available width)` and `Auto (content width)`.
- `Basic/Advanced column`, `Basic/Advanced container` and `Basic/Advanced slide` elements have been removed in favour of generic `Column`, `Container` and `Slide` elements.
- These generic elements have a single `<div>` element and no vertical alignment options like their basic ancestors.
- Layout canvas columns use flex and will be 50% opacity when width set to `None (hidden)`.
- Components on the layout canvas now have a `Configure` option in their ellipsis menu so you can jump straight to the component's edit page.

**Current basic/advanced instances of elements, styles and match heights settings will be automatically converted to generic elements following a database update, but some manual updates will be needed as below.**

#### Breaking changes:

- Columns will bleed where background styles were previously applied to advanced columns due to removal of extra `<div>`. Adding a container around the children of these elements with background styles will simulate 'advanced' behaviour.
- Vertical alignment settings on advanced columns, advanced containers and advanced slides will need to be replaced with flex equivalents (either `justify-content` for default `flex-direction: row` or `align-items` for `flex-direction: column`)
- Styles that target classes `coh-column-inner`,`coh-container-inner` and `coh-slider-item-inner` in the style tree will have no effect as these elements no longer exist.
- `float` has no effect in flex containers so `float` css property will need to be replaced with flex `order`.

## 4.0

### Split model and mapper

Increase efficiency of the layout canvas by splitting its model and mapper out of the canvas

In the following order:

An update via the `update.php` UI or `drush updb -y` is required for this change.

An import via the UI or `drush dx8 import` is required for this change.

A rebuild via the UI or `drush dx8 rebuild` is required for this change.

### IMCE image browser

The image browser now appears in a modal instead of a popup window.

### New front end contextual links for components

The front end component edit contextual links are now:

- "Edit component" (edit the instance of the component in the settings tray)
- "Configure component" (take the user to the component configuration edit page)

### Slider container

Fixes issue when nesting a Slider within a Slider and getting duplicate Pagination and Navigation on the nested Sliders

### Component content

Adds the ability to define a component with its content globally to be used and edited from any
layout canvas

An update via the `update.php` UI or `drush updb -y` `drush entup -y` is required for this change.

An import via the UI or `drush dx8 import` is required for this change.

### In use system

Fixed various issues causing the in-use system to calculate dependencies incorrectly. Changing colors, font stacks or icon libraries rebuilds only the entities used by those website settings.

Files used within a layout canvas or a WYSIWYG (including Drupal default WYSIWYG within a content entity) are tracked in the core drupal `file.usage` service.

In the following order:

1. An entity update via: `drush entup -y` is required for this change.
2. An update via the `update.php` UI or `drush updb -y` is required for this change.
3. An import via the UI or `drush dx8 import` is required for this change.
4. A rebuild via the UI or `drush dx8 rebuild` is required for this change.

### Base styles prefixed with .coh-wysiwyg

Fixed a bug where base styles prefixed with .coh-wysiwyg will no longer show incorrectly in the style preview window.

### Style editor - Float section - Clearfix toggle

The `OFF` state of the clearfix toggle didn’t previously do anything, which meant that if it was toggled on by mistake, turning it off again had no effect.

This change will now remove any `:before` and `:after` from target element, unless otherwise defined (see below).

**Backwards compatibility risk**

_‘:before' and ’:after' pseudos are unexpectedly removed from elements, causing layout issues._

This change will only remove `:before` and `:after` pseudos from styles if all of the following conditions are met:

1. The clearfix toggle is active and in the off position.
2. Custom pseudos aren’t defined in the style tree (e.g. custom string or icon).
3. A style that currently has the toggle switch active and in `OFF` position would need to be re-saved for styles to regenerate.

**Key benefits**

1. Ability to toggle clearfix on and off by breakpoint.
2. Ability to override automatic container element clearfixes with the toggle without having to define the `:before` and `:after` elements in the style tree.

### Video element

This element supports multiple video formats including MP4, OGG, WebM, MKV, Facebook, Vimeo and YouTube. It also provides custom video controls that can be styled consistently across multiple browsers.

This is made possible by using the `mediaelements.js` library.

For best results, a CDN-hosted video should be used rather than Facebook, Vimeo or Youtube, which still use embedded iframes rather than the native video element.

Vimeo controls can only be disabled if the host account is business or pro level, so in this case it is recommended to disable the custom video controls.

If you want the video to autoplay, you must also set the video to be muted (this is due to browser restrictions).

An import via the UI or `drush dx8 import` is required for this feature.

A rebuild via the UI or `drush dx8 rebuild` is required for this feature.

### Picture element

The HTML5 `picture` element provides a native solution to deliver responsive images, typically the same image of different filesize/dimensions across different breakpoints.

It contains a `srcset` element for each breakpoint with the relevant image path, plus an `img` element with the active breakpoint's image.

When it comes to styling, the `picture` element is redundant and you should only target the `img`.

**In desktop first mode, you must assign an image to the XL breakpoint.**

**In mobile first mode, you must assign an image to the XS breakpoint.**

These conditions are necessary to ensure inheritance works correctly and a `srcset` is generated for every breakpoint.
